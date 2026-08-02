<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
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
     * Parsing PDF grid aSc Timetables (Mendukung Jadwal Per Kelas & Jadwal Per Guru)
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

            // Deteksi 1: Cek apakah ini PDF Tipe "Jadwal Per Guru" (Header: Guru [Nama] / Teacher [Nama])
            $teacherHeader = $this->extractTeacherHeader($text, $lines);

            if ($teacherHeader) {
                // Tipe Jadwal Per Guru: Nama Guru Lengkap ada di Header Halaman
                $extractedCells = $this->extractCellsFromTeacherPdf($page, $teacherHeader);
                foreach ($extractedCells as $cell) {
                    $rawItems[] = array_merge($cell, [
                        'teacher_raw'  => $teacherHeader,
                        'target_grade' => $targetGrade,
                    ]);
                }
            } else {
                // Tipe Jadwal Per Kelas: Nama Kelas ada di Header Halaman (e.g. X1, X2... X10)
                $className = $this->extractClassName($text, $lines, $targetGrade);
                if (! $className) {
                    $className = strtoupper($targetGrade === '10' ? 'X' : ($targetGrade === '11' ? 'XI' : 'XII')) . ($index + 1);
                }

                $extractedCells = $this->extractCellsFromText($text, $lines);
                foreach ($extractedCells as $cell) {
                    $rawItems[] = array_merge($cell, [
                        'class_name'   => $className,
                        'target_grade' => $targetGrade,
                    ]);
                }
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
     * Cari ID Mata Pelajaran berdasarkan data Guru dan/atau kode dari PDF
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
            // Jika ada lebih dari 1 mapel atau belum terdaftar di profil guru, cari berdasarkan kode dari PDF
            if ($rawSubjectCode) {
                $codeUpper  = strtoupper(trim($rawSubjectCode));
                $mappedName = self::SUBJECT_MAP[$codeUpper] ?? $codeUpper;

                $foundFromPdf = $allSubjects->first(function ($s) use ($codeUpper, $mappedName, $allowedSubjectIds) {
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

                if ($foundFromPdf) {
                    $matchedSubjectId = $foundFromPdf->id;
                }
            }

            // Fallback: jika masih null tapi ada allowedSubjectIds
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
                // Auto-create kelas di DB jika belum ada agar admin tidak perlu buat manual
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

            // 3. Match Subject berdasarkan data Guru & Kode PDF
            $subjCode = strtoupper(trim($item['subject_code']));
            $subjRes  = $this->resolveSubjectForTeacher($matchedTeacher, $subjects, $subjCode);

            $matchedSubject = $subjRes['subject_id'] ? $subjects->find($subjRes['subject_id']) : null;
            $subjName       = self::SUBJECT_MAP[$subjCode] ?? $subjCode;

            $matchedList[] = [
                'temp_id'             => 'item_' . $index,
                'class_name'          => $schoolClass->name, // Selalu tampilkan format rapi e.g. X-01
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
                'match_confidence'    => $teacherMatch['confidence'], // 'exact', 'fuzzy', 'unmatched'
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

            if ($replaceExisting) {
                Schedule::where('academic_year', $academicYear)->delete();
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
            $str = is_array($line) ? implode(' ', $line) : (string) $line;
            if (preg_match('/^(' . $prefix . '\d{1,2})$/i', trim($str), $matches)) {
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

    protected function extractTeacherHeader(string $fullText, array $lines): ?string
    {
        foreach ($lines as $line) {
            $str = is_array($line) ? implode(' ', $line) : (string) $line;
            $str = trim($str);
            if (preg_match('/^(Guru|Teacher)\s+(.+)$/i', $str, $matches)) {
                return trim($matches[2]);
            }
        }
        return null;
    }

    protected function extractCellsFromTeacherPdf($page, string $teacherName): array
    {
        $cells = [];

        try {
            $dataTm = $page->getDataTm();
        } catch (\Throwable $e) {
            $dataTm = [];
        }

        if (empty($dataTm)) {
            return $cells;
        }

        // Kumpulkan token teks beserta koordinat X dan Y
        $tokens = [];
        foreach ($dataTm as $tm) {
            $raw    = $tm[0] ?? '';
            $rawStr = is_array($raw) ? implode(' ', array_map('trim', $raw)) : (string) $raw;
            $text   = trim($rawStr);

            $matrix = $tm[1] ?? [];
            $x      = floatval($matrix[4] ?? 0);
            $y      = floatval($matrix[5] ?? 0);

            if ($text !== '') {
                $tokens[] = ['text' => $text, 'x' => $x, 'y' => $y];
            }
        }

        // Identifikasi token yang merupakan Nama Kelas (misal: XII C1, XII C2, XI A, X1, dst.)
        $classPattern = '/^(X\d{1,2}|XI\s+[A-Z0-9]+|XII\s+[A-Z0-9]+|X-\d{1,2}|XI-[A-Z0-9]+|XII-[A-Z0-9]+)$/i';

        foreach ($tokens as $item) {
            if (preg_match($classPattern, $item['text'], $matches)) {
                $className = strtoupper($matches[1]);
                $x = $item['x'];
                $y = $item['y'];

                // 1. Tentukan HARI berdasarkan koordinat X
                // (Lebar Halaman ~840pt, X Header: Senin ~150, Selasa ~270, Rabu ~390, Kamis ~510, Jumat ~630, Sabtu ~750)
                $day = 1;
                if ($x < 210) {
                    $day = 1; // Senin
                } elseif ($x < 330) {
                    $day = 2; // Selasa
                } elseif ($x < 450) {
                    $day = 3; // Rabu
                } elseif ($x < 570) {
                    $day = 4; // Kamis
                } elseif ($x < 690) {
                    $day = 5; // Jumat
                } else {
                    $day = 6; // Sabtu
                }

                // 2. Tentukan JAM / PERIOD berdasarkan koordinat Y
                // (Tinggi ~595pt: Jam 0~470, Jam 1~430, Jam 2~395, Jam 3~360, Jam 4~325, Jam 5~290, Jam 6~255, Jam 7~220, Jam 8~185, Jam 9~150, Jam 10~115)
                $period = 1;
                if ($y > 450) {
                    $period = 0;
                } elseif ($y > 415) {
                    $period = 1;
                } elseif ($y > 380) {
                    $period = 2;
                } elseif ($y > 345) {
                    $period = 3;
                } elseif ($y > 310) {
                    $period = 4;
                } elseif ($y > 275) {
                    $period = 5;
                } elseif ($y > 240) {
                    $period = 6;
                } elseif ($y > 205) {
                    $period = 7;
                } elseif ($y > 170) {
                    $period = 8;
                } elseif ($y > 135) {
                    $period = 9;
                } else {
                    $period = 10;
                }

                // 3. Cari Kode Mapel yang berdekatan dengan koordinat X, Y sel ini
                $subjectCode = null;
                $room        = null;

                foreach ($tokens as $t) {
                    if (abs($t['x'] - $x) < 60 && abs($t['y'] - $y) < 35) {
                        $txtUpper = strtoupper($t['text']);

                        if (str_contains($txtUpper, 'LAB ')) {
                            $room = $txtUpper;
                        }

                        foreach (self::SUBJECT_MAP as $code => $fullName) {
                            if ($txtUpper === $code || $txtUpper === strtoupper($fullName)) {
                                $subjectCode = $code;
                                break;
                            }
                        }
                    }
                }

                $cells[] = [
                    'day'          => $day,
                    'period'       => $period,
                    'subject_code' => $subjectCode ?: 'MAT',
                    'class_name'   => $className,
                    'room'         => $room,
                ];
            }
        }

        return $cells;
    }
}
