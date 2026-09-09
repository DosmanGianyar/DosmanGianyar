<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\GuruResource;
use App\Filament\Resources\PasswordResetRequestResource;
use App\Filament\Resources\UserResource;
use App\Filament\Support\AdminAccess;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reset_password_user_exists_and_can_access_panel(): void
    {
        $user = User::where('role', 'admin_reset_password')->first();
        $this->assertNotNull($user);
        $this->assertEquals('resetpassword@sims.sch.id', $user->email);
        $this->assertEquals('admin_reset', $user->nip);
        $this->assertTrue(Hash::check('Dosman123', $user->password));

        $panel = Filament::getPanel('admin');
        $this->assertTrue($user->canAccessPanel($panel));
        $this->assertTrue($user->isAdminResetPassword());
    }

    public function test_admin_reset_password_sidebar_and_resource_access_restrictions(): void
    {
        $user = User::where('role', 'admin_reset_password')->first();
        $this->actingAs($user);

        // Can access PasswordResetRequestResource
        $this->assertTrue(PasswordResetRequestResource::canAccess());

        // Dynamic navigation group should be null (standalone top-level item)
        $this->assertNull(PasswordResetRequestResource::getNavigationGroup());

        // Dashboard navigation should be hidden
        $this->assertFalse(Dashboard::shouldRegisterNavigation());

        // Cannot access other resources
        $this->assertFalse(UserResource::canAccess());
        $this->assertFalse(GuruResource::canAccess());

        // AdminAccess checks
        $this->assertFalse(AdminAccess::can('Kurikulum'));
        $this->assertFalse(AdminAccess::can('Presensi Siswa'));
        $this->assertFalse(AdminAccess::can('Sarpras'));
        $this->assertFalse(AdminAccess::can('Humas'));
        $this->assertFalse(AdminAccess::can('Manajemen User'));
    }

    public function test_superadmin_still_has_full_access(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            $admin = User::factory()->create(['role' => 'admin', 'email' => 'admintest@sims.sch.id']);
        }
        $this->actingAs($admin);

        $this->assertTrue(PasswordResetRequestResource::canAccess());
        $this->assertEquals('Manajemen User', PasswordResetRequestResource::getNavigationGroup());
        $this->assertTrue(Dashboard::shouldRegisterNavigation());
        $this->assertTrue(UserResource::canAccess());
        $this->assertTrue(GuruResource::canAccess());
        $this->assertTrue(AdminAccess::can('Kurikulum'));
    }

    public function test_admin_reset_password_only_queries_students(): void
    {
        $adminReset = User::where('role', 'admin_reset_password')->first();

        // Create a teacher user and a student user
        $guru = User::create([
            'name'     => 'Guru Test ' . uniqid(),
            'nip'      => '9999' . rand(1000, 9999),
            'email'    => 'gurutest_' . uniqid() . '@sims.sch.id',
            'role'     => 'guru',
            'password' => Hash::make('Guru123'),
        ]);

        $siswa = User::create([
            'name'     => 'Siswa Test ' . uniqid(),
            'nisn'     => '8888' . rand(1000, 9999),
            'email'    => 'siswatest_' . uniqid() . '@sims.sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('Siswa123'),
        ]);

        $reqGuru = PasswordResetRequest::create([
            'user_id'      => $guru->id,
            'identifier'   => $guru->nip,
            'status'       => 'pending',
            'requested_at' => now(),
        ]);

        $reqSiswa = PasswordResetRequest::create([
            'user_id'      => $siswa->id,
            'identifier'   => $siswa->nisn,
            'status'       => 'pending',
            'requested_at' => now(),
        ]);

        $this->actingAs($adminReset);

        $results = PasswordResetRequestResource::getEloquentQuery()->pluck('id')->toArray();
        $this->assertContains($reqSiswa->id, $results);
        $this->assertNotContains($reqGuru->id, $results);

        // Cleanup
        $reqGuru->delete();
        $reqSiswa->delete();
        $guru->delete();
        $siswa->delete();
    }
}
