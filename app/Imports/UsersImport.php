<?php

namespace App\Imports;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class UsersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors   = [];
    public int   $imported = 0;
    public int   $skipped  = 0;

    private array $classCache = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // account for header row

            $name  = trim($row['nama'] ?? '');
            $email = strtolower(trim($row['email'] ?? ''));
            $role  = strtolower(trim($row['role'] ?? 'siswa'));
            $nis   = trim($row['nis'] ?? '');
            $nip   = trim($row['nip'] ?? '');
            $kelas = trim($row['kelas'] ?? '');

            if (empty($name) || empty($email)) {
                $this->errors[] = "Baris {$rowNum}: nama/email kosong, dilewati.";
                $this->skipped++;
                continue;
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "Baris {$rowNum}: email '{$email}' tidak valid.";
                $this->skipped++;
                continue;
            }

            if (! in_array($role, ['admin', 'guru', 'siswa', 'pengelola'])) {
                $this->errors[] = "Baris {$rowNum}: role '{$role}' tidak dikenal, gunakan: siswa/guru/admin.";
                $this->skipped++;
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $this->errors[] = "Baris {$rowNum}: email '{$email}' sudah terdaftar, dilewati.";
                $this->skipped++;
                continue;
            }

            if ($nis && User::where('nis', $nis)->exists()) {
                $this->errors[] = "Baris {$rowNum}: NIS '{$nis}' sudah terdaftar, dilewati.";
                $this->skipped++;
                continue;
            }

            // Resolve class_id
            $classId = null;
            if ($kelas) {
                if (! isset($this->classCache[$kelas])) {
                    $this->classCache[$kelas] = SchoolClass::where('name', $kelas)->value('id');
                }
                $classId = $this->classCache[$kelas];
                if (! $classId) {
                    $this->errors[] = "Baris {$rowNum}: kelas '{$kelas}' tidak ditemukan di database.";
                    $this->skipped++;
                    continue;
                }
            }

            // Default password = NIS (siswa) or NIP (guru), else name slug
            $defaultPassword = match (true) {
                $role === 'siswa' && $nis !== '' => $nis,
                $role === 'guru'  && $nip !== '' => $nip,
                default                          => Str::slug($name, ''),
            };

            User::create([
                'name'         => $name,
                'email'        => $email,
                'password'     => Hash::make($defaultPassword),
                'role'         => $role,
                'nis'          => $nis ?: null,
                'nip'          => $nip ?: null,
                'class_id'     => $classId,
                'phone'        => trim($row['no_hp'] ?? '') ?: null,
                'parent_name'  => trim($row['nama_ortu'] ?? '') ?: null,
                'parent_phone' => trim($row['hp_ortu'] ?? '') ?: null,
                'birth_date'   => $this->parseDate($row['tgl_lahir'] ?? null),
                'address'      => trim($row['alamat'] ?? '') ?: null,
                'subject'      => trim($row['mapel'] ?? '') ?: null,
            ]);

            $this->imported++;
        }
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            // Handle Excel serial date number
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
