<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;

$csvPath = public_path('JADWAL GURU_MAPEL HOR.csv');
$handle = fopen($csvPath, 'r');
$lines = [];
while (($data = fgetcsv($handle, 4096, ',')) !== false) {
    $lines[] = $data;
}
fclose($handle);

// 1. Extract Teacher Code Map from bottom table (row index 40 to end)
$teacherCodeToName = [];
for ($i = 40; $i < count($lines); $i++) {
    $row = $lines[$i];
    $code = trim($row[1] ?? '');
    $name = trim($row[2] ?? '');
    if (!empty($code) && !empty($name) && $code !== 'DAFTAR MATA PELAJARAN') {
        $teacherCodeToName[$code] = $name;
    }
}

// Sort teacher codes by length descending so longer codes match first (e.g. "B. DA Wisnontari" before "B. DA")
uksort($teacherCodeToName, fn($a, $b) => strlen($b) <=> strlen($a));

// 2. Match Teacher Codes to DB Teachers
$dbTeachers = User::where('role', 'guru')->get();

function cleanNameAdvanced($name) {
    $name = preg_replace('/,.*$/', '', $name);
    $name = str_replace(['A.A', 'AA', 'Gde', 'Ngr', 'Md', 'DA', 'GA', 'B.', 'P.', 'Putu', 'Wayan', 'Kadek', 'Nyoman', 'Gede', 'I', 'Ni', 'Dewa', 'Anak', 'Agung', 'Desak', 'Luh', 'Drs', 'S.Pd', 'M.Pd', 'S.Ag', 'S.Sn', 'S.Kom', 'S.Si', 'S.Sos', 'S.S'], '', $name);
    $name = preg_replace('/[^a-zA-Z]/', '', $name);
    return strtolower(trim($name));
}

function matchTeacherToDb($fullName, $dbTeachers) {
    $cleanFull = preg_replace('/[^a-zA-Z]/', '', strtolower($fullName));
    foreach ($dbTeachers as $t) {
        $cleanDb = preg_replace('/[^a-zA-Z]/', '', strtolower($t->name));
        if ($cleanFull === $cleanDb || str_contains($cleanDb, $cleanFull) || str_contains($cleanFull, $cleanDb)) {
            return $t;
        }
    }
    $advFull = cleanNameAdvanced($fullName);
    foreach ($dbTeachers as $t) {
        $advDb = cleanNameAdvanced($t->name);
        if (!empty($advFull) && !empty($advDb) && ($advFull === $advDb || str_contains($advDb, $advFull) || str_contains($advFull, $advDb))) {
            return $t;
        }
    }
    return null;
}

$codeToTeacherUser = [];
foreach ($teacherCodeToName as $code => $fullName) {
    $codeToTeacherUser[$code] = matchTeacherToDb($fullName, $dbTeachers);
}

// 3. Parse Schedule Grid Matrix (rows 6 to 37)
$parsedSchedules = [];

for ($r = 6; $r <= 37; $r++) {
    $row = $lines[$r] ?? [];
    $rawClassName = trim($row[1] ?? '');
    if (empty($rawClassName)) continue;

    // Days mapping (6 days, 11 periods each)
    // Cols 3..13 -> Senin (1)
    // Cols 14..24 -> Selasa (2)
    // Cols 25..35 -> Rabu (3)
    // Cols 36..46 -> Kamis (4)
    // Cols 47..57 -> Jumat (5)
    // Cols 58..68 -> Sabtu (6)
    
    for ($day = 1; $day <= 6; $day++) {
        $startCol = 3 + ($day - 1) * 11;
        for ($period = 1; $period <= 11; $period++) {
            $colIdx = $startCol + ($period - 1);
            $cell = trim($row[$colIdx] ?? '');
            if (empty($cell) || in_array(strtoupper($cell), ['-', 'ISTIRAHAT', 'UPACARA', 'SHOLAT'])) {
                continue;
            }

            // Extract Teacher Code & Subject
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
                // Fallback: split by space
                $parts = explode(' ', $cell, 2);
                $matchedCode = $parts[0] ?? $cell;
                $subjectCode = $parts[1] ?? '';
            }

            $teacherUser = $codeToTeacherUser[$matchedCode] ?? null;

            $parsedSchedules[] = [
                'class_raw'     => $rawClassName,
                'day'           => $day,
                'period'        => $period,
                'cell_content'  => $cell,
                'teacher_code'  => $matchedCode,
                'teacher_name'  => $teacherUser?->name ?? ($teacherCodeToName[$matchedCode] ?? $matchedCode),
                'teacher_id'    => $teacherUser?->id,
                'subject_code'  => $subjectCode,
            ];
        }
    }
}

echo "=== PARSED SCHEDULE MATRIX SUMMARY ===\n";
echo "Total schedule slots parsed: " . count($parsedSchedules) . "\n\n";
echo "Sample parsed entries:\n";
for ($i = 0; $i < min(15, count($parsedSchedules)); $i++) {
    $s = $parsedSchedules[$i];
    $tId = $s['teacher_id'] ? "ID {$s['teacher_id']}" : "NO DB MATCH";
    echo "Class {$s['class_raw']} | Day {$s['day']} | Period {$s['period']} | Cell: '{$s['cell_content']}' -> Code: '{$s['teacher_code']}', Subj: '{$s['subject_code']}', Teacher: '{$s['teacher_name']}' ($tId)\n";
}
