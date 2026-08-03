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

        $this->info("\n=== TEACHERS WITH CLASS_ID ===");
        $gurusWithClass = User::where('role', 'guru')->whereNotNull('class_id')->get();
        if ($gurusWithClass->isEmpty()) {
            $this->line("No teachers have class_id set.");
        } else {
            foreach ($gurusWithClass as $g) {
                $this->line("Guru ID: {$g->id} | Name: {$g->name} | class_id: {$g->class_id}");
            }
        }
    }
}
