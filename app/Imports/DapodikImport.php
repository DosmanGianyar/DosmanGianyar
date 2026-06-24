<?php

namespace App\Imports;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class DapodikImport implements ToCollection
{
    public int   $created  = 0;
    public int   $updated  = 0;
    public int   $skipped  = 0;
    public array $errors   = [];
    public array $warnings = [];

    private array $classCache = [];

    public function collection(Collection $rows): void
    {
        [$headerIndex, $colMap] = $this->detectHeader($rows);

        if ($colMap === null) {
            $this->errors[] = 'Kolom NISN tidak ditemukan. Pastikan file adalah ekspor Data Peserta Didik dari Dapodik.';
            return;
        }

        foreach ($rows->slice($headerIndex + 1) as $rowOffset => $row) {
            $lineNum = $headerIndex + $rowOffset + 2;
            $this->processRow($row, $colMap, $lineNum);
        }
    }

    // ─── Header detection ─────────────────────────────────────────────────────

    /**
     * Scan up to row 15 to find the header row containing "nisn".
     * Returns [headerRowIndex, columnMap] or [0, null] if not found.
     */
    private function detectHeader(Collection $rows): array
    {
        foreach ($rows->take(15) as $i => $row) {
            $normalized = $row->map(fn ($v) => $this->normalizeKey((string) $v));
            if ($normalized->contains('nisn') || $normalized->contains('nonisn') || $normalized->contains('nonisn')) {
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

    // ─── Public chunk API ─────────────────────────────────────────────────────

    /**
     * Build colMap from a raw header row array (used by chunked page import).
     */
    public function buildColMap(array $headerRow): array
    {
        $colMap = [];
        foreach ($headerRow as $idx => $cell) {
            $key = $this->normalizeKey((string) $cell);
            if ($key !== '') {
                $colMap[$key] = $idx;
            }
        }
        return $colMap;
    }

    // ─── Row processing ───────────────────────────────────────────────────────

    public function processRow(Collection $row, array $colMap, int $lineNum): void
    {
        $nisn = $this->pick($row, $colMap, ['nisn', 'nonisn', 'nonisn']);

        // Skip rows with no valid NISN (likely empty rows or sub-totals)
        if (! $nisn || ! preg_match('/^\d{8,12}$/', $nisn)) {
            $this->skipped++;
            return;
        }

        $nama = $this->pick($row, $colMap, ['namalengkap', 'namapesertadidik', 'nama', 'namiswa']);
        if (! $nama) {
            $this->errors[] = "Baris {$lineNum} (NISN {$nisn}): nama kosong — dilewati.";
            $this->skipped++;
            return;
        }

        $nis        = $this->pick($row, $colMap, ['nis', 'noinduk', 'nomorinduk', 'nipd']);
        $tglLahir   = $this->parseDate($this->pick($row, $colMap, ['tanggallahir', 'tgllahir']));
        $phone      = $this->pick($row, $colMap, ['nohp', 'telepon', 'notelp', 'hp', 'nohptelepon']);
        $gender     = $this->normalizeGender($this->pick($row, $colMap, ['jeniskelamin', 'lp', 'gender', 'jk']));
        $namaIbu    = $this->pick($row, $colMap, ['namaibuKandung', 'namaibu', 'ibu', 'dataibu']);
        $namaAyah   = $this->pick($row, $colMap, ['namaayahkandung', 'namaayah', 'ayah', 'dataayah']);
        $namaWali   = $this->pick($row, $colMap, ['namawali', 'wali', 'orangtua', 'datawali']);
        $parentName = $namaIbu ?: ($namaWali ?: $namaAyah);
        $kelasName  = $this->pick($row, $colMap, ['rombonganbelajar', 'rombel', 'kelas', 'namakelas', 'rombelsaatini']);
        $alamat     = $this->pick($row, $colMap, ['alamat', 'alamatjalan', 'alamatlengkap']);
        $email      = strtolower($this->pick($row, $colMap, ['email', 'surel', 'email']));

        $classId = $this->resolveClass($kelasName);

        $existing = User::where('nisn', $nisn)->first();

        if ($existing) {
            $this->updateStudent($existing, compact(
                'nama', 'nis', 'gender', 'phone', 'parentName',
                'tglLahir', 'alamat', 'classId'
            ));
        } else {
            $this->createStudent(compact(
                'nisn', 'nama', 'nis', 'gender', 'email', 'phone', 'parentName',
                'tglLahir', 'alamat', 'classId'
            ));
        }
    }

    private function updateStudent(User $user, array $data): void
    {
        $update = [
            'name'        => $data['nama'],
            'gender'      => $data['gender']   ?: $user->gender,
            'phone'       => $data['phone']    ?: $user->phone,
            'parent_name' => $data['parentName'] ?: $user->parent_name,
            'address'     => $data['alamat']   ?: $user->address,
        ];

        if ($data['nis'])       $update['nis']        = $data['nis'];
        if ($data['classId'])   $update['class_id']   = $data['classId'];
        if ($data['tglLahir'])  $update['birth_date'] = $data['tglLahir'];

        $user->update($update);
        $this->updated++;
    }

    private function createStudent(array $data): void
    {
        // Ensure email is unique
        $email = $data['email'];
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
            $email = $data['nisn'] . '@siswa.sims.sch.id';
        }

        User::create([
            'name'        => $data['nama'],
            'email'       => $email,
            'password'    => Hash::make($data['nisn'], ['rounds' => 4]), // temp password = NISN; low rounds intentional for bulk import speed
            'role'        => 'siswa',
            'nisn'        => $data['nisn'],
            'nis'         => $data['nis']         ?: null,
            'gender'      => $data['gender']      ?: null,
            'class_id'    => $data['classId'],
            'parent_name' => $data['parentName']  ?: null,
            'parent_phone'=> null,
            'phone'       => $data['phone']       ?: null,
            'birth_date'  => $data['tglLahir'],
            'address'     => $data['alamat']      ?: null,
        ]);

        $this->created++;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Try multiple column name variants and return the first non-empty value.
     */
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

        $key = strtolower($name);
        if (! array_key_exists($key, $this->classCache)) {
            $existing = SchoolClass::whereRaw('LOWER(name) = ?', [$key])->first();

            if (! $existing) {
                $existing = SchoolClass::create([
                    'name'  => $name,
                    'grade' => $this->extractGrade($name),
                ]);
                $this->warnings[] = "Kelas '{$name}' tidak ditemukan — kelas baru otomatis dibuat.";
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
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
