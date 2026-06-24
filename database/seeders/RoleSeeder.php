<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Kelas dummy ──────────────────────────────────────────────────────
        $kelas = SchoolClass::firstOrCreate(
            ['name' => 'X MIPA 1'],
            ['grade' => 'X', 'major' => 'MIPA']
        );

        // ─── Admin ────────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@sims.sch.id'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // ─── Guru ─────────────────────────────────────────────────────────────
        $guru = User::firstOrCreate(
            ['email' => 'guru@sims.sch.id'],
            [
                'name'     => 'Budi Setiawan, S.Pd',
                'password' => Hash::make('password'),
                'role'     => 'guru',
                'nip'      => '198501012010011001',
                'subject'  => 'Matematika',
                'phone'    => '08123456789',
            ]
        );

        $kelas->update(['homeroom_teacher_id' => $guru->id]);

        // ─── Siswa ────────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'siswa@sims.sch.id'],
            [
                'name'         => 'Ahmad Fauzi',
                'password'     => Hash::make('password'),
                'role'         => 'siswa',
                'nis'          => '2025001',
                'class_id'     => $kelas->id,
                'parent_name'  => 'Hasan Fauzi',
                'parent_phone' => '08198765432',
                'birth_date'   => '2008-05-10',
            ]
        );

        // ─── Siswa Pengelola ──────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'pengelola@sims.sch.id'],
            [
                'name'         => 'Citra Dewi',
                'password'     => Hash::make('password'),
                'role'         => 'siswa_pengelola',
                'nis'          => '2025002',
                'class_id'     => $kelas->id,
                'parent_name'  => 'Dewi Rahayu',
                'parent_phone' => '08177654321',
                'birth_date'   => '2008-07-22',
            ]
        );

        $this->command->info('Seeder selesai. Akun yang dibuat:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin',           'admin@sims.sch.id',     'password'],
                ['Guru',            'guru@sims.sch.id',      'password'],
                ['Siswa',           'siswa@sims.sch.id',     'password'],
                ['Siswa Pengelola', 'pengelola@sims.sch.id', 'password'],
            ]
        );
    }
}
