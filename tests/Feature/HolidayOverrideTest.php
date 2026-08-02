<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayOverrideTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_holiday_deletes_existing_alpa_records_on_that_date(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);
        $yesterday = today()->subDay();

        // Buat absensi Alpa kemarin (karena auto-alpa atau eror)
        $alpa = Attendance::create([
            'user_id' => $siswa->id,
            'date'    => $yesterday->toDateString(),
            'status'  => 'alpa',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id'     => $alpa->id,
            'status' => 'alpa',
        ]);

        // Admin menetapkan kemarin sebagai Hari Libur
        Holiday::create([
            'date'        => $yesterday->toDateString(),
            'description' => 'Libur Darurat/Eror Aplikasi',
            'type'        => 'libur',
            'applies_to'  => 'semua',
        ]);

        // Catatan Alpa kemarin HARUS otomatis terhapus
        $this->assertDatabaseMissing('attendances', [
            'id' => $alpa->id,
        ]);
    }
}
