<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== ALL TEACHERS IN DB ===\n";
foreach (User::where('role', 'guru')->orderBy('name')->get() as $u) {
    echo "ID {$u->id}: {$u->name} (NIP: {$u->nip}, Subject: {$u->subject})\n";
}
