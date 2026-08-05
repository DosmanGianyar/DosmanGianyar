<?php

namespace Database\Seeders;

use App\Models\ConductCategory;
use Illuminate\Database\Seeder;

class ConductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // ─── CATATAN POSITIF / PRESTASI ──────────────────────────────────
            [
                'name' => 'Mewakili Sekolah dalam Lomba / Kejuaraan',
                'type' => 'prestasi',
                'context' => 'lomba',
            ],
            [
                'name' => 'Juara 1 / 2 / 3 Lomba Akademik atau Non-Akademik',
                'type' => 'prestasi',
                'context' => 'lomba',
            ],
            [
                'name' => 'Kedisiplinan & Kerapian Tereladan',
                'type' => 'prestasi',
                'context' => 'akademik',
            ],
            [
                'name' => 'Bertanggung Jawab & Piket Kebersihan Terbaik',
                'type' => 'prestasi',
                'context' => 'akademik',
            ],
            [
                'name' => 'Aktif Berpendapat & Berdiskusi di Kelas',
                'type' => 'prestasi',
                'context' => 'akademik',
            ],
            [
                'name' => 'Presentasi / Hasil Karya Pembelajaran Terbaik',
                'type' => 'prestasi',
                'context' => 'akademik',
            ],
            [
                'name' => 'Membantu Teman Belajar (Peer Tutoring)',
                'type' => 'prestasi',
                'context' => 'akademik',
            ],
            [
                'name' => 'Sikap Santun, Honesty & Kejujuran Tereladan',
                'type' => 'prestasi',
                'context' => 'akademik',
            ],
            [
                'name' => 'Kepedulian Sosial & Membantu Sesama',
                'type' => 'prestasi',
                'context' => 'akademik',
            ],
            [
                'name' => 'Berdedikasi Tinggi dalam Organisasi / Panitia Sekolah',
                'type' => 'prestasi',
                'context' => 'akademik',
            ],
            [
                'name' => 'Nilai Ujian Sempurna',
                'type' => 'prestasi',
                'context' => 'akademik',
            ],

            // ─── CATATAN NEGATIF / PELANGGARAN ──────────────────────────────
            [
                'name' => 'Terlambat Masuk Sekolah',
                'type' => 'pelanggaran',
                'context' => 'sidak',
            ],
            [
                'name' => 'Seragam / Atribut Tidak Lengkap',
                'type' => 'pelanggaran',
                'context' => 'sidak',
            ],
            [
                'name' => 'Penampilan / Rambut Tidak Rapi',
                'type' => 'pelanggaran',
                'context' => 'sidak',
            ],
            [
                'name' => 'Tidak Mengerjakan Tugas / PR',
                'type' => 'pelanggaran',
                'context' => 'kelas',
            ],
            [
                'name' => 'Menggunakan HP Saat Pelajaran Tanpa Izin',
                'type' => 'pelanggaran',
                'context' => 'kelas',
            ],
            [
                'name' => 'Gaduh / Mengganggu Ketertiban Kelas',
                'type' => 'pelanggaran',
                'context' => 'kelas',
            ],
            [
                'name' => 'Tidak Hadir Tanpa Keterangan (Alpa)',
                'type' => 'pelanggaran',
                'context' => 'kelas',
            ],
        ];

        foreach ($categories as $cat) {
            ConductCategory::updateOrCreate(
                ['name' => $cat['name']],
                [
                    'type'      => $cat['type'],
                    'context'   => $cat['context'],
                    'is_active' => true,
                ]
            );
        }
    }
}
