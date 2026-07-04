<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceGridExport implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    private array  $rows;
    private int    $daysInMonth;
    private string $monthLabel;

    // Status letter → ARGB fill color
    private const COLORS = [
        'H' => 'FF86EFAC', // green-300
        'T' => 'FFFCD34D', // amber-300
        'A' => 'FFFCA5A5', // red-300
        'I' => 'FF93C5FD', // blue-300
        'S' => 'FFC4B5FD', // purple-300
        'D' => 'FF67E8F9', // cyan-300
        '·' => 'FFE5E7EB', // weekend gray
    ];

    public function __construct(
        private readonly string $month,    // Y-m
        private readonly int    $classId,
    ) {
        [$year, $mon] = explode('-', $month);

        $start           = Carbon::parse("$year-$mon-01");
        $this->daysInMonth  = $start->daysInMonth;
        $this->monthLabel   = $start->isoFormat('MMMM Y');

        $students = User::where('role', 'siswa')
            ->where('class_id', $this->classId)
            ->with('schoolClass')
            ->orderBy('name')
            ->get();

        // Index records by user_id → day
        $records = Attendance::whereHas('student', fn($q) => $q->where('class_id', $this->classId))
            ->whereYear('date', $year)
            ->whereMonth('date', $mon)
            ->get()
            ->groupBy('user_id')
            ->map(fn($g) => $g->keyBy(fn($r) => (int) $r->date->format('j')));

        // ── Row 1: header labels ──
        $header = ['No', 'Kelas', 'Nama Siswa', 'NISN / NIS'];
        for ($d = 1; $d <= $this->daysInMonth; $d++) {
            $date = $start->copy()->setDay($d);
            $header[] = $d . ($date->isSunday() ? '*' : '');
        }
        $header[] = 'Hdr';
        $header[] = 'Alp';

        $this->rows = [$header];

        foreach ($students as $i => $student) {
            $dayRecords = $records->get($student->id, collect());
            $row = [
                $i + 1,
                $student->schoolClass?->name ?? '—',
                $student->name,
                $student->nis ?? '—',
            ];

            $hadirCount = 0;
            $alpaCount  = 0;

            for ($d = 1; $d <= $this->daysInMonth; $d++) {
                $date   = $start->copy()->setDay($d);
                $status = $dayRecords->get($d)?->status ?? null;

                if ($date->isSunday()) {
                    $row[] = '·';
                } else {
                    $letter = match($status) {
                        'hadir'      => 'H',
                        'terlambat'  => 'T',
                        'alpa'       => 'A',
                        'izin'       => 'I',
                        'sakit'      => 'S',
                        'dispensasi' => 'D',
                        default      => '',
                    };
                    $row[] = $letter;
                    if ($status === 'hadir' || $status === 'terlambat') $hadirCount++;
                    if ($status === 'alpa')  $alpaCount++;
                }
            }

            $row[] = $hadirCount;
            $row[] = $alpaCount;
            $this->rows[] = $row;
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Absensi ' . $this->monthLabel;
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 5, 'B' => 14, 'C' => 32, 'D' => 14];
        for ($d = 0; $d < $this->daysInMonth; $d++) {
            $col = Coordinate::stringFromColumnIndex(5 + $d);
            $widths[$col] = 4;
        }
        // Totals
        $lastDateCol = Coordinate::stringFromColumnIndex(4 + $this->daysInMonth);
        $hdrCol = Coordinate::stringFromColumnIndex(5 + $this->daysInMonth);
        $alpCol = Coordinate::stringFromColumnIndex(6 + $this->daysInMonth);
        $widths[$hdrCol] = 6;
        $widths[$alpCol] = 6;
        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet  = $event->sheet->getDelegate();
                $rows   = count($this->rows);
                $cols   = 4 + $this->daysInMonth + 2; // info + days + totals

                $lastCol = Coordinate::stringFromColumnIndex($cols);

                // ── Header row style ──
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 9],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // ── Month title merged above header: insert a row before ──
                // Actually, just use the first row as header with month in the title.
                // Add month label as a merged title row.
                $sheet->insertNewRowBefore(1);
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Daftar Hadir Siswa — ' . $this->monthLabel);
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(18);

                // Recalculate: header now row 2, data starts row 3
                $dataStart = 3;
                $dataEnd   = $dataStart + $rows - 2; // -1 for header row, rows includes header

                // ── Style header row (now row 2) ──
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 8],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(16);

                // ── Style data rows ──
                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(14);

                    for ($c = 5; $c <= $cols; $c++) {
                        $colLetter = Coordinate::stringFromColumnIndex($c);
                        $val = $sheet->getCell("{$colLetter}{$r}")->getValue();

                        if (isset(self::COLORS[$val])) {
                            $sheet->getStyle("{$colLetter}{$r}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB(self::COLORS[$val]);
                            if ($val === '·') {
                                $sheet->getCell("{$colLetter}{$r}")->setValue('');
                            }
                        }

                        $sheet->getStyle("{$colLetter}{$r}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("{$colLetter}{$r}")->getFont()->setSize(8);
                    }

                    // Left-align name column
                    $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("A{$r}:D{$r}")->getFont()->setSize(8);

                    // Zebra stripe
                    if (($r - $dataStart) % 2 === 0) {
                        $sheet->getStyle("A{$r}:D{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF8FAFC');
                    }
                }

                // ── Borders on all cells ──
                $sheet->getStyle("A2:{$lastCol}{$dataEnd}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']],
                    ],
                ]);

                // ── Legend row ──
                $legendRow = $dataEnd + 2;
                $sheet->mergeCells("A{$legendRow}:D{$legendRow}");
                $sheet->setCellValue("A{$legendRow}", 'Keterangan:');
                $sheet->getStyle("A{$legendRow}")->getFont()->setBold(true)->setSize(8);

                $legendItems = ['H = Hadir', 'T = Terlambat', 'A = Alpa', 'I = Izin', 'S = Sakit', 'D = Dispensasi', '· = Libur'];
                $legendColors = ['FF86EFAC', 'FFFCD34D', 'FFFCA5A5', 'FF93C5FD', 'FFC4B5FD', 'FF67E8F9', 'FFE5E7EB'];

                foreach ($legendItems as $li => $item) {
                    $col = Coordinate::stringFromColumnIndex(5 + $li);
                    $sheet->setCellValue("{$col}{$legendRow}", $item);
                    $sheet->getStyle("{$col}{$legendRow}")->applyFromArray([
                        'font' => ['size' => 8],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $legendColors[$li]]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // ── Freeze panes at E3 ──
                $sheet->freezePane('E3');
            },
        ];
    }
}
