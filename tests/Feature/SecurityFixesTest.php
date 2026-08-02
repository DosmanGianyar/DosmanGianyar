<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_biodata_requires_authentication(): void
    {
        $siswa = User::factory()->create([
            'role' => 'siswa',
            'nis'  => '12345',
        ]);

        // Tamu tanpa login harus di-redirect ke halaman login
        $response = $this->get("/biodata/{$siswa->nis}");
        $response->assertRedirect('/login');

        // Pengguna terautentikasi bisa melihat halaman biodata
        $responseLoggedIn = $this->actingAs($siswa)->get("/biodata/{$siswa->nis}");
        $responseLoggedIn->assertStatus(200);
    }

    public function test_api_login_throttling_prevents_brute_force_after_five_attempts(): void
    {
        $user = User::factory()->create([
            'role'     => 'siswa',
            'nisn'     => '0098765432',
            'password' => bcrypt('correctpassword'),
        ]);

        // 5 Kali percobaan password salah
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'login'    => '0098765432',
                'password' => 'wrongpassword',
            ]);
        }

        // Percobaan ke-6 harus diblokir oleh Rate Limiter (HTTP 429 Too Many Requests)
        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => '0098765432',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
            'message' => 'Terlalu banyak percobaan login gagal. Coba lagi dalam 1 menit.',
        ]);
    }
}
