<?php

namespace Tests\Feature;

use App\Models\DamageReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaDamageReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_store_damage_report_without_asset_and_photo(): void
    {
        $siswa = User::factory()->create([
            'role' => 'siswa',
        ]);

        $response = $this->actingAs($siswa, 'sanctum')
            ->withHeaders(['X-Device-ID' => 'test-device-id'])
            ->postJson('/api/v1/siswa/sarpras/damage-reports', [
                'title'       => 'Papan tulis dan kipas angin, XI B1',
                'description' => 'kipas angin kami rusak tidak bisa di hidupkan, mohon di tindak lanjuti, terimakasih',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('damage_reports', [
            'reporter_id' => $siswa->id,
            'title'       => 'Papan tulis dan kipas angin, XI B1',
            'status'      => 'pending',
        ]);

        // Verify siswa can retrieve damage reports list without 500 error
        $listResponse = $this->actingAs($siswa, 'sanctum')
            ->withHeaders(['X-Device-ID' => 'test-device-id'])
            ->getJson('/api/v1/siswa/sarpras/damage-reports');

        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json());
        $this->assertEquals('Papan tulis dan kipas angin, XI B1', $listResponse->json('0.title'));
    }
}
