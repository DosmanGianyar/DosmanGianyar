<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetRequestResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_password_reset_request_resource_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/password-reset-requests');
        $response->assertStatus(200);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\PasswordResetRequestResource\Pages\ListPasswordResetRequests::class)
            ->assertSuccessful();
    }
}
