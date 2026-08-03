<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;

echo "=== ALL SUBJECTS IN DB ===\n";
foreach (Subject::all() as $s) {
    echo "ID {$s->id}: Code '{$s->code}' | Name '{$s->name}'\n";
}
