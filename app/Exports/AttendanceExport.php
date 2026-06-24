<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private readonly ?int    $classId,
        private readonly string  $month,   // Y-m format
        private readonly ?string $status = null
    ) {}

    public function collection()
    {
        [$year, $mon] = explode('-', $this->month);

        $query = Attendance::with(['user.schoolClass'])
            ->whereYear('date', $year)
            ->whereMonth('date', $mon)
            ->whereHas('user', fn($q) => $q->where('role', 'siswa'));

        if ($this->classId) {
            $query->whereHas('user', fn($q) => $q->where('class_id', $this->classId));
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('date')->orderBy('user_id')->get();
    }

    public function map($row): array
    {
        return [
            $row->user?->name,
            $row->user?->nis,
            $row->user?->schoolClass?->name,
            $row->date->isoFormat('D MMMM Y'),
            $row->check_in_time,
            ucfirst($row->status),
        ];
    }

    public function headings(): array
    {
        return ['Nama Siswa', 'NIS', 'Kelas', 'Tanggal', 'Jam Masuk', 'Status'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1D4ED8']], 'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 14, 'C' => 14, 'D' => 20, 'E' => 12, 'F' => 12];
    }

    public function title(): string
    {
        return 'Rekap Absensi';
    }
}
