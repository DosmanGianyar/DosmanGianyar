<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Permit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StorageCleanupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_delete_attendance_photos_removes_physical_files_and_clears_db_columns(): void
    {
        $file1 = UploadedFile::fake()->image('photo1.jpg');
        $file2 = UploadedFile::fake()->image('photo2.jpg');

        $path1 = $file1->store('attendances', 'public');
        $path2 = $file2->store('attendances', 'public');

        $attendance = Attendance::create([
            'user_id'          => $this->admin->id,
            'date'             => '2026-07-01',
            'check_in_time'    => '07:00:00',
            'check_out_time'   => '15:00:00',
            'photo'            => $path1,
            'check_out_photo'  => $path2,
            'status'           => 'hadir',
        ]);

        Storage::disk('public')->assertExists($path1);
        Storage::disk('public')->assertExists($path2);

        Livewire::actingAs($this->admin)
            ->test(\App\Filament\Pages\StorageCleanupPage::class)
            ->set('attendance_start_date', '2026-07-01')
            ->set('attendance_end_date', '2026-07-31')
            ->call('deleteAttendancePhotos');

        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertMissing($path2);

        $attendance->refresh();
        $this->assertNull($attendance->photo);
        $this->assertNull($attendance->check_out_photo);
        $this->assertEquals('hadir', $attendance->status); // Record database tetap utuh
    }

    public function test_delete_permit_files_removes_physical_files_and_clears_db_column(): void
    {
        $file = UploadedFile::fake()->create('surat.pdf', 100);
        $path = $file->store('permits', 'public');

        $permit = Permit::create([
            'student_id' => $this->admin->id,
            'type'       => 'izin',
            'start_date' => '2026-07-10',
            'end_date'   => '2026-07-12',
            'reason'     => 'Acara keluarga',
            'file'       => $path,
            'status'     => 'approved',
        ]);

        Storage::disk('public')->assertExists($path);

        Livewire::actingAs($this->admin)
            ->test(\App\Filament\Pages\StorageCleanupPage::class)
            ->set('permit_start_date', '2026-07-01')
            ->set('permit_end_date', '2026-07-31')
            ->call('deletePermitFiles');

        Storage::disk('public')->assertMissing($path);

        $permit->refresh();
        $this->assertNull($permit->file);
        $this->assertEquals('approved', $permit->status); // Record database tetap utuh
    }
}
