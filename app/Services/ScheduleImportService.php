<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TujuanPembelajaran;
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

        // 2. Parser Format Grid Matrix Timetable (seperti jadwal 5okt.xlsx)
        return $this->parseMatrixSchedule($rows, $targetGrade);
    }

    /**
     * Normalisasi kode guru (mengatasi variasi spasi titik & typo)
     */
    public static function normalizeTeacherCode(string $raw): string
    {
        $c = trim($raw);
        $c = preg_replace('/\s*\.\s*/', '. ', $c);
        $c = preg_replace('/\s+/', ' ', $c);

        if (strcasecmp($c, 'P. Suwita') === 0) return 'P. Suwila';
        if (strcasecmp($c, 'P. Ngr Afriadii') === 0) return 'P. Ngr Afriadi';

        return $c;
    }

    /**
     * Parser Format Grid Matrix Timetable (seperti jadwal 5okt.xlsx)
     */
    public function parseMatrixSchedule(array $rows, string $targetGrade = 'ALL'): array
    {
        $dayRowIdx    = null;
        $periodRowIdx = null;
        $timeRowIdx   = null;

        // Cari baris hari, jam, waktu di 10 baris pertama
        foreach ($rows as $idx => $r) {
            if ($idx > 10) break;
            $rowUpper = array_map(fn($v) => strtoupper(trim((string)$v)), $r);

            // Cek apakah ada nama hari di baris ini
            $dayCount = 0;
            foreach ($rowUpper as $val) {
                if (in_array($val, ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'])) {
                    $dayCount++;
                }
            }

            if ($dayCount >= 2 && $dayRowIdx === null) {
                $dayRowIdx = $idx;
                continue;
            }

            if ($dayRowIdx !== null && $periodRowIdx === null) {
                $numCount = 0;
                foreach ($r as $val) {
                    if (is_numeric(trim((string)$val)) && (int)$val >= 1 && (int)$val <= 12) {
                        $numCount++;
                    }
                }
                if ($numCount >= 5) {
                    $periodRowIdx = $idx;
                    continue;
                }
            }

            if ($periodRowIdx !== null && $timeRowIdx === null) {
                // Baris waktu (misal "7:10 - 7:55" atau "07:30 - 08:15")
                $timeMatchCount = 0;
                foreach ($r as $val) {
                    if (str_contains((string)$val, '-') && preg_match('/\d{1,2}:\d{2}/', (string)$val)) {
                        $timeMatchCount++;
                    }
                }
                if ($timeMatchCount >= 3) {
                    $timeRowIdx = $idx;
                    break;
                }
            }
        }

        if ($dayRowIdx === null) {
            return [];
        }

        $dayRow    = $rows[$dayRowIdx] ?? [];
        $periodRow = ($periodRowIdx !== null) ? ($rows[$periodRowIdx] ?? []) : [];
        $timeRow   = ($timeRowIdx !== null) ? ($rows[$timeRowIdx] ?? []) : [];

        // Petakan kolom-kolom jadwal (dimulai dari kolom ke-3 / index 2)
        $columnsMeta = [];
        $currentDayName   = 'SENIN';
        $currentDayNumber = 1;
        $maxCols = max(count($dayRow), count($periodRow), count($timeRow));

        for ($c = 2; $c < $maxCols; $c++) {
            $cellDay = strtoupper(trim((string)($dayRow[$c] ?? '')));
            if (!empty($cellDay) && isset(self::DAY_MAP[$cellDay])) {
                $currentDayName   = $cellDay;
                $currentDayNumber = self::DAY_MAP[$cellDay];
            }

            $cellPeriodRaw = trim((string)($periodRow[$c] ?? ''));
            $period = is_numeric($cellPeriodRaw) ? (int)$cellPeriodRaw : 0;
            if ($period <= 0) {
                $period = (($c - 2) % 11) + 1;
            }

            $cellTime  = trim((string)($timeRow[$c] ?? ''));
            $startTime = null;
            $endTime   = null;

            if (!empty($cellTime) && str_contains($cellTime, '-')) {
                $times = explode('-', $cellTime);
                if (!empty($times[0]) && !empty($times[1])) {
                    $st = strtotime(trim($times[0]));
                    $et = strtotime(trim($times[1]));
                    if ($st !== false) $startTime = date('H:i:s', $st);
                    if ($et !== false) $endTime   = date('H:i:s', $et);
                }
            }

            if (!$startTime || !$endTime) {
                $startTime = self::TIME_SLOTS[$period][0] ?? '07:30:00';
                $endTime   = self::TIME_SLOTS[$period][1] ?? '08:15:00';
            }

            $columnsMeta[$c] = [
                'day'        => $currentDayNumber,
                'day_name'   => $currentDayName,
                'period'     => $period,
                'start_time' => $startTime,
                'end_time'   => $endTime,
            ];
        }

        // Mulai baris data kelas (setelah baris waktu/jam)
        $startClassRowIdx = max($dayRowIdx, $periodRowIdx ?? 0, $timeRowIdx ?? 0) + 1;
        $rawItems = [];
        $emptyRowCount = 0;

        for ($r = $startClassRowIdx; $r < count($rows); $r++) {
            $row = $rows[$r] ?? [];
            $rawClassName = trim((string)($row[1] ?? ''));

            if (empty($rawClassName)) {
                $emptyRowCount++;
                if ($emptyRowCount >= 15) break;
                continue;
            }
            $emptyRowCount = 0;

            if (in_array(strtoupper($rawClassName), ['HARI', 'JADWAL GURU', 'JAM', 'WAKTU', 'KODE', 'DAFTAR'])) {
                continue;
            }

            foreach ($columnsMeta as $colIdx => $meta) {
                $cellVal = trim((string)($row[$colIdx] ?? ''));
                if (empty($cellVal) || in_array(strtoupper($cellVal), ['-', 'LIBUR', 'UPACARA', 'ISTIRAHAT', 'SHOLAT'])) {
                    continue;
                }

                $rawItems[] = [
                    'class_name'   => $rawClassName,
                    'day'          => $meta['day'],
                    'period'       => $meta['period'],
                    'start_time'   => $meta['start_time'],
                    'end_time'     => $meta['end_time'],
                    'subject_code' => null,
                    'teacher_raw'  => $cellVal,
                    'room'         => null,
                    'target_grade' => $targetGrade,
                ];
            }
        }

        return $this->matchEntities($rawItems);
    }

    /**
     * Matching Nama Guru Lengkap dari CSV ke Database User
     */
    protected function matchTeacherToDb(string $fullName, $dbTeachers): ?User
    {
        $cleanFull = strtolower(preg_replace('/[^a-zA-Z]/', '', preg_replace('/,.*$/', '', $fullName)));
        if (empty($cleanFull)) {
            return null;
        }

        // 1. Exact match on normalized full names (without degrees/punctuation)
        foreach ($dbTeachers as $t) {
            $cleanDb = strtolower(preg_replace('/[^a-zA-Z]/', '', preg_replace('/,.*$/', '', $t->name)));
            if ($cleanFull === $cleanDb) {
                return $t;
            }
        }

        // 2. High-precision similarity match (>= 85%) on full names
        $bestMatch = null;
        $bestScore = 0;

        foreach ($dbTeachers as $t) {
            $cleanDb = strtolower(preg_replace('/[^a-zA-Z]/', '', preg_replace('/,.*$/', '', $t->name)));
            if (empty($cleanDb)) continue;

            similar_text($cleanFull, $cleanDb, $percent);
            if ($percent > $bestScore && $percent >= 85) {
                $bestScore = $percent;
                $bestMatch = $t;
            }
        }

        return $bestMatch;
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
            $sCode = strtoupper($s->code ?? '');
            $mName = strtoupper($mappedName);

            return ($sCode !== '' && $sCode === $codeUpper)
                || $sName === $codeUpper
                || $sName === $mName;
        });

        if (!$found) {
            return [
                'subject_id'          => null,
                'allowed_subject_ids' => [],
            ];
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
        $teachers = User::whereIn('role', ['guru', 'wali_kelas', 'kepala_sekolah', 'admin'])
            ->with('subjects')
            ->get();

        $defaultSubjectMap = [
            'P. SUDARMA'   => ['Pendidikan Agama & Budi Pekerti', 'AGAMA BP'],
            'P. Sutarjana' => ['Pendidikan Agama & Budi Pekerti', 'AGAMA BP'],
            'B. Lindawati' => ['Pendidikan Agama & Budi Pekerti', 'AGAMA BP'],
            'B. Eka W'     => ['MATEMATIKA TINGKAT LANJUT', 'MTKTL'],
        ];

        // Cache existing schedules to preserve historical subject pairings
        $existingSchedules = Schedule::whereNotNull('subject_id')
            ->get(['class_id', 'teacher_id', 'subject_id'])
            ->groupBy(fn($s) => $s->class_id . '_' . $s->teacher_id);

        // Cache TP per teacher to infer subject by grade when teacher has multiple subjects
        $teacherTps = TujuanPembelajaran::whereNotNull('subject_id')
            ->where('is_active', true)
            ->get(['teacher_id', 'subject_id', 'grade_level'])
            ->groupBy('teacher_id');

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
            $normCode       = self::normalizeTeacherCode($teacherRaw);

            if (!$matchedTeacher && !empty($teacherRaw)) {
                // 2a. Match by teacher_code
                $matchedTeacher = $teachers->first(fn($t) => $t->teacher_code && strcasecmp(trim($t->teacher_code), $normCode) === 0);

                // 2b. Match by name
                if (!$matchedTeacher) {
                    $matchedTeacher = $this->matchTeacherToDb($teacherRaw, $teachers);
                }
            }

            // 3. Match Subject
            $subjCode          = strtoupper(trim($item['subject_code'] ?? ''));
            $matchedSubject    = null;
            $allowedSubjectIds = [];

            if (!empty($subjCode)) {
                $subjRes           = $this->resolveSubjectForTeacher($matchedTeacher, $subjects, $subjCode);
                $matchedSubject    = $subjRes['subject_id'] ? $subjects->find($subjRes['subject_id']) : null;
                $allowedSubjectIds = $subjRes['allowed_subject_ids'];
            } elseif ($matchedTeacher) {
                $allowedSubjectIds = $matchedTeacher->subjects->pluck('id')->all();

                if ($matchedTeacher->subjects->count() === 1) {
                    $matchedSubject = $matchedTeacher->subjects->first();
                } elseif ($matchedTeacher->subjects->count() > 1) {
                    // 1. Cek apakah guru sudah membuat TP khusus untuk tingkat kelas ini (10/11/12) pada salah satu mapelnya
                    $tpsForTeacher = $teacherTps->get($matchedTeacher->id, collect())
                        ->whereIn('subject_id', $allowedSubjectIds);
                    $gradeTpSubjIds = $tpsForTeacher
                        ->filter(fn($tp) => (string) $tp->grade_level === (string) $targetGrade)
                        ->pluck('subject_id')
                        ->unique();

                    if ($gradeTpSubjIds->count() === 1) {
                        $matchedSubject = $subjects->find($gradeTpSubjIds->first());
                    }

                    // 2. Cek riwayat jadwal kelas ini (hanya jika termasuk dalam mapel yang diampu guru)
                    if (!$matchedSubject) {
                        $prevSch = $existingSchedules->get($classId . '_' . $matchedTeacher->id)?->first();
                        if ($prevSch && in_array($prevSch->subject_id, $allowedSubjectIds, true)) {
                            $matchedSubject = $subjects->find($prevSch->subject_id);
                        }
                    }

                    // 3. Jika guru hanya membuat TP pada 1 mapel saja secara keseluruhan
                    if (!$matchedSubject) {
                        $allTpSubjIds = $tpsForTeacher->pluck('subject_id')->unique();
                        if ($allTpSubjIds->count() === 1) {
                            $matchedSubject = $subjects->find($allTpSubjIds->first());
                        }
                    }

                    // 4. Heuristik Tingkat Kelas (Kelas 10 Umum vs Kelas 11/12 Tingkat Lanjut)
                    if (!$matchedSubject) {
                        $isGrade10 = ($targetGrade === 10 || str_starts_with($normalizedClassName, 'X-'));
                        if ($isGrade10) {
                            $matchedSubject = $matchedTeacher->subjects->first(fn($s) => !str_contains(strtoupper($s->name), 'LANJUT') && !str_contains(strtoupper($s->name), 'PKWU') && !str_contains(strtoupper($s->name), 'KODING'));
                        } else {
                            $matchedSubject = $matchedTeacher->subjects->first(fn($s) => str_contains(strtoupper($s->name), 'LANJUT') || str_contains(strtoupper($s->name), 'TINGKAT LANJUT'));
                        }
                    }

                    if (!$matchedSubject) {
                        $matchedSubject = $matchedTeacher->subjects->first();
                    }
                } elseif (isset($defaultSubjectMap[$normCode])) {
                    [$dName, $dCode] = $defaultSubjectMap[$normCode];
                    $matchedSubject = $subjects->first(fn($s) => $s->name === $dName || $s->code === $dCode || str_contains($s->name, $dName));
                    if ($matchedSubject) {
                        $allowedSubjectIds = [$matchedSubject->id];
                    }
                }
            }

            if ($matchedSubject && !in_array($matchedSubject->id, $allowedSubjectIds, true)) {
                $allowedSubjectIds[] = $matchedSubject->id;
            }

            $matchedList[] = [
                'temp_id'             => 'item_' . $index,
                'class_name'          => $schoolClass->name,
                'class_id'            => $classId,
                'day'                 => (int) $item['day'],
                'period'              => (int) $item['period'],
                'start_time'          => $item['start_time'] ?? (self::TIME_SLOTS[$item['period']][0] ?? '07:30:00'),
                'end_time'            => $item['end_time'] ?? (self::TIME_SLOTS[$item['period']][1] ?? '08:15:00'),
                'subject_code'        => $subjCode ?: ($matchedSubject?->code ?? '-'),
                'subject_id'          => $matchedSubject?->id,
                'subject_name'        => $matchedSubject?->name ?? '— Pilih Mata Pelajaran —',
                'allowed_subject_ids' => $allowedSubjectIds,
                'teacher_raw'         => $teacherRaw,
                'teacher_code'        => $normCode,
                'teacher_id'          => $matchedTeacher?->id,
                'teacher_name'        => $matchedTeacher?->name ?? $teacherRaw,
                'match_confidence'    => ($matchedTeacher && $matchedSubject) ? 'exact' : ($matchedTeacher ? 'partial' : 'unmatched'),
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
