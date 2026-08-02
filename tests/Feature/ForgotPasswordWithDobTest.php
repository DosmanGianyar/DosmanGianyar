<?php

namespace Tests\Feature;

use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForgotPasswordWithDobTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_succeeds_when_identifier_and_birth_date_match(): void
    {
        $siswa = User::factory()->create([
            'role'       => 'siswa',
            'nisn'       => '0011223344',
            'birth_date' => '2008-05-15',
        ]);

        $response = $this->post('/forgot-password', [
            'identifier' => '0011223344',
            'birth_date' => '2008-05-15',
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_requests', [
            'user_id'    => $siswa->id,
            'identifier' => '0011223344',
            'status'     => 'pending',
        ]);
    }

    public function test_forgot_password_fails_when_birth_date_does_not_match(): void
    {
        $siswa = User::factory()->create([
            'role'       => 'siswa',
            'nisn'       => '0011223344',
            'birth_date' => '2008-05-15',
        ]);

        $response = $this->post('/forgot-password', [
            'identifier' => '0011223344',
            'birth_date' => '2008-12-31', // Tanggal lahir salah
        ]);

        $response->assertSessionHasErrors(['birth_date']);

        $this->assertDatabaseMissing('password_reset_requests', [
            'user_id' => $siswa->id,
        ]);
    }

    public function test_api_forgot_password_validates_birth_date(): void
    {
        $siswa = User::factory()->create([
            'role'       => 'siswa',
            'nisn'       => '0099887766',
            'birth_date' => '2009-01-20',
        ]);

        // Tanggal lahir salah -> 422 mismatch
        $responseFail = $this->postJson('/api/v1/auth/forgot-password', [
            'identifier' => '0099887766',
            'birth_date' => '2009-08-08',
        ]);
        $responseFail->assertStatus(422);

        // Tanggal lahir benar -> 200 OK
        $responseSuccess = $this->postJson('/api/v1/auth/forgot-password', [
            'identifier' => '0099887766',
            'birth_date' => '2009-01-20',
        ]);
        $responseSuccess->assertStatus(200);
    }
}
