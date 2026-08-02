<?php

namespace Tests\Feature;

use App\Filament\Resources\PermitResource\Pages\ListPermits;
use App\Models\Permit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PermitResourceBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_approve_permits(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $permit1 = Permit::create([
            'student_id' => $siswa->id,
            'type'       => 'izin',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->toDateString(),
            'reason'     => 'Izin 1',
            'status'     => 'pending',
            'file'       => 'permits/test1.jpg',
        ]);

        $permit2 = Permit::create([
            'student_id' => $siswa->id,
            'type'       => 'sakit',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->toDateString(),
            'reason'     => 'Sakit 2',
            'status'     => 'pending',
            'file'       => 'permits/test2.jpg',
        ]);

        $this->actingAs($admin);

        Livewire::test(ListPermits::class)
            ->callTableBulkAction('bulk_approve', [$permit1, $permit2]);

        $this->assertDatabaseHas('permits', ['id' => $permit1->id, 'status' => 'approved']);
        $this->assertDatabaseHas('permits', ['id' => $permit2->id, 'status' => 'approved']);
    }

    public function test_admin_can_bulk_reject_permits(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $permit = Permit::create([
            'student_id' => $siswa->id,
            'type'       => 'dispensasi',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->toDateString(),
            'reason'     => 'Lomba',
            'status'     => 'pending',
            'file'       => 'permits/test3.jpg',
        ]);

        $this->actingAs($admin);

        Livewire::test(ListPermits::class)
            ->callTableBulkAction('bulk_reject', [$permit], [
                'rejection_note' => 'Dokumen kurang lengkap.',
            ]);

        $this->assertDatabaseHas('permits', [
            'id'             => $permit->id,
            'status'         => 'rejected',
            'rejection_note' => 'Dokumen kurang lengkap.',
        ]);
    }
}
