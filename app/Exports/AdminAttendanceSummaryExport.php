<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminAttendanceSummaryExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    public function __construct(
        private readonly array  $rows,
        private readonly string $monthName,
        private readonly int    $workingDays,
        private readonly bool   $showClass = true
    ) {}

    public function array(): array
    {
        $data = [];
        $no   = 1;
        foreach ($this->rows as $row) {
            $r = [
                $no++,
                $row['name'],
                $row['nis'],
            ];
            if ($this->showClass) {
                $r[] = $row['class'];
            }
            $r[] = $row['hadir'];
            $r[] = $row['terlambat'];
            $r[] = $row['izin'];
            $r[] = $row['sakit'];
            $r[] = $row['alpa'];
            $r[] = $row['dispensasi'];
            $r[] = $row['pct'] . '%';
            $data[] = $r;
        }
        return $data;
    }

    public function headings(): array
    {
        $h = ['No', 'Nama Siswa', 'NIS'];
        if ($this->showClass) {
            $h[] = 'Kelas';
        }
        return array_merge($h, ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', 'Dispensasi', '% Kehadiran']);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A5F']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        if ($this->showClass) {
            return ['A' => 5, 'B' => 28, 'C' => 14, 'D' => 12, 'E' => 8, 'F' => 10, 'G' => 7, 'H' => 8, 'I' => 7, 'J' => 12, 'K' => 12];
        }
        return ['A' => 5, 'B' => 28, 'C' => 14, 'D' => 8, 'E' => 10, 'F' => 7, 'G' => 8, 'H' => 7, 'I' => 12, 'J' => 12];
    }

    public function title(): string
    {
        return 'Laporan Presensi';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $lastRow  = count($this->rows) + 1;
                $lastCol  = $this->showClass ? 'K' : 'J';

                // Info row at the top (insert 2 rows before heading)
                $sheet->insertNewRowBefore(1, 2);

                $sheet->setCellValue('A1', 'Laporan Presensi Bulanan — ' . $this->monthName);
                $sheet->mergeCells('A1:' . $lastCol . '1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $sheet->setCellValue('A2', 'Hari Efektif: ' . $this->workingDays . ' hari  |  Total Siswa: ' . count($this->rows));
                $sheet->mergeCells('A2:' . $lastCol . '2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF555555']],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Center all status columns
                $statusStart = $this->showClass ? 'E' : 'D';
                $sheet->getStyle($statusStart . '3:' . $lastCol . ($lastRow + 2))
                    ->getAlignment()->setHorizontal('center');

                // Alternate row shading
                for ($r = 4; $r <= $lastRow + 2; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle('A' . $r . ':' . $lastCol . $r)
                            ->getFill()->setFillType('solid')
                            ->getStartColor()->setARGB('FFF5F8FF');
                    }
                }

                // Thin border for the whole table
                $sheet->getStyle('A3:' . $lastCol . ($lastRow + 2))->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => 'thin', 'color' => ['argb' => 'FFDDDDDD']],
                    ],
                ]);
            },
        ];
    }
}
