<?php

namespace Tests\Feature;

use App\Models\Permit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermitAttachmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_submit_permit_without_attachment(): void
    {
        Storage::fake('public');
        $siswa = User::factory()->create(['role' => 'siswa']);
        Sanctum::actingAs($siswa);

        $response = $this->withHeaders(['X-Device-ID' => 'test-device'])->postJson('/api/v1/permits', [
            'type'       => 'izin',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->toDateString(),
            'reason'     => 'Ada keperluan keluarga.',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('permit.file_url', null);
        $this->assertDatabaseHas('permits', ['student_id' => $siswa->id, 'file' => null]);
    }

    public function test_siswa_can_submit_permit_with_attachment(): void
    {
        Storage::fake('public');
        $siswa = User::factory()->create(['role' => 'siswa']);
        Sanctum::actingAs($siswa);

        $response = $this->withHeaders(['X-Device-ID' => 'test-device'])->postJson('/api/v1/permits', [
            'type'       => 'sakit',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->toDateString(),
            'reason'     => 'Demam tinggi.',
            'file'       => UploadedFile::fake()->image('surat_sakit.jpg'),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('permit.file_url', fn ($url) => $url !== null);

        $permit = Permit::where('student_id', $siswa->id)->firstOrFail();
        $this->assertNotNull($permit->file);
        Storage::disk('public')->assertExists($permit->file);
    }

    public function test_permit_attachment_rejects_disallowed_file_type(): void
    {
        Storage::fake('public');
        $siswa = User::factory()->create(['role' => 'siswa']);
        Sanctum::actingAs($siswa);

        $response = $this->withHeaders(['X-Device-ID' => 'test-device'])->postJson('/api/v1/permits', [
            'type'       => 'izin',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->toDateString(),
            'reason'     => 'Tes.',
            'file'       => UploadedFile::fake()->create('lampiran.exe', 100),
        ]);

        $response->assertStatus(422);
    }

    public function test_guru_permit_payload_includes_file_url_when_present(): void
    {
        Storage::fake('public');
        $siswa = User::factory()->create(['role' => 'siswa']);
        $guru  = User::factory()->create(['role' => 'admin']);

        $storedPath = UploadedFile::fake()->image('surat.jpg')->store('permits', 'public');
        Permit::create([
            'student_id' => $siswa->id,
            'type'       => 'dispensasi',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->toDateString(),
            'reason'     => 'Lomba sekolah.',
            'file'       => $storedPath,
            'status'     => 'pending',
        ]);

        Sanctum::actingAs($guru);

        $response = $this->withHeaders(['X-Device-ID' => 'test-device'])->getJson('/api/v1/guru/permits?status=pending');

        $response->assertOk();
        $response->assertJsonPath('data.0.file_url', fn ($url) => $url !== null);
    }
}
