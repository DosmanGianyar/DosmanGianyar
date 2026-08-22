<?php

namespace Tests\Feature;

use App\Models\ConductLog;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelfReportConductTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_submit_self_report_via_web(): void
    {
        $class = SchoolClass::create(['name' => 'X-A', 'grade' => 10]);
        $student = User::factory()->create([
            'role' => 'siswa',
            'class_id' => $class->id,
        ]);

        $response = $this->actingAs($student)->post(route('siswa.conduct.self-report'), [
            'reason' => 'Terlambat Masuk Sekolah',
            'description' => 'Terjebak kemacetan lalu lintas',
        ]);

        $response->assertRedirect(route('siswa.conduct.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('conduct_logs', [
            'student_id'       => $student->id,
            'is_self_reported' => true,
            'status'           => 'pending',
            'type'             => 'pelanggaran',
        ]);
    }

    public function test_siswa_can_submit_self_report_via_api(): void
    {
        $class = SchoolClass::create(['name' => 'XI-B', 'grade' => 11]);
        $student = User::factory()->create([
            'role' => 'siswa',
            'class_id' => $class->id,
        ]);

        $response = $this->actingAs($student, 'sanctum')
            ->withHeaders(['X-Device-ID' => 'test-device-123'])
            ->postJson('/api/v1/conduct/self-report', [
                'reason' => 'Terlambat Masuk Sekolah',
                'description' => 'Ban sepeda motor bocor',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('conduct_logs', [
            'student_id'       => $student->id,
            'is_self_reported' => true,
            'status'           => 'pending',
        ]);
    }

    public function test_guru_can_view_and_verify_self_report(): void
    {
        $class = SchoolClass::create(['name' => 'XII-C', 'grade' => 12]);
        $student = User::factory()->create(['role' => 'siswa', 'class_id' => $class->id]);
        $guru = User::factory()->create(['role' => 'guru']);

        $log = ConductLog::create([
            'student_id'       => $student->id,
            'type'             => 'pelanggaran',
            'description'      => 'Pengajuan Mandiri Siswa: Terlambat',
            'is_self_reported' => true,
            'status'           => 'pending',
        ]);

        // Guru views verification page
        $viewResponse = $this->actingAs($guru)->get(route('guru.conduct.verification'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee($student->name);

        // Guru verifies
        $verifyResponse = $this->actingAs($guru)->post(route('guru.conduct.verify', $log->id));
        $verifyResponse->assertRedirect();

        $this->assertDatabaseHas('conduct_logs', [
            'id'          => $log->id,
            'status'      => 'verified',
            'verifier_id' => $guru->id,
        ]);
    }

    public function test_guru_can_verify_self_report_via_api(): void
    {
        $class = SchoolClass::create(['name' => 'X-B', 'grade' => 10]);
        $student = User::factory()->create(['role' => 'siswa', 'class_id' => $class->id]);
        $guru = User::factory()->create(['role' => 'guru']);

        $log = ConductLog::create([
            'student_id'       => $student->id,
            'type'             => 'pelanggaran',
            'description'      => 'Pengajuan Mandiri Siswa: Terlambat',
            'is_self_reported' => true,
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($guru, 'sanctum')
            ->withHeaders(['X-Device-ID' => 'test-device-123'])
            ->postJson("/api/v1/guru/conduct-self-reports/{$log->id}/verify");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('conduct_logs', [
            'id'          => $log->id,
            'status'      => 'verified',
            'verifier_id' => $guru->id,
        ]);
    }
}
