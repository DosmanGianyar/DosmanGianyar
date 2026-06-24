<?php

namespace Tests\Feature;

use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConductTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;
    private User $siswa;
    private SchoolClass $kelas;
    private ConductCategory $categoryPelanggaran;
    private ConductCategory $categoryPrestasi;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->kelas = SchoolClass::create(['name' => 'XII IPA 1', 'grade' => 12]);

        $this->guru = User::factory()->create(['role' => 'guru']);
        $this->siswa = User::factory()->create([
            'role'     => 'siswa',
            'class_id' => $this->kelas->id,
        ]);

        $this->categoryPelanggaran = ConductCategory::create([
            'name'        => 'Tidak Berseragam',
            'point_value' => -10,
            'type'        => 'pelanggaran',
            'context'     => 'sidak',
            'is_active'   => true,
        ]);

        $this->categoryPrestasi = ConductCategory::create([
            'name'        => 'Juara Olimpiade',
            'point_value' => 50,
            'type'        => 'prestasi',
            'context'     => 'lomba',
            'is_active'   => true,
        ]);
    }

    // ─── Page Access ──────────────────────────────────────────────────────────

    public function test_guru_can_access_conduct_index(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.conduct.index'))
            ->assertOk();
    }

    public function test_guru_can_access_conduct_create(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.conduct.create', ['context' => 'sidak']))
            ->assertOk();
    }

    public function test_guru_can_access_conduct_create_lainnya(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.conduct.create', ['context' => 'lainnya_prestasi']))
            ->assertOk();
    }

    public function test_guru_can_store_lainnya_prestasi(): void
    {
        $this->actingAs($this->guru)
            ->post(route('guru.conduct.store'), [
                'student_id' => $this->siswa->id,
                'context'    => 'lainnya_prestasi',
                'note'       => 'Membantu korban bencana di luar sekolah',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('conduct_logs', [
            'student_id' => $this->siswa->id,
            'note'       => 'Membantu korban bencana di luar sekolah',
        ]);
    }

    public function test_siswa_cannot_access_guru_conduct(): void
    {
        $this->actingAs($this->siswa)
            ->get(route('guru.conduct.index'))
            ->assertForbidden();
    }

    public function test_siswa_can_view_own_conduct(): void
    {
        $this->actingAs($this->siswa)
            ->get(route('siswa.conduct.index'))
            ->assertOk();
    }

    // ─── Store Poin ───────────────────────────────────────────────────────────

    public function test_guru_can_store_pelanggaran(): void
    {
        $this->actingAs($this->guru)
            ->post(route('guru.conduct.store'), [
                'student_id'  => $this->siswa->id,
                'category_id' => $this->categoryPelanggaran->id,
                'context'     => 'sidak',
                'note'        => 'Tidak memakai dasi saat upacara',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('conduct_logs', [
            'student_id'  => $this->siswa->id,
            'teacher_id'  => $this->guru->id,
            'category_id' => $this->categoryPelanggaran->id,
            'point'       => -10,
        ]);
    }

    public function test_guru_can_store_prestasi(): void
    {
        $this->actingAs($this->guru)
            ->post(route('guru.conduct.store'), [
                'student_id'  => $this->siswa->id,
                'category_id' => $this->categoryPrestasi->id,
                'context'     => 'lomba',
                'note'        => 'Juara 1 OSN Matematika',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('conduct_logs', [
            'student_id' => $this->siswa->id,
            'point'      => 50,
        ]);
    }

    public function test_conduct_store_validates_required_fields(): void
    {
        $this->actingAs($this->guru)
            ->post(route('guru.conduct.store'), [])
            ->assertSessionHasErrors(['student_id', 'category_id']);
    }

    // ─── Total Points ─────────────────────────────────────────────────────────

    public function test_siswa_total_point_sums_correctly(): void
    {
        ConductLog::create([
            'student_id'  => $this->siswa->id,
            'teacher_id'  => $this->guru->id,
            'category_id' => $this->categoryPelanggaran->id,
            'point'       => -10,
        ]);

        ConductLog::create([
            'student_id'  => $this->siswa->id,
            'teacher_id'  => $this->guru->id,
            'category_id' => $this->categoryPrestasi->id,
            'point'       => 50,
        ]);

        $this->siswa->refresh();
        $this->assertEquals(40, $this->siswa->total_point);
    }

    public function test_negative_total_point_is_negative(): void
    {
        ConductLog::create([
            'student_id'  => $this->siswa->id,
            'teacher_id'  => $this->guru->id,
            'category_id' => $this->categoryPelanggaran->id,
            'point'       => -10,
        ]);

        $this->siswa->refresh();
        $this->assertEquals(-10, $this->siswa->total_point);
    }

    // ─── BK Log Auto-trigger ──────────────────────────────────────────────────

    public function test_bk_log_created_when_points_drop_below_threshold(): void
    {
        // Create enough pelanggaran to drop below -75
        for ($i = 0; $i < 8; $i++) {
            ConductLog::create([
                'student_id'  => $this->siswa->id,
                'teacher_id'  => $this->guru->id,
                'category_id' => $this->categoryPelanggaran->id,
                'point'       => -10,
            ]);
        }

        // Total = -80, below -75 threshold → BK Log should exist
        $this->assertDatabaseHas('bk_logs', [
            'student_id' => $this->siswa->id,
        ]);
    }
}
