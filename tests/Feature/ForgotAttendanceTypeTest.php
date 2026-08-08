<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ForgotAttendanceRequest;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForgotAttendanceTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_submit_forgot_attendance_with_type(): void
    {
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

        $response = $this->actingAs($student)->post(route('siswa.forgot-attendance.store'), [
            'type'   => 'pulang',
            'date'   => now()->toDateString(),
            'reason' => 'Handphone lowbat saat mau scan pulang',
        ]);

        $response->assertRedirect(route('siswa.forgot-attendance.index'));
        $this->assertDatabaseHas('forgot_attendance_requests', [
            'student_id' => $student->id,
            'type'       => 'pulang',
            'status'     => 'pending',
        ]);
    }

    public function test_guru_can_approve_forgot_attendance_setting_via_lupa_absen(): void
    {
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

        $req = ForgotAttendanceRequest::create([
            'student_id' => $student->id,
            'type'       => 'pulang',
            'date'       => now()->toDateString(),
            'reason'     => 'Lupa scan pulang',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($guru)->patch(route('guru.forgot-attendance.approve', $req), [
            'teacher_note' => 'Disetujui',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('forgot_attendance_requests', [
            'id'     => $req->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id'          => $student->id,
            'status'           => 'hadir',
            'via_lupa_absen'   => true,
            'lupa_absen_type'  => 'pulang',
            'check_out_time'   => '15:30:00',
        ]);
    }
}
