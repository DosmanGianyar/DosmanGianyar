<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TeacherCodeImportService
{
    /**
     * Import and apply teacher codes from an Excel/CSV file.
     *
     * @param string $filePath Absolute path to the spreadsheet file
     * @param string $mode 'update' (only modify provided codes) or 'replace' (fully synchronize codes, clearing others)
     * @return array
     */
    public function import(string $filePath, string $mode = 'update'): array
    {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'message' => 'File tidak ditemukan.',
                'total'   => 0,
                'updated' => 0,
                'cleared' => 0,
                'unchanged' => 0,
                'not_found' => 0,
            ];
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            Log::error("TeacherCodeImportService load error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
                'total'   => 0,
                'updated' => 0,
                'cleared' => 0,
                'unchanged' => 0,
                'not_found' => 0,
            ];
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => 'File Excel kosong.',
                'total'   => 0,
                'updated' => 0,
                'cleared' => 0,
                'unchanged' => 0,
                'not_found' => 0,
            ];
        }

        // 1. Detect Header Row
        [$headerIdx, $colMap] = $this->detectHeader($rows);
        if ($colMap === null) {
            return [
                'success' => false,
                'message' => 'Header kolom tidak dikenali. Pastikan file memiliki kolom kode_guru dan nama_guru atau nip.',
                'total'   => 0,
                'updated' => 0,
                'cleared' => 0,
                'unchanged' => 0,
                'not_found' => 0,
            ];
        }

        // Preload teachers
        $teachers = User::whereIn('role', ['guru', 'wali_kelas', 'kepala_sekolah', 'admin'])->get();

        $touchedUserIds = [];
        $updatedCount   = 0;
        $clearedCount   = 0;
        $unchangedCount = 0;
        $notFoundRows   = [];
        $totalRows      = 0;

        DB::beginTransaction();
        try {
            for ($i = $headerIdx + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $codeRaw = $this->pickValue($row, $colMap, ['kodeguru', 'kode_guru', 'kode', 'teachercode']);
                $nipRaw  = $this->pickValue($row, $colMap, ['nip']);
                $nameRaw = $this->pickValue($row, $colMap, ['namaguru', 'nama_guru', 'nama', 'name', 'namalengkap']);
                $mailRaw = $this->pickValue($row, $colMap, ['email', 'surel']);

                if (empty($codeRaw) && empty($nipRaw) && empty($nameRaw)) {
                    continue;
                }

                $totalRows++;

                // 2. Identify Teacher in Database
                $matchedTeacher = null;

                // 2a. Match by NIP
                $cleanNip = preg_replace('/\s+/', '', (string) $nipRaw);
                if (!empty($cleanNip)) {
                    $matchedTeacher = $teachers->first(fn($t) => preg_replace('/\s+/', '', (string)$t->nip) === $cleanNip);
                }

                // 2b. Match by Email
                if (!$matchedTeacher && !empty($mailRaw) && filter_var($mailRaw, FILTER_VALIDATE_EMAIL)) {
                    $matchedTeacher = $teachers->first(fn($t) => strcasecmp(trim($t->email), trim($mailRaw)) === 0);
                }

                // 2c. Match by Name
                if (!$matchedTeacher && !empty($nameRaw)) {
                    $normTarget = TeacherCodeSyncService::normalizeName($nameRaw);
                    $matchedTeacher = $teachers->first(fn($t) => TeacherCodeSyncService::normalizeName($t->name) === $normTarget);

                    if (!$matchedTeacher) {
                        $matchedTeacher = $teachers->first(function ($t) use ($normTarget) {
                            $normDb = TeacherCodeSyncService::normalizeName($t->name);
                            return str_contains($normDb, $normTarget) || str_contains($normTarget, $normDb);
                        });
                    }
                }

                if (!$matchedTeacher) {
                    $notFoundRows[] = [
                        'line' => $i + 1,
                        'name' => $nameRaw,
                        'nip'  => $nipRaw,
                        'code' => $codeRaw,
                    ];
                    continue;
                }

                $touchedUserIds[] = $matchedTeacher->id;

                $targetCode = trim((string) $codeRaw);
                if (in_array($targetCode, ['-', '', '—'], true)) {
                    $targetCode = null;
                }

                // Mode: UPDATE vs REPLACE
                if ($mode === 'update') {
                    // Update only if non-empty code provided
                    if ($targetCode !== null) {
                        if ($matchedTeacher->teacher_code !== $targetCode) {
                            $matchedTeacher->teacher_code = $targetCode;
                            $matchedTeacher->saveQuietly();
                            $updatedCount++;
                        } else {
                            $unchangedCount++;
                        }
                    } else {
                        $unchangedCount++;
                    }
                } else {
                    // Mode: REPLACE
                    if ($matchedTeacher->teacher_code !== $targetCode) {
                        $matchedTeacher->teacher_code = $targetCode;
                        $matchedTeacher->saveQuietly();
                        if ($targetCode === null) {
                            $clearedCount++;
                        } else {
                            $updatedCount++;
                        }
                    } else {
                        $unchangedCount++;
                    }
                }
            }

            // In replace mode, any active teacher with a code who wasn't listed in the file gets their code cleared
            if ($mode === 'replace') {
                $unlistedWithCode = User::whereIn('role', ['guru', 'wali_kelas', 'kepala_sekolah', 'admin'])
                    ->whereNotNull('teacher_code')
                    ->whereNotIn('id', $touchedUserIds)
                    ->get();

                foreach ($unlistedWithCode as $ut) {
                    $ut->teacher_code = null;
                    $ut->saveQuietly();
                    $clearedCount++;
                }
            }

            DB::commit();

            return [
                'success'   => true,
                'mode'      => $mode,
                'total'     => $totalRows,
                'updated'   => $updatedCount,
                'cleared'   => $clearedCount,
                'unchanged' => $unchangedCount,
                'not_found' => count($notFoundRows),
                'not_found_details' => $notFoundRows,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("TeacherCodeImportService error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(),
                'total'   => $totalRows,
                'updated' => 0,
                'cleared' => 0,
                'unchanged' => 0,
                'not_found' => 0,
            ];
        }
    }

    private function detectHeader(array $rows): array
    {
        foreach (array_slice($rows, 0, 10, true) as $i => $row) {
            $normalized = array_map(fn($v) => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$v)), $row);
            $hasCode = false;
            $hasNameOrNip = false;

            $colMap = [];
            foreach ($normalized as $idx => $key) {
                if ($key !== '') {
                    $colMap[$key] = $idx;
                    if (str_contains($key, 'kode') || str_contains($key, 'teacher')) {
                        $hasCode = true;
                    }
                    if (str_contains($key, 'nip') || str_contains($key, 'nama')) {
                        $hasNameOrNip = true;
                    }
                }
            }

            if ($hasCode && $hasNameOrNip) {
                return [$i, $colMap];
            }
        }

        return [0, null];
    }

    private function pickValue(array $row, array $colMap, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            $normalizedKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $key));
            if (isset($colMap[$normalizedKey])) {
                $val = trim((string)($row[$colMap[$normalizedKey]] ?? ''));
                if ($val !== '') {
                    return $val;
                }
            }
        }
        return null;
    }
}
