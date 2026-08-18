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
            // Sample Kurasi Resmi 5 Poin (Ni Made Selsa Sanjiwani - Porsenijar 2025)
            [
                'title'                       => 'Juara II Catur Porsenijar 2025 (Ni Made Selsa Sanjiwani)',
                'event_name'                  => 'Pekan Olahraga dan Seni Pelajar (PORSENIJAR) 2025',
                'organizer'                   => 'Dinas Pendidikan Kepemudaan dan Olahraga Provinsi Bali',
                'category'                    => $catOlahraga,
                'field_category'              => 'olahraga',
                'level'                       => 'provinsi',
                'rank'                        => 'Juara 2',
                'achievement_date'            => now()->subDays(10),
                'description'                 => 'Prestasi Kejuaraan Catur Porsenijar Bali 2025 atas nama Ni Made Selsa Sanjiwani. Berkas pengajuan dilengkapi 5 poin bukti Kurasi Talenta Puspresnas/Kemendikdasmen.',
                'status'                      => 'approved',
                'curation_status'             => 'curated',
                'is_curation'                 => true,
                'doc_standard_checklist'      => ['visi_misi', 'tujuan', 'prosedur', 'kriteria_penilaian'],
                'doc_standard_file'           => 'kurasi/1. Dokumen Standar Penyelenggaraan Cabang Ajang Kompetensi Talenta/Panduan PORSENIJAR.pdf',
                'doc_standard_url'            => 'https://sman1-gianyar.sch.id/kurasi/porsenijar2025',
                'selection_level'             => '3_tingkat',
                'selection_level_file'        => 'kurasi/2. Tingkatan Seleksi Ajang Kompetensi Talenta/Panduan PORSENIJAR.pdf',
                'selection_level_url'         => 'https://sman1-gianyar.sch.id/kurasi/tahapan-seleksi',
                'frequency_consistency'       => 'berturut_gt3',
                'frequency_consistency_file'  => 'kurasi/3. Konsistensi Frekuensi Penyelenggaraan Cabang Ajang Kompetensi Talenta/Contoh konsistensi frekuensi penyelenggaran cabang ajang kompetensi talenta.docx',
                'infrastructure_type'         => 'standar_nasional',
                'infrastructure_file'         => 'kurasi/4. Sarana Prasarana Ajang Kompetensi Talenta/Foto Saat Lomba.png',
                'reward_types'                => ['piagam_sertifikat', 'medali', 'uang_pembinaan'],
                'reward_certificate_file'     => 'kurasi/5. Penghargaan dan Apresiasi yang disediakan oleh penyelenggara Ajang Kompetensi Talenta/Piagam juara II Porsenijar 2025 - 48_Ni Made Selsa Sanjiwani.pdf',
                'reward_photo_file'           => 'kurasi/5. Penghargaan dan Apresiasi yang disediakan oleh penyelenggara Ajang Kompetensi Talenta/IMG_6034 - 48_Ni Made Selsa Sanjiwani.png',
                'reward_recap_file'           => 'kurasi/5. Penghargaan dan Apresiasi yang disediakan oleh penyelenggara Ajang Kompetensi Talenta/Rekap Pemenang.pdf',
                'certificate'                 => 'kurasi/5. Penghargaan dan Apresiasi yang disediakan oleh penyelenggara Ajang Kompetensi Talenta/Piagam juara II Porsenijar 2025 - 48_Ni Made Selsa Sanjiwani.pdf',
                'assignment_letter'           => 'kurasi/1. Dokumen Standar Penyelenggaraan Cabang Ajang Kompetensi Talenta/KETENTUAN UMUM  SELEKSI PORSENIJAR CATUR 2025 - 48_Ni Made Selsa Sanjiwani.pdf',
                'photo'                       => 'kurasi/5. Penghargaan dan Apresiasi yang disediakan oleh penyelenggara Ajang Kompetensi Talenta/IMG_6034 - 48_Ni Made Selsa Sanjiwani.png',
                'days'                        => 10,
            ],
        ];

        foreach ($achievements as $ach) {
            StudentAchievement::create([
                'student_id'                  => $siswa->id,
                'category_id'                 => $ach['category']?->id ?? $catAkademik?->id,
                'title'                       => $ach['title'],
                'event_name'                  => $ach['event_name'] ?? null,
                'organizer'                   => $ach['organizer'] ?? null,
                'field_category'             => $ach['field_category'] ?? 'akademik',
                'level'                       => $ach['level'],
                'rank'                        => $ach['rank'],
                'achievement_date'            => $ach['achievement_date'],
                'description'                 => $ach['description'],
                'status'                      => $ach['status'],
                'curation_status'             => $ach['curation_status'] ?? ($ach['status'] === 'approved' ? 'curated' : 'pending'),
                'is_curation'                 => $ach['is_curation'] ?? false,
                'doc_standard_checklist'     => $ach['doc_standard_checklist'] ?? null,
                'doc_standard_file'          => $ach['doc_standard_file'] ?? null,
                'doc_standard_url'           => $ach['doc_standard_url'] ?? null,
                'selection_level'            => $ach['selection_level'] ?? null,
                'selection_level_file'       => $ach['selection_level_file'] ?? null,
                'selection_level_url'        => $ach['selection_level_url'] ?? null,
                'frequency_consistency'      => $ach['frequency_consistency'] ?? null,
                'frequency_consistency_file' => $ach['frequency_consistency_file'] ?? null,
                'infrastructure_type'        => $ach['infrastructure_type'] ?? null,
                'infrastructure_file'        => $ach['infrastructure_file'] ?? null,
                'reward_types'               => $ach['reward_types'] ?? null,
                'reward_certificate_file'    => $ach['reward_certificate_file'] ?? null,
                'reward_photo_file'          => $ach['reward_photo_file'] ?? null,
                'reward_recap_file'          => $ach['reward_recap_file'] ?? null,
                'certificate'                => $ach['certificate'] ?? null,
                'assignment_letter'          => $ach['assignment_letter'] ?? null,
                'verified_by'                 => $ach['status'] === 'approved' ? $guru->id : null,
                'verified_at'                 => $ach['status'] === 'approved' ? now()->subDays($ach['days']) : null,
                'photo'                       => $ach['photo'] ?? null,
                'created_at'                  => now()->subDays($ach['days']),
                'updated_at'                  => now()->subDays($ach['days']),
            ]);
        }

        // ── Library Loans ───────────────────────────────────────────────────
        $libraryLoans = [
            [
                'student_id'   => $siswa->id,
                'phone_number' => '081234567890',
                'book_title'   => 'Fisika Peminatan Kelas XII SMA/MA',
                'book_code'    => 'BIB-2026-012',
                'borrowed_at'  => now()->subDays(5),
                'due_at'       => now()->addDays(2),
                'status'       => 'borrowed',
                'notes'        => 'Kondisi buku baik dan bersih',
            ],
            [
                'student_id'   => $siswa->id,
                'phone_number' => '081234567890',
                'book_title'   => 'Laskar Pelangi - Andrea Hirata',
                'book_code'    => 'BIB-2025-089',
                'borrowed_at'  => now()->subDays(20),
                'due_at'       => now()->subDays(13),
                'returned_at'  => now()->subDays(13),
                'status'       => 'returned',
                'notes'        => 'Sudah dikembalikan tepat waktu',
            ],
        ];

        foreach ($libraryLoans as $loan) {
            \App\Models\LibraryLoan::create(array_merge($loan, [
                'created_by_user_id' => $siswa->id,
            ]));
        }

        $this->command->info('Dummy data berhasil dibuat:');
        $this->command->table(
            ['Data', 'Jumlah'],
            [
                ['Conduct Categories', count($categories)],
                ['Conduct Logs',       count($logs)],
                ['Student Achievements', count($achievements)],
                ['Library Loans',       count($libraryLoans)],
            ]
        );
    }
}
