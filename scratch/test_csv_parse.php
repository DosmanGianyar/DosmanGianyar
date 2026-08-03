<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$csvPath = public_path('JADWAL GURU_MAPEL HOR.csv');
$handle = fopen($csvPath, 'r');
$lines = [];
while (($data = fgetcsv($handle, 4096, ',')) !== false) {
    $lines[] = $data;
}
fclose($handle);

$teacherCodeToName = [];
for ($i = 40; $i < count($lines); $i++) {
    $row = $lines[$i];
    $code = trim($row[1] ?? '');
    $name = trim($row[2] ?? '');
    if (!empty($code) && !empty($name) && $code !== 'DAFTAR MATA PELAJARAN') {
        $teacherCodeToName[$code] = $name;
    }
}

$dbTeachers = User::where('role', 'guru')->get();

function cleanNameAdvanced($name) {
    $name = preg_replace('/,.*$/', '', $name); // remove titles after comma
    $name = str_replace(['A.A', 'AA', 'Gde', 'Ngr', 'Md', 'DA', 'GA', 'B.', 'P.', 'Putu', 'Wayan', 'Kadek', 'Nyoman', 'Gede', 'I', 'Ni', 'Dewa', 'Anak', 'Agung', 'Desak', 'Luh', 'Drs', 'S.Pd', 'M.Pd', 'S.Ag', 'S.Sn', 'S.Kom', 'S.Si', 'S.Sos', 'S.S'], '', $name);
    $name = preg_replace('/[^a-zA-Z]/', '', $name);
    return strtolower(trim($name));
}

function matchTeacher($fullName, $dbTeachers) {
    // 1. Direct clean match
    $cleanFull = preg_replace('/[^a-zA-Z]/', '', strtolower($fullName));
    foreach ($dbTeachers as $t) {
        $cleanDb = preg_replace('/[^a-zA-Z]/', '', strtolower($t->name));
        if ($cleanFull === $cleanDb || str_contains($cleanDb, $cleanFull) || str_contains($cleanFull, $cleanDb)) {
            return [$t, 'direct'];
        }
    }

    // 2. Token / Advanced clean match
    $advFull = cleanNameAdvanced($fullName);
    $bestMatch = null;
    $maxScore = 0;

    foreach ($dbTeachers as $t) {
        $advDb = cleanNameAdvanced($t->name);
        if (empty($advFull) || empty($advDb)) continue;
        if ($advFull === $advDb || str_contains($advDb, $advFull) || str_contains($advFull, $advDb)) {
            return [$t, 'advanced'];
        }
        similar_text($advFull, $advDb, $score);
        if ($score > $maxScore && $score > 60) {
            $maxScore = $score;
            $bestMatch = $t;
        }
    }

    return [$bestMatch, 'fuzzy (' . round($maxScore) . '%)'];
}

echo "=== MATCHING ALL 65 TEACHERS ===\n";
$matched = 0;
foreach ($teacherCodeToName as $code => $fullName) {
    [$found, $method] = matchTeacher($fullName, $dbTeachers);
    if ($found) {
        $matched++;
        echo "✅ [$code] '$fullName' => DB ID {$found->id}: '{$found->name}' ($method)\n";
    } else {
        echo "❌ [$code] '$fullName' => NO MATCH FOUND!\n";
    }
}

echo "\nResult: $matched / " . count($teacherCodeToName) . " matched!\n";
