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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuruDataExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, WithCustomValueBinder, ShouldAutoSize
{
    public function collection(): Collection
    {
        return User::whereIn('role', ['guru', 'admin'])
            ->with('subjects')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'nip',
            'nama',
            'email',
            'role',
            'jenis_kelamin',
            'no_hp',
            'mapel',
        ];
    }

    /**
     * @param User $user
     */
    public function map($user): array
    {
        $subjectNames = $user->subjects->pluck('name')->implode(', ');
        if (empty($subjectNames) && ! empty($user->subject)) {
            $subjectNames = $user->subject;
        }

        return [
            $user->nip ? (string) $user->nip : '',
            $user->name ?? '',
            $user->email ?? '',
            $user->role ?? 'guru',
            $user->gender ?? '',
            $user->phone ? (string) $user->phone : '',
            $subjectNames,
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($value !== null && $value !== '') {
            $column = $cell->getColumn();
            // Force NIP (A) and No HP (F) to be explicit String data type to prevent scientific notation (1.966E+17) in Excel & Google Sheets
            if (in_array($column, ['A', 'F'], true)) {
                $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
                return true;
            }
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIP
            'F' => NumberFormat::FORMAT_TEXT, // No HP
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF15803D']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }
}
