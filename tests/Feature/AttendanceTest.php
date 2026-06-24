<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $siswa;

    // School coordinates (SMA N 1 Gianyar)
    private const SCHOOL_LAT = -8.5398;
    private const SCHOOL_LNG = 115.3285;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->siswa = User::factory()->create(['role' => 'siswa']);

        // Ensure a default attendance location exists
        AttendanceLocation::create([
            'name'       => 'SMA Negeri 1 Gianyar',
            'latitude'   => self::SCHOOL_LAT,
            'longitude'  => self::SCHOOL_LNG,
            'radius'     => 200,
            'is_default' => true,
            'is_active'  => true,
        ]);
    }

    // ─── Page Access ──────────────────────────────────────────────────────────

    public function test_attendance_page_accessible_for_siswa(): void
    {
        $this->actingAs($this->siswa)
            ->get(route('siswa.attendance.show'))
            ->assertOk();
    }

    public function test_attendance_page_requires_auth(): void
    {
        $this->get(route('siswa.attendance.show'))
            ->assertRedirect(route('login'));
    }

    // ─── Check-in Validation ──────────────────────────────────────────────────

    public function test_checkin_rejects_fake_gps(): void
    {
        $this->actingAs($this->siswa)
            ->postJson(route('siswa.attendance.store'), [
                'photo'     => 'data:image/jpeg;base64,' . base64_encode(str_repeat('x', 100)),
                'latitude'  => self::SCHOOL_LAT,
                'longitude' => self::SCHOOL_LNG,
                'accuracy'  => 2.5,  // < 5m = fake GPS
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('attendances', [
            'user_id'     => $this->siswa->id,
            'is_fake_gps' => 1,
            'status'      => 'alpa',
        ]);
    }

    public function test_checkin_rejects_outside_geofence(): void
    {
        $this->actingAs($this->siswa)
            ->postJson(route('siswa.attendance.store'), [
                'photo'     => 'data:image/jpeg;base64,' . base64_encode(str_repeat('x', 100)),
                'latitude'  => -6.2000,  // Jakarta, far from school
                'longitude' => 106.8000,
                'accuracy'  => 15.0,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_cannot_checkin_twice(): void
    {
        // Pre-create today's attendance record
        Attendance::create([
            'user_id'       => $this->siswa->id,
            'date'          => today(),
            'check_in_time' => '07:00:00',
            'latitude'      => self::SCHOOL_LAT,
            'longitude'     => self::SCHOOL_LNG,
            'status'        => 'hadir',
            'is_fake_gps'   => false,
        ]);

        $this->actingAs($this->siswa)
            ->postJson(route('siswa.attendance.store'), [
                'photo'     => 'data:image/jpeg;base64,' . base64_encode(str_repeat('x', 100)),
                'latitude'  => self::SCHOOL_LAT,
                'longitude' => self::SCHOOL_LNG,
                'accuracy'  => 15.0,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    // ─── Check-out Validation ─────────────────────────────────────────────────

    public function test_checkout_fails_without_checkin(): void
    {
        $this->actingAs($this->siswa)
            ->postJson(route('siswa.attendance.checkout'), [
                'photo'     => 'data:image/jpeg;base64,' . base64_encode(str_repeat('x', 100)),
                'latitude'  => self::SCHOOL_LAT,
                'longitude' => self::SCHOOL_LNG,
                'accuracy'  => 15.0,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    // ─── GeofenceService Unit ─────────────────────────────────────────────────

    public function test_haversine_inside_zone(): void
    {
        $location = [
            'lat'    => self::SCHOOL_LAT,
            'lng'    => self::SCHOOL_LNG,
            'radius' => 200,
            'name'   => 'Test',
        ];

        // Same coordinates → distance 0 → inside
        $this->assertTrue(
            \App\Services\GeofenceService::isInsideZone(self::SCHOOL_LAT, self::SCHOOL_LNG, $location)
        );
    }

    public function test_haversine_outside_zone(): void
    {
        $location = [
            'lat'    => self::SCHOOL_LAT,
            'lng'    => self::SCHOOL_LNG,
            'radius' => 200,
            'name'   => 'Test',
        ];

        // 1km north → outside 200m radius
        $this->assertFalse(
            \App\Services\GeofenceService::isInsideZone(self::SCHOOL_LAT + 0.009, self::SCHOOL_LNG, $location)
        );
    }
}
