<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::where('nisn', '0071234571')->orWhere('nis', '0071234571')->first();
if ($u) {
    echo "ID: {$u->id}\n";
    echo "NAME: {$u->name}\n";
    echo "NIS: {$u->nis}\n";
    echo "NISN: {$u->nisn}\n";
    echo "MUST_CHANGE: " . ($u->must_change_password ? 'true' : 'false') . "\n";
    echo "CHECK_NISN (0071234571): " . (Illuminate\Support\Facades\Hash::check('0071234571', $u->password) ? 'MATCH' : 'NO') . "\n";
    echo "CHECK_NISN_NO_ZERO (71234571): " . (Illuminate\Support\Facades\Hash::check('71234571', $u->password) ? 'MATCH' : 'NO') . "\n";
    echo "CHECK_NIS ({$u->nis}): " . (Illuminate\Support\Facades\Hash::check($u->nis, $u->password) ? 'MATCH' : 'NO') . "\n";
    echo "CHECK_12345678: " . (Illuminate\Support\Facades\Hash::check('12345678', $u->password) ? 'MATCH' : 'NO') . "\n";
} else {
    echo "USER 0071234571 NOT FOUND IN LOCAL DB\n";
}
