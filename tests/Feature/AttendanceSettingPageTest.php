<?php

namespace Tests\Feature;

use App\Filament\Pages\AttendanceSettingPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceSettingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_attendance_setting_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(AttendanceSettingPage::getUrl());

        $response->assertSuccessful();
    }
}
