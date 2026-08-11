<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latestAtt = \App\Models\TeacherAttendance::latest()->first();
$latestJou = \App\Models\TeacherJournal::latest()->first();

echo "Latest TeacherAttendance: " . ($latestAtt ? "ID {$latestAtt->id}, Teacher {$latestAtt->teacher_id}" : "NONE") . "\n";
echo "Latest TeacherJournal: " . ($latestJou ? "ID {$latestJou->id}, Teacher {$latestJou->teacher_id}" : "NONE") . "\n";
