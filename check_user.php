<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'perpustakaan@sims.sch.id')->first();
if ($user) {
    echo "USER_FOUND: " . $user->name . " | ROLE: " . $user->role . " | CAN_ACCESS: " . ($user->canAccessPanel(app(\Filament\Panel::class)) ? 'YES' : 'NO') . "\n";
} else {
    echo "USER_NOT_FOUND\n";
}
