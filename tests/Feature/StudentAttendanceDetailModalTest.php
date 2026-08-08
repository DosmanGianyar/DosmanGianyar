<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Permit;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\StudentAttendanceDetailService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceDetailModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_attendance_detail_service_returns_correct_breakdown(): void
    {
        Carbon::setTestNow('2026-08-10');

        $guru = User::factory()->create(['role' => 'guru']);
        $class = SchoolClass::create([
            'name'                => 'X PPLG 1',
            'grade'               => 'X',
            'homeroom_teacher_id' => $guru->id,
        ]);
        $student = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $class->id,
        ]);

        $monday  = Carbon::parse('2026-08-03'); // Monday
        $tuesday = Carbon::parse('2026-08-04'); // Tuesday

        // Create an attendance record
        Attendance::create([
            'user_id'        => $student->id,
            'date'           => $monday->toDateString(),
            'status'         => 'hadir',
            'check_in_time'  => '07:05:00',
            'check_out_time' => '15:30:00',
        ]);

        // Create an approved permit for sakit
        Permit::create([
            'student_id'  => $student->id,
            'type'        => 'sakit',
            'start_date'  => $tuesday->toDateString(),
            'end_date'    => $tuesday->toDateString(),
            'reason'      => 'Demam tinggi',
            'status'      => 'approved',
            'approved_by' => $guru->id,
        ]);

        $data = StudentAttendanceDetailService::getDetail(
            $student->id,
            8,
            2026
        );

        $this->assertEquals($student->name, $data['student']['name']);
        $this->assertEquals(1, $data['counts']['hadir']);
        $this->assertEquals(1, $data['counts']['sakit']);
    }

    public function test_guru_can_access_student_detail_json_endpoint(): void
    {
        Carbon::setTestNow('2026-08-10');

        $guru = User::factory()->create(['role' => 'guru']);
        $class = SchoolClass::create([
            'name'                => 'X PPLG 1',
            'grade'               => 'X',
            'homeroom_teacher_id' => $guru->id,
        ]);
        $student = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $class->id,
        ]);

        $response = $this->actingAs($guru)->getJson(route('guru.attendance.student-detail', [
            'student' => $student->id,
            'month'   => 8,
            'year'    => 2026,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'student' => ['id', 'name', 'nis', 'class_name'],
            'month_name',
            'counts',
            'logs',
        ]);
    }
}
