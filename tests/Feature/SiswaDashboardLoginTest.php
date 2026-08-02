<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaDashboardLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_login_and_view_dashboard_without_500_error(): void
    {
        $class = SchoolClass::create(['name' => 'X-01', 'grade' => '10']);
        $siswa = User::factory()->create([
            'role'     => 'siswa',
            'nisn'     => '0000000001',
            'class_id' => $class->id,
            'password' => 'PlayReview123',
        ]);

        $response = $this->post('/login', [
            'login'    => '0000000001',
            'password' => 'PlayReview123',
        ]);

        $response->assertRedirect('/siswa/dashboard');
        $this->assertAuthenticatedAs($siswa);

        $dashboardResponse = $this->actingAs($siswa)->get('/siswa/dashboard');
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Kartu Pelajar Digital');
        $dashboardResponse->assertSee('Angkatan 62');
    }
}
