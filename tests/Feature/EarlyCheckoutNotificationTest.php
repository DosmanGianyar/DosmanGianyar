<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EarlyCheckoutNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_early_checkout_notification_only_sent_to_homeroom_teacher_via_web(): void
    {
        $homeroomTeacher = User::factory()->create(['role' => 'guru', 'name' => 'Wali Kelas']);
        $otherTeacher    = User::factory()->create(['role' => 'guru', 'name' => 'Guru Lain']);
        $admin           = User::factory()->create(['role' => 'admin', 'name' => 'Admin Sekolah']);

        $class = SchoolClass::create([
            'name'                 => 'X-A',
            'grade'                => 10,
            'homeroom_teacher_id' => $homeroomTeacher->id,
        ]);

        $student = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $class->id,
            'name'     => 'Siswa Budi',
        ]);

        $response = $this->actingAs($student)->post(route('siswa.early-checkout.store'), [
            'date'           => now()->toDateString(),
            'requested_time' => '11:00',
            'reason'         => 'Ada keperluan keluarga mendesak',
        ]);

        $response->assertRedirect(route('siswa.early-checkout.index'));
        $response->assertSessionHas('success');

        // Wali kelas harus menerima notifikasi
        $notif = AppNotification::where('user_id', $homeroomTeacher->id)->where('title', 'Izin Pulang Lebih Awal')->first();
        $this->assertNotNull($notif);
        $this->assertEquals('early_checkout', $notif->type);

        // Guru lain dan admin TIDAK boleh menerima notifikasi
        $this->assertEquals(0, AppNotification::where('user_id', $otherTeacher->id)->count());
        $this->assertEquals(0, AppNotification::where('user_id', $admin->id)->count());
    }

    public function test_early_checkout_notification_only_sent_to_homeroom_teacher_via_api(): void
    {
        $homeroomTeacher = User::factory()->create(['role' => 'guru', 'name' => 'Wali Kelas XI']);
        $otherTeacher    = User::factory()->create(['role' => 'guru', 'name' => 'Guru Lain XI']);

        $class = SchoolClass::create([
            'name'                 => 'XI-B',
            'grade'                => 11,
            'homeroom_teacher_id' => $homeroomTeacher->id,
        ]);

        $student = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $class->id,
            'name'     => 'Siswa Siti',
        ]);

        $response = $this->actingAs($student, 'sanctum')
            ->withHeaders(['X-Device-ID' => 'test-device-sitii'])
            ->postJson('/api/v1/early-checkout', [
                'date'             => now()->toDateString(),
                'requested_time'   => '10:30',
                'reason'           => 'Sakit flu',
                'type'             => 'dengan_absen',
            ]);

        $response->assertStatus(201);

        // Wali kelas menerima notifikasi
        $this->assertEquals(1, AppNotification::where('user_id', $homeroomTeacher->id)->where('title', 'Izin Pulang Lebih Awal')->count());

        // Guru lain TIDAK menerima notifikasi
        $this->assertEquals(0, AppNotification::where('user_id', $otherTeacher->id)->count());
    }
}
