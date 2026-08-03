<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;

$csvPath = public_path('JADWAL GURU_MAPEL HOR.csv');
if (!file_exists($csvPath)) {
    die("CSV file not found at $csvPath\n");
}

$handle = fopen($csvPath, 'r');
$lines = [];
while (($data = fgetcsv($handle, 4096, ',')) !== false) {
    $lines[] = $data;
}
fclose($handle);

echo "Total CSV rows: " . count($lines) . "\n";

// 1. Extract Teacher Code Map from bottom table (rows from row index 40 to end)
$teacherCodeToName = [];
for ($i = 40; $i < count($lines); $i++) {
    $row = $lines[$i];
    $code = trim($row[1] ?? '');
    $name = trim($row[2] ?? '');
    if (!empty($code) && !empty($name) && $code !== 'DAFTAR MATA PELAJARAN') {
        $teacherCodeToName[$code] = $name;
    }
}

echo "Found " . count($teacherCodeToName) . " teacher codes in CSV:\n";
foreach ($teacherCodeToName as $code => $fullName) {
    echo "  - [$code] => $fullName\n";
}

// 2. Match CSV Teacher Full Names against Database Teachers (User role=guru)
$dbTeachers = User::where('role', 'guru')->get();
echo "\n--- TEACHER NAME MATCHING ---\n";
$matchedTeachers = 0;
$unmatchedTeachers = 0;

function cleanName($name) {
    $name = preg_replace('/,.*$/', '', $name); // remove titles after comma
    $name = preg_replace('/\b(S\.Pd|M\.Pd|S\.Ag|S\.Sn|S\.Kom|S\.Si|S\.Sos|S\.S|Drs|M\.Si)\b/i', '', $name);
    $name = preg_replace('/[^a-zA-Z\s]/', '', $name);
    return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
}

foreach ($teacherCodeToName as $code => $fullName) {
    $cleanFull = cleanName($fullName);
    $found = null;
    foreach ($dbTeachers as $t) {
        $cleanDb = cleanName($t->name);
        if ($cleanFull === $cleanDb || str_contains($cleanDb, $cleanFull) || str_contains($cleanFull, $cleanDb)) {
            $found = $t;
            break;
        }
    }
    if ($found) {
        $matchedTeachers++;
        echo "✅ [$code] '$fullName' => DB Teacher ID {$found->id}: '{$found->name}'\n";
    } else {
        $unmatchedTeachers++;
        echo "❌ [$code] '$fullName' => NO MATCH IN DB!\n";
    }
}

echo "\nSummary: Matched $matchedTeachers / " . count($teacherCodeToName) . " teachers ($unmatchedTeachers unmatched)\n";
