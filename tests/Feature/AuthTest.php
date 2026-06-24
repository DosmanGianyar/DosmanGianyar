<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── Login Page ───────────────────────────────────────────────────────────

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_redirected_from_login(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($siswa)->get(route('login'))
            ->assertRedirect(route('siswa.dashboard'));
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    public function test_siswa_can_login_with_email(): void
    {
        $user = User::factory()->create(['role' => 'siswa', 'password' => 'password']);

        $this->post(route('login.submit'), [
            'login'    => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('siswa.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_siswa_can_login_with_nis(): void
    {
        $user = User::factory()->create(['role' => 'siswa', 'nis' => '2024001', 'password' => 'password']);

        $this->post(route('login.submit'), [
            'login'    => '2024001',
            'password' => 'password',
        ])->assertRedirect(route('siswa.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_guru_redirected_to_guru_dashboard(): void
    {
        $guru = User::factory()->create(['role' => 'guru', 'password' => 'password']);

        $this->post(route('login.submit'), [
            'login'    => $guru->email,
            'password' => 'password',
        ])->assertRedirect(route('guru.dashboard'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['role' => 'siswa', 'password' => 'password']);

        $this->post(route('login.submit'), [
            'login'    => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $this->post(route('login.submit'), [
            'login'    => 'nobody@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('login');
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    // ─── Role Middleware ──────────────────────────────────────────────────────

    public function test_guest_cannot_access_siswa_dashboard(): void
    {
        $this->get(route('siswa.dashboard'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_guru_dashboard(): void
    {
        $this->get(route('guru.dashboard'))->assertRedirect(route('login'));
    }

    public function test_siswa_cannot_access_guru_routes(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($siswa)
            ->get(route('guru.dashboard'))
            ->assertForbidden();
    }

    public function test_guru_cannot_access_siswa_routes(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $this->actingAs($guru)
            ->get(route('siswa.dashboard'))
            ->assertForbidden();
    }

    public function test_siswa_pengelola_accesses_siswa_dashboard(): void
    {
        $pengelola = User::factory()->create(['role' => 'siswa_pengelola']);

        $this->actingAs($pengelola)
            ->get(route('siswa.dashboard'))
            ->assertOk();
    }
}
