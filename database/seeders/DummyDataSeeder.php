<?php

namespace Database\Seeders;

use App\Models\AchievementCategory;
use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Models\StudentAchievement;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $siswa = User::where('email', 'siswa@sims.sch.id')->first();
        $guru  = User::where('email', 'guru@sims.sch.id')->first();

        if (! $siswa || ! $guru) {
            $this->command->error('Jalankan RoleSeeder dulu.');
            return;
        }

        // ── Conduct Categories ───────────────────────────────────────────────
        $categories = [
            // Pelanggaran Sidak
            ['name' => 'Terlambat masuk sekolah',        'point_value' => -5,  'type' => 'pelanggaran', 'context' => 'sidak'],
            ['name' => 'Seragam tidak lengkap',           'point_value' => -15, 'type' => 'pelanggaran', 'context' => 'sidak'],
            // Pelanggaran Kelas
            ['name' => 'Tidak mengerjakan tugas',         'point_value' => -10, 'type' => 'pelanggaran', 'context' => 'kelas'],
            ['name' => 'Menggunakan HP saat pelajaran',   'point_value' => -10, 'type' => 'pelanggaran', 'context' => 'kelas'],
            ['name' => 'Gaduh di dalam kelas',            'point_value' => -5,  'type' => 'pelanggaran', 'context' => 'kelas'],
            ['name' => 'Tidak hadir tanpa keterangan',    'point_value' => -20, 'type' => 'pelanggaran', 'context' => 'kelas'],
            // Prestasi Akademik
            ['name' => 'Aktif berpendapat di kelas',      'point_value' => 5,   'type' => 'prestasi',    'context' => 'akademik'],
            ['name' => 'Membantu sesama',                 'point_value' => 5,   'type' => 'prestasi',    'context' => 'akademik'],
            ['name' => 'Nilai ujian sempurna',            'point_value' => 10,  'type' => 'prestasi',    'context' => 'akademik'],
            // Prestasi Lomba
            ['name' => 'Mewakili sekolah dalam lomba',    'point_value' => 20,  'type' => 'prestasi',    'context' => 'lomba'],
        ];

        foreach ($categories as $cat) {
            ConductCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['point_value' => $cat['point_value'], 'type' => $cat['type'], 'context' => $cat['context'], 'is_active' => true]
            );
        }

        $catTerlambat  = ConductCategory::where('name', 'Terlambat masuk sekolah')->first();
        $catHP         = ConductCategory::where('name', 'Menggunakan HP saat pelajaran')->first();
        $catSeragam    = ConductCategory::where('name', 'Seragam tidak lengkap')->first();
        $catGaduh      = ConductCategory::where('name', 'Gaduh di dalam kelas')->first();
        $catTugas      = ConductCategory::where('name', 'Tidak mengerjakan tugas')->first();
        $catAktif      = ConductCategory::where('name', 'Aktif berpendapat di kelas')->first();
        $catLomba      = ConductCategory::where('name', 'Mewakili sekolah dalam lomba')->first();
        $catNilai      = ConductCategory::where('name', 'Nilai ujian sempurna')->first();

        // ── Conduct Logs ─────────────────────────────────────────────────────
        $logs = [
            ['category' => $catTerlambat, 'note' => 'Terlambat 25 menit',              'point' => -5,  'days' => 3],
            ['category' => $catHP,        'note' => 'Bermain game saat pelajaran IPA',  'point' => -10, 'days' => 8],
            ['category' => $catAktif,     'note' => 'Aktif menjawab pertanyaan guru',   'point' => 5,   'days' => 10],
            ['category' => $catSeragam,   'note' => 'Tidak memakai dasi',               'point' => -15, 'days' => 14],
            ['category' => $catLomba,     'note' => 'Mewakili sekolah OSN Matematika',  'point' => 20,  'days' => 18],
            ['category' => $catGaduh,     'note' => 'Berisik saat ulangan berlangsung', 'point' => -5,  'days' => 22],
            ['category' => $catTugas,     'note' => 'Tidak mengumpulkan PR Fisika',     'point' => -10, 'days' => 28],
            ['category' => $catNilai,     'note' => 'Nilai Matematika 100',             'point' => 10,  'days' => 35],
            ['category' => $catTerlambat, 'note' => 'Terlambat 15 menit',              'point' => -5,  'days' => 40],
            ['category' => $catAktif,     'note' => 'Presentasi terbaik di kelas',      'point' => 5,   'days' => 45],
        ];

        foreach ($logs as $log) {
            ConductLog::create([
                'student_id'  => $siswa->id,
                'teacher_id'  => $guru->id,
                'category_id' => $log['category']?->id,
                'point'       => $log['point'],
                'note'        => $log['note'],
                'created_at'  => now()->subDays($log['days']),
                'updated_at'  => now()->subDays($log['days']),
            ]);
        }

        // ── Student Achievements ──────────────────────────────────────────────
        $catAkademik   = AchievementCategory::where('name', 'like', '%Akademik%')->first();
        $catOlahraga   = AchievementCategory::where('name', 'like', '%Olahraga%')->first();
        $catSeni       = AchievementCategory::where('name', 'like', '%Seni%')->first();
        $catTeknologi  = AchievementCategory::where('name', 'like', '%Teknologi%')->first();

        $achievements = [
            [
                'title'            => 'Juara 1 Olimpiade Matematika',
                'category'         => $catAkademik,
                'level'            => 'kabupaten',
                'rank'             => 'Juara 1',
                'achievement_date' => now()->subDays(20),
                'description'      => 'Meraih juara 1 pada Olimpiade Matematika tingkat Kabupaten Gianyar.',
                'status'           => 'approved',
                'days'             => 18,
            ],
            [
                'title'            => 'Juara 2 Lomba Debat Bahasa Indonesia',
                'category'         => $catAkademik,
                'level'            => 'sekolah',
                'rank'             => 'Juara 2',
                'achievement_date' => now()->subDays(40),
                'description'      => 'Meraih juara 2 lomba debat bahasa Indonesia antar kelas.',
                'status'           => 'approved',
                'days'             => 38,
            ],
            [
                'title'            => 'Peserta Lomba Karya Ilmiah Remaja',
                'category'         => $catTeknologi ?? $catAkademik,
                'level'            => 'provinsi',
                'rank'             => 'Peserta',
                'achievement_date' => now()->subDays(60),
                'description'      => 'Mewakili sekolah pada Lomba Karya Ilmiah Remaja tingkat Provinsi Bali.',
                'status'           => 'approved',
                'days'             => 58,
            ],
            [
                'title'            => 'Juara 3 Renang Gaya Bebas 50m',
                'category'         => $catOlahraga,
                'level'            => 'kabupaten',
                'rank'             => 'Juara 3',
                'achievement_date' => now()->subDays(15),
                'description'      => 'Meraih juara 3 cabang renang gaya bebas 50m Porseni Kabupaten.',
                'status'           => 'pending',
                'days'             => 13,
            ],
            [
                'title'            => 'Penari Terbaik Festival Seni Budaya',
                'category'         => $catSeni,
                'level'            => 'sekolah',
                'rank'             => 'Terbaik',
                'achievement_date' => now()->subDays(5),
                'description'      => 'Dinobatkan sebagai penari terbaik pada Festival Seni Budaya Sekolah.',
                'status'           => 'pending',
                'days'             => 3,
            ],
        ];

        foreach ($achievements as $ach) {
            StudentAchievement::create([
                'student_id'       => $siswa->id,
                'category_id'      => $ach['category']?->id ?? $catAkademik?->id,
                'title'            => $ach['title'],
                'level'            => $ach['level'],
                'rank'             => $ach['rank'],
                'achievement_date' => $ach['achievement_date'],
                'description'      => $ach['description'],
                'status'           => $ach['status'],
                'verified_by'      => $ach['status'] === 'approved' ? $guru->id : null,
                'verified_at'      => $ach['status'] === 'approved' ? now()->subDays($ach['days']) : null,
                'photo'            => null,
                'created_at'       => now()->subDays($ach['days']),
                'updated_at'       => now()->subDays($ach['days']),
            ]);
        }

        $this->command->info('Dummy data berhasil dibuat:');
        $this->command->table(
            ['Data', 'Jumlah'],
            [
                ['Conduct Categories', count($categories)],
                ['Conduct Logs',       count($logs)],
                ['Student Achievements', count($achievements)],
            ]
        );
    }
}
