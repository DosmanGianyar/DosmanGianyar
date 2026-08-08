<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Extracurricular;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruLayananTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_access_layanan_page_and_api()
    {
        $guru = User::factory()->create(['role' => 'guru', 'nip' => '198001012005011001']);
        $wali = User::factory()->create(['role' => 'guru', 'name' => 'Wali Test']);
        SchoolClass::factory()->create(['name' => 'X MIPA 1', 'grade' => 'X', 'homeroom_teacher_id' => $wali->id]);
        Extracurricular::create(['name' => 'Pramuka', 'is_active' => true]);

        // Web Route Test
        $response = $this->actingAs($guru)->get(route('guru.layanan.index'));
        $response->assertStatus(200);
        $response->assertSee('Layanan &amp; Informasi Direktori Sekolah', false);
        $response->assertSee('Wali Test');
        $response->assertSee('Pramuka');

        // API Endpoint Test
        $apiResponse = $this->actingAs($guru, 'sanctum')->getJson('/api/v1/guru/layanan');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonPath('status', 'success');
    }
}
