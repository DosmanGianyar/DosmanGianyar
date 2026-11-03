<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherCodeExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, WithCustomValueBinder, ShouldAutoSize
{
    private int $rowNumber = 0;

    public function collection(): Collection
    {
        return User::whereIn('role', ['guru', 'wali_kelas', 'kepala_sekolah', 'admin'])
            ->with('subjects')
            ->orderByRaw('CASE WHEN teacher_code IS NOT NULL AND teacher_code != "" THEN 0 ELSE 1 END')
            ->orderBy('teacher_code')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'kode_guru',
            'nama_guru',
            'nip',
            'mata_pelajaran',
            'email',
        ];
    }

    /**
     * @param User $user
     */
    public function map($user): array
    {
        $this->rowNumber++;

        $subjectNames = $user->subjects->pluck('name')->implode(', ');
        if (empty($subjectNames) && !empty($user->subject)) {
            $subjectNames = $user->subject;
        }

        return [
            $this->rowNumber,
            $user->teacher_code ?? '',
            $user->name ?? '',
            $user->nip ? (string) $user->nip : '',
            $subjectNames,
            $user->email ?? '',
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        // Treat NIP and kode_guru explicitly as text strings to prevent scientific notation or auto-formatting
        if (in_array($cell->getColumn(), ['B', 'D'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'B' => '@', // Kode Guru Text
            'D' => '@', // NIP Text
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();

        // Header style
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'], // Indigo / Navy
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Center No and Kode Guru
        if ($highestRow > 1) {
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:B{$highestRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'name' => 'Courier New',
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $sheet->getStyle("D2:D{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Table borders
            $sheet->getStyle("A1:F{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        return [];
    }
}
