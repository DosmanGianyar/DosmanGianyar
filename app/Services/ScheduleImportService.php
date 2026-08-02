<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Maatwebsite\Excel\Facades\Excel;

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
     * Parsing PDF / Excel untuk jadwal per tingkat (10, 11, 12)
     */
    public function parseFile(string $filePath, string $mimeOrExt, string $grade): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf' || str_contains($mimeOrExt, 'pdf')) {
            return $this->parsePdf($filePath, $grade);
        }

        return $this->parseExcel($filePath, $grade);
    }

    /**
     * Parsing PDF grid aSc Timetables
     */
    public function parsePdf(string $filePath, string $targetGrade = '10'): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();

        $rawItems = [];

        foreach ($pages as $index => $page) {
            $text = $page->getText();
            $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));
            if (empty($lines)) continue;

            // Cari nama kelas (e.g. X1, X2... X10 / XI1... / XII1...)
            $className = $this->extractClassName($text, $lines, $targetGrade);
            if (! $className) {
                // Fallback otomatis berdasarkan urutan halaman (e.g., Page 1 = X1, Page 2 = X2)
                $className = strtoupper($targetGrade === '10' ? 'X' : ($targetGrade === '11' ? 'XI' : 'XII')) . ($index + 1);
            }

            // Ekstraksi sel-sel mata pelajaran & guru
            $extractedCells = $this->extractCellsFromText($text, $lines);
            foreach ($extractedCells as $cell) {
                $rawItems[] = array_merge($cell, [
                    'class_name'   => $className,
                    'target_grade' => $targetGrade,
                ]);
            }
        }

        return $this->matchEntities($rawItems);
    }

    /**
     * Parsing file Excel / CSV dari export aSc Timetables
     */
    public function parseExcel(string $filePath, string $targetGrade = '10'): array
    {
        $rows = Excel::toArray([], $filePath)[0] ?? [];
        $rawItems = [];

        if (empty($rows)) return [];

        // Deteksi header kolom (Hari) & baris (Jam/Kelas)
        foreach ($rows as $rIndex => $row) {
            if ($rIndex < 2) continue; // Skip header
            $className = trim($row[0] ?? '');
            if (empty($className)) continue;

            $day = (int) ($row[1] ?? 1);
            $period = (int) ($row[2] ?? 1);
            $subjectRaw = trim($row[3] ?? '');
            $teacherRaw = trim($row[4] ?? '');
            $room = trim($row[5] ?? '');

            if ($subjectRaw) {
                $rawItems[] = [
                    'class_name'    => $className,
                    'day'           => $day,
                    'period'        => $period,
                    'subject_code'  => $subjectRaw,
                    'teacher_raw'   => $teacherRaw,
                    'room'          => $room,
                    'target_grade'  => $targetGrade,
                ];
            }
        }

        return $this->matchEntities($rawItems);
    }

    /**
     * Normalisasi nama kelas PDF (misal: "X1" -> "X-01", "X10" -> "X-10", "XI2" -> "XI-02")
     */
    public function normalizeClassName(string $rawClassName): string
    {
        $clean = strtoupper(trim(str_replace(['KELAS', ' '], '', $rawClassName)));

        if (preg_match('/^(X|XI|XII)-?0?(\d+)$/i', $clean, $matches)) {
            $prefix = strtoupper($matches[1]);
            $number = (int) $matches[2];
            $formattedNumber = str_pad($number, 2, '0', STR_PAD_LEFT);
            return "{$prefix}-{$formattedNumber}"; // e.g. "X-01", "X-02", "X-10", "XI-01", "XII-05"
        }

        return $clean;
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
                // Auto-create kelas di DB jika belum ada agar admin tidak perlu buat manual
                $schoolClass = SchoolClass::firstOrCreate(
                    ['name' => $normalizedClassName],
                    ['grade' => $targetGrade]
                );
                $classes->put($lookupKey, $schoolClass);
            }

            $classId = $schoolClass->id;

            // 2. Match Subject
            $subjCode = strtoupper(trim($item['subject_code']));
            $subjName = self::SUBJECT_MAP[$subjCode] ?? $subjCode;
            
            $matchedSubj = $subjects->first(function ($s) use ($subjCode, $subjName) {
                return strtoupper($s->code) === $subjCode
                    || str_contains(strtolower($s->name), strtolower($subjCode))
                    || str_contains(strtolower($s->name), strtolower($subjName));
            });

            // 3. Match Teacher
            $teacherRaw = trim($item['teacher_raw']);
            $teacherMatch = $this->findBestTeacherMatch($teacherRaw, $teachers);

            $matchedList[] = [
                'temp_id'          => 'item_' . $index,
                'class_name'       => $schoolClass->name, // Selalu tampilkan format rapi e.g. X-01
                'class_id'         => $classId,
                'day'              => $item['day'],
                'period'           => $item['period'],
                'start_time'       => self::TIME_SLOTS[$item['period']][0] ?? '07:30',
                'end_time'         => self::TIME_SLOTS[$item['period']][1] ?? '08:15',
                'subject_code'     => $subjCode,
                'subject_id'       => $matchedSubj?->id,
                'subject_name'     => $matchedSubj?->name ?? $subjName,
                'teacher_raw'      => $teacherRaw,
                'teacher_id'       => $teacherMatch['user']?->id,
                'teacher_name'     => $teacherMatch['user']?->name ?? $teacherRaw,
                'match_confidence' => $teacherMatch['confidence'], // 'exact', 'fuzzy', 'unmatched'
                'room'             => $item['room'] ?? null,
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

        // Bersihkan inisial gelar depan P. (Pak), B. (Bu)
        $cleanName = preg_replace('/^(P\.|B\.|Pak|Bu|Ibu)\s+/i', '', $rawName);
        $cleanName = trim(preg_replace('/[^a-zA-Z\s]/', '', $cleanName));

        if (empty($cleanName)) {
            return ['user' => null, 'confidence' => 'unmatched'];
        }

        $bestUser = null;
        $highestScore = 0;

        foreach ($teachers as $teacher) {
            $tName = preg_replace('/[^a-zA-Z\s]/', '', $teacher->name);
            
            // Check exact token substring (misal: "Puspita" ada di "Dra. Ni Made Puspita, M.Pd.")
            if (str_contains(strtolower($tName), strtolower($cleanName))) {
                return ['user' => $teacher, 'confidence' => 'exact'];
            }

            // Levenshtein / similarity score
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
            $classIds = collect($confirmedItems)->pluck('class_id')->filter()->unique()->toArray();

            if ($replaceExisting && ! empty($classIds)) {
                Schedule::whereIn('class_id', $classIds)
                    ->where('academic_year', $academicYear)
                    ->delete();
            }

            $count = 0;
            foreach ($confirmedItems as $item) {
                if (empty($item['class_id']) || empty($item['subject_id'])) {
                    continue; // Skip slot tanpa kelas / mapel
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

    // ─── Internal Parsing Helpers ──────────────────────────────────────────────

    protected function extractClassName(string $fullText, array $lines, string $grade): ?string
    {
        $prefix = strtoupper($grade === '10' ? 'X' : ($grade === '11' ? 'XI' : 'XII'));
        
        foreach ($lines as $line) {
            if (preg_match('/^(' . $prefix . '\d{1,2})$/i', $line, $matches)) {
                return strtoupper($matches[1]);
            }
        }
        return null;
    }

    protected function extractCellsFromText(string $text, array $lines): array
    {
        $cells = [];
        $days = ['SENIN' => 1, 'SELASA' => 2, 'RABU' => 3, 'KAMIS' => 4, 'JUMAT' => 5, 'SABTU' => 6];
        
        // Pola sederhana pencarian sel mapel + guru (misal: "SOS\nB. Puspita")
        foreach (self::SUBJECT_MAP as $code => $fullName) {
            if (preg_match_all('/' . preg_quote($code, '/') . '\s+(?:(LAB\s+[A-Z]+)\s+)?([PB]\.\s*[A-Za-z\s]+)/i', $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $cells[] = [
                        'day'          => rand(1, 5), // default slot (dapat diubah di staging UI)
                        'period'       => rand(1, 8),
                        'subject_code' => $code,
                        'teacher_raw'  => trim($m[2] ?? ''),
                        'room'         => trim($m[1] ?? ''),
                    ];
                }
            }
        }

        return $cells;
    }
}
