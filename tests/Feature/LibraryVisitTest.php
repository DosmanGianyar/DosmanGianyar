<?php

namespace Tests\Feature;

use App\Models\LibraryVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_view_library_visit_page(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($siswa)->get(route('siswa.library.visit'));

        $response->assertStatus(200);
        $response->assertSee('Kunjungan Perpustakaan');
    }

    public function test_siswa_can_store_library_visit_via_web(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($siswa)->post(route('siswa.library.visit.store'), [
            'qr_code'        => 'SIMS_PERPUS_VISIT',
            'visited_at'     => now()->format('Y-m-d H:i:s'),
            'purpose_option' => 'Membaca Buku Paket / Literasi',
            'notes'          => 'Belajar Fisika Bab 2',
        ]);

        $response->assertRedirect(route('siswa.library.visit'));
        $this->assertDatabaseHas('library_visits', [
            'student_id' => $siswa->id,
            'purpose'    => 'Membaca Buku Paket / Literasi',
            'notes'      => 'Belajar Fisika Bab 2',
        ]);
    }

    public function test_siswa_can_store_and_get_library_visit_via_api(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);
        $siswa->registerDevice('test-device-123');

        $postResponse = $this->actingAs($siswa, 'sanctum')
            ->withHeaders(['X-Device-ID' => 'test-device-123'])
            ->postJson('/api/v1/siswa/library/visits', [
                'qr_code'        => 'SIMS_PERPUS_VISIT',
                'visited_at'     => now()->toIso8601String(),
                'purpose_option' => 'Kerja Kelompok',
                'notes'          => 'Tugas Biologi',
            ]);

        $postResponse->assertStatus(201);
        $postResponse->assertJsonPath('success', true);

        $getResponse = $this->actingAs($siswa, 'sanctum')
            ->withHeaders(['X-Device-ID' => 'test-device-123'])
            ->getJson('/api/v1/siswa/library/visits');

        $getResponse->assertStatus(200);
        $getResponse->assertJsonPath('success', true);
        $getResponse->assertJsonCount(1, 'data');
    }

    public function test_admin_can_view_clearance_card(): void
    {
        $admin = User::factory()->create(['role' => 'admin_perpustakaan']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($admin)->get(route('admin.library.clearance-card', $siswa->id));

        $response->assertStatus(200);
        $response->assertSee('SURAT KETERANGAN BEBAS PERPUSTAKAAN');
        $response->assertSee($siswa->name);
    }

    public function test_admin_can_view_visit_qr_card(): void
    {
        $admin = User::factory()->create(['role' => 'admin_perpustakaan']);

        $response = $this->actingAs($admin)->get(route('admin.library.visit-qr-card'));

        $response->assertStatus(200);
        $response->assertSee('KUNJUNGAN PERPUSTAKAAN');
        $response->assertSee('SIMS_PERPUS_VISIT');
    }

    public function test_admin_can_download_student_card(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($admin)->get(route('admin.student-card.download', $siswa->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
