<?php

namespace Tests\Feature;

use App\Filament\Pages\AttendanceSettingPage;
use App\Models\AttendanceSetting;
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

    public function test_admin_can_reset_single_day_to_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Ubah jam sabtu ke nilai khusus (misal 14:00)
        AttendanceSetting::updateOrCreate(
            ['day_of_week' => 6],
            [
                'day_name'       => 'Sabtu',
                'check_in_open'  => '06:00:00',
                'check_in_late'  => '08:00:00',
                'check_in_close' => '09:00:00',
                'check_out_open' => '14:00:00',
                'is_active'      => true,
            ]
        );

        // Reset khusus hari Sabtu (6)
        Livewire::actingAs($admin)
            ->test(AttendanceSettingPage::class)
            ->call('resetDay', 6);

        $sabtu = AttendanceSetting::where('day_of_week', 6)->first();

        $this->assertEquals('05:00:00', $sabtu->check_in_open);
        $this->assertEquals('11:00:00', $sabtu->check_out_open);
    }

    public function test_admin_can_reset_all_days_globally(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Set acak
        AttendanceSetting::query()->update(['check_out_open' => '15:00:00']);

        Livewire::actingAs($admin)
            ->test(AttendanceSettingPage::class)
            ->callAction('resetGlobal');

        $seninData = AttendanceSetting::where('day_of_week', 1)->first();
        $sabtuData = AttendanceSetting::where('day_of_week', 6)->first();

        $this->assertEquals('13:30:00', $seninData->check_out_open);
        $this->assertEquals('11:00:00', $sabtuData->check_out_open);
    }
}
