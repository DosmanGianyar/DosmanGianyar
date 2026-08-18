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

class SiswaDataExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    public function collection(): Collection
    {
        return User::whereIn('role', ['siswa', 'pengelola'])
            ->with('schoolClass')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'nisn',
            'nis',
            'nama',
            'email',
            'role',
            'jenis_kelamin',
            'kelas',
            'no_hp',
            'tgl_lahir',
            'alamat',
            'nama_ortu',
            'hp_ortu',
            'nama_ayah',
            'hp_ayah',
            'pekerjaan_ayah',
            'nama_ibu',
            'hp_ibu',
            'pekerjaan_ibu',
            'nama_wali',
            'hp_wali',
            'pekerjaan_wali',
            'golongan_darah',
            'riwayat_penyakit',
            'tinggi_cm',
            'berat_kg',
        ];
    }

    /**
     * @param User $user
     */
    public function map($user): array
    {
        return [
            $user->nisn ? (string) $user->nisn : '',
            $user->nis ? (string) $user->nis : '',
            $user->name ?? '',
            $user->email ?? '',
            $user->role ?? 'siswa',
            $user->gender ?? '',
            $user->schoolClass?->name ?? '',
            $user->phone ? (string) $user->phone : '',
            $user->birth_date ? $user->birth_date->format('Y-m-d') : '',
            $user->address ?? '',
            $user->parent_name ?? '',
            $user->parent_phone ? (string) $user->parent_phone : '',
            $user->father_name ?? '',
            $user->father_phone ? (string) $user->father_phone : '',
            $user->father_job ?? '',
            $user->mother_name ?? '',
            $user->mother_phone ? (string) $user->mother_phone : '',
            $user->mother_job ?? '',
            $user->guardian_name ?? '',
            $user->guardian_phone ? (string) $user->guardian_phone : '',
            $user->guardian_job ?? '',
            $user->blood_type ?? '',
            $user->medical_history ?? '',
            $user->height_cm ?? '',
            $user->weight_kg ?? '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NISN
            'B' => NumberFormat::FORMAT_TEXT, // NIS
            'H' => NumberFormat::FORMAT_TEXT, // No HP
            'L' => NumberFormat::FORMAT_TEXT, // HP Ortu
            'N' => NumberFormat::FORMAT_TEXT, // HP Ayah
            'Q' => NumberFormat::FORMAT_TEXT, // HP Ibu
            'T' => NumberFormat::FORMAT_TEXT, // HP Wali
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }
}
