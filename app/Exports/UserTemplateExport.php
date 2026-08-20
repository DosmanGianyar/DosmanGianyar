<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserTemplateExport extends DefaultValueBinder implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithCustomValueBinder
{
    public function array(): array
    {
        return [
            // Example siswa
            ['Budi Santoso',    'budi@sman1gianyar.sch.id',  'siswa',          '2024001', '',          'X IPA 1', '081234567890', 'Wayan Santoso', '081234567891', '2008-05-15', 'Jl. Raya Gianyar No.1', ''],
            // Example guru
            ['Ni Made Sari',    'sari@sman1gianyar.sch.id',  'guru',           '',        '198505012010', '',      '082345678901', '',              '',             '1985-01-05', 'Jl. Melati No.3',      'Matematika'],
        ];
    }

    public function headings(): array
    {
        return [
            'nama',         // Wajib
            'email',        // Wajib, harus unik
            'role',         // siswa | guru | admin | pengelola
            'nis',          // NIS siswa (dipakai sebagai password default)
            'nip',          // NIP guru (dipakai sebagai password default)
            'kelas',        // Nama kelas, harus sudah ada di database (contoh: X IPA 1)
            'no_hp',
            'nama_ortu',
            'hp_ortu',
            'tgl_lahir',    // Format: YYYY-MM-DD
            'alamat',
            'mapel',        // Mata pelajaran (khusus guru)
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($value !== null && $value !== '') {
            $column = $cell->getColumn();
            // D = nis, E = nip, G = no_hp, I = hp_ortu
            if (in_array($column, ['D', 'E', 'G', 'I'], true)) {
                $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
                return true;
            }
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1D4ED8']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, 'B' => 30, 'C' => 15, 'D' => 12,
            'E' => 18, 'F' => 15, 'G' => 15, 'H' => 20,
            'I' => 15, 'J' => 15, 'K' => 30, 'L' => 15,
        ];
    }
}
