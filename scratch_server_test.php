<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\ScheduleImportService();
$file = __DIR__ . '/public/lain/JADWAL dosman  SMT GANJIL 2627 (3 Agustus 2026)_b2.xlsx';
$items = $service->parseFile($file);

echo "SERVER TEST EXCEL RESULT: " . count($items) . " items parsed successfully!\n";
