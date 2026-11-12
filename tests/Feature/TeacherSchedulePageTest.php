<?php

namespace Tests\Feature;

use App\Filament\Pages\TeacherSchedulePage;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherSchedulePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_teacher_schedule_page(): void
    {
        $admin = User::create([
            'name'     => 'Admin Kurikulum',
            'email'    => 'admin_kurikulum@sims.sch.id',
            'password' => bcrypt('password'),
            'role'     => 'admin_kurikulum',
        ]);

        $teacher = User::create([
            'name'         => 'Ni Kadek Novi Ari, S.Pd',
            'email'        => 'noviari@sims.sch.id',
            'password'     => bcrypt('password'),
            'role'         => 'guru',
            'teacher_code' => 'B. Novi Ari',
        ]);

        $class = SchoolClass::create(['name' => 'XI-B1', 'grade' => 11]);
        $subject = Subject::create(['name' => 'Bahasa Indonesia', 'code' => 'INDO']);

        Schedule::create([
            'class_id'      => $class->id,
            'teacher_id'    => $teacher->id,
            'subject_id'    => $subject->id,
            'day'           => 1,
            'period'        => 2,
            'start_time'    => '08:15:00',
            'end_time'      => '09:00:00',
            'academic_year' => '2026/2027 Ganjil',
        ]);

        Schedule::create([
            'class_id'      => $class->id,
            'teacher_id'    => $teacher->id,
            'subject_id'    => $subject->id,
            'day'           => 1,
            'period'        => 3,
            'start_time'    => '09:00:00',
            'end_time'      => '09:45:00',
            'academic_year' => '2026/2027 Ganjil',
        ]);

        $this->actingAs($admin);

        Livewire::test(TeacherSchedulePage::class)
            ->assertSuccessful()
            ->set('selectedTeacherId', $teacher->id)
            ->assertSee('Ni Kadek Novi Ari, S.Pd')
            ->assertSee('XI-B1')
            ->assertSee('INDO');
    }
}
