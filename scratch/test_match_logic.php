<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\ScheduleImportService;

$service = new ScheduleImportService();
$filePath = base_path('public/JADWAL GURU_MAPEL HOR.csv');

// Let's test current matching vs precise matching
$dbTeachers = User::where('role', 'guru')->get();

echo "Testing teacher matching for all CSV teachers against DB teachers (" . $dbTeachers->count() . " in DB):\n\n";

$rows = [];
$handle = fopen($filePath, 'r');
if ($handle !== false) {
    while (($data = fgetcsv($handle, 4096, ',')) !== false) {
        $rows[] = $data;
    }
    fclose($handle);
}

$teacherCodeToName = [];
for ($i = 40; $i < count($rows); $i++) {
    $r = $rows[$i];
    $code = trim($r[1] ?? '');
    $name = trim($r[2] ?? '');
    if (!empty($code) && !empty($name) && !str_contains(strtoupper($code), 'DAFTAR')) {
        $teacherCodeToName[$code] = $name;
    }
}

foreach ($teacherCodeToName as $code => $csvFullName) {
    $cleanCsv = strtolower(preg_replace('/[^a-zA-Z]/', '', preg_replace('/,.*$/', '', $csvFullName)));
    
    $bestMatch = null;
    $bestScore = 0;
    
    foreach ($dbTeachers as $t) {
        $cleanDb = strtolower(preg_replace('/[^a-zA-Z]/', '', preg_replace('/,.*$/', '', $t->name)));
        
        // Exact normalized name match
        if ($cleanCsv === $cleanDb) {
            $bestMatch = $t;
            $bestScore = 100;
            break;
        }
        
        // Similar text percentage
        similar_text($cleanCsv, $cleanDb, $percent);
        if ($percent > $bestScore && $percent >= 80) {
            $bestScore = $percent;
            $bestMatch = $t;
        }
    }
    
    echo sprintf("Code: %-18s | CSV: %-40s | Matched DB: %s (Score: %d%%)\n", 
        $code, 
        $csvFullName, 
        $bestMatch ? $bestMatch->name : 'NONE',
        $bestScore
    );
}
