<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
if ($user) {
    auth()->login($user);
    echo "Logged in as: " . $user->name . " (Role/ID: " . $user->id . ")\n";
}

try {
    $request = Illuminate\Http\Request::create('/admin', 'GET');
    $response = $kernel->handle($request);
    echo "HTTP Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() !== 200) {
        echo "Response Content:\n";
        echo substr($response->getContent(), 0, 3000) . "\n";
    }
} catch (\Throwable $e) {
    echo "EXPLICIT EXCEPTION CAUGHT:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
