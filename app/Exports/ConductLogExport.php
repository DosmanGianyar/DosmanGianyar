<?php

namespace App\Exports;

use App\Models\ConductLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConductLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private readonly ?int   $classId,
        private readonly string $month   // Y-m
    ) {}

    public function collection()
    {
        [$year, $mon] = explode('-', $this->month);

        $query = ConductLog::with(['student.schoolClass', 'teacher', 'category'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $mon);

        if ($this->classId) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $this->classId));
        }

        return $query->orderBy('created_at')->get();
    }

    public function map($row): array
    {
        return [
            $row->student?->name,
            $row->student?->nis,
            $row->student?->schoolClass?->name,
            $row->category?->name,
            ucfirst($row->category?->type),
            ucfirst($row->category?->type ?? '—'),
            $row->teacher?->name,
            $row->note,
            $row->created_at->isoFormat('D MMMM Y'),
        ];
    }

    public function headings(): array
    {
        return ['Nama Siswa', 'NIS', 'Kelas', 'Kategori', 'Tipe', 'Jenis', 'Dicatat Oleh', 'Catatan', 'Tanggal'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1D4ED8']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 12, 'C' => 14, 'D' => 22, 'E' => 12, 'F' => 8, 'G' => 22, 'H' => 30, 'I' => 20];
    }

    public function title(): string
    {
        return 'Rekap Poin Perilaku';
    }
}
