<?php

namespace Database\Seeders;

use App\Models\LibraryBook;
use Illuminate\Database\Seeder;

class LibraryBookSeeder extends Seeder
{
    public function run(): void
    {
        LibraryBook::updateOrCreate(
            ['book_code' => 'BK-PEL-001'],
            [
                'isbn' => '978-602-244-345-2',
                'title' => 'Matematika Tingkat Lanjut SMA/MA Kelas XI (Kurikulum Merdeka)',
                'author' => 'Pusat Perbukuan Kemendikdasmen',
                'publisher' => 'Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi',
                'publish_year' => 2024,
                'category' => 'Pelajaran',
                'total_stock' => 15,
                'borrowed_count' => 3,
                'shelf_location' => 'Rak Pelajaran A-01',
                'cover_image' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=400&q=80',
                'description' => 'Buku teks utama mata pelajaran Matematika Tingkat Lanjut untuk siswa kelas XI SMA Kurikulum Merdeka. Membahas tentang Polinomial, Matriks, Transformasi Geometri, dan Fungsi Trigonometri dilengkapi latihan soal HOTS.',
            ]
        );

        LibraryBook::updateOrCreate(
            ['book_code' => 'BK-FIK-002'],
            [
                'isbn' => '978-602-03-8591-4',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'publish_year' => 2021,
                'category' => 'Fiksi',
                'total_stock' => 10,
                'borrowed_count' => 2,
                'shelf_location' => 'Rak Novel Fiksi B-03',
                'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=400&q=80',
                'description' => 'Novel inspiratif tentang perjuangan sepuluh anak di Pulau Belitung dalam menempuh pendidikan di sekolah sederhana. Kisah hangat penuh persahabatan, ketabahan, keberanian, dan mimpi-mimpi indah anak bangsa.',
            ]
        );

        LibraryBook::updateOrCreate(
            ['book_code' => 'BK-SAI-003'],
            [
                'isbn' => '978-602-486-123-9',
                'title' => 'Fisika Modern & Kosmologi untuk Siswa SMA',
                'author' => 'Dr. Bambang Sutrisno, M.Sc.',
                'publisher' => 'Erlangga',
                'publish_year' => 2023,
                'category' => 'Sains',
                'total_stock' => 8,
                'borrowed_count' => 1,
                'shelf_location' => 'Rak Sains C-05',
                'cover_image' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=400&q=80',
                'description' => 'Panduan eksplorasi ilmu fisika modern mulai dari teori relativitas Einstein, mekanika kuantum dasar, struktur atom, hingga pengenalan astrofisika dan astrobiologi secara populer dan komunikatif.',
            ]
        );

        LibraryBook::updateOrCreate(
            ['book_code' => 'BK-SEJ-004'],
            [
                'isbn' => '978-979-407-219-6',
                'title' => 'Sejarah Kebudayaan & Kerajaan-Kerajaan Bali Nusantara',
                'author' => 'Prof. Dr. I Gde Parimartha',
                'publisher' => 'Udayana University Press',
                'publish_year' => 2022,
                'category' => 'Sejarah',
                'total_stock' => 6,
                'borrowed_count' => 0,
                'shelf_location' => 'Rak Sejarah D-02',
                'cover_image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=400&q=80',
                'description' => 'Buku referensi sejarah mendalam mengenai peradaban kuno Bali, dinamika sosial politik zaman kerajaan Warmadewa hingga Gelgel, serta warisan arsitektur, prasasti, dan tradisi luhur di Pulau Dewata.',
            ]
        );
    }
}
