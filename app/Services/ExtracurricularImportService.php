<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class ExtracurricularImportService
{
    /**
     * Parse raw CSV file content and return structured preview rows with auto-matched Teacher & Student IDs.
     */
    public static function parseCsv(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        $rawRows = [];
        while (($data = fgetcsv($handle, 2000, ',')) !== false) {
            $rawRows[] = array_map('trim', $data);
        }
        fclose($handle);

        // Find header row containing "NAMA EKSTRA"
        $headerIndex = -1;
        foreach ($rawRows as $idx => $row) {
            $joined = strtoupper(implode(' ', $row));
            if (str_contains($joined, 'NAMA EKSTRA') || str_contains($joined, 'PEMBINA')) {
                $headerIndex = $idx;
                break;
            }
        }

        if ($headerIndex === -1) {
            return [];
        }

        $allTeachers = User::where('role', 'guru')->get(['id', 'name']);
        $allStudents = User::where('role', 'siswa')->get(['id', 'name']);

        $parsedGroups = [];
        $currentEkstra = null;

        for ($i = $headerIndex + 1; $i < count($rawRows); $i++) {
            $row = $rawRows[$i];
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $no           = $row[0] ?? '';
            $namaEkstra   = trim($row[1] ?? '');
            $namaPembina  = trim($row[2] ?? '');
            $namaKetua    = trim($row[3] ?? '');
            $contact      = trim($row[4] ?? '');

            // Skip CSV header rows
            if (str_contains(strtoupper($namaEkstra), 'NAMA EKSTRA') || str_contains(strtoupper($namaEkstra), 'PEMBINA DAN KETUA')) {
                continue;
            }

            // Clean title/prefix numbers if present in namaEkstra or no
            if (! empty($namaEkstra)) {
                $currentEkstra = [
                    'name'           => preg_replace('/^\d+[\s,\.]*/', '', $namaEkstra),
                    'pembinas'       => [],
                    'ketua_raw'      => $namaKetua,
                    'contact_person' => $contact,
                ];

                if (! empty($namaPembina)) {
                    $currentEkstra['pembinas'][] = $namaPembina;
                }

                $parsedGroups[] = &$currentEkstra;
                unset($currentEkstra);
            } elseif (! empty($namaPembina) && count($parsedGroups) > 0) {
                // Continuation line for additional pembina
                $lastIndex = count($parsedGroups) - 1;
                $parsedGroups[$lastIndex]['pembinas'][] = $namaPembina;
                if (! empty($namaKetua) && empty($parsedGroups[$lastIndex]['ketua_raw'])) {
                    $parsedGroups[$lastIndex]['ketua_raw'] = $namaKetua;
                }
                if (! empty($contact) && empty($parsedGroups[$lastIndex]['contact_person'])) {
                    $parsedGroups[$lastIndex]['contact_person'] = $contact;
                }
            }
        }

        // Process matching for each group
        $previewData = [];
        foreach ($parsedGroups as $group) {
            $ekstraName    = trim($group['name']);
            $contactPerson = trim($group['contact_person']);

            // Process Teachers (Pembina)
            $matchedTeachers = [];
            foreach ($group['pembinas'] as $pembinaRaw) {
                $matchedTeacherId = self::findBestUserMatch($pembinaRaw, $allTeachers);
                $matchedTeachers[] = [
                    'raw_name'   => $pembinaRaw,
                    'teacher_id' => $matchedTeacherId,
                ];
            }

            // Process Students (Ketua & Wakil Ketua)
            $rawKetuaText = trim($group['ketua_raw']);
            $studentMatches = self::splitAndMatchStudents($rawKetuaText, $allStudents);

            $previewData[] = [
                'temp_id'        => uniqid('extra_'),
                'name'           => $ekstraName,
                'contact_person' => $contactPerson,
                'pembinas'       => $matchedTeachers,
                'ketua'          => $studentMatches['ketua'] ?? null,
                'wakil_ketua'    => $studentMatches['wakil_ketua'] ?? null,
            ];
        }

        return $previewData;
    }

    public function saveExtracurriculars(array $items): int
    {
        $count = 0;
        DB::transaction(function () use ($items, &$count) {
            foreach ($items as $item) {
                if (empty($item['name'])) {
                    continue;
                }

                $teacherIds     = array_filter(array_map('intval', $item['teacher_ids'] ?? []));
                $firstTeacherId = $teacherIds[0] ?? null;

                $extra = Extracurricular::updateOrCreate(
                    ['name' => trim($item['name'])],
                    [
                        'contact_person' => $item['contact_person'] ?? null,
                        'pembina_id'     => $firstTeacherId,
                    ]
                );

                // Sync Teachers (Pembina)
                $extra->teachers()->sync($teacherIds);

                // Sync Students (Ketua & Wakil Ketua)
                $studentSync = [];
                if (! empty($item['ketua_id'])) {
                    $studentSync[(int) $item['ketua_id']] = ['role' => 'ketua'];
                }
                if (! empty($item['wakil_ketua_id'])) {
                    $studentSync[(int) $item['wakil_ketua_id']] = ['role' => 'wakil_ketua'];
                }
                $extra->students()->sync($studentSync);

                $count++;
            }
        });

        return $count;
    }

    /**
     * Split student string by '&', 'dan', or ';' and assign 1st as Ketua and 2nd as Wakil Ketua.
     */
    public static function splitAndMatchStudents(string $rawText, $allStudents): array
    {
        if (empty($rawText)) {
            return ['ketua' => null, 'wakil_ketua' => null];
        }

        // Remove phone notes in parentheses like "(Devasya)"
        $cleanText = preg_replace('/\([^\)]*\)/', '', $rawText);
        $names = preg_split('/\s*(&|dan|;)\s*/i', $cleanText);
        $names = array_values(array_filter(array_map('trim', $names)));

        $ketua = null;
        $wakil = null;

        if (isset($names[0]) && ! empty($names[0])) {
            $matchedId = self::findBestUserMatch($names[0], $allStudents);
            $ketua = [
                'raw_name'   => $names[0],
                'student_id' => $matchedId,
            ];
        }

        if (isset($names[1]) && ! empty($names[1])) {
            $matchedId = self::findBestUserMatch($names[1], $allStudents);
            $wakil = [
                'raw_name'   => $names[1],
                'student_id' => $matchedId,
            ];
        }

        return [
            'ketua'       => $ketua,
            'wakil_ketua' => $wakil,
        ];
    }

    /**
     * Find best matching User ID based on name similarity.
     */
    public static function findBestUserMatch(string $rawName, $users): ?int
    {
        $cleanRaw = self::cleanName($rawName);
        if (empty($cleanRaw)) {
            return null;
        }

        $bestId    = null;
        $bestScore = 0;

        $rawTokens = array_filter(explode(' ', $cleanRaw), fn ($w) => strlen($w) >= 3);

        foreach ($users as $user) {
            $cleanUser = self::cleanName($user->name);

            // Exact match
            if ($cleanRaw === $cleanUser) {
                return $user->id;
            }

            // Substring containment match
            if (str_contains($cleanUser, $cleanRaw) || str_contains($cleanRaw, $cleanUser)) {
                $score = 90;
            } else {
                // Token overlap score
                $userTokens   = array_filter(explode(' ', $cleanUser), fn ($w) => strlen($w) >= 3);
                $commonTokens = array_intersect($rawTokens, $userTokens);
                if (count($rawTokens) > 0 && count($commonTokens) >= min(2, count($rawTokens))) {
                    $score = 85;
                } else {
                    similar_text($cleanRaw, $cleanUser, $percent);
                    $score = $percent;
                }
            }

            if ($score >= 50 && $score > $bestScore) {
                $bestScore = $score;
                $bestId    = $user->id;
            }
        }

        return $bestId;
    }

    /**
     * Clean titles and punctuation from name.
     */
    private static function cleanName(string $name): string
    {
        // Strip academic titles like S.Pd, M.Pd, S.Sn, S.Ag, Drs, Ir, A.Md, etc.
        $cleaned = preg_replace('/\b(S\.Pd|M\.Pd|S\.Sn|S\.Ag|S\.Kom|S\.Sos|A\.Md|Drs|Dr|H|Hj|M\.Si)\b/i', '', $name);
        $cleaned = preg_replace('/[^\w\s]/', ' ', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        return strtolower(trim($cleaned));
    }
}
