<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceSipinterLateSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_check_in_after_0715_without_sipinter_is_recorded_as_hadir_with_check_in_time(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 9, 7, 30, 15)); // Senin 07:30:15 (lewat 07:15)

        AttendanceSetting::create([
            'day_of_week'      => 1,
            'check_in_open'    => '06:00:00',
            'check_in_late'    => '07:15:00',
            'check_in_close'   => '08:30:00',
            'check_out_open'   => '15:00:00',
            'office_latitude'  => -8.5368510,
            'office_longitude' => 115.3292060,
            'allowed_radius'   => 500,
        ]);

        $class = SchoolClass::create(['name' => 'X-01', 'grade' => '10']);
        $student = User::factory()->create([
            'role'      => 'siswa',
            'class_id'  => $class->id,
            'device_id' => 'test-device-123',
        ]);

        $loc   = \App\Services\GeofenceService::getLocationForClass($class->id);
        $photo = UploadedFile::fake()->image('selfie.jpg', 400, 400);

        $response = $this->actingAs($student, 'sanctum')
            ->withHeader('X-Device-ID', 'test-device-123')
            ->postJson('/api/v1/attendance/checkin', [
                'photo'     => $photo,
                'latitude'  => $loc['lat'],
                'longitude' => $loc['lng'],
                'accuracy'  => 10,
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'hadir')
            ->assertJsonPath('check_in_time', '07:30');

        $att = Attendance::where('user_id', $student->id)->whereDate('date', '2026-11-09')->first();
        $this->assertNotNull($att);
        $this->assertSame('hadir', $att->status);
        $this->assertSame('07:30:15', $att->check_in_time);

        Carbon::setTestNow();
    }

    public function test_sipinter_late_report_marks_attendance_as_terlambat_and_rejection_reverts_to_hadir(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 9, 7, 25, 0));

        $class = SchoolClass::create(['name' => 'X-01', 'grade' => '10']);
        $student = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $class->id,
        ]);

        // Siswa sudah absen masuk jam 07:20 -> status awal hadir
        $att = Attendance::create([
            'user_id'       => $student->id,
            'date'          => '2026-11-09',
            'check_in_time' => '07:20:00',
            'photo'         => 'selfies/test.jpg',
            'status'        => 'hadir',
        ]);

        $cat = ConductCategory::create([
            'name'      => 'Terlambat Masuk Sekolah',
            'type'      => 'pelanggaran',
            'context'   => 'sidak',
            'is_active' => true,
        ]);

        // Laporan SIPINTER dibuat
        $log = ConductLog::create([
            'student_id'       => $student->id,
            'category_id'      => $cat->id,
            'type'             => 'pelanggaran',
            'note'             => '[Terlambat Masuk Sekolah] Ban bocor',
            'description'      => 'Pengajuan Mandiri Siswa: Terlambat Masuk Sekolah',
            'is_self_reported' => true,
            'status'           => 'pending',
        ]);

        $this->assertSame('terlambat', $att->fresh()->status);
        $this->assertSame('07:20:00', $att->fresh()->check_in_time);

        // Jika laporan SIPINTER ditolak (rejected), status kembali menjadi hadir
        $log->update(['status' => 'rejected']);
        $this->assertSame('hadir', $att->fresh()->status);

        Carbon::setTestNow();
    }
}

