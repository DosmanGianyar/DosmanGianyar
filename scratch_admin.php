<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = DB::table('users')->where('role', 'like', '%admin%')->orWhereIn('role', ['pengelola', 'superadmin'])->get();
echo "==================== ADMIN ACCOUNTS ====================\n";
foreach ($users as $u) {
    echo "ROLE: " . str_pad($u->role, 18) . " | EMAIL: " . str_pad($u->email ?? '-', 26) . " | NAME: " . str_pad($u->name, 25) . " | USERNAME/NIP: " . ($u->nip ?? $u->email ?? '-') . "\n";
}
echo "=======================================================\n";
