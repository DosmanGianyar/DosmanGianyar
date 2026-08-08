<?php

namespace Tests\Feature;

use App\Models\Extracurricular;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiPembinaExtracurricularTest extends TestCase
{
    use RefreshDatabase;

    public function test_extracurricular_can_have_multiple_pembinas(): void
    {
        $teacher1 = User::factory()->create(['role' => 'guru', 'name' => 'Drs. Wayan']);
        $teacher2 = User::factory()->create(['role' => 'guru', 'name' => 'Made S.Pd']);

        $extra = Extracurricular::create([
            'name'       => 'PMR Wira',
            'is_active'  => true,
            'pembina_id' => $teacher1->id,
        ]);

        $extra->teachers()->sync([$teacher1->id, $teacher2->id]);

        $this->assertCount(2, $extra->fresh()->teachers);
        $this->assertTrue($extra->isTeacherPembina($teacher1->id));
        $this->assertTrue($extra->isTeacherPembina($teacher2->id));
        $this->assertStringContainsString('Drs. Wayan', $extra->pembina_names);
        $this->assertStringContainsString('Made S.Pd', $extra->pembina_names);
    }

    public function test_api_returns_combined_pembina_names(): void
    {
        $student  = User::factory()->create(['role' => 'siswa']);
        $teacher1 = User::factory()->create(['role' => 'guru', 'name' => 'Pembina Satu']);
        $teacher2 = User::factory()->create(['role' => 'guru', 'name' => 'Pembina Dua']);

        $extra = Extracurricular::create([
            'name'      => 'Pramuka',
            'is_active' => true,
        ]);
        $extra->teachers()->sync([$teacher1->id, $teacher2->id]);

        $response = $this->actingAs($student)->getJson('/api/v1/extracurriculars', [
            'X-Device-ID' => 'test-device-id',
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name'         => 'Pramuka',
            'pembina_name' => 'Pembina Satu, Pembina Dua',
        ]);
    }
}
