<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetAllPasswordsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_all_passwords_command_resets_passwords_and_sets_must_change_flag(): void
    {
        $siswa = User::factory()->create([
            'role'                 => 'siswa',
            'nisn'                 => '0012345678',
            'password'             => Hash::make('oldpassword'),
            'must_change_password' => false,
        ]);

        $guru = User::factory()->create([
            'role'                 => 'guru',
            'nip'                  => '198501012010011001',
            'password'             => Hash::make('oldpassword'),
            'must_change_password' => false,
        ]);

        $orangtua = User::factory()->create([
            'role'                 => 'orangtua',
            'phone'                => '081234567890',
            'password'             => Hash::make('oldpassword'),
            'must_change_password' => false,
        ]);

        $admin = User::factory()->create([
            'role'                 => 'admin',
            'password'             => Hash::make('adminpassword'),
            'must_change_password' => false,
        ]);

        $this->artisan('users:reset-passwords --force')
            ->assertExitCode(0);

        $siswa->refresh();
        $guru->refresh();
        $orangtua->refresh();
        $admin->refresh();

        $this->assertTrue(Hash::check('0012345678', $siswa->password));
        $this->assertTrue($siswa->must_change_password);

        $this->assertTrue(Hash::check('198501012010011001', $guru->password));
        $this->assertTrue($guru->must_change_password);

        $this->assertTrue(Hash::check('081234567890', $orangtua->password));
        $this->assertTrue($orangtua->must_change_password);

        $this->assertTrue(Hash::check('adminpassword', $admin->password));
        $this->assertFalse($admin->must_change_password);
    }
}
