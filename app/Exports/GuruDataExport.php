<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuruDataExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, ShouldAutoSize
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
