<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Imports\SiswaDataImport;
use Maatwebsite\Excel\Facades\Excel;

echo "Memulai pengimporan public/data-siswa.xlsx ke database server...\n";

try {
    $start = microtime(true);
    $import = new SiswaDataImport();
    Excel::import($import, __DIR__ . '/public/data-siswa.xlsx');
    $duration = round(microtime(true) - $start, 2);

    echo "=== IMPOR DATABASE SERVER SELESAI ({$duration} detik) ===\n";
    echo "• Siswa baru dibuat  : " . $import->created . "\n";
    echo "• Siswa di-update    : " . $import->updated . "\n";
    echo "• Data tidak berubah : " . $import->unchanged . "\n";
    echo "• Baris dilewati     : " . $import->skipped . "\n";
    echo "• Jumlah Error       : " . count($import->errors) . "\n";

    if (count($import->errors) > 0) {
        echo "Contoh Error:\n";
        print_r(array_slice($import->errors, 0, 5));
    }
} catch (\Throwable $e) {
    echo "ERROR IMPOR SERVER: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
