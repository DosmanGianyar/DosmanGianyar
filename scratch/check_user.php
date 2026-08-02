<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::where('nisn', '0071234571')->orWhere('nis', '0071234571')->first();
if (! $u) {
    echo "USER NOT FOUND\n";
    exit;
}

echo "Current User Password Hash: " . $u->password . "\n";
echo "Testing Hash::check('0071234571', password): " . (Illuminate\Support\Facades\Hash::check('0071234571', $u->password) ? 'YES' : 'NO') . "\n";

// Let's test calling $u->update(['password' => '12345678', 'must_change_password' => false])
try {
    $u->password = '12345678';
    $u->must_change_password = false;
    $u->save();
    echo "UPDATE SUCCESSFUL!\n";
    echo "New Password Hash: " . $u->password . "\n";
    echo "Testing Hash::check('12345678', new_password): " . (Illuminate\Support\Facades\Hash::check('12345678', $u->password) ? 'YES' : 'NO') . "\n";

    // Revert back to 0071234571 for testing
    $u->password = '0071234571';
    $u->must_change_password = true;
    $u->save();
    echo "REVERTED BACK TO 0071234571 SUCCESSFUL!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
