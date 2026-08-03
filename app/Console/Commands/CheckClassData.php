<?php

namespace App\Console\Commands;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Console\Command;

class CheckClassData extends Command
{
    protected $signature = 'check:class-data';
    protected $description = 'Check school classes and teacher/student assignments';

    public function handle()
    {
        $this->info("=== SCHOOL CLASSES ===");
        $classes = SchoolClass::with('homeroomTeacher')->get();
        foreach ($classes as $c) {
            $teacherName = $c->homeroomTeacher ? $c->homeroomTeacher->name : 'None';
            $studentCount = User::where('role', 'siswa')->where('class_id', $c->id)->count();
            $guruCount = User::where('role', 'guru')->where('class_id', $c->id)->count();
            $this->line("ID: {$c->id} | Name: {$c->name} | Grade: {$c->grade} | Homeroom: {$teacherName} | Students: {$studentCount} | Teachers with class_id: {$guruCount}");
        }

        $this->info("\n=== INVALID CLASSES (TEACHER NAMES IN CLASSES TABLE) ===");
        $invalidClasses = SchoolClass::where('grade', '0')->orWhereNull('grade')->get();
        $this->line("Found " . $invalidClasses->count() . " invalid class entries in `classes` table.");

        foreach ($invalidClasses as $ic) {
            $studentCount = User::where('class_id', $ic->id)->count();
            $scheduleCount = \App\Models\Schedule::where('class_id', $ic->id)->count();
            $journalCount = \App\Models\TeacherJournal::where('class_id', $ic->id)->count();
            $attendanceCount = \App\Models\Attendance::whereHas('student', fn($q) => $q->where('class_id', $ic->id))->count();
            
            $this->line("Class ID {$ic->id}: {$ic->name} -> Students: {$studentCount}, Schedules: {$scheduleCount}, Journals: {$journalCount}, Attendances: {$attendanceCount}");
        }
    }
}
