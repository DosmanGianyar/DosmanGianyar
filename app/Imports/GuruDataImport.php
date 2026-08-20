<?php

namespace App\Imports;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class GuruDataImport implements ToCollection, SkipsEmptyRows
{
    public int   $created   = 0;
    public int   $updated   = 0;
    public int   $unchanged = 0;
    public int   $skipped   = 0;
    public array $errors    = [];
    public array $warnings  = [];

    private array $subjectCache = [];

    public function collection(Collection $rows): void
    {
        [$headerIndex, $colMap] = $this->detectHeader($rows);

        if ($colMap === null) {
            $this->errors[] = 'Header kolom tidak ditemukan. Pastikan file Excel memiliki kolom NIP / Nama.';
            return;
        }

        foreach ($rows->slice($headerIndex + 1) as $rowOffset => $row) {
            $lineNum = $headerIndex + $rowOffset + 2;
            $this->processRow($row, $colMap, $lineNum);
        }
    }

    private function detectHeader(Collection $rows): array
    {
        foreach ($rows->take(15) as $i => $row) {
            $normalized = $row->map(fn ($v) => $this->normalizeKey((string) $v));
            if ($normalized->contains('nip') || $normalized->contains('nama')) {
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
        $nip   = $this->pick($row, $colMap, ['nip']);
        $nama  = $this->pick($row, $colMap, ['nama', 'namalengkap', 'namaguru']);
        $email = strtolower($this->pick($row, $colMap, ['email', 'surel']));

        if (! $nip && ! $nama && ! $email) {
            $this->skipped++;
            return;
        }

        // Search existing guru by NIP or Email
        $existing = null;
        if ($nip) {
            $existing = User::where('nip', $nip)->whereIn('role', ['guru', 'admin'])->first();
        }
        if (! $existing && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existing = User::where('email', $email)->whereIn('role', ['guru', 'admin'])->first();
        }

        $role     = strtolower($this->pick($row, $colMap, ['role'])) ?: null;
        $phone    = User::formatPhoneNumber($this->pick($row, $colMap, ['nohp', 'telepon', 'notelp', 'hp']));
        $gender   = $this->normalizeGender($this->pick($row, $colMap, ['jeniskelamin', 'jk', 'gender', 'lp']));
        $mapelRaw = $this->pick($row, $colMap, ['matapelajaran', 'mapel', 'subjects', 'subject']);

        $newSubjectIds = $mapelRaw ? $this->resolveSubjects($mapelRaw) : null;

        if ($existing) {
            // Existing teacher: Check for changes
            $changes = [];

            if ($nama && $this->isDifferent($existing->name, $nama)) {
                $changes['name'] = $nama;
            }
            if ($nip && $this->isDifferent($existing->nip, $nip)) {
                $changes['nip'] = $nip;
            }
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) && $this->isDifferent($existing->email, $email)) {
                if (! User::where('email', $email)->where('id', '!=', $existing->id)->exists()) {
                    $changes['email'] = $email;
                }
            }
            if ($role && in_array($role, ['guru', 'admin'], true) && $this->isDifferent($existing->role, $role)) {
                $changes['role'] = $role;
            }
            if ($gender && $this->isDifferent($existing->gender, $gender)) {
                $changes['gender'] = $gender;
            }
            if ($phone !== null && $this->isDifferent($existing->phone, $phone)) {
                $changes['phone'] = $phone;
            }

            // Check subjects change
            $subjectsChanged = false;
            if ($newSubjectIds !== null) {
                $currentSubjectIds = $existing->subjects->pluck('id')->sort()->values()->all();
                $targetSubjectIds  = collect($newSubjectIds)->sort()->values()->all();
                if ($currentSubjectIds !== $targetSubjectIds) {
                    $subjectsChanged = true;
                }
            }

            if (! empty($changes) || $subjectsChanged) {
                if (! empty($changes)) {
                    $existing->update($changes);
                }
                if ($subjectsChanged) {
                    $existing->subjects()->sync($newSubjectIds);
                }
                $this->updated++;
            } else {
                $this->unchanged++;
            }
        } else {
            // New Teacher: Create
            if (! $nama) {
                $this->errors[] = "Baris {$lineNum}: nama kosong — guru baru tidak dapat dibuat.";
                $this->skipped++;
                return;
            }

            if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
                $emailKey = $nip ?: ('guru' . rand(1000, 9999));
                $email = $emailKey . '@guru.sims.sch.id';
            }

            $defaultPassword = $nip ?: $email;

            $user = User::create([
                'name'     => $nama,
                'email'    => $email,
                'password' => Hash::make($defaultPassword, ['rounds' => 4]),
                'role'     => in_array($role, ['guru', 'admin'], true) ? $role : 'guru',
                'nip'      => $nip ?: null,
                'phone'    => $phone ?: null,
                'gender'   => $gender ?: null,
            ]);

            if ($newSubjectIds) {
                $user->subjects()->sync($newSubjectIds);
            }

            $this->created++;
        }
    }

    private function isDifferent(mixed $oldVal, mixed $newVal): bool
    {
        $oldStr = trim((string) $oldVal);
        $newStr = trim((string) $newVal);

        return $oldStr !== $newStr;
    }

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

        return array_unique($ids);
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
