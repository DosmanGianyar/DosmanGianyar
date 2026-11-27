<?php

namespace Tests\Feature;

use App\Filament\Widgets\AchievementStatsOverview;
use App\Models\Extracurricular;
use App\Models\StudentAchievement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementStudentCountTest extends TestCase
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

    public function test_same_student_with_multiple_achievements_is_counted_per_achievement(): void
    {
        $guru    = User::factory()->create(['role' => 'guru', 'device_id' => 'guru-cnt-1']);
        $siswaA  = User::factory()->create(['role' => 'siswa', 'device_id' => 's-a']);
        $siswaB  = User::factory()->create(['role' => 'siswa', 'device_id' => 's-b']);
        $extra   = Extracurricular::create(['name' => 'Karya Ilmiah', 'pembina_id' => $guru->id, 'is_active' => true]);

        // Siswa A menang 2 lomba berbeda (perorangan) -> harus dihitung 2 siswa
        StudentAchievement::create([
            'student_id'         => $siswaA->id,
            'extracurricular_id' => $extra->id,
            'pembina_id'         => $guru->id,
            'title'              => 'Olimpiade Fisika',
            'achievement_date'   => '2026-10-01',
            'level'              => 'nasional',
            'rank'               => 'Juara 1',
            'participation_type' => 'perorangan',
            'status'             => 'approved',
        ]);
        StudentAchievement::create([
            'student_id'         => $siswaA->id,
            'extracurricular_id' => $extra->id,
            'pembina_id'         => $guru->id,
            'title'              => 'Olimpiade Matematika',
            'achievement_date'   => '2026-10-15',
            'level'              => 'provinsi',
            'rank'               => 'Juara 2',
            'participation_type' => 'perorangan',
            'status'             => 'approved',
        ]);

        // Lomba beregu 1 dengan Siswa A & Siswa B (plus 1 duplikat entri Siswa A pada tim yg sama) -> harus dihitung 2 siswa
        StudentAchievement::create([
            'student_id'         => $siswaA->id,
            'extracurricular_id' => $extra->id,
            'pembina_id'         => $guru->id,
            'title'              => 'Cerdas Cermat',
            'achievement_date'   => '2026-11-01',
            'level'              => 'nasional',
            'rank'               => 'Juara 1',
            'participation_type' => 'beregu',
            'team_code'          => 'TIM-01',
            'status'             => 'approved',
        ]);
        StudentAchievement::create([
            'student_id'         => $siswaB->id,
            'extracurricular_id' => $extra->id,
            'pembina_id'         => $guru->id,
            'title'              => 'Cerdas Cermat',
            'achievement_date'   => '2026-11-01',
            'level'              => 'nasional',
            'rank'               => 'Juara 1',
            'participation_type' => 'beregu',
            'team_code'          => 'TIM-01',
            'status'             => 'approved',
        ]);

        // Total per prestasi = 1 + 1 + 2 = 4 siswa (walau hanya 2 siswa unik)
        $widget = new class extends AchievementStatsOverview {
            public function exposedStats(): array
            {
                return $this->getStats();
            }
        };
        $stats = $widget->exposedStats();
        $siswaStat = collect($stats)->first(fn ($s) => str_starts_with((string) $s->getLabel(), 'Siswa Berprestasi'));
        $this->assertEquals('4 Siswa', $siswaStat->getValue());
        $this->assertStringContainsString('2 siswa unik', (string) $siswaStat->getDescription());

        // Cek API Guru
        $apiRes = $this->withHeaders($this->authHeaders($guru))
            ->getJson('/api/v1/guru/achievements');
        $apiRes->assertOk()
            ->assertJsonPath('stats.total_students', 4);
    }
}
