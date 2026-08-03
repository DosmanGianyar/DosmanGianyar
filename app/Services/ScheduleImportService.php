<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ScheduleImportService
{
    /**
     * Peta standar jam ke- -> [start_time, end_time]
     */
    public const TIME_SLOTS = [
        0  => ['07:10', '07:55'],
        1  => ['07:30', '08:15'],
        2  => ['08:15', '09:00'],
        3  => ['09:00', '09:45'],
        4  => ['10:00', '10:45'],
        5  => ['10:45', '11:30'],
        6  => ['11:30', '12:15'],
        7  => ['12:30', '13:15'],
        8  => ['13:15', '14:00'],
        9  => ['16:00', '16:45'],
        10 => ['17:00', '17:45'],
        11 => ['17:45', '18:30'],
    ];

    /**
     * Peta singkatan kode mapel ke nama mapel standar
     */
    public const SUBJECT_MAP = [
        'SOS'      => 'Sosiologi',
        'KKA'      => 'Kewirausahaan / KKA',
        'MAT'      => 'Matematika',
        'MAT.L'    => 'Matematika Lanjutan',
        'INDO'     => 'Bahasa Indonesia',
        'SEJ'      => 'Sejarah',
        'TIK'      => 'Informatika / TIK',
        'SENI'     => 'Seni Budaya',
        'FIS'      => 'Fisika',
        'GEO'      => 'Geografi',
        'BIO'      => 'Biologi',
        'B BALI'   => 'Bahasa Bali',
        'EKO'      => 'Ekonomi',
        'PPKN'     => 'Pendidikan Pancasila (PPKn)',
        'KIM'      => 'Kimia',
        'INGG'     => 'Bahasa Inggris',
        'ING.L'    => 'Bahasa Inggris Lanjutan',
        'AGAMA BP' => 'Pendidikan Agama & Budi Pekerti',
        'PJOK'     => 'Pendidikan Jasmani Olahraga Kesehatan',
        'PKWU'     => 'Prakarya dan Kewirausahaan (PKWU)',
        'ANTRO'    => 'Antropologi',
    ];

    /**
     * Map nama hari ke nomor (1: Senin, ..., 6: Sabtu)
     */
    public const DAY_MAP = [
        'SENIN'  => 1,
        'SELASA' => 2,
        'RABU'   => 3,
        'KAMIS'  => 4,
        'JUMAT'  => 5,
        'SABTU'  => 6,
        'MINGGU' => 7,
    ];

    /**
     * Main entry point: Parsing file CSV / Excel untuk jadwal pelajaran
     */
    public function parseFile(string $filePath, string $mimeOrExt = '', string $targetGrade = 'ALL'): array
    {
        return $this->parseCsvSchedule($filePath, $targetGrade);
    }

    /**
     * Parser Fleksibel: Mendukung Format Tabel Daftar & Format Matrix Timetable
     */
    public function parseCsvSchedule(string $filePath, string $targetGrade = 'ALL'): array
    {
        if (!file_exists($filePath)) {
            Log::error("ScheduleImportService: File non existent: {$filePath}");
            return [];
        }

        $rows = [];
        $ext  = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv') {
            $handle = fopen($filePath, 'r');
            if ($handle !== false) {
                while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            try {
                $spreadsheet = IOFactory::load($filePath);
                $sheet       = $spreadsheet->getActiveSheet();
                $rows        = $sheet->toArray(null, true, true, false);
            } catch (\Throwable $e) {
                Log::error("ScheduleImportService error loading spreadsheet: " . $e->getMessage());
                return [];
            }
        }

        if (empty($rows)) {
            return [];
        }

        // 1. Deteksi jika file menggunakan Format List/Tabel Standar (Baris per Baris: Kelas, Hari, Jam, Mapel, Guru, Ruang)
        $headerRowIdx = null;
        $colClass     = null;
        $colDay       = null;
        $colPeriod    = null;
        $colSubject   = null;
        $colTeacher   = null;
        $colRoom      = null;

        foreach ($rows as $idx => $r) {
            if ($idx > 5) break;
            $rowUpper = array_map(fn($v) => strtoupper(trim((string)$v)), $r);
            
            foreach ($rowUpper as $cIdx => $val) {
                if (in_array($val, ['KELAS', 'CLASS'])) $colClass = $cIdx;
                if (in_array($val, ['HARI', 'DAY'])) $colDay = $cIdx;
                if (in_array($val, ['JAM', 'JAM KE', 'PERIOD', 'WAKTU', 'TIME'])) $colPeriod = $cIdx;
                if (in_array($val, ['MAPEL', 'MATA PELAJARAN', 'SUBJECT', 'KODE MAPEL'])) $colSubject = $cIdx;
                if (in_array($val, ['GURU', 'NAMA GURU', 'TEACHER', 'KODE GURU'])) $colTeacher = $cIdx;
                if (in_array($val, ['RUANG', 'RUANGAN', 'ROOM'])) $colRoom = $cIdx;
            }

            if ($colClass !== null && $colSubject !== null && $colTeacher !== null) {
                $headerRowIdx = $idx;
                break;
            }
        }

        if ($headerRowIdx !== null) {
            $rawListItems = [];
            for ($i = $headerRowIdx + 1; $i < count($rows); $i++) {
                $r = $rows[$i];
                $className  = trim($r[$colClass] ?? '');
                $subjectRaw = trim($r[$colSubject] ?? '');
                $teacherRaw = trim($r[$colTeacher] ?? '');

                if (empty($className) || empty($subjectRaw)) continue;

                $dayStr = strtoupper(trim($r[$colDay] ?? '1'));
                $day    = self::DAY_MAP[$dayStr] ?? (is_numeric($dayStr) ? (int)$dayStr : 1);

                $periodVal = trim($r[$colPeriod] ?? '1');
                $period    = is_numeric($periodVal) ? (int)$periodVal : 1;
                $room      = ($colRoom !== null) ? trim($r[$colRoom] ?? '') : null;

                $rawListItems[] = [
                    'class_name'   => $className,
                    'day'          => $day > 0 ? $day : 1,
                    'period'       => $period > 0 ? $period : 1,
                    'subject_code' => $subjectRaw,
                    'teacher_raw'  => $teacherRaw,
                    'room'         => $room,
                    'target_grade' => $targetGrade,
                ];
            }

            return $this->matchEntities($rawListItems);
        }

        // 2. Parser Format Grid Matrix Timetable
        $teacherCodeToName = [];
        $totalRows         = count($rows);
        $startMappingIndex = 39;

        foreach ($rows as $idx => $r) {
            if ($idx >= 35) {
                $val = strtoupper(trim($r[1] ?? ''));
                if (str_contains($val, 'DAFTAR') || str_contains($val, 'P. SUDARMA')) {
                    $startMappingIndex = $idx;
                    break;
                }
            }
        }

        for ($i = $startMappingIndex; $i < $totalRows; $i++) {
            $r    = $rows[$i];
            $code = trim($r[1] ?? '');
            $name = trim($r[2] ?? '');
            if (!empty($code) && !empty($name) && !str_contains(strtoupper($code), 'DAFTAR')) {
                $teacherCodeToName[$code] = $name;
            }
        }

        // Urutkan kode guru berdasarkan panjang karakter terpanjang lebih dahulu
        uksort($teacherCodeToName, fn($a, $b) => strlen($b) <=> strlen($a));

        // Cocokkan Kode Guru ke Database User role='guru'
        $dbTeachers        = User::where('role', 'guru')->get();
        $codeToTeacherUser = [];
        foreach ($teacherCodeToName as $code => $fullName) {
            $codeToTeacherUser[$code] = $this->matchTeacherToDb($fullName, $dbTeachers);
        }

        $rawItems = [];

        for ($r = 6; $r < $startMappingIndex; $r++) {
            $row = $rows[$r] ?? [];
            $rawClassName = trim($row[1] ?? '');
            if (empty($rawClassName) || str_contains(strtoupper($rawClassName), 'HARI') || str_contains(strtoupper($rawClassName), 'JADWAL')) {
                continue;
            }

            // Iterate 6 hari (Senin - Sabtu), 11 jam pelajaran per hari
            for ($day = 1; $day <= 6; $day++) {
                $startCol = 2 + ($day - 1) * 11;
                for ($period = 1; $period <= 11; $period++) {
                    $colIdx = $startCol + ($period - 1);
                    $cell   = trim($row[$colIdx] ?? '');

                    if (empty($cell) || in_array(strtoupper($cell), ['-', 'ISTIRAHAT', 'UPACARA', 'SHOLAT', 'LIBUR'])) {
                        continue;
                    }

                    $matchedCode = null;
                    $subjectCode = null;

                    foreach ($teacherCodeToName as $code => $fullName) {
                        if (str_starts_with($cell, $code)) {
                            $matchedCode = $code;
                            $subjectCode = trim(substr($cell, strlen($code)));
                            break;
                        }
                    }

                    if (!$matchedCode) {
                        $parts       = explode(' ', $cell, 2);
                        $matchedCode = $parts[0] ?? $cell;
                        $subjectCode = $parts[1] ?? '';
                    }

                    $teacherUser = $codeToTeacherUser[$matchedCode] ?? null;
                    $teacherRaw  = $teacherUser?->name ?? ($teacherCodeToName[$matchedCode] ?? $matchedCode);

                    $rawItems[] = [
                        'class_name'   => $rawClassName,
                        'day'          => $day,
                        'period'       => $period,
                        'subject_code' => $subjectCode,
                        'teacher_raw'  => $teacherRaw,
                        'teacher_user' => $teacherUser,
                        'room'         => null,
                        'target_grade' => $targetGrade,
                    ];
                }
            }
        }

        return $this->matchEntities($rawItems);
    }

    /**
     * Matching Nama Guru Lengkap dari CSV ke Database User
     */
    protected function matchTeacherToDb(string $fullName, $dbTeachers): ?User
    {
        $cleanFull = preg_replace('/[^a-zA-Z]/', '', strtolower($fullName));
        foreach ($dbTeachers as $t) {
            $cleanDb = preg_replace('/[^a-zA-Z]/', '', strtolower($t->name));
            if ($cleanFull === $cleanDb || str_contains($cleanDb, $cleanFull) || str_contains($cleanFull, $cleanDb)) {
                return $t;
            }
        }

        $advFull = $this->cleanNameForMatching($fullName);
        foreach ($dbTeachers as $t) {
            $advDb = $this->cleanNameForMatching($t->name);
            if (!empty($advFull) && !empty($advDb) && ($advFull === $advDb || str_contains($advDb, $advFull) || str_contains($advFull, $advDb))) {
                return $t;
            }
        }

        return null;
    }

    protected function cleanNameForMatching(string $name): string
    {
        $name = preg_replace('/,.*$/', '', $name);
        $name = str_replace(
            ['A.A', 'AA', 'Gde', 'Ngr', 'Md', 'DA', 'GA', 'B.', 'P.', 'Putu', 'Wayan', 'Kadek', 'Nyoman', 'Gede', 'I', 'Ni', 'Dewa', 'Anak', 'Agung', 'Desak', 'Luh', 'Drs', 'S.Pd', 'M.Pd', 'S.Ag', 'S.Sn', 'S.Kom', 'S.Si', 'S.Sos', 'S.S'],
            '',
            $name
        );
        $name = preg_replace('/[^a-zA-Z]/', '', $name);
        return strtolower(trim($name));
    }

    /**
     * Normalisasi nama kelas (misal: "X1" -> "X-01", "X10" -> "X-10", "XI A" -> "XI-A", "XII B1" -> "XII-B1")
     */
    public function normalizeClassName(string $rawClassName): string
    {
        $clean = strtoupper(trim($rawClassName));
        $clean = str_replace('KELAS', '', $clean);
        $clean = trim($clean);

        if (preg_match('/^X\s*(\d+)$/i', $clean, $m)) {
            $num = str_pad((int)$m[1], 2, '0', STR_PAD_LEFT);
            return "X-{$num}";
        }

        if (preg_match('/^(XI|XII)\s*(.+)$/i', $clean, $m)) {
            $prefix = strtoupper($m[1]);
            $suffix = strtoupper(str_replace(' ', '', $m[2]));
            return "{$prefix}-{$suffix}";
        }

        if (preg_match('/^(X|XI|XII)-?0?(\d+)$/i', $clean, $m)) {
            $prefix = strtoupper($m[1]);
            $num = str_pad((int)$m[2], 2, '0', STR_PAD_LEFT);
            return "{$prefix}-{$num}";
        }

        return str_replace(' ', '-', $clean);
    }

    /**
     * Cari / Buat ID Mata Pelajaran berdasarkan kode dari CSV
     */
    public function resolveSubjectForTeacher(?User $teacher, Collection $allSubjects, ?string $rawSubjectCode = null): array
    {
        $codeUpper  = strtoupper(trim($rawSubjectCode ?? ''));
        $mappedName = self::SUBJECT_MAP[$codeUpper] ?? $codeUpper;

        if (empty($codeUpper)) {
            return ['subject_id' => null, 'allowed_subject_ids' => []];
        }

        $found = $allSubjects->first(function ($s) use ($codeUpper, $mappedName) {
            $sName = strtoupper($s->name);
            $sCode = strtoupper($s->code);
            $mName = strtoupper($mappedName);

            return $sCode === $codeUpper
                || $sName === $codeUpper
                || $sName === $mName
                || str_contains($sName, $codeUpper)
                || str_contains($sName, $mName);
        });

        if (!$found) {
            $found = Subject::create([
                'code' => $codeUpper,
                'name' => self::SUBJECT_MAP[$codeUpper] ?? $codeUpper,
            ]);
            $allSubjects->push($found);
        }

        return [
            'subject_id'          => $found->id,
            'allowed_subject_ids' => [$found->id],
        ];
    }

    /**
     * Jalankan pencocokan cerdas (Smart Matching) ke Database
     */
    public function matchEntities(array $rawItems): array
    {
        $classes  = SchoolClass::all()->keyBy(fn($c) => strtoupper(str_replace([' ', '-'], '', $c->name)));
        $subjects = Subject::all();
        $teachers = User::where('role', 'guru')->get();

        $matchedList = [];

        foreach ($rawItems as $index => $item) {
            $normalizedClassName = $this->normalizeClassName($item['class_name']);
            $cleanClassName      = strtoupper(str_replace(['KELAS', ' ', '-'], '', $item['class_name']));
            
            $targetGrade = 10;
            if (str_starts_with($normalizedClassName, 'XI-')) {
                $targetGrade = 11;
            } elseif (str_starts_with($normalizedClassName, 'XII-')) {
                $targetGrade = 12;
            }

            // 1. Match / Auto-Create Class
            $lookupKey   = str_replace([' ', '-'], '', $normalizedClassName);
            $schoolClass = $classes->get($lookupKey) ?? $classes->get($cleanClassName);

            if (! $schoolClass) {
                $schoolClass = SchoolClass::firstOrCreate(
                    ['name' => $normalizedClassName],
                    ['grade' => $targetGrade]
                );
                $classes->put($lookupKey, $schoolClass);
            }

            $classId = $schoolClass->id;

            // 2. Match Teacher
            $matchedTeacher = $item['teacher_user'] ?? null;
            $teacherRaw     = trim($item['teacher_raw']);
            if (!$matchedTeacher && !empty($teacherRaw)) {
                $matchedTeacher = $this->matchTeacherToDb($teacherRaw, $teachers);
            }

            // 3. Match Subject
            $subjCode = strtoupper(trim($item['subject_code']));
            $subjRes  = $this->resolveSubjectForTeacher($matchedTeacher, $subjects, $subjCode);

            $matchedSubject = $subjRes['subject_id'] ? $subjects->find($subjRes['subject_id']) : null;
            $subjName       = self::SUBJECT_MAP[$subjCode] ?? $subjCode;

            $matchedList[] = [
                'temp_id'             => 'item_' . $index,
                'class_name'          => $schoolClass->name,
                'class_id'            => $classId,
                'day'                 => $item['day'],
                'period'              => $item['period'],
                'start_time'          => self::TIME_SLOTS[$item['period']][0] ?? '07:30',
                'end_time'            => self::TIME_SLOTS[$item['period']][1] ?? '08:15',
                'subject_code'        => $subjCode,
                'subject_id'          => $subjRes['subject_id'],
                'subject_name'        => $matchedSubject?->name ?? $subjName,
                'allowed_subject_ids' => $subjRes['allowed_subject_ids'],
                'teacher_raw'         => $teacherRaw,
                'teacher_id'          => $matchedTeacher?->id,
                'teacher_name'        => $matchedTeacher?->name ?? $teacherRaw,
                'match_confidence'    => $matchedTeacher ? 'exact' : 'unmatched',
                'room'                => $item['room'] ?? null,
            ];
        }

        return $matchedList;
    }

    /**
     * Simpan data jadwal yang telah dikonfirmasi ke Database
     */
    public function saveSchedules(array $confirmedItems, string $academicYear, bool $replaceExisting = true): int
    {
        return DB::transaction(function () use ($confirmedItems, $academicYear, $replaceExisting) {
            if ($replaceExisting) {
                Schedule::where('academic_year', $academicYear)->delete();
            }

            $count = 0;
            foreach ($confirmedItems as $item) {
                if (empty($item['class_id']) || empty($item['subject_id'])) {
                    continue;
                }

                Schedule::create([
                    'class_id'      => $item['class_id'],
                    'subject_id'    => $item['subject_id'],
                    'teacher_id'    => $item['teacher_id'] ?: null,
                    'day'           => (int) $item['day'],
                    'period'        => (int) $item['period'],
                    'start_time'    => $item['start_time'] ?? '07:30:00',
                    'end_time'      => $item['end_time'] ?? '08:15:00',
                    'room'          => $item['room'] ?? null,
                    'academic_year' => $academicYear,
                ]);

                $count++;
            }

            return $count;
        });
    }

    /**
     * Buat akun Guru baru secara instan jika belum ada di DB
     */
    public function createDraftTeacher(string $rawName): User
    {
        $cleanName = trim(preg_replace('/^(P\.|B\.|Pak|Bu|Ibu)\s+/i', '', $rawName));
        $username  = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanName));
        
        return User::create([
            'name'                 => $rawName,
            'email'                => $username . rand(100, 999) . '@sman1gianyar.sch.id',
            'username'             => $username . rand(10, 99),
            'password'             => bcrypt('password123'),
            'role'                 => 'guru',
            'must_change_password' => true,
        ]);
    }
}
