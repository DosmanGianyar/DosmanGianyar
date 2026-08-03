<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ScheduleImportService;
use App\Models\Schedule;

$service = new ScheduleImportService();
$csvPath = public_path('JADWAL GURU_MAPEL HOR.csv');

echo "=== IMPORTING SCHEDULE FROM JADWAL GURU_MAPEL HOR.csv ===\n";
$items = $service->parseCsvSchedule($csvPath, 'ALL');
echo "Parsed " . count($items) . " schedule items.\n";

$savedCount = $service->saveSchedules($items, '2026/2027 Ganjil', true);
echo "Successfully saved {$savedCount} schedule records to database!\n";
echo "Total Schedule records in DB: " . Schedule::count() . "\n";
