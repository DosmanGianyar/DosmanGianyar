<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\ScheduleImportService();

echo "Testing ScheduleImportService instantiability...\n";
if (class_exists(\Smalot\PdfParser\Parser::class)) {
    echo "Smalot PdfParser IS INSTALLED AND READY!\n";
} else {
    echo "WARNING: Smalot PdfParser IS NOT INSTALLED!\n";
}

$file = __DIR__ . '/public/lain/JADWAL dosman  SMT GANJIL 2627 (3 Agustus 2026)_b2.xlsx';
if (file_exists($file)) {
    $items = $service->parseFile($file);
    echo "Parsed Excel sample: " . count($items) . " items!\n";
} else {
    echo "Sample file not found at " . $file . "\n";
}
