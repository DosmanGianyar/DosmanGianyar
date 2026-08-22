<?php

namespace App\Imports;

use App\Models\SchoolClass;
use App\Models\User;
use App\Services\OrangtuaSyncService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SiswaDataImport implements ToCollection, SkipsEmptyRows
{
    public int   $created   = 0;
    public int   $updated   = 0;
    public int   $unchanged = 0;
    public int   $skipped   = 0;
    public array $errors    = [];
    public array $warnings  = [];

    private array $classCache = [];

    public function collection(Collection $rows): void
    {
        User::withoutEvents(function () use ($rows) {
            [$headerIndex, $colMap] = $this->detectHeader($rows);

            if ($colMap === null) {
                $this->errors[] = 'Header kolom tidak ditemukan. Pastikan file Excel memiliki kolom NISN / Nama.';
                return;
            }

            foreach ($rows->slice($headerIndex + 1) as $rowOffset => $row) {
                $lineNum = $headerIndex + $rowOffset + 2;
                $this->processRow($row, $colMap, $lineNum);
            }
        });

        // Resync parent accounts once import completes
        OrangtuaSyncService::syncAll();
    }

    private function detectHeader(Collection $rows): array
    {
        foreach ($rows->take(15) as $i => $row) {
            $normalized = $row->map(fn ($v) => $this->normalizeKey((string) $v));
            if ($normalized->contains('nisn') || $normalized->contains('nonisn') || $normalized->contains('nama')) {
                $colMap = [];
                foreach ($normalized as $idx => $key) {
                    if ($key !== '') {
                        $colMap[$key] = $idx;
                    }
                }
                return [$i, $colMap];
            }
        }
        return [0, null];
    }

    public function processRow(Collection $row, array $colMap, int $lineNum): void
    {
        $nisn  = $this->pick($row, $colMap, ['nisn', 'nonisn', 'nisnsiswa']);
        $nis   = $this->pick($row, $colMap, ['nis', 'nonis', 'noinduk', 'nipd']);
        $nama  = $this->pick($row, $colMap, ['nama', 'namalengkap', 'namapesertadidik', 'namasiswa']);
        $email = strtolower($this->pick($row, $colMap, ['email', 'surel']));

        // Skip completely empty lines
        if (! $nisn && ! $nis && ! $nama && ! $email) {
            $this->skipped++;
            return;
        }

        // Search existing user (NISN is primary immutable key)
        $existing = null;
        if ($nisn) {
            $existing = User::where('nisn', $nisn)->whereIn('role', ['siswa', 'pengelola'])->first();
        }
        if (! $existing && $nis) {
            $existing = User::where('nis', $nis)->whereIn('role', ['siswa', 'pengelola'])->first();
        }
        if (! $existing && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existing = User::where('email', $email)->whereIn('role', ['siswa', 'pengelola'])->first();
        }

        // Read all attribute fields from row
        $role        = strtolower($this->pick($row, $colMap, ['role'])) ?: null;
        $gender      = $this->normalizeGender($this->pick($row, $colMap, ['jeniskelamin', 'jk', 'gender', 'lp']));
        $kelasName   = $this->pick($row, $colMap, ['kelas', 'rombonganbelajar', 'rombel', 'namakelas', 'namarombel']);
        $phone       = User::formatPhoneNumber($this->pick($row, $colMap, ['nohp', 'telepon', 'notelp', 'hp', 'nomorhp', 'nohpsiswa']));
        $tglLahir    = $this->parseDate($this->pick($row, $colMap, ['tgllahir', 'tanggallahir', 'birthdate', 'tgl_lahir']));
        $alamat      = $this->pick($row, $colMap, ['alamat', 'alamatjalan', 'alamat_jalan']);
        $parentName  = $this->pick($row, $colMap, ['namaortu', 'nama_ortu', 'orangtua']);
        $parentPhone = User::formatPhoneNumber($this->pick($row, $colMap, ['hportu', 'hp_ortu', 'nohportu']));

        $fatherName  = $this->pick($row, $colMap, ['namaayah', 'nama_ayah', 'ayah']);
        $fatherPhone = User::formatPhoneNumber($this->pick($row, $colMap, ['hpayah', 'hp_ayah', 'nohpayah']));
        $fatherJob   = $this->pick($row, $colMap, ['pekerjaanayah', 'pekerjaan_ayah']);

        $motherName  = $this->pick($row, $colMap, ['namaibu', 'nama_ibu', 'ibu', 'namaibukandung']);
        $motherPhone = User::formatPhoneNumber($this->pick($row, $colMap, ['hpibu', 'hp_ibu', 'nohpibu']));
        $motherJob   = $this->pick($row, $colMap, ['pekerjaanibu', 'pekerjaan_ibu']);

        $guardianName  = $this->pick($row, $colMap, ['namawali', 'nama_wali', 'wali']);
        $guardianPhone = User::formatPhoneNumber($this->pick($row, $colMap, ['hpwali', 'hp_wali', 'nohpwali']));
        $guardianJob   = $this->pick($row, $colMap, ['pekerjaanwali', 'pekerjaan_wali']);

        $bloodType      = strtoupper($this->pick($row, $colMap, ['golongandarah', 'goldar', 'bloodtype']));
        $medicalHistory = $this->pick($row, $colMap, ['riwayatpenyakit', 'penyakit']);
        $heightCm       = is_numeric($h = $this->pick($row, $colMap, ['tinggicm', 'tinggibadan', 'tinggi'])) ? (int)$h : null;
        $weightKg       = is_numeric($w = $this->pick($row, $colMap, ['beratkg', 'beratbadan', 'berat'])) ? (int)$w : null;

        $hobbies          = $this->pick($row, $colMap, ['hobi', 'hobbies']);
        $aspirations      = $this->pick($row, $colMap, ['citacita', 'aspirations']);
        $rtRw             = $this->pick($row, $colMap, ['rtrw']);
        $kelurahan        = $this->pick($row, $colMap, ['kelurahan', 'desa']);
        $kecamatan        = $this->pick($row, $colMap, ['kecamatan']);
        $kabupaten        = $this->pick($row, $colMap, ['kabupaten', 'kota']);
        $residenceStatus  = $this->pick($row, $colMap, ['statustempattinggal', 'residence_status']);
        $transportation   = $this->pick($row, $colMap, ['transportasi', 'transportation']);
        $distanceKm       = is_numeric($d = $this->pick($row, $colMap, ['jarakkm', 'distance_km'])) ? (float)$d : null;
        $travelTimeMin    = is_numeric($tt = $this->pick($row, $colMap, ['waktutempuhmenit', 'travel_time_minutes'])) ? (int)$tt : null;

        $classId = $this->resolveClass($kelasName);

        if ($existing) {
            // Existing student: Check for changes
            $changes = [];

            if ($nama && $this->isDifferent($existing->name, $nama)) {
                $changes['name'] = $nama;
            }

            // NISN is immutable primary key, set only if student currently has no NISN
            if ($nisn && empty($existing->nisn)) {
                $changes['nisn'] = $nisn;
            }

            // NIS can be updated! If updated, clear conflicting NIS on other users to avoid UNIQUE constraint error
            if ($nis && $this->isDifferent($existing->nis, $nis)) {
                $changes['nis'] = $nis;
            }

            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) && $this->isDifferent($existing->email, $email)) {
                $changes['email'] = $email;
            }
            if ($role && in_array($role, ['siswa', 'pengelola'], true) && $this->isDifferent($existing->role, $role)) {
                $changes['role'] = $role;
            }
            if ($gender && $this->isDifferent($existing->gender, $gender)) {
                $changes['gender'] = $gender;
            }
            if ($classId !== null && $existing->class_id !== $classId) {
                $changes['class_id'] = $classId;
            }
            if ($phone !== null && $this->isDifferent($existing->phone, $phone)) {
                $changes['phone'] = $phone;
            }
            if ($tglLahir !== null && $existing->birth_date?->format('Y-m-d') !== $tglLahir) {
                $changes['birth_date'] = $tglLahir;
            }
            if ($alamat !== null && $this->isDifferent($existing->address, $alamat)) {
                $changes['address'] = $alamat;
            }
            if ($parentName !== null && $this->isDifferent($existing->parent_name, $parentName)) {
                $changes['parent_name'] = $parentName;
            }
            if ($parentPhone !== null && $this->isDifferent($existing->parent_phone, $parentPhone)) {
                $changes['parent_phone'] = $parentPhone;
            }

            if ($fatherName !== null && $this->isDifferent($existing->father_name, $fatherName)) {
                $changes['father_name'] = $fatherName;
            }
            if ($fatherPhone !== null && $this->isDifferent($existing->father_phone, $fatherPhone)) {
                $changes['father_phone'] = $fatherPhone;
            }
            if ($fatherJob !== null && $this->isDifferent($existing->father_job, $fatherJob)) {
                $changes['father_job'] = $fatherJob;
            }

            if ($motherName !== null && $this->isDifferent($existing->mother_name, $motherName)) {
                $changes['mother_name'] = $motherName;
            }
            if ($motherPhone !== null && $this->isDifferent($existing->mother_phone, $motherPhone)) {
                $changes['mother_phone'] = $motherPhone;
            }
            if ($motherJob !== null && $this->isDifferent($existing->mother_job, $motherJob)) {
                $changes['mother_job'] = $motherJob;
            }

            if ($guardianName !== null && $this->isDifferent($existing->guardian_name, $guardianName)) {
                $changes['guardian_name'] = $guardianName;
            }
            if ($guardianPhone !== null && $this->isDifferent($existing->guardian_phone, $guardianPhone)) {
                $changes['guardian_phone'] = $guardianPhone;
            }
            if ($guardianJob !== null && $this->isDifferent($existing->guardian_job, $guardianJob)) {
                $changes['guardian_job'] = $guardianJob;
            }

            if ($bloodType !== null && $this->isDifferent($existing->blood_type, $bloodType)) {
                $changes['blood_type'] = $bloodType;
            }
            if ($medicalHistory !== null && $this->isDifferent($existing->medical_history, $medicalHistory)) {
                $changes['medical_history'] = $medicalHistory;
            }
            if ($heightCm !== null && $existing->height_cm !== $heightCm) {
                $changes['height_cm'] = $heightCm;
            }
            if ($weightKg !== null && $existing->weight_kg !== $weightKg) {
                $changes['weight_kg'] = $weightKg;
            }

            if ($hobbies !== null && $this->isDifferent($existing->hobbies, $hobbies)) {
                $changes['hobbies'] = $hobbies;
            }
            if ($aspirations !== null && $this->isDifferent($existing->aspirations, $aspirations)) {
                $changes['aspirations'] = $aspirations;
            }
            if ($rtRw !== null && $this->isDifferent($existing->rt_rw, $rtRw)) {
                $changes['rt_rw'] = $rtRw;
            }
            if ($kelurahan !== null && $this->isDifferent($existing->kelurahan, $kelurahan)) {
                $changes['kelurahan'] = $kelurahan;
            }
            if ($kecamatan !== null && $this->isDifferent($existing->kecamatan, $kecamatan)) {
                $changes['kecamatan'] = $kecamatan;
            }
            if ($kabupaten !== null && $this->isDifferent($existing->kabupaten, $kabupaten)) {
                $changes['kabupaten'] = $kabupaten;
            }
            if ($residenceStatus !== null && $this->isDifferent($existing->residence_status, $residenceStatus)) {
                $changes['residence_status'] = $residenceStatus;
            }
            if ($transportation !== null && $this->isDifferent($existing->transportation, $transportation)) {
                $changes['transportation'] = $transportation;
            }
            if ($distanceKm !== null && (float)$existing->distance_km !== (float)$distanceKm) {
                $changes['distance_km'] = $distanceKm;
            }
            if ($travelTimeMin !== null && (int)$existing->travel_time_minutes !== (int)$travelTimeMin) {
                $changes['travel_time_minutes'] = $travelTimeMin;
            }

            if (! empty($changes)) {
                // Clear potential UNIQUE constraint conflicts before applying updates
                if (isset($changes['nis']) && filled($changes['nis'])) {
                    User::where('nis', $changes['nis'])
                        ->where('id', '!=', $existing->id)
                        ->update(['nis' => null]);
                }
                if (isset($changes['email']) && filled($changes['email'])) {
                    $conflictingUsers = User::where('email', $changes['email'])
                        ->where('id', '!=', $existing->id)
                        ->get();
                    foreach ($conflictingUsers as $cu) {
                        $cu->update(['email' => 'old_' . $cu->id . '_' . time() . '@siswa.sims.sch.id']);
                    }
                }

                $existing->update($changes);
                $this->updated++;
            } else {
                $this->unchanged++;
            }
        } else {
            // New Student: Create
            if (! $nama) {
                $this->errors[] = "Baris {$lineNum}: nama kosong — siswa baru tidak dapat dibuat.";
                $this->skipped++;
                return;
            }

            if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
                $emailKey = $nisn ?: ($nis ?: 'siswa' . rand(1000, 9999));
                $email = $emailKey . '@siswa.sims.sch.id';
            }

            $defaultPassword = $nisn ?: ($nis ?: $email);

            // Clear potential UNIQUE conflicts before creating new user
            if ($nis && filled($nis)) {
                User::where('nis', $nis)->update(['nis' => null]);
            }
            if ($nisn && filled($nisn)) {
                User::where('nisn', $nisn)->update(['nisn' => null]);
            }
            if ($email && filled($email)) {
                $conflictingUsers = User::where('email', $email)->get();
                foreach ($conflictingUsers as $cu) {
                    $cu->update(['email' => 'old_' . $cu->id . '_' . time() . '@siswa.sims.sch.id']);
                }
            }

            User::create([
                'name'                 => $nama,
                'email'                => $email,
                'password'             => Hash::make($defaultPassword, ['rounds' => 4]),
                'must_change_password' => true,
                'role'                 => in_array($role, ['siswa', 'pengelola'], true) ? $role : 'siswa',
                'nisn'                 => $nisn ?: null,
                'nis'                  => $nis ?: null,
                'gender'               => $gender ?: null,
                'class_id'             => $classId,
                'phone'                => $phone ?: null,
                'birth_date'           => $tglLahir,
                'address'              => $alamat ?: null,
                'parent_name'          => $parentName ?: null,
                'parent_phone'         => $parentPhone ?: null,
                'father_name'          => $fatherName ?: null,
                'father_phone'         => $fatherPhone ?: null,
                'father_job'           => $fatherJob ?: null,
                'mother_name'          => $motherName ?: null,
                'mother_phone'         => $motherPhone ?: null,
                'mother_job'           => $motherJob ?: null,
                'guardian_name'        => $guardianName ?: null,
                'guardian_phone'       => $guardianPhone ?: null,
                'guardian_job'         => $guardianJob ?: null,
                'blood_type'           => $bloodType ?: null,
                'medical_history'      => $medicalHistory ?: null,
                'height_cm'            => $heightCm,
                'weight_kg'            => $weightKg,
                'hobbies'              => $hobbies ?: null,
                'aspirations'          => $aspirations ?: null,
                'rt_rw'                => $rtRw ?: null,
                'kelurahan'            => $kelurahan ?: null,
                'kecamatan'            => $kecamatan ?: null,
                'kabupaten'            => $kabupaten ?: null,
                'residence_status'     => $residenceStatus ?: null,
                'transportation'       => $transportation ?: null,
                'distance_km'          => $distanceKm,
                'travel_time_minutes'  => $travelTimeMin,
            ]);

            $this->created++;
        }
    }

    private function isDifferent(mixed $oldVal, mixed $newVal): bool
    {
        $oldStr = trim((string) $oldVal);
        $newStr = trim((string) $newVal);

        return $oldStr !== $newStr;
    }

    private function pick(Collection $row, array $colMap, array $keys): string
    {
        foreach ($keys as $key) {
            $normalized = $this->normalizeKey($key);
            if (isset($colMap[$normalized]) && filled($row[$colMap[$normalized]])) {
                return trim((string) $row[$colMap[$normalized]]);
            }
        }
        return '';
    }

    public function normalizeKey(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($value));
    }

    private function normalizeGender(string $value): ?string
    {
        $v = strtoupper(trim($value));
        if (in_array($v, ['L', 'LAKI-LAKI', 'LAKI LAKI', 'LAKILAKI'])) return 'L';
        if (in_array($v, ['P', 'PEREMPUAN', 'WANITA'])) return 'P';
        return null;
    }

    private function resolveClass(string $name): ?int
    {
        if (! $name) return null;

        $key = strtolower(trim($name));
        if (! array_key_exists($key, $this->classCache)) {
            $existing = SchoolClass::whereRaw('LOWER(name) = ?', [$key])->first();

            if (! $existing) {
                $existing = SchoolClass::create([
                    'name'  => trim($name),
                    'grade' => $this->extractGrade($name),
                ]);
                $this->warnings[] = "Kelas '{$name}' tidak ditemukan — kelas baru dibuat otomatis.";
            }

            $this->classCache[$key] = $existing->id;
        }
        return $this->classCache[$key];
    }

    private function extractGrade(string $className): ?string
    {
        if (preg_match('/^(X{1,3}I{0,3}|IX|IV|V?I{0,3})/i', trim($className), $m)) {
            return strtoupper($m[1]);
        }
        return null;
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) return null;
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
