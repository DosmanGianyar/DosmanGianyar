<?php

namespace Tests\Feature;

use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrangtuaWebTest extends TestCase
{
    use RefreshDatabase;

    private function makeFamily(): array
    {
        $orangtua = User::factory()->create([
            'role'     => 'orangtua',
            'phone'    => '081234567890',
            'password' => Hash::make('081234567890'),
        ]);
        $student = User::factory()->create(['role' => 'siswa']);
        $orangtua->children()->attach($student->id);

        return [$orangtua, $student];
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    public function test_orangtua_can_login_with_phone(): void
    {
        [$orangtua] = $this->makeFamily();

        $this->post(route('login.submit'), [
            'login'    => '081234567890',
            'password' => '081234567890',
        ])->assertRedirect(route('orangtua.dashboard'));

        $this->assertAuthenticatedAs($orangtua);
    }

    public function test_orangtua_can_login_with_unnormalized_phone_format(): void
    {
        [$orangtua] = $this->makeFamily();

        // Format +62 harus dinormalisasi ke 0-prefix sebelum dicocokkan.
        $this->post(route('login.submit'), [
            'login'    => '+62 812-3456-7890',
            'password' => '081234567890',
        ])->assertRedirect(route('orangtua.dashboard'));

        $this->assertAuthenticatedAs($orangtua);
    }

    public function test_authenticated_orangtua_redirected_from_login(): void
    {
        [$orangtua] = $this->makeFamily();

        $this->actingAs($orangtua)->get(route('login'))
            ->assertRedirect(route('orangtua.dashboard'));
    }

    // ─── Dashboard & Data Access ─────────────────────────────────────────────

    public function test_orangtua_dashboard_shows_children(): void
    {
        [$orangtua, $student] = $this->makeFamily();

        $this->actingAs($orangtua)
            ->get(route('orangtua.dashboard'))
            ->assertOk()
            ->assertSee($student->name);
    }

    public function test_orangtua_can_view_own_child_attendance(): void
    {
        [$orangtua, $student] = $this->makeFamily();

        $this->actingAs($orangtua)
            ->get(route('orangtua.attendance.history', $student->id))
            ->assertOk();
    }

    public function test_orangtua_can_view_own_child_conduct(): void
    {
        [$orangtua, $student] = $this->makeFamily();

        $this->actingAs($orangtua)
            ->get(route('orangtua.conduct.index', $student->id))
            ->assertOk();
    }

    public function test_orangtua_can_view_own_child_achievements(): void
    {
        [$orangtua, $student] = $this->makeFamily();

        $this->actingAs($orangtua)
            ->get(route('orangtua.achievements.index', $student->id))
            ->assertOk();
    }

    public function test_orangtua_cannot_view_other_familys_child(): void
    {
        [$orangtua] = $this->makeFamily();
        $otherStudent = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($orangtua)
            ->get(route('orangtua.attendance.history', $otherStudent->id))
            ->assertNotFound();
    }

    public function test_siswa_cannot_access_orangtua_routes(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($siswa)
            ->get(route('orangtua.dashboard'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_orangtua_dashboard(): void
    {
        $this->get(route('orangtua.dashboard'))->assertRedirect(route('login'));
    }

    // ─── Forgot Password ─────────────────────────────────────────────────────

    public function test_orangtua_forgot_password_with_phone_creates_request(): void
    {
        [$orangtua] = $this->makeFamily();

        $this->post(route('forgot-password.submit'), [
            'identifier' => '081234567890',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('password_reset_requests', [
            'user_id' => $orangtua->id,
            'status'  => 'pending',
        ]);
    }

    public function test_password_reset_approve_sets_orangtua_password_to_phone(): void
    {
        [$orangtua] = $this->makeFamily();
        $orangtua->update(['password' => Hash::make('some-old-password')]);

        $admin = User::factory()->create(['role' => 'admin']);
        $resetRequest = PasswordResetRequest::create([
            'user_id'      => $orangtua->id,
            'identifier'   => '081234567890',
            'status'       => 'pending',
            'requested_at' => now(),
        ]);

        $resetRequest->approve($admin);

        $this->assertTrue(Hash::check('081234567890', $orangtua->fresh()->password));
    }
}
