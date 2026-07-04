<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'Dosman123';

    public function run(): void
    {
        // ─── Kelas dummy ──────────────────────────────────────────────────────
        $kelas = SchoolClass::firstOrCreate(
            ['name' => 'X MIPA 1'],
            ['grade' => 'X', 'major' => 'MIPA']
        );

        $accounts = [
            [
                'role'  => 'admin',
                'email' => 'admin@sims.sch.id',
                'name'  => 'Administrator',
                'extra' => [],
            ],
            [
                'role'  => 'admin_kesiswaan',
                'email' => 'kesiswaan@sims.sch.id',
                'name'  => 'Admin Kesiswaan',
                'extra' => [],
            ],
            [
                'role'  => 'admin_kurikulum',
                'email' => 'kurikulum@sims.sch.id',
                'name'  => 'Admin Kurikulum',
                'extra' => [],
            ],
            [
                'role'  => 'admin_sarpras',
                'email' => 'sarpras@sims.sch.id',
                'name'  => 'Admin Sarpras',
                'extra' => [],
            ],
            [
                'role'  => 'admin_humas',
                'email' => 'humas@sims.sch.id',
                'name'  => 'Admin Humas',
                'extra' => [],
            ],
            [
                'role'  => 'guru',
                'email' => 'guru@sims.sch.id',
                'name'  => 'Budi Setiawan, S.Pd',
                'extra' => [
                    'nip'     => '198501012010011001',
                    'subject' => 'Matematika',
                    'phone'   => '08123456789',
                ],
            ],
            [
                'role'  => 'siswa',
                'email' => 'siswa@sims.sch.id',
                'name'  => 'Ahmad Fauzi',
                'extra' => [
                    'nis'          => '2025001',
                    'class_id'     => $kelas->id,
                    'parent_name'  => 'Hasan Fauzi',
                    'parent_phone' => '08198765432',
                    'birth_date'   => '2008-05-10',
                ],
            ],
            [
                'role'  => 'siswa_pengelola',
                'email' => 'pengelola@sims.sch.id',
                'name'  => 'Citra Dewi',
                'extra' => [
                    'nis'          => '2025002',
                    'class_id'     => $kelas->id,
                    'parent_name'  => 'Dewi Rahayu',
                    'parent_phone' => '08177654321',
                    'birth_date'   => '2008-07-22',
                ],
            ],
        ];

        $rows = [];
        foreach ($accounts as $acc) {
            $exists = User::where('email', $acc['email'])->exists();

            if (! $exists) {
                User::create(array_merge(
                    ['email' => $acc['email'], 'name' => $acc['name'], 'role' => $acc['role']],
                    ['password' => Hash::make(self::DEFAULT_PASSWORD)],
                    $acc['extra'],
                ));
            }

            $rows[] = [
                $acc['role'],
                $acc['email'],
                $exists ? '(sudah ada, tidak diubah)' : self::DEFAULT_PASSWORD,
                $exists ? 'SKIP' : 'DIBUAT',
            ];
        }

        // Set homeroom teacher jika belum
        $guru = User::where('email', 'guru@sims.sch.id')->first();
        if ($guru && ! $kelas->homeroom_teacher_id) {
            $kelas->update(['homeroom_teacher_id' => $guru->id]);
        }

        $this->command->info('');
        $this->command->info('=== Status Akun Per Role ===');
        $this->command->table(
            ['Role', 'Email', 'Password', 'Status'],
            $rows
        );
    }
}
