<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ExtracurricularExportController;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularSession;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruExtracurricularJournalTest extends TestCase
{
    use RefreshDatabase;

    private function headers(): array
    {
        return ['X-Device-ID' => 'test-device-id'];
    }

    public function test_pembina_can_list_assigned_extracurriculars_including_multi_pembina(): void
    {
        $pembina1 = User::factory()->create(['role' => 'guru', 'name' => 'Pembina Utama']);
        $pembina2 = User::factory()->create(['role' => 'guru', 'name' => 'Pembina Pendamping']);
        $otherGuru = User::factory()->create(['role' => 'guru', 'name' => 'Guru Lain']);

        $extra1 = Extracurricular::create([
            'name'       => 'Basket',
            'pembina_id' => $pembina1->id,
            'is_active'  => true,
        ]);
        $extra2 = Extracurricular::create([
            'name'      => 'Pramuka',
            'is_active' => true,
        ]);
        $extra2->teachers()->attach($pembina2->id);

        $extraInactive = Extracurricular::create([
            'name'       => 'Ekstra Nonaktif',
            'pembina_id' => $pembina1->id,
            'is_active'  => false,
        ]);

        $res1 = $this->actingAs($pembina1)->getJson('/api/v1/guru/extracurriculars', $this->headers());
        $res1->assertOk()
            ->assertJsonCount(1, 'extracurriculars')
            ->assertJsonFragment(['name' => 'Basket'])
            ->assertJsonMissing(['name' => 'Ekstra Nonaktif']);

        $res2 = $this->actingAs($pembina2)->getJson('/api/v1/guru/extracurriculars', $this->headers());
        $res2->assertOk()
            ->assertJsonCount(1, 'extracurriculars')
            ->assertJsonFragment(['name' => 'Pramuka']);

        $res3 = $this->actingAs($otherGuru)->getJson('/api/v1/guru/extracurriculars', $this->headers());
        $res3->assertOk()->assertJsonCount(0, 'extracurriculars');
    }

    public function test_non_pembina_cannot_access_or_modify_other_extracurricular(): void
    {
        $pembina = User::factory()->create(['role' => 'guru']);
        $intruder = User::factory()->create(['role' => 'guru']);

        $extra = Extracurricular::create([
            'name'       => 'Futsal',
            'pembina_id' => $pembina->id,
            'is_active'  => true,
        ]);

        $session = ExtracurricularSession::create([
            'extracurricular_id' => $extra->id,
            'title'              => 'Latihan Fisik',
            'session_date'       => '2026-11-23',
            'start_time'         => '15:00',
            'end_time'           => '17:00',
            'created_by'         => $pembina->id,
            'is_open'            => false,
        ]);

        $this->actingAs($intruder)
            ->getJson("/api/v1/guru/extracurriculars/{$extra->id}/members", $this->headers())
            ->assertForbidden();

        $this->actingAs($intruder)
            ->getJson("/api/v1/guru/extracurriculars/{$extra->id}/journals", $this->headers())
            ->assertForbidden();

        $this->actingAs($intruder)
            ->postJson("/api/v1/guru/extracurriculars/{$extra->id}/journals", [
                'title'        => 'Hack',
                'session_date' => '2026-11-23',
                'start_time'   => '15:00',
                'end_time'     => '17:00',
                'material'     => 'Test',
                'activity'     => 'Test',
            ], $this->headers())
            ->assertForbidden();

        $this->actingAs($intruder)
            ->postJson("/api/v1/guru/extracurricular-journals/{$session->id}/update", [
                'title' => 'Hack Update',
            ], $this->headers())
            ->assertForbidden();

        $this->actingAs($intruder)
            ->postJson("/api/v1/guru/extracurricular-journals/{$session->id}/attendance", [
                'attendances' => [
                    ['user_id' => $pembina->id, 'status' => 'hadir'],
                ],
            ], $this->headers())
            ->assertForbidden();

        $this->actingAs($intruder)
            ->deleteJson("/api/v1/guru/extracurricular-journals/{$session->id}", [], $this->headers())
            ->assertForbidden();
    }

    public function test_members_endpoint_combines_active_members_and_pivot_without_duplicates(): void
    {
        $pembina = User::factory()->create(['role' => 'guru']);
        $classX = SchoolClass::create(['name' => 'X-A', 'grade' => 'X']);
        $classXI = SchoolClass::create(['name' => 'XI-A', 'grade' => 'XI']);

        $student1 = User::factory()->create(['role' => 'siswa', 'name' => 'Andi Pratama', 'nis' => '1001', 'class_id' => $classX->id]);
        $student2 = User::factory()->create(['role' => 'siswa', 'name' => 'Budi Santoso', 'nis' => '1002', 'class_id' => $classXI->id]);
        $studentPending = User::factory()->create(['role' => 'siswa', 'name' => 'Cici Pending', 'nis' => '1003', 'class_id' => $classX->id]);

        $extra = Extracurricular::create([
            'name'       => 'PMR',
            'pembina_id' => $pembina->id,
            'is_active'  => true,
        ]);

        ExtracurricularMember::create([
            'extracurricular_id' => $extra->id,
            'user_id'            => $student1->id,
            'role'               => 'ketua',
            'status'             => 'active',
        ]);
        ExtracurricularMember::create([
            'extracurricular_id' => $extra->id,
            'user_id'            => $studentPending->id,
            'role'               => 'member',
            'status'             => 'pending_join',
        ]);

        // student1 juga ada di pivot + student2 hanya ada di pivot
        DB::table('extracurricular_students')->insert([
            ['extracurricular_id' => $extra->id, 'student_id' => $student1->id, 'role' => 'ketua', 'created_at' => now(), 'updated_at' => now()],
            ['extracurricular_id' => $extra->id, 'student_id' => $student2->id, 'role' => 'anggota', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $res = $this->actingAs($pembina)
            ->getJson("/api/v1/guru/extracurriculars/{$extra->id}/members", $this->headers());

        $res->assertOk()
            ->assertJsonCount(2, 'members')
            ->assertJsonFragment(['user_id' => $student1->id, 'name' => 'Andi Pratama', 'class_name' => 'X-A', 'role_label' => 'Ketua'])
            ->assertJsonFragment(['user_id' => $student2->id, 'name' => 'Budi Santoso', 'class_name' => 'XI-A', 'role_label' => 'Anggota'])
            ->assertJsonMissing(['name' => 'Cici Pending']);
    }

    public function test_pembina_can_create_show_update_attendance_delete_journal_and_export_pdf(): void
    {
        Storage::fake('public');

        $pembina = User::factory()->create(['role' => 'guru', 'name' => 'I Made Pembina']);
        $classX = SchoolClass::create(['name' => 'X-1', 'grade' => 'X']);

        $sHadir = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Hadir', 'nis' => '2001', 'class_id' => $classX->id]);
        $sIzin  = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Izin', 'nis' => '2002', 'class_id' => $classX->id]);
        $sSakit = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Sakit', 'nis' => '2003', 'class_id' => $classX->id]);
        $sAlpa  = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Alpa', 'nis' => '2004', 'class_id' => $classX->id]);

        $extra = Extracurricular::create([
            'name'       => 'Karya Ilmiah Remaja',
            'pembina_id' => $pembina->id,
            'is_active'  => true,
        ]);
        foreach ([$sHadir, $sIzin, $sSakit, $sAlpa] as $idx => $stu) {
            ExtracurricularMember::create([
                'extracurricular_id' => $extra->id,
                'user_id'            => $stu->id,
                'role'               => $idx === 0 ? 'ketua' : 'member',
                'status'             => 'active',
            ]);
        }

        // 1. Create journal via multipart with JSON-encoded attendances + photo
        $photo = UploadedFile::fake()->image('kegiatan.jpg', 320, 240);
        $attendancesPayload = [
            ['user_id' => $sHadir->id, 'status' => 'hadir'],
            ['user_id' => $sIzin->id,  'status' => 'izin', 'note' => 'Acara keluarga'],
            ['user_id' => $sSakit->id, 'status' => 'sakit'],
            ['user_id' => $sAlpa->id,  'status' => 'alpa'],
        ];

        $createRes = $this->actingAs($pembina)->post(
            "/api/v1/guru/extracurriculars/{$extra->id}/journals",
            [
                'title'        => 'Pertemuan Riset Dasar',
                'session_date' => '2026-11-23',
                'start_time'   => '15:00',
                'end_time'     => '17:00',
                'location'     => 'Lab IPA',
                'material'     => 'Metodologi Penelitian',
                'activity'     => 'Penyusunan latar belakang dan rumusan masalah.',
                'obstacles'    => 'Beberapa siswa belum membawa laptop.',
                'notes'        => 'Pertemuan berikutnya presentasi BAB 1.',
                'photo'        => $photo,
                'attendances'  => json_encode($attendancesPayload),
            ],
            $this->headers()
        );

        $createRes->assertCreated()
            ->assertJsonPath('journal.title', 'Pertemuan Riset Dasar')
            ->assertJsonPath('journal.hadir_count', 1)
            ->assertJsonPath('journal.izin_count', 1)
            ->assertJsonPath('journal.sakit_count', 1)
            ->assertJsonPath('journal.alpa_count', 1)
            ->assertJsonPath('journal.has_journal', true);

        $sessionId = (int) $createRes->json('journal.id');
        $session = ExtracurricularSession::findOrFail($sessionId);
        $this->assertNotNull($session->photo);
        Storage::disk('public')->assertExists($session->photo);
        $firstPhoto = $session->photo;

        // 2. Also test array attendances payload (JSON request)
        $createRes2 = $this->actingAs($pembina)->postJson(
            "/api/v1/guru/extracurriculars/{$extra->id}/journals",
            [
                'title'        => 'Pertemuan Lanjutan',
                'session_date' => '2026-11-24',
                'start_time'   => '15:30',
                'end_time'     => '17:00',
                'material'     => 'Analisis Data',
                'activity'     => 'Latihan olah data kuesioner.',
                'attendances'  => [
                    ['user_id' => $sHadir->id, 'status' => 'hadir'],
                ],
            ],
            $this->headers()
        );
        $createRes2->assertCreated()->assertJsonPath('journal.hadir_count', 1);

        // 3. List journals
        $listRes = $this->actingAs($pembina)->getJson(
            "/api/v1/guru/extracurriculars/{$extra->id}/journals?month=11&year=2026",
            $this->headers()
        );
        $listRes->assertOk()->assertJsonCount(2, 'data');

        // 4. Show journal detail with members & statuses
        $showRes = $this->actingAs($pembina)->getJson(
            "/api/v1/guru/extracurricular-journals/{$sessionId}",
            $this->headers()
        );
        $showRes->assertOk()
            ->assertJsonCount(4, 'members')
            ->assertJsonFragment(['user_id' => $sIzin->id, 'attendance' => 'izin', 'note' => 'Acara keluarga']);

        // 5. Update journal & attendance
        $updateRes = $this->actingAs($pembina)->postJson(
            "/api/v1/guru/extracurricular-journals/{$sessionId}/update",
            [
                'title'    => 'Pertemuan Riset Dasar (Revisi)',
                'material' => 'Metodologi Penelitian Kuantitatif',
            ],
            $this->headers()
        );
        $updateRes->assertOk()
            ->assertJsonPath('journal.title', 'Pertemuan Riset Dasar (Revisi)')
            ->assertJsonPath('journal.material', 'Metodologi Penelitian Kuantitatif');

        $attRes = $this->actingAs($pembina)->postJson(
            "/api/v1/guru/extracurricular-journals/{$sessionId}/attendance",
            [
                'attendances' => [
                    ['user_id' => $sAlpa->id, 'status' => 'hadir'],
                ],
            ],
            $this->headers()
        );
        $attRes->assertOk()
            ->assertJsonPath('journal.hadir_count', 2)
            ->assertJsonPath('journal.alpa_count', 0);

        // 6. Export PDF renders cleanly with 4 statuses and journal box
        $pdfResponse = app(ExtracurricularExportController::class)->pdf($session->fresh());
        $this->assertSame(200, $pdfResponse->getStatusCode());
        $this->assertStringContainsString('application/pdf', (string) $pdfResponse->headers->get('content-type'));

        // 7. Delete journal removes DB record, attendances, and photo
        $delRes = $this->actingAs($pembina)->deleteJson(
            "/api/v1/guru/extracurricular-journals/{$sessionId}",
            [],
            $this->headers()
        );
        $delRes->assertOk();
        $this->assertDatabaseMissing('extracurricular_sessions', ['id' => $sessionId]);
        $this->assertSame(0, ExtracurricularAttendance::where('session_id', $sessionId)->count());
        Storage::disk('public')->assertMissing($firstPhoto);
    }

    public function test_pembina_can_download_members_pdf_via_web_and_non_pembina_is_forbidden(): void
    {
        $pembina = User::factory()->create(['role' => 'guru', 'name' => 'Pembina PDF']);
        $intruder = User::factory()->create(['role' => 'guru', 'name' => 'Guru Bukan Pembina']);
        $classXI = SchoolClass::create(['name' => 'XI-B1', 'grade' => 'XI']);

        $student1 = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Satu', 'nisn' => '0102173896', 'class_id' => $classXI->id]);
        $student2 = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Dua', 'nisn' => '0102173897', 'class_id' => $classXI->id]);

        $extra = Extracurricular::create([
            'name'       => 'Basket',
            'pembina_id' => $pembina->id,
            'is_active'  => true,
        ]);

        ExtracurricularMember::create([
            'extracurricular_id' => $extra->id,
            'user_id'            => $student1->id,
            'role'               => 'ketua',
            'status'             => 'active',
        ]);
        DB::table('extracurricular_students')->insert([
            'extracurricular_id' => $extra->id,
            'student_id'         => $student2->id,
            'role'               => 'anggota',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $okRes = $this->actingAs($pembina)
            ->get(route('guru.extracurricular.members.pdf', $extra->id));
        $okRes->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $okRes->headers->get('content-type'));

        $this->actingAs($intruder)
            ->get(route('guru.extracurricular.members.pdf', $extra->id))
            ->assertForbidden();
    }

    public function test_pembina_can_get_signed_pdf_url_and_download_without_session(): void
    {
        $pembina = User::factory()->create(['role' => 'guru', 'name' => 'Pembina Mobile']);
        $intruder = User::factory()->create(['role' => 'guru', 'name' => 'Guru Lain']);
        $classX = SchoolClass::create(['name' => 'X-A', 'grade' => 'X']);
        $student = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Ekstra', 'class_id' => $classX->id]);

        $extra = Extracurricular::create([
            'name'       => 'Robotik',
            'pembina_id' => $pembina->id,
            'is_active'  => true,
        ]);
        ExtracurricularMember::create([
            'extracurricular_id' => $extra->id,
            'user_id'            => $student->id,
            'role'               => 'member',
            'status'             => 'active',
        ]);

        $this->actingAs($intruder)
            ->getJson("/api/v1/guru/extracurriculars/{$extra->id}/members-pdf-url", $this->headers())
            ->assertForbidden();

        $res = $this->actingAs($pembina)
            ->getJson("/api/v1/guru/extracurriculars/{$extra->id}/members-pdf-url", $this->headers());

        $res->assertOk()->assertJsonStructure(['url', 'filename']);
        $signedUrl = (string) $res->json('url');
        $parsed = parse_url($signedUrl);
        $signedPath = ($parsed['path'] ?? '') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');

        // Reset session/auth state to verify unauthenticated signed download
        auth()->guard('web')->logout();
        $this->app['auth']->forgetGuards();

        $downloadRes = $this->get($signedPath);
        $downloadRes->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $downloadRes->headers->get('content-type'));

        $tampered = preg_replace('/signature=[^&]+/', 'signature=tampered000', $signedPath);
        $this->get($tampered)->assertForbidden();
    }
}
