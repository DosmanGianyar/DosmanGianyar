<?php

namespace Database\Seeders;

use App\Models\SchoolRegulation;
use Illuminate\Database\Seeder;

class SchoolRegulationSeeder extends Seeder
{
    public function run(): void
    {
        // Menyimpan / Memperbarui tata tertib tanpa menghapus data yang ada.
        $regulations = [

            // ── Kehadiran, Ketidakhadiran & Keterlambatan Peserta Didik ─────────────
            ['category' => 'kehadiran', 'sort_order' => 1,
             'title'   => 'Kewajiban Hadir & Piket Kelas',
             'content' => 'Peserta didik yang bertugas piket wajib membersihkan ruang kelas sebelum pukul 07.15 WITA. Seluruh peserta didik wajib berada di kelas tepat pukul 07.15 WITA.'],

            ['category' => 'kehadiran', 'sort_order' => 2,
             'title'   => 'Doa, Puja Trisandya & Lagu Kebangsaan',
             'content' => 'Pukul 07.15 WITA peserta didik beragama Hindu diwajibkan sembahyang Puja Trisandya, sedangkan umat lain diwajibkan berdoa sesuai keyakinannya. Dilanjutkan menyanyikan lagu kebangsaan Indonesia Raya yang dipimpin pengurus kelas.'],

            ['category' => 'kehadiran', 'sort_order' => 3,
             'title'   => 'Ketentuan Ketidakhadiran (Sakit & Izin)',
             'content' => 'Sakit 1 hari: Orang tua/wali wajib memberitahukan dengan bersurat kepada wali kelas/guru piket/guru BK. Sakit 2 hari/lebih: Melampirkan surat keterangan dokter. Izin 1 hari: Mengisi form izin H-2 dan dikumpulkan H-1. Izin 2 hari/lebih: Orang tua/wali wajib datang langsung ke sekolah mengurus perizinan kepada Kepala Sekolah. Tanpa pemberitahuan dinyatakan alpa.'],

            ['category' => 'kehadiran', 'sort_order' => 4,
             'title'   => 'Meninggalkan KBM / Lingkungan Sekolah',
             'content' => 'Meninggalkan sekolah saat KBM dikarenakan sakit/izin harus mendapat izin guru kelas/piket dan dijemput orang tua/wali. Keperluan sekolah wajib membawa surat dispensasi. Izin sementara wajib membawa surat izin permisi dan ditunjukkan ke satpam.'],

            ['category' => 'kehadiran', 'sort_order' => 5,
             'title'   => 'Batas Waktu Keterlambatan',
             'content' => 'Peserta didik yang hadir setelah pukul 07.15 WITA dinyatakan terlambat dan wajib melapor ke guru piket sebelum memasuki ruang kelas.'],

            // ── Kegiatan Belajar Mengajar & Waktu Belajar ─────────────────────
            ['category' => 'kbm', 'sort_order' => 1,
             'title'   => 'Persiapan & Salam Pembukaan KBM',
             'content' => 'Piket kelas menyiapkan sarana pembelajaran berkoordinasi dengan guru mapel. Setiap mengawali pelajaran wajib mengucapkan salam Panganjali Umat dan doa, serta mengakhiri dengan doa dan salam Paramasanthi.'],

            ['category' => 'kbm', 'sort_order' => 2,
             'title'   => 'Mengikuti KBM Penuh & Kepulangan',
             'content' => 'Mengikuti keseluruhan kegiatan belajar mengajar sejak jam pertama hingga jam terakhir, serta pulang bersama-sama setelah tanda bel pelajaran terakhir berbunyi.'],

            ['category' => 'kbm', 'sort_order' => 3,
             'title'   => 'Prosedur Guru Berhalangan Hadir',
             'content' => 'Jika 10 menit setelah bel berbunyi guru belum hadir, ketua kelas/pengurus kelas wajib menghubungi guru bersangkutan atau guru piket. Bila guru berhalangan hadir, siswa wajib mengerjakan tugas yang diberikan.'],

            ['category' => 'kbm', 'sort_order' => 4,
             'title'   => 'Penutupan KBM & Penghematan Energi',
             'content' => 'Sesudah KBM berakhir, peserta didik wajib membersihkan ruang kelas serta mematikan semua alat elektronik yang ada di kelas dan meninggalkan ruang kelas dengan tertib.'],

            ['category' => 'kbm', 'sort_order' => 5,
             'title'   => 'Batas Kehadiran Minimal & Waktu Ekstra',
             'content' => 'Memenuhi kehadiran di sekolah sekurang-kurangnya 90% dari hari efektif sekolah selama satu semester. Pelaksanaan ekstrakurikuler maksimal sampai pukul 18.30 WITA di sekolah (melebihi batas waktu wajib seizin pembina ekstra/kesiswaan).'],

            // ── Tata Tertib Kerapian Peserta Didik ───────────────────────────
            ['category' => 'kerapian', 'sort_order' => 1,
             'title'   => 'Ketentuan Rambut Peserta Didik Putra',
             'content' => 'Rambut putra wajib dicukur dengan wajar/standar (pendek dan rapi, tidak mengenai telinga, tidak mengenai alis, tidak dicukur modifikasi, dan tidak ada kuncir). Dilarang mewarnai rambut.'],

            ['category' => 'kerapian', 'sort_order' => 2,
             'title'   => 'Ketentuan Rambut & Kerapian Putri',
             'content' => 'Rambut putri wajib diikat satu tanpa poni dan tidak diperkenankan menggunakan jedai. Dilarang mewarnai rambut dan kuku.'],

            ['category' => 'kerapian', 'sort_order' => 3,
             'title'   => 'Ketentuan Tindik, Tato & Aksesoris',
             'content' => 'Siswa putra dilarang bertindik. Siswa putri dilarang bertindik lebih dari sepasang. Semua siswa dilarang bertato. Dilarang memakai perhiasan/aksesoris berlebihan kecuali jam tangan (putri hanya 1 pasang anting/giwang tidak berlebihan) dan tidak membawa peralatan berhias berlebihan kecuali sisir.'],

            // ── Tata Cara Berpakaian & Seragam ───────────────────────────────
            ['category' => 'berpakaian', 'sort_order' => 1,
             'title'   => 'Seragam Hari Senin & Selasa',
             'content' => 'Kemeja putih dengan badge merah putih, celana/rok abu-abu, dasi, kaos kaki putih berlogo sekolah (tinggi min 10 cm diatas mata kaki), serta topi pada saat upacara bendera.'],

            ['category' => 'berpakaian', 'sort_order' => 2,
             'title'   => 'Seragam Hari Rabu',
             'content' => 'Kemeja batik sekolah, celana/rok biru, dan kaos kaki putih berlogo sekolah (tinggi minimal 10 cm diatas mata kaki).'],

            ['category' => 'berpakaian', 'sort_order' => 3,
             'title'   => 'Seragam Hari Kamis (Pakaian Adat)',
             'content' => 'Putra: Pakaian kemeja, kamben dan saput, memakai destar (tanpa sandal jepit). Putri: Pakaian kebaya (kain sari), kamben, selendang (tanpa sandal jepit).'],

            ['category' => 'berpakaian', 'sort_order' => 4,
             'title'   => 'Seragam Hari Jumat & Sabtu',
             'content' => 'Kemeja pramuka, celana/rok pramuka, dan kaos kaki hitam berlogo pramuka (tinggi minimal 10 cm diatas mata kaki).'],

            ['category' => 'berpakaian', 'sort_order' => 5,
             'title'   => 'Pakaian Hari Keagamaan & Hari Jadi Bali',
             'content' => 'Putra: Kemeja putih, kamben dan saput, memakai destar putih (tanpa sandal jepit). Putri: Kebaya (kain sari) putih, kamben, selendang (tanpa sandal jepit).'],

            ['category' => 'berpakaian', 'sort_order' => 6,
             'title'   => 'Spesifikasi Atribut Tambahan',
             'content' => 'Kemeja lengan pendek dengan saku kiri lengkap badge nama & sekolah (panjang bawah 25-30 cm dari pinggang). Celana putra lingkar kaki min 44 cm (tidak dilipat). Rok putri dengan lipit hadap depan & resleting tengah (5 cm dibawah lutut). Ikat pinggang hitam 3 cm berlogo sekolah. Sepatu hitam selain pantofel & kaos dalam/singlet putih.'],

            // ── Kegiatan Ekstrakurikuler ─────────────────────────────────────
            ['category' => 'ekstrakurikuler', 'sort_order' => 1,
             'title'   => 'Jumlah Ekstrakurikuler Wajib',
             'content' => 'Setiap peserta didik wajib mengikuti minimal 1 (satu) dan maksimal 2 (dua) kegiatan ekstrakurikuler di SMA Negeri 1 Gianyar sesuai minat dan bakat.'],

            ['category' => 'ekstrakurikuler', 'sort_order' => 2,
             'title'   => 'Perpindahan & Pendampingan Ekstra',
             'content' => 'Perpindahan kegiatan ekstrakurikuler diberi batas waktu 1 bulan dari pemilihan awal. Setiap kegiatan ekstrakurikuler harus sepengetahuan dan didampingi Pembina/Koordinator. Kehadiran kurang dari 75% tidak mendapat nilai ekstra.'],

            // ── Hak & Kewajiban Peserta Didik ─────────────────────────────────
            ['category' => 'hak_kewajiban', 'sort_order' => 1,
             'title'   => 'Hak Utama Peserta Didik',
             'content' => 'Berhak: (1) Melaksanakan ibadah, (2) Memperoleh pembelajaran sebaik-baiknya, (3) Memperoleh layanan akademik, administrasi & BK, (4) Memanfaatkan fasilitas sekolah, (5) Mendapatkan beasiswa/bantuan jika memenuhi syarat, (6) Menerima rapor, (7) Mendapatkan perlindungan, (8) Menjadi pengurus OSIS/MPK, (9) Menyampaikan saran/kritik membangun via jalur resmi.'],

            ['category' => 'hak_kewajiban', 'sort_order' => 2,
             'title'   => 'Kewajiban Utama Peserta Didik',
             'content' => 'Wajib: (1) Menaati Tata Tertib Sekolah, (2) Berperan aktif dan menjaga ketertiban sekolah, (3) Mengikuti KBM tekun & disiplin, (4) Menyelesaikan tugas akademik/non-akademik, (5) Mengikuti upacara & kegiatan keagamaan, (6) Menjaga kebersihan & sarana sekolah, (7) Menjaga nama baik sekolah, guru, keluarga, dan diri sendiri.'],

            // ── Perilaku & Upacara Bendera ───────────────────────────────────
            ['category' => 'perilaku', 'sort_order' => 1,
             'title'   => 'Ketentuan Upacara Bendera',
             'content' => 'Peserta didik wajib mengikuti upacara bendera pada hari Senin atau hari besar nasional dengan tertib dan khidmah menggunakan seragam upacara lengkap.'],

            ['category' => 'perilaku', 'sort_order' => 2,
             'title'   => 'Sikap terhadap Guru & Sesama',
             'content' => 'Wajib bersikap hormat, sopan, dan santun kepada seluruh guru, staf, dan karyawan sekolah. Menjaga tutur kata, kerapian berpakaian, dan kesopanan dalam berinteraksi.'],

            // ── Larangan Peserta Didik ────────────────────────────────────────
            ['category' => 'larangan', 'sort_order' => 1,
             'title'   => 'Larangan Bolos & Perundungan (Bullying)',
             'content' => 'Dilarang meninggalkan sekolah tanpa izin (bolos), berkeliaran di luar kelas saat KBM, serta dilarang keras melakukan perundungan (bullying) baik fisik, verbal, maupun siber (cyberbullying).'],

            ['category' => 'larangan', 'sort_order' => 2,
             'title'   => 'Larangan Berkelahi, Merokok & NAPZA',
             'content' => 'Dilarang keras berkelahi dengan sesama siswa/pihak luar, merokok/vape di dalam maupun luar sekolah saat berseragam, serta dilarang membawa/mengkonsumsi/mengedarkan minuman beralkohol dan obat-obatan terlarang (NAPZA).'],

            ['category' => 'larangan', 'sort_order' => 3,
             'title'   => 'Larangan Senjata Berbahaya, Knalpot Racing & Pelanggaran Kendaraan',
             'content' => 'Dilarang membawa senjata tajam/api/petasan, memarkir kendaraan di luar area parkir resmi, menggunakan knalpot racing/bising (UU No. 22/2009), atau mengendarai sepeda/motor pada jam pelajaran di halaman sekolah.'],

            ['category' => 'larangan', 'sort_order' => 4,
             'title'   => 'Larangan Tindakan Kriminal, Peretasan & Pemalsuan',
             'content' => 'Dilarang melakukan perjudian, pencurian, pemerasan (premanisme), pelecehan seksual, menonton/menyebarkan konten pornografi, meretas akun sekolah, memalsukan dokumen sekolah, atau melakukan tindakan kriminal lainnya.'],

        ];

        foreach ($regulations as $reg) {
            SchoolRegulation::firstOrCreate(
                ['title' => $reg['title']],
                $reg
            );
        }
    }
}
