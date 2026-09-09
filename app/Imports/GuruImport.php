<?php

namespace App\Imports;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class GuruImport implements ToCollection
{
    public int   $created  = 0;
    public int   $updated  = 0;
    public int   $skipped  = 0;
    public array $errors   = [];
    public array $warnings = [];

    /**
     * Fallback role jika kolom role/jenis tidak ada di file.
     * Nilai: 'guru' | 'pegawai' | null
     */
    public ?string $defaultRole = null;

    private array $subjectCache = [];

    public function collection(Collection $rows): void
    {
        [$headerIndex, $colMap] = $this->detectHeader($rows);

        if ($colMap === null) {
            $this->errors[] = 'Kolom NIP tidak ditemukan. Pastikan file memiliki kolom NIP.';
            return;
        }

        foreach ($rows->slice($headerIndex + 1) as $rowOffset => $row) {
            $lineNum = $headerIndex + $rowOffset + 2;
            $this->processRow($row, $colMap, $lineNum);
        }
    }

    // ─── Header detection ─────────────────────────────────────────────────────

    private function detectHeader(Collection $rows): array
    {
        foreach ($rows->take(15) as $i => $row) {
            $normalized = $row->map(fn ($v) => $this->normalizeKey((string) $v));
            if ($normalized->contains('nip')) {
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
        $rawNip = $this->pick($row, $colMap, ['nip']);
        $nip    = preg_replace('/\s+/', '', $rawNip);

        if (! $nip) {
            $this->skipped++;
            return;
        }

        $nama = $this->pick($row, $colMap, ['namalengkap', 'nama', 'namaguru', 'namapegawai', 'namaptk']);
        if (! $nama) {
            $this->errors[] = "Baris {$lineNum} (NIP {$nip}): nama kosong — dilewati.";
            $this->skipped++;
            return;
        }

        // Deteksi role dari file atau fallback ke defaultRole
        $rawRole  = $this->pick($row, $colMap, ['role', 'jenis', 'tipe', 'status', 'jabatan', 'kategori', 'posisi']);
        $role     = $this->normalizeRole($rawRole) ?: ($this->defaultRole ?: 'guru');

        $email    = strtolower($this->pick($row, $colMap, ['email', 'surel']));
        $phone    = $this->pick($row, $colMap, ['nohp', 'telepon', 'notelp', 'hp', 'nohptelepon']);
        $gender   = $this->normalizeGender($this->pick($row, $colMap, ['jeniskelamin', 'lp', 'gender', 'jk']));
        $mapelRaw = $this->pick($row, $colMap, ['matapelajaran', 'mapel', 'subjects', 'subject']);

        $existing = User::where('nip', $nip)->first();

        if ($existing) {
            $this->updateUser($existing, compact('nama', 'role', 'email', 'phone', 'gender', 'mapelRaw'));
        } else {
            $this->createUser(compact('nip', 'nama', 'role', 'email', 'phone', 'gender', 'mapelRaw'));
        }
    }

    private function updateUser(User $user, array $data): void
    {
        $update = [
            'name'   => $data['nama'], // Selalu update nama agar gelar baru tersimpan
            'phone'  => $data['phone']  ?: $user->phone,
            'gender' => $data['gender'] ?: $user->gender,
        ];

        // Update role jika ditentukan dan user bukan admin (mencegah penurunan role admin secara tidak sengaja)
        if (! empty($data['role']) && $user->role !== 'admin') {
            $update['role'] = $data['role'];
        }

        // Update email jika diisi valid dan belum dipakai orang lain
        if (! empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) && $data['email'] !== $user->email) {
            if (! User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
                $update['email'] = $data['email'];
            }
        }

        $user->update($update);

        if (! empty($data['mapelRaw'])) {
            $user->subjects()->sync($this->resolveSubjects($data['mapelRaw']));
        }

        $this->updated++;
    }

    private function createUser(array $data): void
    {
        $role  = $data['role'] ?: 'guru';
        $nip   = preg_replace('/\s+/', '', $data['nip']);
        $email = str_replace(' ', '', strtolower($data['email'] ?? ''));

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
            $domain = ($role === 'pegawai') ? 'pegawai.sims.sch.id' : 'guru.sims.sch.id';
            $email = $nip . '@' . $domain;
            if (User::where('email', $email)->exists()) {
                $email = $nip . '.' . substr(uniqid(), -4) . '@' . $domain;
            }
        }

        $defaultPassword = $nip ?: ($email ?: 'Guru123');

        $user = User::create([
            'name'     => $data['nama'],
            'email'    => $email,
            'password' => Hash::make($defaultPassword, ['rounds' => 4]), // low rounds intentional for bulk import speed
            'role'     => $role,
            'nip'      => $nip,
            'phone'    => $data['phone']  ?: null,
            'gender'   => $data['gender'] ?: null,
        ]);

        if (! empty($data['mapelRaw'])) {
            $user->subjects()->sync($this->resolveSubjects($data['mapelRaw']));
        }

        $this->created++;
    }

    public function normalizeRole(?string $value): ?string
    {
        if (! $value) return null;
        $v = strtolower(trim($value));
        if (in_array($v, ['pegawai', 'staff', 'staf', 'tu', 'tata usaha', 'tenaga kependidikan', 'karyawan', 'tendik'])) {
            return 'pegawai';
        }
        if (in_array($v, ['guru', 'pengajar', 'pendidik', 'teacher'])) {
            return 'guru';
        }
        if (in_array($v, ['admin', 'administrator'])) {
            return 'admin';
        }
        return null;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * "Matematika, Fisika" → [id, id] — cari atau buat Subject baru per nama.
     */
    private function resolveSubjects(string $raw): array
    {
        $names = array_filter(array_map('trim', explode(',', $raw)));
        $ids   = [];

        foreach ($names as $name) {
            $key = strtolower($name);
            if (! array_key_exists($key, $this->subjectCache)) {
                $subject = Subject::whereRaw('LOWER(name) = ?', [$key])->first();

                if (! $subject) {
                    $subject = Subject::create(['name' => $name]);
                    $this->warnings[] = "Mata pelajaran '{$name}' tidak ditemukan — dibuat otomatis.";
                }

                $this->subjectCache[$key] = $subject->id;
            }
            $ids[] = $this->subjectCache[$key];
        }

        return $ids;
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
}
