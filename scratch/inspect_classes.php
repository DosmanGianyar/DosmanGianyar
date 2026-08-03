<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- SCHOOL CLASSES ---\n";
foreach (App\Models\SchoolClass::with('homeroomTeacher')->get() as $c) {
    echo "ID: {$c->id} | Name: {$c->name} | Grade: {$c->grade} | Homeroom: " . ($c->homeroomTeacher?->name ?? 'None') . "\n";
}

echo "\n--- USERS IN CLASSES --- \n";
foreach (App\Models\SchoolClass::all() as $c) {
    $teachersInClass = App\Models\User::where('class_id', $c->id)->where('role', 'guru')->get();
    if ($teachersInClass->isNotEmpty()) {
        echo "Class {$c->name} (ID {$c->id}) has GURU with class_id:\n";
        foreach ($teachersInClass as $g) {
            echo "   - Guru ID {$g->id}: {$g->name} (role={$g->role})\n";
        }
    }
}
