<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = DB::table('users')->get();
echo "TOTAL USERS IN SERVER DB: " . $users->count() . "\n";
echo "=================================================================\n";
foreach ($users as $u) {
    if (!in_array($u->role, ['siswa', 'orangtua'])) {
        $loginId = $u->nip ?? $u->email ?? $u->id;
        echo "ROLE: " . str_pad($u->role, 16) . " | NAME: " . str_pad($u->name, 25) . " | EMAIL: " . str_pad($u->email ?? '-', 24) . " | NIP/LOGIN: " . ($u->nip ?? '-') . "\n";
    }
}
echo "=================================================================\n";
