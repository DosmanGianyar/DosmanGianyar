<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Models\Dispensation;
use App\Models\EarlyCheckoutRequest;
use App\Models\ExitPass;
use App\Models\ForgotAttendanceRequest;
use App\Models\Permit;
use App\Models\PiketSession;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PiketPortalTest extends TestCase
{
    use RefreshDatabase;

    private function createPiketAccount(): User
    {
        return User::where('email', 'piket@sims.sch.id')->first()
            ?? User::factory()->create([
                'name'     => 'Meja Piket SMAN 1 Gianyar',
                'email'    => 'piket@sims.sch.id',
                'nip'      => 'piket',
                'role'     => 'piket',
                'password' => Hash::make('Dosman123'),
            ]);
    }

    public function test_piket_login_redirects_to_teacher_selection_and_blocks_dashboard_before_selecting(): void
    {
        $piket = $this->createPiketAccount();

        $loginRes = $this->post('/login', [
            'login'    => 'piket',
            'password' => 'Dosman123',
        ]);
        $loginRes->assertRedirect(route('piket.select-teacher'));
        $this->assertAuthenticatedAs($piket);

        $this->get(route('piket.dashboard'))
            ->assertRedirect(route('piket.select-teacher'));

        $this->get(route('piket.permits.index'))
            ->assertRedirect(route('piket.select-teacher'));
    }

    public function test_non_piket_users_are_forbidden_from_piket_portal(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($guru)->get(route('piket.select-teacher'))->assertForbidden();
        $this->actingAs($guru)->get(route('piket.dashboard'))->assertForbidden();
        $this->actingAs($siswa)->get(route('piket.select-teacher'))->assertForbidden();
        $this->actingAs($siswa)->get(route('piket.permits.index'))->assertForbidden();
    }

    public function test_piket_teacher_duty_verifications_record_selected_teacher_and_sync_attendance(): void
    {
        $piket = $this->createPiketAccount();
        $teacher1 = User::factory()->create(['role' => 'guru', 'name' => 'I Made Guru Satu', 'nip' => '198001012010011001']);
        $teacher2 = User::factory()->create(['role' => 'guru', 'name' => 'Ni Luh Guru Dua', 'nip' => '198202022010012002']);

        $class = SchoolClass::create(['name' => 'XI-A1', 'grade' => 'XI']);
        $student1 = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Satu', 'class_id' => $class->id]);
        $student2 = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Dua', 'class_id' => $class->id]);

        // 1. Pilih guru piket 1
        $this->actingAs($piket)
            ->post(route('piket.select-teacher.store'), [
                'teacher_id' => $teacher1->id,
                'notes'      => 'Shift Pagi',
            ])
            ->assertRedirect(route('piket.dashboard'));

        $this->assertSame($teacher1->id, session('piket_teacher_id'));
        $this->assertSame($teacher1->name, session('piket_teacher_name'));
        $session1 = PiketSession::where('teacher_id', $teacher1->id)->firstOrFail();
        $this->assertNull($session1->ended_at);

        // Buka dashboard dan halaman-halaman utama
        $this->get(route('piket.dashboard'))->assertOk()->assertSee($teacher1->name);
        $this->get(route('piket.permits.index'))->assertOk();
        $this->get(route('piket.early-checkouts.index'))->assertOk();
        $this->get(route('piket.forgot-attendance.index'))->assertOk();
        $this->get(route('piket.late-verification.index'))->assertOk();
        $this->get(route('piket.dispensation.create'))->assertOk();
        $this->get(route('piket.exit-passes.index'))->assertOk();
        $this->get(route('piket.logs.index'))->assertOk();

        // 2. Approve Permit (izin tidak sekolah) -> tercatat atas nama teacher1
        $today = now()->toDateString();
        $permit = Permit::create([
            'student_id' => $student1->id,
            'type'       => 'sakit',
            'start_date' => $today,
            'end_date'   => $today,
            'reason'     => 'Demam tinggi',
            'status'     => 'pending',
        ]);

        $this->patch(route('piket.permits.approve', $permit))->assertRedirect();
        $permit->refresh();
        $this->assertSame('approved', $permit->status);
        $this->assertSame($teacher1->id, $permit->approved_by);
        $this->assertNotEquals($piket->id, $permit->approved_by);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $student1->id,
            'status'  => 'sakit',
        ]);

        // 3. Reject Permit lain
        $permitReject = Permit::create([
            'student_id' => $student2->id,
            'type'       => 'izin',
            'start_date' => $today,
            'end_date'   => $today,
            'reason'     => 'Alasan tidak jelas',
            'status'     => 'pending',
        ]);
        $this->patch(route('piket.permits.reject', $permitReject), [
            'rejection_note' => 'Lampiran belum ada',
        ])->assertRedirect();
        $permitReject->refresh();
        $this->assertSame('rejected', $permitReject->status);
        $this->assertSame($teacher1->id, $permitReject->approved_by);

        // 4. Approve Early Checkout (tanpa_absen)
        $early = EarlyCheckoutRequest::create([
            'student_id'       => $student1->id,
            'date'             => $today,
            'requested_time'   => '11:30:00',
            'reason'           => 'Kontrol dokter',
            'type'             => 'tanpa_absen',
            'absence_category' => 'izin',
            'status'           => 'pending',
        ]);
        $this->patch(route('piket.early-checkouts.approve', $early))->assertRedirect();
        $early->refresh();
        $this->assertSame('approved', $early->status);
        $this->assertSame($teacher1->id, $early->reviewed_by);

        // 5. Approve Forgot Attendance
        $forgot = ForgotAttendanceRequest::create([
            'student_id' => $student2->id,
            'type'       => 'masuk',
            'date'       => $today,
            'reason'     => 'HP mati di gerbang',
            'status'     => 'pending',
        ]);
        $this->patch(route('piket.forgot-attendance.approve', $forgot))->assertRedirect();
        $forgot->refresh();
        $this->assertSame('approved', $forgot->status);
        $this->assertSame($teacher1->id, $forgot->reviewed_by);
        $this->assertDatabaseHas('attendances', [
            'user_id'        => $student2->id,
            'status'         => 'hadir',
            'via_lupa_absen' => 1,
        ]);

        // 6. Verify Late ConductLog
        $cat = ConductCategory::create([
            'name'        => 'Terlambat Datang',
            'type'        => 'pelanggaran',
            'points'      => 5,
            'description' => 'Terlambat masuk sekolah',
        ]);
        $conduct = ConductLog::create([
            'student_id'       => $student1->id,
            'type'             => 'pelanggaran',
            'category_id'      => $cat->id,
            'description'      => 'Macet di jalan',
            'is_self_reported' => true,
            'status'           => 'pending',
        ]);
        $this->post(route('piket.late-verification.verify', $conduct))->assertRedirect();
        $conduct->refresh();
        $this->assertSame('verified', $conduct->status);
        $this->assertSame($teacher1->id, $conduct->verifier_id);

        // 7. Store Dispensation
        $this->post(route('piket.dispensation.store'), [
            'activity_name' => 'Lomba LKS',
            'date'          => $today,
            'student_ids'   => [$student1->id, $student2->id],
        ])->assertRedirect();
        $disp = Dispensation::where('activity_name', 'Lomba LKS')->firstOrFail();
        $this->assertSame($teacher1->id, $disp->approved_by);
        $this->assertCount(2, $disp->students);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $student1->id,
            'status'  => 'dispensasi',
        ]);

        // 8. Mark ExitPass Returned
        $pass = ExitPass::create([
            'student_id' => $student1->id,
            'reason'     => 'uks',
            'out_time'   => now()->subMinutes(10),
            'status'     => 'out',
        ]);
        $this->patch(route('piket.exit-passes.return', $pass))->assertRedirect();
        $pass->refresh();
        $this->assertSame('returned', $pass->status);
        $this->assertNotNull($pass->in_time);

        // Activity log mencatat via_piket_account & piket_teacher_id
        $this->assertTrue(
            ActivityLog::where('action', 'piket_permit_approve')
                ->get()
                ->contains(fn ($l) => ($l->changes['piket_teacher_id'] ?? null) === $teacher1->id)
        );

        // 9. Ganti guru piket ke teacher2 -> session1 tertutup, session2 aktif
        $this->post(route('piket.select-teacher.store'), [
            'teacher_id' => $teacher2->id,
            'notes'      => 'Shift Siang',
        ])->assertRedirect(route('piket.dashboard'));

        $session1->refresh();
        $this->assertNotNull($session1->ended_at);
        $session2 = PiketSession::where('teacher_id', $teacher2->id)->firstOrFail();
        $this->assertNull($session2->ended_at);
        $this->assertSame($teacher2->id, session('piket_teacher_id'));

        // 10. Logout -> menutup session2 aktif
        $this->post(route('logout'))->assertRedirect(route('login'));
        $session2->refresh();
        $this->assertNotNull($session2->ended_at);
    }
}
