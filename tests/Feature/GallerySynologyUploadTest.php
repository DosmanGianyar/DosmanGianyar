<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\GalleryPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GallerySynologyUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('synology');
    }

    private function authHeaders(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return [
            'Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken,
            'X-Device-ID'   => $user->device_id ?: 'test-device-1',
        ];
    }

    public function test_guru_can_upload_up_to_10_auto_converted_photos_and_11th_is_rejected(): void
    {
        $guru = User::factory()->create([
            'role'      => 'guru',
            'device_id' => 'guru-dev-1',
        ]);

        $gallery = Gallery::create([
            'title'               => 'Upacara Hari Guru',
            'event_date'          => '2026-11-25',
            'is_published'        => true,
            'allow_upload'        => true,
            'max_photos_per_user' => 10,
        ]);

        // Upload 10 foto sekaligus (ukuran awal > 1600px untuk membuktikan scaleDown)
        $files = [];
        for ($i = 0; $i < 10; $i++) {
            $files[] = UploadedFile::fake()->image("foto_{$i}.png", 2400, 1800);
        }

        $res = $this->withHeaders($this->authHeaders($guru))
            ->post("/api/v1/galleries/{$gallery->id}/photos", [
                'photos'  => $files,
                'caption' => 'Dokumentasi upacara',
            ]);

        $res->assertStatus(201)
            ->assertJsonPath('data.my_uploaded_count', 10)
            ->assertJsonPath('data.remaining_quota', 0);

        $firstPhoto = GalleryPhoto::where('gallery_id', $gallery->id)->firstOrFail();
        $this->assertEquals('synology', $firstPhoto->storage_disk);
        $this->assertLessThanOrEqual(1600, $firstPhoto->width);
        $this->assertLessThanOrEqual(1600, $firstPhoto->height);
        $this->assertStringEndsWith('.jpg', $firstPhoto->photo);
        Storage::disk('synology')->assertExists($firstPhoto->photo);
        Storage::disk('synology')->assertExists($firstPhoto->thumbnail);

        // Foto ke-11 harus ditolak 422
        $res11 = $this->withHeaders($this->authHeaders($guru))
            ->post("/api/v1/galleries/{$gallery->id}/photos", [
                'photo' => UploadedFile::fake()->image('extra.jpg', 800, 600),
            ]);

        $res11->assertStatus(422);
    }

    public function test_only_admin_selected_students_can_upload_photos(): void
    {
        $allowedStudent = User::factory()->create(['role' => 'siswa', 'device_id' => 'siswa-ok']);
        $otherStudent   = User::factory()->create(['role' => 'siswa', 'device_id' => 'siswa-no']);

        $gallery = Gallery::create([
            'title'               => 'Lomba Sekolah',
            'event_date'          => '2026-11-27',
            'is_published'        => true,
            'allow_upload'        => true,
            'max_photos_per_user' => 10,
        ]);
        $gallery->allowedStudents()->attach($allowedStudent->id);

        // Siswa yang tidak dipilih mendapat 403
        $this->withHeaders($this->authHeaders($otherStudent))
            ->post("/api/v1/galleries/{$gallery->id}/photos", [
                'photo' => UploadedFile::fake()->image('blocked.jpg', 800, 600),
            ])
            ->assertStatus(403);

        // Siswa yang dipilih Admin boleh upload
        $okRes = $this->withHeaders($this->authHeaders($allowedStudent))
            ->post("/api/v1/galleries/{$gallery->id}/photos", [
                'photo' => UploadedFile::fake()->image('allowed.jpg', 1200, 900),
            ]);

        $okRes->assertStatus(201)
            ->assertJsonPath('data.my_uploaded_count', 1)
            ->assertJsonPath('data.remaining_quota', 9);
    }

    public function test_closed_gallery_blocks_upload_and_delete_removes_synology_files(): void
    {
        $guru = User::factory()->create(['role' => 'guru', 'device_id' => 'guru-del']);

        $gallery = Gallery::create([
            'title'               => 'Kegiatan Selesai',
            'event_date'          => '2026-11-27',
            'is_published'        => true,
            'allow_upload'        => true,
            'max_photos_per_user' => 10,
        ]);

        $uploadRes = $this->withHeaders($this->authHeaders($guru))
            ->post("/api/v1/galleries/{$gallery->id}/photos", [
                'photo' => UploadedFile::fake()->image('keep.jpg', 1000, 800),
            ])
            ->assertStatus(201);

        $photoId = $uploadRes->json('data.photos.0.id');
        $photo   = GalleryPhoto::findOrFail($photoId);
        Storage::disk('synology')->assertExists($photo->photo);
        Storage::disk('synology')->assertExists($photo->thumbnail);

        // Tutup upload → guru pun ditolak 403
        $gallery->update(['allow_upload' => false]);
        $this->withHeaders($this->authHeaders($guru))
            ->post("/api/v1/galleries/{$gallery->id}/photos", [
                'photo' => UploadedFile::fake()->image('after_close.jpg', 800, 600),
            ])
            ->assertStatus(403);

        // Hapus foto oleh pengunggah → file di disk synology ikut terhapus
        $this->withHeaders($this->authHeaders($guru))
            ->delete("/api/v1/galleries/{$gallery->id}/photos/{$photoId}")
            ->assertStatus(200);

        Storage::disk('synology')->assertMissing($photo->photo);
        Storage::disk('synology')->assertMissing($photo->thumbnail);
        $this->assertDatabaseMissing('gallery_photos', ['id' => $photoId]);
    }
}
