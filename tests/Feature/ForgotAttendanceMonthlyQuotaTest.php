<?php

namespace Tests\Feature;

use App\Models\ForgotAttendanceRequest;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForgotAttendanceMonthlyQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_submission_type_counts_as_one_and_blocks_after_10_per_month(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 8, 0, 0));

        $class = SchoolClass::create(['name' => 'X-01', 'grade' => 10]);
        $student = User::factory()->create([
            'role'      => 'siswa',
            'class_id'  => $class->id,
            'device_id' => 'test-device-1',
        ]);

        // 1. Pengajuan masuk & pulang di tanggal yang sama (Nov 1) -> dihitung 1x
        ForgotAttendanceRequest::create([
            'student_id' => $student->id,
            'type'       => 'masuk',
            'date'       => '2026-11-01',
            'reason'     => 'Lupa masuk',
            'status'     => 'approved',
        ]);
        ForgotAttendanceRequest::create([
            'student_id' => $student->id,
            'type'       => 'pulang',
            'date'       => '2026-11-01',
            'reason'     => 'Lupa pulang',
            'status'     => 'approved',
        ]);

        $this->assertSame(1, ForgotAttendanceRequest::monthlyUsedCount($student->id, 2026, 11));

        // 2. Pengajuan keduanya (semua absen) di Nov 2 -> dihitung 1x (total 2x)
        ForgotAttendanceRequest::create([
            'student_id' => $student->id,
            'type'       => 'keduanya',
            'date'       => '2026-11-02',
            'reason'     => 'Lupa keduanya',
            'status'     => 'approved',
        ]);

        $this->assertSame(2, ForgotAttendanceRequest::monthlyUsedCount($student->id, 2026, 11));

        // 3. Tambahkan 8 tanggal lagi (Nov 3 s/d Nov 10) -> total menjadi 10x
        for ($d = 3; $d <= 10; $d++) {
            ForgotAttendanceRequest::create([
                'student_id' => $student->id,
                'type'       => $d % 2 === 0 ? 'masuk' : 'pulang',
                'date'       => sprintf('2026-11-%02d', $d),
                'reason'     => 'Alasan ' . $d,
                'status'     => 'pending',
            ]);
        }

        $this->assertSame(10, ForgotAttendanceRequest::monthlyUsedCount($student->id, 2026, 11));
        $this->assertSame(0, ForgotAttendanceRequest::monthlyRemainingQuota($student->id, 2026, 11));

        // 4. Pengajuan ke-11 pada bulan yang sama (Nov 11) via API harus ditolak (422)
        $responseApi = $this->withHeaders(['X-Device-ID' => 'test-device-1'])
            ->actingAs($student, 'sanctum')
            ->postJson('/api/v1/forgot-attendance', [
                'type'   => 'masuk',
                'date'   => '2026-11-11',
                'reason' => 'HP mati',
            ]);
        $responseApi->assertStatus(422);

        // 5. Pengajuan ke-11 pada bulan yang sama (Nov 11) via Web harus ditolak
        $responseWeb = $this->actingAs($student)->post(route('siswa.forgot-attendance.store'), [
            'type'   => 'keduanya',
            'date'   => '2026-11-11',
            'reason' => 'HP mati',
        ]);
        $responseWeb->assertSessionHasErrors('date');

        // 6. Pergantian bulan ke Desember 2026 -> kuota direset kembali menjadi 0/10 (sisa 10x)
        Carbon::setTestNow(Carbon::create(2026, 12, 2, 8, 0, 0));
        $this->assertSame(0, ForgotAttendanceRequest::monthlyUsedCount($student->id));
        $this->assertSame(10, ForgotAttendanceRequest::monthlyRemainingQuota($student->id));

        $responseDec = $this->withHeaders(['X-Device-ID' => 'test-device-1'])
            ->actingAs($student, 'sanctum')
            ->postJson('/api/v1/forgot-attendance', [
                'type'   => 'keduanya',
                'date'   => '2026-12-01',
                'reason' => 'Lupa absen awal bulan baru',
            ]);
        $responseDec->assertStatus(201);
        $this->assertSame(1, ForgotAttendanceRequest::monthlyUsedCount($student->id, 2026, 12));

        Carbon::setTestNow();
    }
}
