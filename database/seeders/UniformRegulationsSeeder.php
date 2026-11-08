<?php

namespace Database\Seeders;

use App\Models\SchoolRegulation;
use Illuminate\Database\Seeder;

class UniformRegulationsSeeder extends Seeder
{
    public function run(): void
    {
        $uniforms = [
            [
                'category'   => 'berpakaian',
                'badge_text' => 'Senin',
                'title'      => 'Seragam Nasional Putih Abu-Abu (Hari Senin)',
                'image'      => 'images/uniforms/senin_putih_abu.png',
                'sort_order' => 1,
                'content'    => '<h2>Ketentuan Seragam Nasional Hari Senin</h2><p>Seluruh peserta didik SMAN 1 Gianyar wajib mengenakan seragam nasional putih abu-abu lengkap pada hari Senin untuk pelaksanaan Upacara Bendera dan KBM:</p><ul><li><strong>Baju:</strong> Kemeja putih lengan pendek rapi, dimasukkan ke dalam celana/rok, dilengkapi bet OSIS di saku kiri, bet bendera merah putih, dan bet lokasi sekolah di lengan kanan.</li><li><strong>Bawahan:</strong> Celana panjang abu-abu standar (putra) / Rok abu-abu rempel dengan panjang di bawah lutut (putri). Tidak diperkenankan celana model pensil/ketat atau cutbray ekstrem.</li><li><strong>Kelengkapan Upacara:</strong> Topi pet upacara berlogo sekolah dan dasi abu-abu resmi sekolah.</li><li><strong>Ikat Pinggang:</strong> Berwarna hitam polos berlogo standar sekolah dengan lebar standar 3 cm.</li><li><strong>Kaos Kaki:</strong> Kaos kaki putih polos berlogo sekolah, dengan panjang minimal 10 cm di atas mata kaki.</li><li><strong>Sepatu:</strong> Sepatu bertali dominan hitam polos (minimal 80% warna hitam).</li></ul><blockquote><strong>Catatan:</strong> Bagi siswa putri yang memakai jilbab, mengenakan jilbab putih polos segi empat standar sekolah.</blockquote>',
            ],
            [
                'category'   => 'berpakaian',
                'badge_text' => 'Selasa & Rabu',
                'title'      => 'Seragam Batik Khas Sekolah (Selasa & Rabu)',
                'image'      => 'images/uniforms/selasa_rabu_batik.png',
                'sort_order' => 2,
                'content'    => '<h2>Ketentuan Seragam Batik Hari Selasa & Rabu</h2><p>Sebagai wujud pelestarian budaya dan identitas sekolah, peserta didik mengenakan kemeja batik resmi SMAN 1 Gianyar:</p><ul><li><strong>Baju:</strong> Kemeja batik resmi identitas SMAN 1 Gianyar yang dikeluarkan oleh sekolah, bersih dan rapi.</li><li><strong>Bawahan:</strong> Celana panjang putih (putra) / Rok putih rempel (putri) standar sekolah.</li><li><strong>Kancing & Kerapian:</strong> Baju dikenakan dengan rapi, kancing terpasang dengan baik dan sopan.</li><li><strong>Ikat Pinggang & Kaos Kaki:</strong> Ikat pinggang hitam sekolah, kaos kaki putih polos.</li><li><strong>Sepatu:</strong> Sepatu sekolah dominan hitam bertali.</li></ul>',
            ],
            [
                'category'   => 'berpakaian',
                'badge_text' => 'Kamis',
                'title'      => 'Pakaian Adat Bali (Hari Kamis & Purnama/Tilem)',
                'image'      => 'images/uniforms/kamis_adat_bali.png',
                'sort_order' => 3,
                'content'    => '<h2>Ketentuan Busana Adat Bali Hari Kamis</h2><p>Sesuai dengan Pergub Bali No. 79 Tahun 2018 tentang Penggunaan Busana Adat Bali, peserta didik mengenakan busana adat Bali madya yang sopan dan beretika:</p><ul><li><strong>Siswa Putra:</strong> Mengenakan baju safari putih / kemeja warna sopan berkerah, kamen (kain) dengan lipatan tengah (kancut) yang rapi, saput, dan udeng (destar) dengan simpul rapi di bagian depan.</li><li><strong>Siswa Putri:</strong> Mengenakan baju kebaya putih / warna sopan (tidak transparan dan tidak berbelahan rendah), kamen panjang menutupi mata kaki, dan selendang/senteng yang terikat rapi di pinggang di luar kebaya.</li><li><strong>Persembahyangan:</strong> Membawa perlengkapan sarana persembahyangan suci bagi siswa beragama Hindu untuk persembahyangan bersama di Padmasana sekolah.</li><li><strong>Non-Hindu:</strong> Menyesuaikan dengan mengenakan pakaian formal rapi beretika atau busana adat yang sopan.</li></ul>',
            ],
            [
                'category'   => 'berpakaian',
                'badge_text' => 'Jumat & Sabtu',
                'title'      => 'Seragam Pramuka Lengkap (Jumat & Sabtu)',
                'image'      => 'images/uniforms/jumat_sabtu_pramuka.png',
                'sort_order' => 4,
                'content'    => '<h2>Ketentuan Seragam Pramuka Hari Jumat & Sabtu</h2><p>Mencerminkan kedisiplinan dan jiwa kepanduan penegak gugus depan SMAN 1 Gianyar:</p><ul><li><strong>Baju:</strong> Kemeja pramuka penegak warna cokelat muda dengan atribut lengkap (badge WOSM, pandu dunia, tunas kelapa, nomor gudep, dan lokasi Gianyar).</li><li><strong>Bawahan:</strong> Celana panjang cokelat tua standar (putra) / Rok pramuka cokelat tua (putri).</li><li><strong>Setangan Leher (Hasduk):</strong> Setangan leher merah putih dengan cincin/kolong kacu dipasang rapi di bawah kerah baju.</li><li><strong>Tutup Kepala:</strong> Baret pramuka berlambang penegak (putra) / Topi boni pramuka (putri).</li><li><strong>Sepatu & Kaos Kaki:</strong> Sepatu bertali hitam polos dan kaos kaki hitam standar.</li></ul>',
            ],
            [
                'category'   => 'berpakaian',
                'badge_text' => 'Jam PJOK',
                'title'      => 'Pakaian Olahraga Resmi Sekolah',
                'image'      => 'images/uniforms/pakaian_olahraga.png',
                'sort_order' => 5,
                'content'    => '<h2>Ketentuan Pakaian Olahraga (PJOK)</h2><p>Ketentuan pemakaian seragam olahraga peserta didik:</p><ul><li><strong>Seragam:</strong> Kaos olahraga resmi berlogo SMAN 1 Gianyar dan celana training panjang sekolah.</li><li><strong>Waktu Pemakaian:</strong> Hanya dikenakan pada saat jam pelajaran PJOK berlangsung atau kegiatan olahraga terprogram.</li><li><strong>Ketentuan Ganti:</strong> Siswa wajib mengganti kembali pakaian olahraga dengan seragam hari berjalan setelah pembelajaran PJOK selesai demi menjaga kerapian dan kenyamanan kelas.</li><li><strong>Sepatu:</strong> Sepatu kets / sepatu olahraga yang aman untuk beraktivitas fisik.</li></ul>',
            ],
            [
                'category'   => 'berpakaian',
                'badge_text' => 'Luar Jam Belajar',
                'title'      => 'Pakaian Bebas Rapi di Luar Jam Belajar Resmi',
                'image'      => 'images/uniforms/pakaian_bebas_rapi.png',
                'sort_order' => 6,
                'content'    => '<h2>Ketentuan Pakaian di Luar Jam Pembelajaran Resmi</h2><p>Apabila peserta didik berkunjung ke lingkungan sekolah pada sore hari, hari libur, atau mengikuti kegiatan ekstrakurikuler/organisasi:</p><ul><li><strong>Baju:</strong> Mengenakan kemeja, polo shirt berkerah, atau kaos sopan (dilarang kaos singlet, baju tidur, atau pakaian berbahan tipis/terbuka).</li><li><strong>Bawahan:</strong> Celana panjang rapi (kain/jeans sopan). <strong>Dilarang keras</strong> memakai celana pendek, celana robek (ripped jeans), atau pakaian ketat yang tidak pantas.</li><li><strong>Alas Kaki:</strong> Wajib mengenakan sepatu tertutup rapi. <strong>Dilarang</strong> menggunakan sandal jepit di area sekolah.</li><li><strong>Sikap & Etika:</strong> Tetap menjaga sopan santun dan tata krama saat berinteraksi dengan guru, staf, maupun sesama siswa di lingkungan sekolah.</li></ul>',
            ],
        ];

        foreach ($uniforms as $item) {
            SchoolRegulation::updateOrCreate(
                [
                    'category'   => $item['category'],
                    'badge_text' => $item['badge_text'],
                ],
                [
                    'title'      => $item['title'],
                    'content'    => $item['content'],
                    'image'      => $item['image'],
                    'sort_order' => $item['sort_order'],
                    'is_active'  => true,
                ]
            );
        }
    }
}
