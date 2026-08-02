<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
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
    ];

    /**
     * Peta singkatan kode mapel ke nama mapel standar
     */
    public const SUBJECT_MAP = [
        'SOS'      => 'Sosiologi',
        'KKA'      => 'Kewirausahaan / KKA',
        'MAT'      => 'Matematika',
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
        'AGAMA BP' => 'Pendidikan Agama & Budi Pekerti',
        'PJOK'     => 'Pendidikan Jasmani Olahraga Kesehatan',
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
     * Main entry point: Parsing file Excel untuk jadwal pelajaran
     */
    public function parseFile(string $filePath, string $mimeOrExt = '', string $grade = 'ALL'): array
    {
        return $this->parseExcel($filePath, $grade);
    }

    /**
     * Parsing file Excel (.xlsx, .xls, .csv) dari export aSc Timetables atau format tabel/grid
     */
    public function parseExcel(string $filePath, string $targetGrade = 'ALL'): array
    {
        $rawItems = [];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetCount  = $spreadsheet->getSheetCount();

            // Periksa setiap sheet untuk menemukan data jadwal (Matrix Grid atau List)
            for ($i = 0; $i < $sheetCount; $i++) {
                $sheet      = $spreadsheet->getSheet($i);
                $sheetItems = $this->parseWorksheet($sheet, $targetGrade);
                if (!empty($sheetItems)) {
                    $rawItems = array_merge($rawItems, $sheetItems);
                }
            }
        } catch (\Throwable $e) {
            Log::error("ScheduleImportService parseExcel Error: " . $e->getMessage());
        }

        // Jika cara Spreadsheet belum mendapatkan item, coba via Maatwebsite Excel array
        if (empty($rawItems)) {
            try {
                $sheets = Excel::toArray([], $filePath);
                foreach ($sheets as $rows) {
                    $sheetItems = $this->parseRowsArray($rows, $targetGrade);
                    if (!empty($sheetItems)) {
                        $rawItems = array_merge($rawItems, $sheetItems);
                    }
                }
            } catch (\Throwable $e) {
                Log::error("ScheduleImportService parseRowsArray Error: " . $e->getMessage());
            }
        }

        return $this->matchEntities($rawItems);
    }

    /**
     * Parsing sheet PhpSpreadsheet secara spesifik (Mendukung Tipe Matrix Grid & Tipe Daftar Tabel)
     */
    protected function parseWorksheet($sheet, string $targetGrade): array
    {
        $rawItems        = [];
        $highestRow      = (int) $sheet->getHighestRow();
        $highestCol      = $sheet->getHighestColumn();
        $highestColIndex = (int) \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        if ($highestRow < 2 || $highestColIndex < 2) {
            return [];
        }

        // 1. Deteksi Tipe Grid Matrix (Cari baris header nama-nama kelas, e.g., Row 1..10)
        $gridHeaderRow = null;
        $classColumns  = []; // colIndex => ClassName
        $dayCol        = null;
        $periodCol     = null;

        for ($r = 1; $r <= min(10, $highestRow); $r++) {
            $colsWithClass = [];
            for ($c = 1; $c <= $highestColIndex; $c++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $val       = trim((string)$sheet->getCell($colLetter . $r)->getFormattedValue());
                $valUpper  = strtoupper($val);

                if (in_array($valUpper, ['HARI', 'DAY'])) {
                    $dayCol = (int) $c;
                } elseif (in_array($valUpper, ['JAM', 'PERIOD', 'WAKTU', 'TIME', 'JAM KE'])) {
                    if (!$periodCol) $periodCol = (int) $c;
                } elseif (preg_match('/^(X|XI|XII)[-\s]?\d{1,2}$/i', $val) || preg_match('/^(XI|XII)[-\s]?[A-Z]\d?$/i', $val)) {
                    $colsWithClass[$c] = $this->normalizeClassName($val);
                }
            }

            if (count($colsWithClass) >= 2) { // Ditemukan minimal 2 kolom kelas
                $gridHeaderRow = (int) $r;
                $classColumns  = $colsWithClass;
                break;
            }
        }

        // Jika ini adalah Tipe Matrix Grid (Hari, Jam, Kolom Kelas)
        if (!is_null($gridHeaderRow) && !empty($classColumns)) {
            $currentDay = 1;

            $startRow = ((int) $gridHeaderRow) + 1;
            for ($r = $startRow; $r <= $highestRow; $r++) {
                // Deteksi Hari
                if ($dayCol) {
                    $dayLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dayCol);
                    $dayVal    = strtoupper(trim((string)$sheet->getCell($dayLetter . $r)->getFormattedValue()));
                    if (!empty($dayVal) && isset(self::DAY_MAP[$dayVal])) {
                        $currentDay = self::DAY_MAP[$dayVal];
                    }
                }

                // Deteksi Jam / Period
                $period = 1;
                if ($periodCol) {
                    $periodLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($periodCol);
                    $periodVal    = trim((string)$sheet->getCell($periodLetter . $r)->getFormattedValue());
                    if (is_numeric($periodVal)) {
                        $period = (int)$periodVal;
                    }
                }

                // Iterasi kolom-kolom kelas
                foreach ($classColumns as $cIndex => $className) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex);
                    $cellVal   = trim((string)$sheet->getCell($colLetter . $r)->getFormattedValue());

                    if (empty($cellVal) || in_array(strtoupper($cellVal), ['UP', 'ISTIRAHAT', 'UPACARA', 'SHOLAT', '-'])) {
                        continue;
                    }

                    // Parse isi sel (misal: "AGAMA BP 63", "MAT 29", "SOS 42", "FISIKA (P. Madra)")
                    $parsedCell = $this->parseCellContent($cellVal);
                    if ($parsedCell['subject_code']) {
                        $rawItems[] = [
                            'class_name'   => $className,
                            'day'          => $currentDay,
                            'period'       => $period,
                            'subject_code' => $parsedCell['subject_code'],
                            'teacher_raw'  => $parsedCell['teacher_raw'],
                            'room'         => $parsedCell['room'] ?? null,
                            'target_grade' => $targetGrade,
                        ];
                    }
                }
            }

            return $rawItems;
        }

        // 2. Tipe Daftar / Table List (Baris per Baris)
        $headers = [];
        for ($c = 1; $c <= $highestColIndex; $c++) {
            $colLetter      = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $hVal           = strtoupper(trim((string)$sheet->getCell($colLetter . '1')->getFormattedValue()));
            $headers[$hVal] = $c;
        }

        $colClass = $headers['KELAS'] ?? $headers['CLASS'] ?? 1;
        $colGuru  = $headers['GURU'] ?? $headers['NAMA GURU'] ?? $headers['TEACHER'] ?? null;
        $colMapel = $headers['MATA PELAJARAN'] ?? $headers['MAPEL'] ?? $headers['SUBJECT'] ?? null;
        $colHari  = $headers['HARI'] ?? $headers['DAY'] ?? null;
        $colJam   = $headers['JAM'] ?? $headers['PERIOD'] ?? null;

        if ($colGuru && $colMapel) {
            for ($r = 2; $r <= $highestRow; $r++) {
                $cValLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colClass);
                $className  = trim((string)$sheet->getCell($cValLetter . $r)->getFormattedValue());
                if (empty($className)) continue;

                $gValLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colGuru);
                $teacherRaw = trim((string)$sheet->getCell($gValLetter . $r)->getFormattedValue());

                $mValLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colMapel);
                $subjRaw    = trim((string)$sheet->getCell($mValLetter . $r)->getFormattedValue());

                $day = 1;
                if ($colHari) {
                    $hValLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colHari);
                    $dayStr     = strtoupper(trim((string)$sheet->getCell($hValLetter . $r)->getFormattedValue()));
                    $day        = self::DAY_MAP[$dayStr] ?? (is_numeric($dayStr) ? (int)$dayStr : 1);
                }

                $period = 1;
                if ($colJam) {
                    $jValLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colJam);
                    $jStr       = trim((string)$sheet->getCell($jValLetter . $r)->getFormattedValue());
                    if (is_numeric($jStr)) $period = (int)$jStr;
                }

                if (!empty($subjRaw) && !empty($teacherRaw)) {
                    $rawItems[] = [
                        'class_name'   => $this->normalizeClassName($className),
                        'day'          => $day,
                        'period'       => $period,
                        'subject_code' => $subjRaw,
                        'teacher_raw'  => $teacherRaw,
                        'room'         => null,
                        'target_grade' => $targetGrade,
                    ];
                }
            }
        }

        return $rawItems;
    }

    /**
     * Fallback parsing dari Maatwebsite Excel array
     */
    protected function parseRowsArray(array $rows, string $targetGrade): array
    {
        $rawItems = [];
        if (count($rows) < 2) return [];

        foreach ($rows as $rIndex => $row) {
            if ($rIndex < 2) continue; // Skip header
            $className = trim($row[0] ?? '');
            if (empty($className)) continue;

            $day        = (int) ($row[1] ?? 1);
            $period     = (int) ($row[2] ?? 1);
            $subjectRaw = trim($row[3] ?? '');
            $teacherRaw = trim($row[4] ?? '');
            $room       = trim($row[5] ?? '');

            if ($subjectRaw) {
                $rawItems[] = [
                    'class_name'   => $this->normalizeClassName($className),
                    'day'          => $day > 0 ? $day : 1,
                    'period'       => $period > 0 ? $period : 1,
                    'subject_code' => $subjectRaw,
                    'teacher_raw'  => $teacherRaw,
                    'room'         => $room,
                    'target_grade' => $targetGrade,
                ];
            }
        }

        return $rawItems;
    }

    /**
     * Ekstraksi Mapel & Kode/Nama Guru dari isi sel (misal: "AGAMA BP 63", "MAT 29", "SOS 42")
     */
    protected function parseCellContent(string $cellContent): array
    {
        $clean = trim($cellContent);
        
        // Pola 1: "AGAMA BP 63", "SOS 42", "MAT 29", "FIS 68"
        if (preg_match('/^([A-Z\s]+)\s+(\d+)$/i', $clean, $matches)) {
            return [
                'subject_code' => trim($matches[1]),
                'teacher_raw'  => trim($matches[2]),
                'room'         => null,
            ];
        }

        // Pola 2: "FISIKA (Pak Madra)"
        if (preg_match('/^([A-Z\s]+)\s*\(([^)]+)\)$/i', $clean, $matches)) {
            return [
                'subject_code' => trim($matches[1]),
                'teacher_raw'  => trim($matches[2]),
                'room'         => null,
            ];
        }

        // Pola 3: Kata pertama adalah kode mapel, sisanya nama/kode guru
        $parts = explode(' ', $clean, 2);
        return [
            'subject_code' => trim($parts[0] ?? $clean),
            'teacher_raw'  => trim($parts[1] ?? ''),
            'room'         => null,
        ];
    }

    /**
     * Normalisasi nama kelas Excel (misal: "X1" -> "X-01", "X10" -> "X-10", "XI2" -> "XI-02")
     */
    public function normalizeClassName(string $rawClassName): string
    {
        $clean = strtoupper(trim(str_replace(['KELAS', ' '], '', $rawClassName)));

        if (preg_match('/^(X|XI|XII)-?0?(\d+)$/i', $clean, $matches)) {
            $prefix          = strtoupper($matches[1]);
            $number          = (int) $matches[2];
            $formattedNumber = str_pad($number, 2, '0', STR_PAD_LEFT);
            return "{$prefix}-{$formattedNumber}"; // e.g. "X-01", "X-02", "X-10"
        }

        return $clean;
    }

    /**
     * Cari ID Mata Pelajaran berdasarkan data Guru dan/atau kode dari Excel
     */
    public function resolveSubjectForTeacher(?User $teacher, Collection $allSubjects, ?string $rawSubjectCode = null): array
    {
        $allowedSubjectIds = [];
        $matchedSubjectId  = null;

        // 1. Cek mata pelajaran yang terdaftar pada profil Guru di DB
        if ($teacher && ! empty($teacher->subject)) {
            $tSubjNames = array_map('trim', explode(',', $teacher->subject));

            foreach ($tSubjNames as $tName) {
                $mappedName = self::SUBJECT_MAP[strtoupper($tName)] ?? $tName;

                $found = $allSubjects->first(function ($s) use ($tName, $mappedName) {
                    $sName      = strtoupper($s->name);
                    $sCode      = strtoupper($s->code);
                    $tNameUpper = strtoupper($tName);
                    $mNameUpper = strtoupper($mappedName);

                    return $sCode === $tNameUpper
                        || $sCode === $mNameUpper
                        || $sName === $tNameUpper
                        || $sName === $mNameUpper
                        || str_contains($sName, $tNameUpper)
                        || str_contains($tNameUpper, $sName)
                        || str_contains($sName, $mNameUpper);
                });

                if ($found) {
                    $allowedSubjectIds[] = $found->id;
                }
            }
        }

        $allowedSubjectIds = array_values(array_unique($allowedSubjectIds));

        // Jika Guru di DB HANYA mengampu 1 mata pelajaran -> OTOMATIS PILIH MAPEL TERSEBUT!
        if (count($allowedSubjectIds) === 1) {
            $matchedSubjectId = $allowedSubjectIds[0];
        } else {
            // Cari berdasarkan kode dari Excel
            if ($rawSubjectCode) {
                $codeUpper  = strtoupper(trim($rawSubjectCode));
                $mappedName = self::SUBJECT_MAP[$codeUpper] ?? $codeUpper;

                $foundFromExcel = $allSubjects->first(function ($s) use ($codeUpper, $mappedName, $allowedSubjectIds) {
                    if (! empty($allowedSubjectIds) && ! in_array($s->id, $allowedSubjectIds)) {
                        return false;
                    }

                    $sName = strtoupper($s->name);
                    $sCode = strtoupper($s->code);

                    return $sCode === $codeUpper
                        || $sName === $codeUpper
                        || $sName === strtoupper($mappedName)
                        || str_contains($sName, $codeUpper)
                        || str_contains($sName, strtoupper($mappedName));
                });

                if ($foundFromExcel) {
                    $matchedSubjectId = $foundFromExcel->id;
                }
            }

            // Fallback
            if (! $matchedSubjectId && ! empty($allowedSubjectIds)) {
                $matchedSubjectId = $allowedSubjectIds[0];
            }
        }

        return [
            'subject_id'          => $matchedSubjectId,
            'allowed_subject_ids' => $allowedSubjectIds,
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
            $targetGrade         = (int) ($item['target_grade'] ?? 10);
            
            // 1. Match / Auto-Create Class (misal X1 -> X-01)
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
            $teacherRaw     = trim($item['teacher_raw']);
            $teacherMatch   = $this->findBestTeacherMatch($teacherRaw, $teachers);
            $matchedTeacher = $teacherMatch['user'];

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
                'match_confidence'    => $teacherMatch['confidence'],
                'room'                => $item['room'] ?? null,
            ];
        }

        return $matchedList;
    }

    /**
     * Pencocokan nama guru dengan algoritma Fuzzy & Token Matching
     */
    protected function findBestTeacherMatch(string $rawName, $teachers): array
    {
        if (empty($rawName)) {
            return ['user' => null, 'confidence' => 'unmatched'];
        }

        $cleanName = preg_replace('/^(P\.|B\.|Pak|Bu|Ibu)\s+/i', '', $rawName);
        $cleanName = trim(preg_replace('/[^a-zA-Z\s]/', '', $cleanName));

        if (empty($cleanName)) {
            return ['user' => null, 'confidence' => 'unmatched'];
        }

        $bestUser     = null;
        $highestScore = 0;

        foreach ($teachers as $teacher) {
            $tName = preg_replace('/[^a-zA-Z\s]/', '', $teacher->name);
            
            if (str_contains(strtolower($tName), strtolower($cleanName))) {
                return ['user' => $teacher, 'confidence' => 'exact'];
            }

            similar_text(strtolower($cleanName), strtolower($tName), $percent);
            if ($percent > $highestScore) {
                $highestScore = $percent;
                $bestUser     = $teacher;
            }
        }

        if ($highestScore >= 65 && $bestUser) {
            return ['user' => $bestUser, 'confidence' => 'fuzzy'];
        }

        return ['user' => null, 'confidence' => 'unmatched'];
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
