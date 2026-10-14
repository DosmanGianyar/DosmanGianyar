<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun khusus untuk kredensial reviewer Google Play Console.
 * Jalankan manual: php artisan db:seed --class=PlayStoreDemoSeeder
 */
class PlayStoreDemoSeeder extends Seeder
{
    private const EMAIL    = 'playstore.demo@sims.sch.id';
    private const PASSWORD = 'PlayStore123!';

    public function run(): void
    {
        $kelas = SchoolClass::first();

        $user = User::updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name'                 => 'Siswa Demo (Play Store Review)',
                'role'                 => 'siswa',
                'password'             => Hash::make(self::PASSWORD),
                'must_change_password' => false,
                'nis'                  => '0000000002',
                'nisn'                 => '0000000002',
                'class_id'             => $kelas?->id,
                'gender'               => 'L',
                'birth_date'           => '2008-01-01',
                'parent_name'          => 'Orang Tua Demo',
                'parent_phone'         => '081234567890',
                'device_id'            => null,
                'device_locked_at'     => null,
            ]
        );

        $user->update([
            'device_id'        => null,
            'device_locked_at' => null,
        ]);

        $this->command->info('Akun demo Play Store siap:');
        $this->command->table(
            ['Email', 'Password'],
            [[self::EMAIL, self::PASSWORD]]
        );
    }
}
