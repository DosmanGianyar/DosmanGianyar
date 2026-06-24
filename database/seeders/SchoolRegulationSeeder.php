<?php

namespace Database\Seeders;

use App\Models\SchoolRegulation;
use Illuminate\Database\Seeder;

class SchoolRegulationSeeder extends Seeder
{
    public function run(): void
    {
        SchoolRegulation::truncate();

        $regulations = [

            // ── Kehadiran & Keterlambatan ─────────────────────────────────────
            ['category' => 'kehadiran', 'sort_order' => 1,
             'title'   => 'Kewajiban Hadir',
             'content' => 'Siswa wajib hadir mengikuti kegiatan belajar mengajar minimal 80% dari total hari efektif dalam satu semester. Ketidakhadiran di bawah batas minimum dapat mengakibatkan tidak diperkenankan mengikuti ujian akhir semester.'],

            ['category' => 'kehadiran', 'sort_order' => 2,
             'title'   => 'Jam Masuk Sekolah',
             'content' => 'Bel masuk berbunyi pukul 07.00 WITA. Siswa yang tiba setelah pukul 07.15 WITA dinyatakan terlambat dan wajib melapor ke guru piket sebelum masuk kelas.'],

            ['category' => 'kehadiran', 'sort_order' => 3,
             'title'   => 'Prosedur Izin Tidak Hadir',
             'content' => 'Siswa yang tidak hadir karena sakit atau keperluan keluarga wajib menyampaikan surat izin tertulis dari orang tua/wali yang ditujukan kepada wali kelas, paling lambat pada hari pertama masuk kembali.'],

            ['category' => 'kehadiran', 'sort_order' => 4,
             'title'   => 'Surat Keterangan Sakit',
             'content' => 'Siswa yang sakit selama lebih dari 3 (tiga) hari berturut-turut wajib menyertakan surat keterangan dokter atau puskesmas. Tanpa surat keterangan, ketidakhadiran lebih dari 3 hari dihitung sebagai alpa.'],

            ['category' => 'kehadiran', 'sort_order' => 5,
             'title'   => 'Akumulasi Keterlambatan',
             'content' => 'Akumulasi 3 kali terlambat dalam satu bulan dihitung setara 1 hari alpa. Siswa yang terlambat lebih dari 30 menit tanpa alasan yang sah tidak diperkenankan masuk kelas pada jam pelajaran tersebut.'],

            // ── Tata Cara Berpakaian ──────────────────────────────────────────
            ['category' => 'berpakaian', 'sort_order' => 1,
             'title'   => 'Seragam Harian',
             'content' => "Senin–Selasa: seragam putih abu-abu dengan atribut lengkap (nama, logo sekolah, dan OSIS).\nRabu–Kamis: seragam batik sekolah.\nJumat: seragam pramuka lengkap.\nPakaian harus bersih, rapi, dan tidak kusut."],

            ['category' => 'berpakaian', 'sort_order' => 2,
             'title'   => 'Atribut Wajib',
             'content' => 'Siswa wajib mengenakan: (1) Badge nama di dada kiri, (2) Badge OSIS di lengan kiri, (3) Ikat pinggang hitam polos, (4) Sepatu hitam tertutup dan kaos kaki putih. Atribut yang hilang/rusak segera dilaporkan ke wali kelas.'],

            ['category' => 'berpakaian', 'sort_order' => 3,
             'title'   => 'Ketentuan Rambut Siswa Putra',
             'content' => 'Rambut siswa putra harus pendek, rapi, tidak melebihi kerah baju, tidak menutupi telinga, dan tidak dicat/diwarnai. Potongan rambut mohawk, undercut ekstrem, atau model tidak wajar lainnya tidak diperbolehkan.'],

            ['category' => 'berpakaian', 'sort_order' => 4,
             'title'   => 'Ketentuan Penampilan Siswa Putri',
             'content' => 'Siswa putri wajib menggunakan jilbab (bagi yang berhijab) atau mengikat rambut dengan rapi. Tidak diperkenankan menggunakan make-up tebal, cat kuku berwarna, atau perhiasan berlebihan selama jam sekolah.'],

            ['category' => 'berpakaian', 'sort_order' => 5,
             'title'   => 'Pakaian Olahraga',
             'content' => 'Seragam olahraga resmi sekolah hanya dikenakan pada jam pelajaran olahraga atau kegiatan ekstrakurikuler yang membutuhkan. Siswa tidak diperkenankan mengenakan pakaian olahraga di luar jadwal yang ditentukan.'],

            // ── Tata Perilaku ─────────────────────────────────────────────────
            ['category' => 'perilaku', 'sort_order' => 1,
             'title'   => 'Sikap terhadap Guru dan Staf',
             'content' => 'Siswa wajib bersikap hormat, sopan, dan santun kepada seluruh guru, staf, dan karyawan sekolah. Mengucapkan salam saat berpapasan, tidak memotong pembicaraan guru, dan menggunakan bahasa yang baik adalah kewajiban setiap siswa.'],

            ['category' => 'perilaku', 'sort_order' => 2,
             'title'   => 'Ketertiban dalam Kelas',
             'content' => 'Siswa wajib hadir di kelas sebelum guru masuk, menyiapkan buku dan alat tulis, tidak berbicara sendiri saat guru menjelaskan, dan meminta izin kepada guru sebelum meninggalkan ruang kelas.'],

            ['category' => 'perilaku', 'sort_order' => 3,
             'title'   => 'Kebersihan Lingkungan',
             'content' => 'Setiap siswa bertanggung jawab menjaga kebersihan kelas dan lingkungan sekolah. Sampah dibuang pada tempat yang telah disediakan, bangku dan meja dijaga kebersihan dan keutuhannya, serta dinding dan fasilitas sekolah tidak dicoret-coret.'],

            ['category' => 'perilaku', 'sort_order' => 4,
             'title'   => 'Kegiatan di Luar Kelas',
             'content' => 'Selama jam istirahat, siswa diperbolehkan berada di kantin, lapangan, atau area yang telah ditentukan. Siswa tidak diperkenankan berkeliaran di luar lingkungan sekolah tanpa izin dari guru piket.'],

            ['category' => 'perilaku', 'sort_order' => 5,
             'title'   => 'Penggunaan Fasilitas Sekolah',
             'content' => 'Siswa berhak menggunakan seluruh fasilitas sekolah (perpustakaan, laboratorium, lapangan olahraga) sesuai jadwal dan peraturan yang berlaku. Kerusakan fasilitas akibat kelalaian siswa menjadi tanggung jawab siswa bersangkutan.'],

            // ── Larangan ──────────────────────────────────────────────────────
            ['category' => 'larangan', 'sort_order' => 1,
             'title'   => 'Larangan Penggunaan Ponsel',
             'content' => 'Dilarang menggunakan ponsel/HP selama kegiatan belajar mengajar berlangsung. Ponsel harus dalam keadaan silent atau dimatikan. Pelanggaran pertama: ponsel disita dan dikembalikan kepada orang tua. Pelanggaran berulang: sanksi lebih berat.'],

            ['category' => 'larangan', 'sort_order' => 2,
             'title'   => 'Larangan Merokok',
             'content' => 'Dilarang keras merokok atau membawa rokok (termasuk rokok elektronik/vape) di dalam maupun di luar lingkungan sekolah selama memakai seragam sekolah. Pelanggaran dikenakan sanksi skorsing dan pemanggilan orang tua.'],

            ['category' => 'larangan', 'sort_order' => 3,
             'title'   => 'Larangan Kekerasan dan Perundungan',
             'content' => 'Dilarang melakukan tindakan kekerasan fisik, verbal, maupun siber (cyberbullying) terhadap sesama siswa, guru, atau staf sekolah. Pelanggaran berat dapat berakibat dikeluarkan dari sekolah sesuai peraturan yang berlaku.'],

            ['category' => 'larangan', 'sort_order' => 4,
             'title'   => 'Larangan Membawa Benda Berbahaya',
             'content' => 'Dilarang membawa senjata tajam, senjata api, petasan/mercon, minuman beralkohol, obat-obatan terlarang (narkoba), atau benda berbahaya lainnya ke lingkungan sekolah. Pelanggaran akan dilaporkan kepada pihak berwajib.'],

            ['category' => 'larangan', 'sort_order' => 5,
             'title'   => 'Larangan Meninggalkan Sekolah Tanpa Izin',
             'content' => 'Dilarang meninggalkan area sekolah pada jam pelajaran atau jam sekolah tanpa izin resmi dari guru piket atau guru kelas. Siswa yang bolos akan dikenakan sanksi sesuai peraturan dan orang tua akan dihubungi.'],

            ['category' => 'larangan', 'sort_order' => 6,
             'title'   => 'Larangan Membawa dan Menyebarkan Konten Negatif',
             'content' => 'Dilarang membawa, menyimpan, atau menyebarkan materi pornografi, ujaran kebencian, hoaks, atau konten berbau SARA dalam bentuk apapun, baik secara langsung maupun melalui media sosial menggunakan identitas siswa sekolah ini.'],
        ];

        foreach ($regulations as $reg) {
            SchoolRegulation::create($reg);
        }
    }
}
