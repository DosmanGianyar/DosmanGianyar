<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruTeachingSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_get_occupied_periods_and_store_multi_periods(): void
    {
        $guruA = User::factory()->create(['role' => 'guru', 'name' => 'Guru A']);
        $guruB = User::factory()->create(['role' => 'guru', 'name' => 'Guru B']);
        $class = SchoolClass::create(['name' => 'X IPA 1', 'grade' => 10]);
        $siswa = User::factory()->create(['role' => 'siswa', 'class_id' => $class->id]);

        $headers = ['X-Device-ID' => 'test-device-123'];

        // Guru A mengabsen jam 1, 2, 3
        $this->actingAs($guruA);
        $resStore = $this->postJson('/api/v1/guru/teaching-sessions', [
            'class_id'    => $class->id,
            'date'        => '2026-08-01',
            'periods'     => [1, 2, 3],
            'attendances' => [
                ['student_id' => $siswa->id, 'status' => 'hadir'],
            ],
        ], $headers);

        $resStore->assertStatus(201);
        $this->assertDatabaseHas('teacher_attendances', ['teacher_id' => $guruA->id, 'class_id' => $class->id, 'period' => 1]);
        $this->assertDatabaseHas('teacher_attendances', ['teacher_id' => $guruA->id, 'class_id' => $class->id, 'period' => 2]);
        $this->assertDatabaseHas('teacher_attendances', ['teacher_id' => $guruA->id, 'class_id' => $class->id, 'period' => 3]);

        // Guru B mengecek occupied periods untuk kelas tersebut
        $this->actingAs($guruB);
        $resOcc = $this->getJson('/api/v1/guru/teaching-sessions/occupied-periods?class_id=' . $class->id . '&date=2026-08-01', $headers);
        $resOcc->assertStatus(200);
        $resOcc->assertJsonFragment(['period' => 1, 'is_self' => false, 'teacher_name' => 'Guru A']);

        // Guru B mencoba mengabsen jam 2, 3, 4 (jam 2 dan 3 bentrok dengan Guru A)
        $resConflict = $this->postJson('/api/v1/guru/teaching-sessions', [
            'class_id'    => $class->id,
            'date'        => '2026-08-01',
            'periods'     => [2, 3, 4],
            'attendances' => [
                ['student_id' => $siswa->id, 'status' => 'hadir'],
            ],
        ], $headers);

        $resConflict->assertStatus(422);

        // Guru B mengabsen jam 4, 5 (jam bebas) -> Sukses
        $resSuccess = $this->postJson('/api/v1/guru/teaching-sessions', [
            'class_id'    => $class->id,
            'date'        => '2026-08-01',
            'periods'     => [4, 5],
            'attendances' => [
                ['student_id' => $siswa->id, 'status' => 'hadir'],
            ],
        ], $headers);

        $resSuccess->assertStatus(201);
        $this->assertDatabaseHas('teacher_attendances', ['teacher_id' => $guruB->id, 'class_id' => $class->id, 'period' => 4]);
        $this->assertDatabaseHas('teacher_attendances', ['teacher_id' => $guruB->id, 'class_id' => $class->id, 'period' => 5]);
    }

    public function test_guru_can_get_class_students_with_morning_attendance(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $class = SchoolClass::create(['name' => 'X IPA 2', 'grade' => 10]);
        $siswaA = User::factory()->create(['role' => 'siswa', 'class_id' => $class->id, 'name' => 'Siswa A']);
        $siswaB = User::factory()->create(['role' => 'siswa', 'class_id' => $class->id, 'name' => 'Siswa B']);

        // Siswa A sakit pada tanggal 2026-08-08
        \App\Models\Attendance::create([
            'user_id' => $siswaA->id,
            'date'    => '2026-08-08',
            'status'  => 'sakit',
        ]);

        $headers = ['X-Device-ID' => 'test-device-123'];
        $this->actingAs($guru);
        $response = $this->getJson('/api/v1/guru/teaching-sessions/class-students/' . $class->id . '?date=2026-08-08', $headers);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id'               => $siswaA->id,
            'morning_status'   => 'sakit',
            'suggested_status' => 'sakit',
        ]);
        $response->assertJsonFragment([
            'id'               => $siswaB->id,
            'morning_status'   => null,
            'suggested_status' => 'hadir',
        ]);
    }
}
