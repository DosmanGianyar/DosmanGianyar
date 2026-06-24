<?php

namespace App\Exports;

use App\Models\HomeroomConsultation;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HomeroomConsultationExport implements FromCollection, WithHeadings, WithMapping,
    WithColumnWidths, WithStyles, WithTitle, WithEvents
{
    public function __construct(
        private readonly int    $teacherId,
        private readonly string $month,   // Y-m
    ) {}

    public function collection()
    {
        [$year, $mon] = explode('-', $this->month);

        return HomeroomConsultation::where('teacher_id', $this->teacherId)
            ->where('status', 'completed')
            ->whereYear('conducted_date', $year)
            ->whereMonth('conducted_date', $mon)
            ->with('student')
            ->orderBy('conducted_date')
            ->get();
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Nama Siswa', 'Topik', 'Catatan Bimbingan', 'Tindak Lanjut'];
    }

    public function map($row): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $row->conducted_date?->format('d/m/Y') ?? '-',
            $row->student->name,
            $row->topic,
            $row->teacher_note ?? '-',
            $row->follow_up ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 14, 'C' => 28, 'D' => 30, 'E' => 50, 'F' => 35];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Jurnal Bimbingan';
    }

    public function registerEvents(): array
    {
        $teacher   = User::find($this->teacherId);
        $class     = SchoolClass::where('homeroom_teacher_id', $this->teacherId)->first();
        $monthName = Carbon::parse($this->month . '-01')->isoFormat('MMMM Y');

        return [
            AfterSheet::class => function (AfterSheet $event) use ($teacher, $class, $monthName) {
                $sheet = $event->sheet->getDelegate();

                // Insert 3 header rows
                $sheet->insertNewRowBefore(1, 3);

                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'JURNAL BIMBINGAN WALI KELAS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', ($class?->name ?? '') . '  —  ' . $monthName);
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A3:F3');
                $sheet->setCellValue('A3', 'Wali Kelas: ' . ($teacher?->name ?? ''));
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['size' => 9, 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Style heading row (now row 4)
                $sheet->getStyle('A4:F4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3730A3']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Border all data
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:F{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                    ],
                ]);

                // Wrap text for notes columns
                $sheet->getStyle("E5:F{$lastRow}")->getAlignment()->setWrapText(true);

                // Row heights
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(4)->setRowHeight(18);
                for ($r = 5; $r <= $lastRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(50);
                }

                // Vertical center data rows
                $sheet->getStyle("A5:F{$lastRow}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);
            },
        ];
    }
}
