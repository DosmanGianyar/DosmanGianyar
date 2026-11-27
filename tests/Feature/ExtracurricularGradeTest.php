<?php

namespace Tests\Feature;

use App\Models\Extracurricular;
use App\Models\ExtracurricularGrade;
use App\Models\ExtracurricularMember;
use App\Models\StudentGrade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtracurricularGradeTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return [
            'Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken,
            'X-Device-ID'   => $user->device_id ?: 'test-device-1',
        ];
    }

    public function test_pembina_can_list_and_save_batch_grades_and_download_pdf(): void
    {
        $pembina = User::factory()->create(['role' => 'guru', 'device_id' => 'pembina-1']);
        $student = User::factory()->create(['role' => 'siswa', 'device_id' => 'siswa-1']);

        $extra = Extracurricular::create([
            'name'       => 'Basket SMANSA',
            'pembina_id' => $pembina->id,
            'is_active'  => true,
        ]);

        ExtracurricularMember::create([
            'extracurricular_id' => $extra->id,
            'user_id'            => $student->id,
            'role'               => 'ketua',
            'status'             => 'active',
        ]);

        $year = StudentGrade::currentAcademicYear();
        $sem  = StudentGrade::currentSemester();

        // 1. Pembina ambil daftar penilaian
        $getRes = $this->withHeaders($this->authHeaders($pembina))
            ->getJson("/api/v1/guru/extracurriculars/{$extra->id}/grades?academic_year={$year}&semester={$sem}");

        $getRes->assertOk()
            ->assertJsonPath('summary.total_members', 1)
            ->assertJsonPath('summary.graded_count', 0)
            ->assertJsonPath('members.0.user_id', $student->id)
            ->assertJsonPath('members.0.predicate', null);

        // 2. Simpan batch nilai A
        $saveRes = $this->withHeaders($this->authHeaders($pembina))
            ->postJson("/api/v1/guru/extracurriculars/{$extra->id}/grades/batch", [
                'academic_year' => $year,
                'semester'      => $sem,
                'grades'        => [
                    [
                        'user_id'     => $student->id,
                        'predicate'   => 'A',
                        'description' => 'Sangat aktif memimpin tim basket.',
                    ],
                ],
            ]);

        $saveRes->assertOk()
            ->assertJsonPath('saved_count', 1);

        $this->assertDatabaseHas('extracurricular_grades', [
            'extracurricular_id' => $extra->id,
            'user_id'            => $student->id,
            'academic_year'      => $year,
            'semester'           => $sem,
            'predicate'          => 'A',
            'graded_by'          => $pembina->id,
        ]);

        // 3. Siswa melihat nilai terbarunya di /api/v1/extracurriculars/my
        $myRes = $this->withHeaders($this->authHeaders($student))
            ->getJson('/api/v1/extracurriculars/my');

        $myRes->assertOk()
            ->assertJsonPath('my_extracurriculars.0.latest_grade.predicate', 'A')
            ->assertJsonPath('my_extracurriculars.0.latest_grade.predicate_label', 'Sangat Baik')
            ->assertJsonPath('my_extracurriculars.0.latest_grade.description', 'Sangat aktif memimpin tim basket.');

        // 4. Signed PDF URL & download PDF berhasil (200 application/pdf)
        $pdfUrlRes = $this->withHeaders($this->authHeaders($pembina))
            ->getJson("/api/v1/guru/extracurriculars/{$extra->id}/grades-pdf-url?academic_year={$year}&semester={$sem}");

        $pdfUrlRes->assertOk();
        $signedUrl = $pdfUrlRes->json('url');
        $this->assertNotEmpty($signedUrl);

        $parsed = parse_url($signedUrl);
        $pathAndQuery = ($parsed['path'] ?? '') . '?' . ($parsed['query'] ?? '');
        $pdfDownload = $this->get($pathAndQuery);
        $pdfDownload->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfDownload->headers->get('Content-Type'));
    }

    public function test_non_pembina_gets_403_and_non_member_is_rejected_422(): void
    {
        $pembina      = User::factory()->create(['role' => 'guru', 'device_id' => 'pembina-2']);
        $otherTeacher = User::factory()->create(['role' => 'guru', 'device_id' => 'other-2']);
        $outsider     = User::factory()->create(['role' => 'siswa', 'device_id' => 'outsider-2']);

        $extra = Extracurricular::create([
            'name'       => 'PMR',
            'pembina_id' => $pembina->id,
            'is_active'  => true,
        ]);

        $year = StudentGrade::currentAcademicYear();
        $sem  = StudentGrade::currentSemester();

        $this->withHeaders($this->authHeaders($otherTeacher))
            ->getJson("/api/v1/guru/extracurriculars/{$extra->id}/grades")
            ->assertStatus(403);

        $this->withHeaders($this->authHeaders($pembina))
            ->postJson("/api/v1/guru/extracurriculars/{$extra->id}/grades/batch", [
                'academic_year' => $year,
                'semester'      => $sem,
                'grades'        => [
                    [
                        'user_id'   => $outsider->id,
                        'predicate' => 'B',
                    ],
                ],
            ])
            ->assertStatus(422);

        $this->assertEquals(0, ExtracurricularGrade::count());
    }
}
