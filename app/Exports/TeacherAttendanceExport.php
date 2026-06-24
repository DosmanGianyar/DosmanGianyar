<?php

namespace App\Exports;

use App\Models\TeacherAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private readonly string $month,
        private readonly ?int   $teacherId = null,
    ) {}

    public function collection()
    {
        [$year, $mon] = explode('-', $this->month);

        $query = TeacherAttendance::with(['teacher', 'schoolClass', 'subject'])
            ->whereYear('date', $year)
            ->whereMonth('date', $mon);

        if ($this->teacherId) {
            $query->where('teacher_id', $this->teacherId);
        }

        return $query->orderBy('date')->orderBy('teacher_id')->orderBy('period')->get();
    }

    public function map($row): array
    {
        return [
            $row->teacher?->name,
            $row->date->isoFormat('D MMMM Y'),
            'Jam ke-' . $row->period,
            $row->schoolClass?->name,
            $row->subject?->name,
            $row->start_time ? substr($row->start_time, 0, 5) : '—',
            $row->end_time   ? substr($row->end_time, 0, 5)   : '—',
            $row->statusLabel(),
            $row->note ?? '',
        ];
    }

    public function headings(): array
    {
        return ['Nama Guru', 'Tanggal', 'Jam', 'Kelas', 'Mata Pelajaran', 'Mulai', 'Selesai', 'Status', 'Catatan'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1D4ED8']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 18, 'C' => 10, 'D' => 12, 'E' => 22, 'F' => 10, 'G' => 10, 'H' => 14, 'I' => 30];
    }

    public function title(): string
    {
        return 'Absensi Guru';
    }
}
