<?php

namespace Tests\Feature;

use App\Models\Extracurricular;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Services\AchievementPembinaMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AchievementPembinaTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_matches_competition_title_to_closest_extracurricular_and_pembina(): void
    {
        $pembinaMath = User::factory()->create([
            'name' => 'I Wayan Matematika, S.Pd.',
            'role' => 'guru',
            'must_change_password' => false,
        ]);

        $pembinaKir = User::factory()->create([
            'name' => 'Ni Luh Sains, M.Pd.',
            'role' => 'guru',
            'must_change_password' => false,
        ]);

        $pembinaSilat = User::factory()->create([
            'name' => 'I Made Bela Diri, S.Or.',
            'role' => 'guru',
            'must_change_password' => false,
        ]);

        $extraMath = Extracurricular::create([
            'name' => 'Kelompok Belajar Matematika',
            'pembina_id' => $pembinaMath->id,
            'is_active' => true,
        ]);

        $extraKir = Extracurricular::create([
            'name' => 'KIR (Karya Ilmiah Remaja)',
            'pembina_id' => $pembinaKir->id,
            'is_active' => true,
        ]);

        $extraSilat = Extracurricular::create([
            'name' => 'Pencak Silat Bakti Negara',
            'pembina_id' => $pembinaSilat->id,
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'name' => 'Putu Juara',
            'role' => 'siswa',
            'must_change_password' => false,
        ]);

        // 1. Create achievement with math competition title (without setting pembina/extra)
        $achMath = StudentAchievement::create([
            'student_id' => $student->id,
            'title' => 'Juara 1 Olimpiade Matematika Nasional (OSN)',
            'field_category' => 'akademik_sains',
            'level' => 'nasional',
            'rank' => 'Juara 1',
            'organizer' => 'Puspresnas',
            'achievement_date' => '2026-08-17',
            'status' => 'approved',
        ]);

        $this->assertEquals($extraMath->id, $achMath->extracurricular_id);
        $this->assertEquals($pembinaMath->id, $achMath->pembina_id);

        // 2. Create achievement with essay/LKTI competition title
        $achKir = StudentAchievement::create([
            'student_id' => $student->id,
            'title' => 'Juara 2 Lomba Esai Ilmiah dan Riset Inovasi Pelajar',
            'field_category' => 'akademik_sains',
            'level' => 'provinsi',
            'rank' => 'Juara 2',
            'organizer' => 'Universitas Udayana',
            'achievement_date' => '2026-09-10',
            'status' => 'approved',
        ]);

        $this->assertEquals($extraKir->id, $achKir->extracurricular_id);
        $this->assertEquals($pembinaKir->id, $achKir->pembina_id);

        // 3. Test bulk sync when existing records had null pembina_id and extracurricular_id
        StudentAchievement::where('id', $achMath->id)->update([
            'extracurricular_id' => null,
            'pembina_id' => null,
        ]);

        $matchedCount = AchievementPembinaMatcher::syncAllAchievements(false);
        $this->assertGreaterThanOrEqual(1, $matchedCount);

        $achMath->refresh();
        $this->assertEquals($extraMath->id, $achMath->extracurricular_id);
        $this->assertEquals($pembinaMath->id, $achMath->pembina_id);
    }

    public function test_siswa_must_select_pembina_when_submitting_achievement(): void
    {
        Storage::fake('public');

        $category = \App\Models\AchievementCategory::create([
            'name' => 'Prestasi Olahraga',
            'points' => 25,
            'is_active' => true,
        ]);

        $pembina = User::factory()->create([
            'name' => 'Drs. Pembina Basket',
            'role' => 'guru',
            'must_change_password' => false,
        ]);

        $extra = Extracurricular::create([
            'name' => 'Basket',
            'pembina_id' => $pembina->id,
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'name' => 'Kadek Bintang',
            'role' => 'siswa',
            'must_change_password' => false,
        ]);

        // Missing pembina_id should fail validation
        $responseFail = $this->actingAs($student)->post(route('siswa.achievements.store'), [
            'category_id' => $category->id,
            'title' => 'DBL Bali Series 2026',
            'field_category' => 'olahraga',
            'level' => 'provinsi',
            'rank' => 'Juara 1',
            'participation_type' => 'individu',
            'organizer' => 'DBL Indonesia',
            'achievement_date' => '2026-10-01',
            'certificate' => UploadedFile::fake()->create('sertifikat.pdf', 200, 'application/pdf'),
            'photo' => UploadedFile::fake()->image('kegiatan.jpg'),
        ]);

        $responseFail->assertSessionHasErrors('pembina_id');

        // Providing pembina_id succeeds and auto-links extracurricular
        $responseOk = $this->actingAs($student)->post(route('siswa.achievements.store'), [
            'category_id' => $category->id,
            'title' => 'DBL Bali Series Bola Basket 2026',
            'field_category' => 'olahraga',
            'extracurricular_id' => $extra->id,
            'pembina_id' => $pembina->id,
            'level' => 'provinsi',
            'rank' => 'Juara 1',
            'participation_type' => 'individu',
            'organizer' => 'DBL Indonesia',
            'achievement_date' => '2026-10-01',
            'certificate' => UploadedFile::fake()->create('sertifikat.pdf', 200, 'application/pdf'),
            'photo' => UploadedFile::fake()->image('kegiatan.jpg'),
        ]);

        $responseOk->assertRedirect(route('siswa.achievements.index'));

        $this->assertDatabaseHas('student_achievements', [
            'student_id' => $student->id,
            'title' => 'DBL Bali Series Bola Basket 2026',
            'pembina_id' => $pembina->id,
            'extracurricular_id' => $extra->id,
        ]);
    }

    public function test_guru_can_view_juara_siswa_binaan_web_and_api(): void
    {
        $pembina = User::factory()->create([
            'name' => 'Guru Pembina Tari',
            'role' => 'guru',
            'must_change_password' => false,
        ]);

        $otherGuru = User::factory()->create([
            'name' => 'Guru Lain',
            'role' => 'guru',
            'must_change_password' => false,
        ]);

        $extra = Extracurricular::create([
            'name' => 'Seni Tari',
            'pembina_id' => $pembina->id,
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'name' => 'Komang Penari',
            'role' => 'siswa',
            'must_change_password' => false,
        ]);

        StudentAchievement::create([
            'student_id' => $student->id,
            'extracurricular_id' => $extra->id,
            'pembina_id' => $pembina->id,
            'title' => 'Festival Tari Kreasi Bali 2026',
            'field_category' => 'seni_budaya',
            'level' => 'provinsi',
            'rank' => 'Juara 1',
            'organizer' => 'Dinas Kebudayaan Provinsi Bali',
            'achievement_date' => '2026-10-15',
            'status' => 'approved',
        ]);

        // Web page for Guru Pembina
        $webResponse = $this->actingAs($pembina)->get(route('guru.achievements.index'));
        $webResponse->assertStatus(200);
        $webResponse->assertSee('Festival Tari Kreasi Bali 2026');
        $webResponse->assertSee('Komang Penari');

        // API endpoint for Guru Pembina
        $apiResponse = $this->actingAs($pembina, 'sanctum')
            ->withHeaders(['X-Device-ID' => 'test-device-pembina'])
            ->getJson('/api/v1/guru/achievements');
        $apiResponse->assertStatus(200)
            ->assertJsonPath('stats.total', 1)
            ->assertJsonPath('achievements.0.title', 'Festival Tari Kreasi Bali 2026');

        // Other guru should have 0 binaan achievements
        $otherApiResponse = $this->actingAs($otherGuru, 'sanctum')
            ->withHeaders(['X-Device-ID' => 'test-device-other'])
            ->getJson('/api/v1/guru/achievements');
        $otherApiResponse->assertStatus(200)
            ->assertJsonPath('stats.total', 0);
    }

    public function test_admin_export_pdf_and_excel_include_pembina_ekstra(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Sekolah',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $pembina1 = User::factory()->create([
            'name' => 'I Ketut Pembina Futsal, S.Pd.',
            'role' => 'guru',
            'must_change_password' => false,
        ]);

        $pembina2 = User::factory()->create([
            'name' => 'I Made Asisten Futsal, S.Or.',
            'role' => 'guru',
            'must_change_password' => false,
        ]);

        $extra = Extracurricular::create([
            'name' => 'Futsal',
            'pembina_id' => $pembina1->id,
            'is_active' => true,
        ]);
        $extra->teachers()->attach([$pembina1->id, $pembina2->id]);

        $student1 = User::factory()->create([
            'name' => 'Gede Striker',
            'role' => 'siswa',
            'must_change_password' => false,
        ]);

        $student2 = User::factory()->create([
            'name' => 'Kadek Kiper',
            'role' => 'siswa',
            'must_change_password' => false,
        ]);

        $ach1 = StudentAchievement::create([
            'student_id' => $student1->id,
            'extracurricular_id' => $extra->id,
            'pembina_id' => $pembina1->id,
            'title' => 'Turnamen Futsal Antar SMA Se-Bali',
            'field_category' => 'olahraga',
            'level' => 'provinsi',
            'rank' => 'Juara 1',
            'participation_type' => 'beregu',
            'team_code' => 'TIM-FUTSAL-01',
            'organizer' => 'KONI Bali',
            'achievement_date' => '2026-10-20',
            'status' => 'approved',
        ]);

        StudentAchievement::create([
            'student_id' => $student2->id,
            'extracurricular_id' => $extra->id,
            'pembina_id' => $pembina1->id,
            'title' => 'Turnamen Futsal Antar SMA Se-Bali',
            'field_category' => 'olahraga',
            'level' => 'provinsi',
            'rank' => 'Juara 1',
            'participation_type' => 'beregu',
            'team_code' => 'TIM-FUTSAL-01',
            'organizer' => 'KONI Bali',
            'achievement_date' => '2026-10-20',
            'status' => 'approved',
        ]);

        // Both pembinas from the extracurricular should be returned
        $this->assertCount(2, $ach1->fresh(['extracurricular.teachers', 'extracurricular.pembina', 'pembina'])->pembinaList());

        // Test Excel/CSV Export (grouped into 1 competition row with both students and both pembinas)
        $excelResponse = $this->actingAs($admin)->get(route('admin.achievement-report.excel'));
        $excelResponse->assertStatus(200);
        $csvContent = $excelResponse->streamedContent();
        $this->assertStringContainsString('Pembina Ekstra', $csvContent);
        $this->assertStringContainsString('1. I Ketut Pembina Futsal, S.Pd.', $csvContent);
        $this->assertStringContainsString('2. I Made Asisten Futsal, S.Or.', $csvContent);
        $this->assertStringContainsString('1. Gede Striker', $csvContent);
        $this->assertStringContainsString('2. Kadek Kiper', $csvContent);
        $this->assertStringNotContainsString('Status Kurasi', $csvContent);

        // Test PDF Export
        $pdfResponse = $this->actingAs($admin)->get(route('admin.achievement-report.pdf'));
        $pdfResponse->assertStatus(200);

        // Verify the official RAB Kop Sekolah partial renders cleanly with Aksara Bali images & metadata
        $kopHtml = view('partials.kop-surat-pdf')->render();
        $this->assertStringContainsString('PEMERINTAH PROVINSI BALI', $kopHtml);
        $this->assertStringContainsString('DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA', $kopHtml);
        $this->assertStringContainsString('SMA NEGERI 1 GIANYAR', $kopHtml);
        $this->assertStringContainsString('www.dosmangianyar.com', $kopHtml);
        $this->assertStringContainsString('NPSN : 50102079', $kopHtml);
        $this->assertStringContainsString('data:image/png;base64,', $kopHtml);
    }
}

