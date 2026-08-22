<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProfileSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_save_buku_induk_profile_via_web(): void
    {
        AppSetting::set('allow_student_profile_edit', true);

        $siswa = User::factory()->create([
            'role' => 'siswa',
        ]);

        $payload = [
            'nickname'             => 'Kadek',
            'birth_place'          => 'Gianyar',
            'religion'             => 'Hindu',
            'citizenship'          => 'WNI',
            'child_order'          => 2,
            'siblings_count'       => 3,
            'orphan_status'        => 'Lengkap',
            'daily_language'       => 'Bahasa Bali',
            'address'              => 'Jl. Ngurah Rai No. 15',
            'living_with'          => 'Orang Tua',
            'prev_school_name'     => 'SMP N 2 Gianyar',
            'father_name'          => 'I Wayan Sudiarta',
            'father_birth_place'   => 'Gianyar',
            'father_religion'      => 'Hindu',
            'mother_name'          => 'Ni Made Asri',
            'mother_birth_place'   => 'Denpasar',
            'guardian_name'        => 'I Gede Budi',
            'physical_disability'  => '-',
        ];

        $response = $this->actingAs($siswa)->put(route('siswa.profile.update'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $siswa->refresh();
        $this->assertEquals('Kadek', $siswa->nickname);
        $this->assertEquals('Gianyar', $siswa->birth_place);
        $this->assertEquals('Hindu', $siswa->religion);
        $this->assertEquals(2, $siswa->child_order);
        $this->assertEquals('SMP N 2 Gianyar', $siswa->prev_school_name);
        $this->assertEquals('I Wayan Sudiarta', $siswa->father_name);
        $this->assertEquals('Ni Made Asri', $siswa->mother_name);
    }

    public function test_siswa_can_save_buku_induk_profile_via_api(): void
    {
        AppSetting::set('allow_student_profile_edit', true);

        $siswa = User::factory()->create([
            'role' => 'siswa',
        ]);

        $payload = [
            'nickname'             => 'Wayan',
            'birth_place'          => 'Ubud',
            'religion'             => 'Hindu',
            'child_order'          => 1,
            'prev_school_name'     => 'SMP N 1 Ubud',
            'father_name'          => 'I Nyoman Rai',
            'mother_name'          => 'Ni Ketut Sukma',
        ];

        $response = $this->actingAs($siswa, 'sanctum')->putJson('/api/v1/auth/profile', $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('user.nickname', 'Wayan');
        $response->assertJsonPath('user.birth_place', 'Ubud');
        $response->assertJsonPath('user.father_name', 'I Nyoman Rai');

        $siswa->refresh();
        $this->assertEquals('Wayan', $siswa->nickname);
        $this->assertEquals('Ubud', $siswa->birth_place);
    }

    public function test_siswa_cannot_update_profile_when_locked(): void
    {
        AppSetting::set('allow_student_profile_edit', false);

        $siswa = User::factory()->create([
            'role' => 'siswa',
        ]);

        $response = $this->actingAs($siswa)->put(route('siswa.profile.update'), [
            'nickname' => 'Hacker',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull($siswa->fresh()->nickname);
    }
}
