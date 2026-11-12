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

class SiswaEmailExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, WithCustomValueBinder, ShouldAutoSize
{
    private int $rowNumber = 0;

    public function __construct(
        public ?int $classId = null,
        public ?string $status = 'all'
    ) {}

    public function collection(): Collection
    {
        $this->rowNumber = 0;

        $query = User::whereIn('users.role', ['siswa', 'pengelola'])
            ->with('schoolClass');

        if ($this->classId) {
            $query->where('users.class_id', $this->classId);
        }

        if ($this->status === 'with_email') {
            $query->whereNotNull('users.email')->where('users.email', '!=', '');
        } elseif ($this->status === 'without_email') {
            $query->where(function ($q) {
                $q->whereNull('users.email')->orWhere('users.email', '');
            });
        }

        return $query
            ->leftJoin('classes', 'users.class_id', '=', 'classes.id')
            ->select('users.*')
            ->orderByRaw('classes.grade IS NULL, classes.grade ASC')
            ->orderBy('classes.name', 'asc')
            ->orderBy('users.name', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NISN',
            'NIS',
            'Nama Lengkap',
            'Kelas',
            'Jenis Kelamin',
            'Email Siswa',
            'Status Email',
            'No. WhatsApp / HP',
            'Terakhir Update Data',
        ];
    }

    /**
     * @param User $user
     */
    public function map($user): array
    {
        $this->rowNumber++;
        $hasEmail = ! empty($user->email);

        return [
            $this->rowNumber,
            $user->nisn ? (string) $user->nisn : '',
            $user->nis ? (string) $user->nis : '',
            $user->name ?? '',
            $user->schoolClass?->name ?? 'Tanpa Kelas',
            $user->gender === 'L' ? 'Laki-laki' : ($user->gender === 'P' ? 'Perempuan' : ($user->gender ?? '')),
            $user->email ?? '',
            $hasEmail ? 'Sudah Ada Email' : 'Belum Ada Email',
            $user->phone ? (string) $user->phone : '',
            $user->updated_at ? $user->updated_at->timezone('Asia/Makassar')->format('d/m/Y H:i') . ' WITA' : '',
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($value !== null && $value !== '') {
            $column = $cell->getColumn();
            // Force NISN (B), NIS (C), No HP (I) to be explicit String to preserve leading zeroes
            if (in_array($column, ['B', 'C', 'I'], true)) {
                $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
                return true;
            }
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // NISN
            'C' => NumberFormat::FORMAT_TEXT, // NIS
            'I' => NumberFormat::FORMAT_TEXT, // No HP
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A8A']], // Dark Blue / Navy
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }
}

