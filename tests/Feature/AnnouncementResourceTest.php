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
}
