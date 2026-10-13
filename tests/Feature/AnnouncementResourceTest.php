<?php

namespace Tests\Feature;

use App\Filament\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnnouncementResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_announcement_resource_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(AnnouncementResource::getUrl('index'));

        $response->assertSuccessful();
    }

    public function test_admin_humas_can_access_announcement_resource_page(): void
    {
        $adminHumas = User::factory()->create(['role' => 'admin_humas']);

        $response = $this->actingAs($adminHumas)->get(AnnouncementResource::getUrl('index'));

        $response->assertSuccessful();
    }

    public function test_admin_can_create_announcement_via_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(AnnouncementResource\Pages\CreateAnnouncement::class)
            ->fillForm([
                'title'   => 'Pengumuman Libur Semester',
                'body'    => 'Pengumuman libur semester ganjil dimulai minggu depan.',
                'target'  => 'all',
                'is_pinned' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('announcements', [
            'title'     => 'Pengumuman Libur Semester',
            'target'    => 'all',
            'is_pinned' => true,
            'author_id' => $admin->id,
        ]);
    }

    public function test_api_announcement_includes_image_url(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $announcement = Announcement::create([
            'title'        => 'Lomba Poster 2026',
            'body'         => 'Ikuti lomba poster tingkat sekolah.',
            'image'        => 'announcements/poster.jpg',
            'target'       => 'all',
            'is_active'    => true,
            'published_at' => now()->subHour(),
            'author_id'    => $student->id,
        ]);

        $response = $this->actingAs($student)
            ->withHeaders(['X-Device-ID' => 'test-device-id'])
            ->getJson('/api/v1/announcements');

        $response->assertSuccessful();
        $response->assertJsonPath('announcements.0.title', 'Lomba Poster 2026');
        $this->assertNotNull($response->json('announcements.0.image_url'));
        $this->assertStringContainsString('storage/announcements/poster.jpg', $response->json('announcements.0.image_url'));

        $allResponse = $this->actingAs($student)
            ->withHeaders(['X-Device-ID' => 'test-device-id'])
            ->getJson('/api/v1/announcements/all');
        $allResponse->assertSuccessful();
        $this->assertNotNull($allResponse->json('announcements.0.image_url'));
        $this->assertStringContainsString('storage/announcements/poster.jpg', $allResponse->json('announcements.0.image_url'));
    }

    public function test_admin_can_see_preview_modal_for_announcement(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $announcement = Announcement::create([
            'title'         => 'Pengumuman Penting',
            'body'          => 'Isi pengumuman penting.',
            'image'         => 'announcements/banner.webp',
            'target'        => 'siswa',
            'is_active'     => true,
            'show_as_modal' => true,
            'published_at'  => now(),
            'author_id'     => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(AnnouncementResource\Pages\ListAnnouncements::class)
            ->assertTableActionExists('preview');
    }
}
