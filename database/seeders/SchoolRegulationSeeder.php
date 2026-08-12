<?php

namespace Database\Seeders;

use App\Models\SchoolRegulation;
use Illuminate\Database\Seeder;

class SchoolRegulationSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tata Tertib Resmi SMA Negeri 1 Gianyar (Literal dari PDF Scan TataTertib SIswa.pdf) ──
        $regulations = [

            // ── A. Kehadiran, Ketidakhadiran dan Keterlambatan Peserta Didik ──────────
            ['category' => 'kehadiran', 'sort_order' => 1,
             'title'   => 'A.1. Kehadiran Peserta Didik (1)',
             'content' => 'Peserta Didik yang bertugas sebagai piket wajib membersihkan ruang kelas sebelum pukul 07.15 WITA.'],

            ['category' => 'kehadiran', 'sort_order' => 2,
             'title'   => 'A.1. Kehadiran Peserta Didik (2)',
             'content' => 'Seluruh Peserta Didik wajib berada di kelas pukul 07.15 WITA.'],

            ['category' => 'kehadiran', 'sort_order' => 3,
             'title'   => 'A.1. Kehadiran Peserta Didik (3)',
             'content' => 'Pukul 07.15 WITA Peserta Didik beragama Hindu diwajibkan sembahyang Puja Trisandya, sedangkan umat lain diwajibkan berdoa sesuai dengan keyakinannya, kemudian wajib menyanyikan lagu kebangsaan Indonesia Raya yang dipimpin oleh salah satu pengurus kelas.'],

            ['category' => 'kehadiran', 'sort_order' => 4,
             'title'   => 'A.2. Ketidakhadiran Peserta Didik (1)',
             'content' => "Peserta Didik yang tidak hadir pada pembelajaran dan kegiatan sekolah, dikarenakan:\n• Sakit selama 1 hari, maka orang tua/wali wajib memberitahukan dengan bersurat kepada wali kelas/guru piket/guru BK.\n• Sakit selama 2 hari atau lebih, wajib melampirkan surat keterangan dokter.\n• Ijin selama 1 hari, peserta didik mengisi form surat ijin yang disediakan oleh sekolah H-2 sebelum ijin dan terlebih dahulu meminta tanda tangan orang tua dan dikumpulkan H-1 kepada perangkat kelas.\n• Ijin selama 2 hari atau lebih, maka orang tua / wali wajib datang kesekolah untuk mengurus perizinannya kepada kepala sekolah.\n• Peserta Didik yang tidak hadir tanpa pemberitahuan akan dinyatakan alpha."],

            ['category' => 'kehadiran', 'sort_order' => 5,
             'title'   => 'A.2. Ketidakhadiran Peserta Didik (2)',
             'content' => "Peserta Didik yang tidak dapat melanjutkan untuk mengikuti KBM dan kegiatan sekolah secara penuh dan meninggalkan sekolah, dikarenakan:\n• Sakit, harus mendapatkan izin dari guru di kelas dan/atau guru piket.\n• Izin, harus dijemput oleh orang tua / wali dan mendapatkan izin dari guru di kelas dan/atau piket.\n• Keperluan yang berkaitan dengan kegiatan sekolah, harus mendapatkan izin atau surat dispensasi dari pihak sekolah.\n• Izin yang bersifat sementara, harus sepengetahuan guru di kelas dan piket serta membawa surat izin permisi yang disediakan sekolah dan menunjukkan surat izin kepada satpam sekolah dan kembali pada waktu yang sudah disepakati.\n• Peserta didik yang tidak mengikuti KBM atau kegiatan sekolah sampai akhir tanpa pemberitahuan (bolos) akan dinyatakan alpha."],

            ['category' => 'kehadiran', 'sort_order' => 6,
             'title'   => 'A.3. Keterlambatan Peserta Didik',
             'content' => 'Peserta Didik yang hadir setelah pukul 07.15 WITA dinyatakan terlambat.'],

            // ── B. Kegiatan Belajar Mengajar ──────────────────────────────────────────
            ['category' => 'kbm', 'sort_order' => 1,
             'title'   => 'B. Kegiatan Belajar Mengajar (1)',
             'content' => 'Sebelum kegiatan belajar mengajar dimulai, piket kelas menyiapkan sarana pembelajaran yang dibutuhkan dengan berkoordinasi terlebih dahulu dengan guru mata pelajaran.'],

            ['category' => 'kbm', 'sort_order' => 2,
             'title'   => 'B. Kegiatan Belajar Mengajar (2)',
             'content' => 'Setiap mengawali pelajaran Peserta Didik wajib mengucapkan salam (Panganjali Umat) dan doa, serta mengakhiri dengan doa dan mengucapkan salam (Paramasanthi).'],

            ['category' => 'kbm', 'sort_order' => 3,
             'title'   => 'B. Kegiatan Belajar Mengajar (3)',
             'content' => 'Mengikuti keseluruhan kegiatan belajar mengajar sejak jam pertama hingga jam terakhir, serta pulang secara bersama-sama setelah tanda bel pelajaran terakhir berbunyi.'],

            ['category' => 'kbm', 'sort_order' => 4,
             'title'   => 'B. Kegiatan Belajar Mengajar (4)',
             'content' => 'Peserta Didik tidak diperkenankan meninggalkan kelas saat pembelajaran sedang berlangsung kecuali mendapat izin dari guru pengajar, dan tidak diperkenankan meninggalkan halaman sekolah, kecuali seizin wali kelas atau guru piket.'],

            ['category' => 'kbm', 'sort_order' => 5,
             'title'   => 'B. Kegiatan Belajar Mengajar (5)',
             'content' => 'Jika 10 (sepuluh) menit setelah bel berbunyi guru belum hadir, maka ketua kelas atau pengurus kelas wajib menghubungi guru bersangkutan atau menanyakan kepada guru piket. Bila guru berhalangan hadir, Peserta Didik wajib mengerjakan tugas yang diberikan sesuai dengan petunjuk.'],

            ['category' => 'kbm', 'sort_order' => 6,
             'title'   => 'B. Kegiatan Belajar Mengajar (6)',
             'content' => 'Sesudah KBM berakhir, Peserta Didik wajib membersihkan ruang kelas serta mematikan semua alat elektronik yang ada di kelas dan meninggalkan ruang kelas dengan tertib.'],

            // ── C. Waktu Belajar ──────────────────────────────────────────────────────
            ['category' => 'kbm', 'sort_order' => 7,
             'title'   => 'C. Waktu Belajar (1)',
             'content' => 'Memenuhi kehadiran di sekolah sekurang-kurangnya 90% dari hari efektif sekolah selama satu semester.'],

            ['category' => 'kbm', 'sort_order' => 8,
             'title'   => 'C. Waktu Belajar (2)',
             'content' => 'Peserta Didik tidak diperkenankan memajukan mata pelajaran yang tidak sesuai dengan jadwal.'],

            ['category' => 'kbm', 'sort_order' => 9,
             'title'   => 'C. Waktu Belajar (3)',
             'content' => 'Pelaksanaan ekstrakurikuler maksimal sampai pukul 18.30 WITA di sekolah dan apabila melebihi waktu yang telah ditentukan harus mendapat izin dari pembina ekstra atau pembina kesiswaan.'],

            // ── D. Tata Tertib Kerapian Peserta Didik ─────────────────────────────────
            ['category' => 'kerapian', 'sort_order' => 1,
             'title'   => 'D. Tata Tertib Kerapian Peserta Didik',
             'content' => "Peserta Didik berpenampilan dengan ketentuan:\n• Rambut putra wajib dicukur dengan wajar atau standar (pendek dan rapi, tidak mengenai telinga, tidak mengenai alis, tidak dicukur modifikasi dan tidak ada kuncir).\n• Rambut putri wajib diikat satu tanpa poni dan tidak diperkenankan menggunakan jedai.\n• Semua Peserta Didik tidak diperkenankan mewarnai rambut dan kuku.\n• Peserta Didik putra tidak diperkenankan bertindik dan Peserta Didik putri tidak diperkenankan bertindik lebih dari sepasang.\n• Semua Peserta Didik tidak diperkenankan bertato.\n• Semua Peserta Didik tidak diperkenankan membawa atau memakai perhiasan dan aksesoris dalam bentuk apapun kecuali jam tangan, bagi Peserta Didik putri hanya diperkenankan memakai satu pasang giwang/anting yang tidak berlebihan.\n• Peserta Didik tidak diperkenankan menggunakan dan membawa peralatan berhias yang berlebihan, kecuali sisir."],

            // ── E. Tata Tertib Berpakaian ──────────────────────────────────────────────
            ['category' => 'berpakaian', 'sort_order' => 1,
             'title'   => 'E. Tata Tertib Berpakaian (Seragam Harian)',
             'content' => "Peserta Didik diwajibkan berpakaian dengan ketentuan:\n1. Hari Senin dan Selasa memakai baju kemeja putih dengan badge merah putih, celana/rok abu-abu, dasi, kaos kaki putih berlogo sekolah dengan tinggi minimal 10 cm diatas mata kaki dan topi pada saat upacara bendera.\n2. Hari Rabu memakai baju kemeja batik, celana/rok biru, dan kaos kaki putih berlogo sekolah dengan tinggi minimal 10 cm diatas mata kaki.\n3. Hari Kamis memakai pakaian kemeja, kamben dan saput, memakai destar dan tidak menggunakan sandal jepit untuk putra. Dan memakai pakaian kebaya (kain sari), kamben, selendang dan tidak menggunakan sandal jepit untuk putri.\n4. Hari Jumat dan Sabtu memakai kemeja pramuka, celana/rok pramuka, dan kaos kaki hitam berlogo pramuka dengan tinggi minimal 10 cm diatas mata kaki.\n5. Hari Keagamaan Hindu dan Hari Jadi Provinsi Bali,\n   Putra: Menggunakan pakaian kemeja putih, kamben dan saput, memakai destar putih dan tidak menggunakan sandal jepit.\n   Putri: Menggunakan pakaian kebaya (kain sari) putih, kamben, selendang, dan tidak menggunakan sandal jepit."],

            ['category' => 'berpakaian', 'sort_order' => 2,
             'title'   => 'E. Tata Tertib Berpakaian (Ketentuan Tambahan)',
             'content' => "Ketentuan tambahan pada hari Senin, Selasa, Rabu, Jumat dan Sabtu yaitu:\n1. Kemeja yang digunakan merupakan kemeja lengan pendek memakai satu saku di sebelah kiri tanpa jahitan belakang lengkap dengan badge nama Peserta Didik, badge nama sekolah, panjang bawah 25 cm-30 cm dari pinggang, lebar lengan 7 cm dari lengan dan panjang lengan 3 cm di bawah siku.\n2. Baju kemeja dimasukkan ke dalam rok / celana panjang (kecuali kemeja pramuka putri).\n3. Putra: Celana panjang ukuran standar, panjang celana sampai mata kaki dengan ukuran lingkar kaki minimal 44 cm (tidak dilipat pada bagian bawahnya).\n   Putri: Rok dengan lipit hadap pada tengah muka, resleting di tengah belakang, panjang rok 5 cm di bawah lutut (dengan ketentuan model yang sudah ditetapkan dan tidak dilipat pada bagian bawahnya).\n4. Memakai ikat pinggang ukuran lebar 3 cm warna hitam berlogo sekolah.\n5. Memakai kaos dalam (singlet) berwarna putih (tidak diperkenankan menggunakan kaos bukan singlet).\n6. Memakai sepatu hitam selain pantofel."],

            // ── F. Kegiatan Ekstrakurikuler ───────────────────────────────────────────
            ['category' => 'ekstrakurikuler', 'sort_order' => 1,
             'title'   => 'F. Kegiatan Ekstrakurikuler',
             'content' => "1. Semua peserta didik Mengikuti minimal 1 (satu) dan maksimal 2 (dua) kegiatan Ekstrakurikuler di SMA Negeri 1 Gianyar sesuai minat dan bakat.\n2. Jika Peserta Didik yang bersangkutan pindah kegiatan ekstrakurikuler diberi batas waktu 1 bulan dari pemilihan awal.\n3. Setiap kegiatan kelompok ekstrakurikuler harus sepengetahuan dan didampingi Pembina/Koordinator Kelompok/Pelatih.\n4. Kehadiran Peserta Didik kurang dari 75 % dalam kegiatan ekstrakurikuler tidak mendapat nilai ekstra."],

            // ── G. Hak Peserta Didik ──────────────────────────────────────────────────
            ['category' => 'hak_kewajiban', 'sort_order' => 1,
             'title'   => 'G. Hak Peserta Didik',
             'content' => "Setiap peserta didik mempunyai hak:\n1. Melaksanakan ibadah sesuai keyakinan yang dianutnya.\n2. Memperoleh pembelajaran sebaik-baiknya.\n3. Memperoleh layanan bidang akademik dan administrasi sesuai peraturan / ketentuan yang berlaku.\n4. Memanfaatkan fasilitas untuk kelancaran proses belajar dan kegiatan ekstrakurikuler.\n5. Mendapatkan beasiswa atau bantuan lainnya sesuai persyaratan.\n6. Memperoleh laporan hasil belajar.\n7. Mendapatkan informasi, perhatian dan perlindungan dari sekolah.\n8. Memperoleh layanan BK dalam bidang pribadi, belajar, sosial, dan karir.\n9. Menjadi pengurus OSIS/MPK di SMA Negeri 1 Gianyar.\n10. Memilih kegiatan Ekstrakurikuler di SMA Negeri 1 Gianyar sesuai minat dan bakat.\n11. Memberikan saran dan kritik yang membangun terhadap kebijakan sekolah melalui jalur pengurus kelas, OSIS/MPK, wali kelas, dan BK sesuai etika."],

            // ── H. Kewajiban Peserta Didik ────────────────────────────────────────────
            ['category' => 'hak_kewajiban', 'sort_order' => 2,
             'title'   => 'H. Kewajiban Peserta Didik',
             'content' => "Setiap peserta didik mempunyai kewajiban:\n1. Menaati Tata Tertib yang berlaku di Sekolah.\n2. Berperan aktif mengikuti kegiatan sekolah.\n3. Berperan aktif menciptakan suasana tertib dan kondusif di lingkungan sekolah dan sekitarnya.\n4. Mengikuti kegiatan belajar mengajar dengan tekun, bersungguh-sungguh, disiplin, tertib, dan penuh tanggung jawab.\n5. Menyelesaikan tugas-tugas akademik maupun non akademik yang diberikan oleh sekolah.\n6. Mengikuti minimal 1 (satu) dan maksimal 2 (dua) kegiatan Ekstrakurikuler di SMA Negeri 1 Gianyar sesuai minat dan bakat.\n7. Mengikuti upacara bendera pada hari-hari tertentu dan kegiatan keagamaan dengan tertib.\n8. Menjaga dan memelihara sarana-prasarana serta kebersihan lingkungan sekolah.\n9. Menjaga keamanan barang-barang milik pribadi dan apabila terjadi kehilangan, bukan tanggung jawab sekolah.\n10. Menjaga nama baik sekolah, guru, keluarga dan diri sendiri baik di dalam maupun di luar sekolah.\n11. Menjaga ketertiban dan kesopanan dalam berpakaian, bersikap, serta bertutur kata terhadap orang tua, guru, tenaga kependidikan, teman, dan masyarakat lainnya."],

            // ── I. Larangan Peserta Didik ──────────────────────────────────────────────
            ['category' => 'larangan', 'sort_order' => 1,
             'title'   => 'I. Larangan Peserta Didik (1 - 8)',
             'content' => "Peserta Didik dilarang:\n1. Melanggar kewajiban-kewajiban yang harus dipatuhi oleh peserta didik sebagaimana tertera pada point H.\n2. Meninggalkan sekolah sebelum berakhirnya kegiatan belajar mengajar tanpa ijin (bolos).\n3. Melakukan Perundungan (bullying).\n4. Berkeliaran atau berada di luar kelas pada saat jam-jam kegiatan belajar mengajar.\n5. Berkeliaran di luar lingkungan sekolah pada saat jam-jam kegiatan belajar mengajar maupun istirahat.\n6. Memarkir sepeda motor di luar area parkir yang telah disepakati.\n7. Menggunakan Knalpot Racing/bising (tidak sesuai dengan UU No 22 Tahun 2009 Pasal 285 tentang Lalu Lintas dan Penggunaan Jalan).\n8. Mengendarai sepeda / sepeda motor pada jam pelajaran di halaman sekolah."],

            ['category' => 'larangan', 'sort_order' => 2,
             'title'   => 'I. Larangan Peserta Didik (9 - Penampilan)',
             'content' => "9. Berpenampilan tidak sesuai dengan peraturan:\n   a. Peserta didik Pria Berambut panjang menyentuh alis/telinga/kerah baju, memakai jelly, berkuku panjang, mengecat kuku dan rambut, bertindik, memakai anting-anting, gelang atau aksesoris wanita lainnya.\n   b. Peserta didik Wanita: Bermake-up, berkuku panjang, mengecat kuku dan rambut, memakai perhiasan atau aksesoris yang berlebihan."],

            ['category' => 'larangan', 'sort_order' => 3,
             'title'   => 'I. Larangan Peserta Didik (10 - 16)',
             'content' => "10. Bertingkah/berbicara teriak-teriak dan berbuat onar yang mengundang kerawanan di sekolah.\n11. Berpacaran di lingkungan sekolah baik pada saat jam-jam sekolah maupun di luar jam sekolah.\n12. Membawa senjata tajam, petasan, dan benda lainnya yang tidak ada hubungannya dengan kegiatan belajar mengajar.\n13. Berkelahi dengan sesama peserta didik SMA Negeri 1 Gianyar, maupun peserta didik/orang lain dari luar SMA Negeri 1 Gianyar.\n14. Merokok selama masih mengenakan seragam sekolah baik di sekolah maupun di luar sekolah.\n15. Membawa/mengkonsumsi/mengedarkan obat-obat terlarang (NAPZA) maupun minuman beralkohol, baik di sekolah maupun di luar sekolah.\n16. Berjudi atau hal-hal yang bisa diindikasikan perjudian."],

            ['category' => 'larangan', 'sort_order' => 4,
             'title'   => 'I. Larangan Peserta Didik (17 - 24)',
             'content' => "17. Mengambil barang - barang baik milik sekolah maupun milik teman yang bukan miliknya.\n18. Melakukan pemerasan atau sejenisnya yang bersifat atau diindikasikan Premanisme.\n19. Melakukan pelecehan/penghinaan kehormatan martabat guru, tenaga kependidikan maupun sesama peserta didik.\n20. Menonton foto/video yang memuat unsur pornografi.\n21. Pelecehan seksual dan perbuatan asusila.\n22. Meretas akun sekolah.\n23. Memalsukan dokumen administrasi sekolah.\n24. Melakukan tindakan kriminal."],

            // ── J. Upacara Bendera ─────────────────────────────────────────────────────
            ['category' => 'perilaku', 'sort_order' => 1,
             'title'   => 'J. Upacara Bendera',
             'content' => "1. Peserta Didik wajib mengikuti upacara bendera pada hari senin atau hari besar nasional tertentu yang telah ditetapkan, baik yang diadakan di sekolah maupun diluar sekolah.\n2. Peserta Didik wajib mengenakan seragam upacara sesuai dengan imbauan dari dinas terkait.\n3. Perangkat upacara adalah siswa yang ditunjuk oleh pihak sekolah.\n4. Semua Peserta Didik wajib mengikuti dengan tertib dan khidmah seluruh rangkaian upacara bendera."],

            // ── K. Ketentuan Lain ──────────────────────────────────────────────────────
            ['category' => 'hak_kewajiban', 'sort_order' => 3,
             'title'   => 'K. Ketentuan Lain',
             'content' => "1. Intisari tata tertib ini, merupakan kutipan dari tata tertib SMA Negeri 1 Gianyar yang ditetapkan berdasarkan keputusan kepala sekolah nomor B.10.400.3/1861/SMAN 1 GIANYAR/DISDIK.\n2. Foto contoh kerapian pada poin D dan E terlampir dalam dokumen PDF resmi."],

        ];

        // Seed data dengan truncate & recreate untuk menjamin 100% presisi sesuai dokumen PDF
        SchoolRegulation::truncate();

        foreach ($regulations as $reg) {
            SchoolRegulation::create($reg);
        }
    }
}
