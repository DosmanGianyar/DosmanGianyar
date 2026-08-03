<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\ScheduleImportService;

$dbTeachers = User::where('role', 'guru')->get();
echo "Total Teachers with role='guru' in DB: " . $dbTeachers->count() . "\n";

if ($dbTeachers->count() > 0) {
    echo "Sample Teachers in DB:\n";
    foreach ($dbTeachers->take(10) as $t) {
        echo "  - {$t->name} (Email: {$t->email}, NIP/Username: {$t->username})\n";
    }
} else {
    echo "NO TEACHERS found in DB with role='guru'.\n";
}

$service = new ScheduleImportService();
$filePath = base_path('public/JADWAL GURU_MAPEL HOR.csv');

if (file_exists($filePath)) {
    $items = $service->parseCsvSchedule($filePath);
    $matched = array_filter($items, fn($i) => !empty($i['teacher_id']));
    $unmatched = array_filter($items, fn($i) => empty($i['teacher_id']));
    echo "Schedule Items Test:\n";
    echo "  - Total Items: " . count($items) . "\n";
    echo "  - Matched to DB Teacher: " . count($matched) . "\n";
    echo "  - Unmatched (Need creation/matching): " . count($unmatched) . "\n";
}
