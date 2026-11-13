<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ForgotAttendanceRequest;
use App\Models\PasswordResetRequest;
use App\Models\Permit;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutoApproveStudentRequestsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_approve_command_handles_all_conditions_correctly(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 13, 10, 0, 0));

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $guru = User::factory()->create([
            'role' => 'guru',
        ]);

        $class = SchoolClass::create([
            'name'                 => 'X-1',
            'grade'                => 10,
            'homeroom_teacher_id' => $guru->id,
        ]);

        $student = User::factory()->create([
            'role'      => 'siswa',
            'class_id'  => $class->id,
            'nisn'      => '1234567890',
            'password'  => Hash::make('customPassword123'),
        ]);

        // 1. Pending Lupa Absen
        $forgot = ForgotAttendanceRequest::create([
            'student_id' => $student->id,
            'type'       => 'masuk',
            'date'       => '2026-11-12',
            'reason'     => 'HP drop',
            'status'     => 'pending',
        ]);

        // 2. Pending Reset Password Siswa
        $resetReq = PasswordResetRequest::create([
            'user_id'      => $student->id,
            'identifier'   => '1234567890',
            'status'       => 'pending',
            'requested_at' => now()->subMinutes(30),
        ]);

        // 3. Pending Izin 1 Hari (<= 2 hari) -> harus disetujui
        $permit1Day = Permit::create([
            'student_id' => $student->id,
            'type'       => 'izin',
            'start_date' => '2026-11-13',
            'end_date'   => '2026-11-13',
            'reason'     => 'Upacara keluarga',
            'status'     => 'pending',
        ]);

        // 4. Pending Sakit 2 Hari (<= 2 hari) -> harus disetujui
        $permit2Days = Permit::create([
            'student_id' => $student->id,
            'type'       => 'sakit',
            'start_date' => '2026-11-14',
            'end_date'   => '2026-11-15',
            'reason'     => 'Demam',
            'status'     => 'pending',
        ]);

        // 5. Pending Sakit 3 Hari (> 2 hari) -> HARUS TETAP PENDING (Manual)
        $permit3Days = Permit::create([
            'student_id' => $student->id,
            'type'       => 'sakit',
            'start_date' => '2026-11-16',
            'end_date'   => '2026-11-18',
            'reason'     => 'Rawat inap',
            'status'     => 'pending',
        ]);

        // 6. Pending Dispensasi (1 hari) -> HARUS TETAP PENDING (Dispensasi biarkan manual)
        $permitDispen = Permit::create([
            'student_id' => $student->id,
            'type'       => 'dispensasi',
            'start_date' => '2026-11-13',
            'end_date'   => '2026-11-13',
            'reason'     => 'Lomba OSN',
            'status'     => 'pending',
        ]);

        // Jalankan perintah auto-approve
        $this->artisan('requests:auto-approve')
            ->expectsOutputToContain('Auto-Approve Selesai')
            ->assertExitCode(0);

        // Verifikasi 1: Lupa Absen menjadi approved & presensi hadir dibuat
        $forgot->refresh();
        $this->assertSame('approved', $forgot->status);
        $this->assertNotNull($forgot->reviewed_at);
        $attForgot = Attendance::where('user_id', $student->id)->whereDate('date', '2026-11-12')->first();
        $this->assertNotNull($attForgot);
        $this->assertSame('hadir', $attForgot->status);
        $this->assertTrue((bool) $attForgot->via_lupa_absen);

        // Verifikasi 2: Reset Password Siswa approved & password di-reset ke NISN
        $resetReq->refresh();
        $student->refresh();
        $this->assertSame('approved', $resetReq->status);
        $this->assertTrue(Hash::check('1234567890', $student->password));

        // Verifikasi 3 & 4: Izin 1 hari & Sakit 2 hari menjadi approved
        $permit1Day->refresh();
        $this->assertSame('approved', $permit1Day->status);
        $attIzin = Attendance::where('user_id', $student->id)->whereDate('date', '2026-11-13')->first();
        $this->assertNotNull($attIzin);
        $this->assertSame('izin', $attIzin->status);

        $permit2Days->refresh();
        $this->assertSame('approved', $permit2Days->status);

        // Verifikasi 5: Sakit 3 hari (> 2 hari) TETAP pending
        $permit3Days->refresh();
        $this->assertSame('pending', $permit3Days->status);

        // Verifikasi 6: Dispensasi TETAP pending
        $permitDispen->refresh();
        $this->assertSame('pending', $permitDispen->status);

        Carbon::setTestNow();
    }
}

