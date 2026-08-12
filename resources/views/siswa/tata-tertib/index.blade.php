@extends('layouts.siswa')

@section('title', 'Tata Tertib Sekolah Resmi')
@section('page-title', 'Tata Tertib Sekolah')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- Top Header Action Bar --}}
    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden print:hidden">
        <div class="absolute -right-12 -bottom-12 opacity-10 pointer-events-none">
            <svg class="w-72 h-72 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </div>
        <div class="relative z-10 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-semibold text-blue-200 border border-white/20">
                    <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Dokumen Resmi SMAN 1 Gianyar
                </div>

                {{-- Mode Switch Tabs --}}
                <div class="inline-flex p-1 bg-slate-800/80 backdrop-blur-md rounded-xl border border-white/10 text-xs font-semibold">
                    <button type="button" id="tab-doc-btn" onclick="switchViewMode('doc')"
                            class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 bg-blue-600 text-white shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        📜 Naskah Utuh Verbatim
                    </button>
                    <button type="button" id="tab-pdf-btn" onclick="switchViewMode('pdf')"
                            class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 text-slate-300 hover:text-white hover:bg-white/5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        📑 Scan PDF Original
                    </button>
                </div>
            </div>

            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Intisari Tata Tertib Peserta Didik</h1>
                <p class="text-blue-100 text-xs md:text-sm mt-1 leading-relaxed max-w-2xl">
                    Salinan sah dan utuh sesuai dengan Keputusan Kepala SMA Negeri 1 Gianyar. Dilengkapi pengesahan MPK, OSIS, Komite Sekolah & Kepala Sekolah.
                </p>
            </div>

            {{-- Controls & Actions --}}
            <div class="pt-2 flex flex-wrap items-center justify-between gap-3 border-t border-white/10">
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Quick Section Jump --}}
                    <select onchange="scrollToSection(this.value)" class="bg-white/10 backdrop-blur-md text-white text-xs font-medium px-3 py-2 rounded-xl border border-white/20 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="" class="text-slate-800">📌 Lompat ke Bagian...</option>
                        <option value="sec-a" class="text-slate-800">A. Kehadiran & Ketidakhadiran</option>
                        <option value="sec-b" class="text-slate-800">B. Kegiatan Belajar Mengajar</option>
                        <option value="sec-c" class="text-slate-800">C. Waktu Belajar</option>
                        <option value="sec-d" class="text-slate-800">D. Tata Tertib Kerapian</option>
                        <option value="sec-e" class="text-slate-800">E. Tata Tertib Berpakaian</option>
                        <option value="sec-f" class="text-slate-800">F. Ekstrakurikuler</option>
                        <option value="sec-g" class="text-slate-800">G. Hak Peserta Didik</option>
                        <option value="sec-h" class="text-slate-800">H. Kewajiban Peserta Didik</option>
                        <option value="sec-i" class="text-slate-800">I. Larangan Peserta Didik</option>
                        <option value="sec-j" class="text-slate-800">J. Upacara Bendera</option>
                        <option value="sec-k" class="text-slate-800">K. Ketentuan Lain</option>
                        <option value="sec-pengesahan" class="text-slate-800">✍️ Lembar Pengesahan</option>
                    </select>

                    {{-- Search Input --}}
                    <div class="relative">
                        <input type="text" id="doc-search" oninput="filterDocumentText(this.value)" placeholder="Cari kata kunci..." class="bg-white/10 backdrop-blur-md text-white placeholder-blue-200 text-xs px-3 py-2 pl-8 rounded-xl border border-white/20 focus:outline-none focus:ring-2 focus:ring-blue-400 w-44 md:w-56">
                        <svg class="w-3.5 h-3.5 text-blue-200 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white font-medium text-xs rounded-xl border border-white/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak
                    </button>
                    <a href="{{ $pdfUrl }}" target="_blank" download class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- VIEW MODE 1: Official Verbatim Paper Document View --}}
    <div id="view-doc" class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 md:p-12 space-y-8 print:shadow-none print:border-none print:p-0">

        {{-- Official Letterhead (Kop Surat) --}}
        <div class="text-center pb-6 border-b-4 border-double border-slate-800 space-y-1 relative">
            <div class="flex items-center justify-between max-w-2xl mx-auto px-4 pb-2">
                <div class="w-16 h-16 md:w-20 md:h-20 shrink-0 flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMAN 1 Gianyar" class="max-h-full max-w-full object-contain" onerror="this.style.display='none'">
                </div>
                <div class="text-center flex-1 px-2">
                    <h3 class="font-bold text-slate-800 text-xs md:text-sm uppercase tracking-wider">Pemerintah Provinsi Bali</h3>
                    <h4 class="font-bold text-slate-800 text-xs md:text-sm uppercase tracking-wider">Dinas Pendidikan Kepemudaan dan Olahraga</h4>
                    <h2 class="font-black text-slate-900 text-lg md:text-2xl uppercase tracking-tight font-serif mt-0.5">SMA Negeri 1 Gianyar</h2>
                    <p class="text-slate-600 text-[11px] md:text-xs mt-1">Jalan Ratna, Tegal Gianyar (80511), Telepon : (0361) 943034</p>
                    <p class="text-slate-600 text-[11px] md:text-xs">Laman : www.sman1-gianyar.sch.id, Pos-el : sman1.gianyar1963@gmail.com</p>
                </div>
                <div class="w-16 h-16 md:w-20 md:h-20 shrink-0 hidden sm:flex items-center justify-center">
                    <img src="{{ asset('images/logo-bali.png') }}" alt="Logo Pemprov Bali" class="max-h-full max-w-full object-contain" onerror="this.style.display='none'">
                </div>
            </div>
        </div>

        {{-- Document Title --}}
        <div class="text-center space-y-1 py-2">
            <h1 class="font-extrabold text-slate-900 text-xl md:text-2xl uppercase tracking-wide border-b-2 border-slate-900 inline-block pb-1">
                Intisari Tata Tertib Peserta Didik SMA Negeri 1 Gianyar
            </h1>
        </div>

        {{-- Verbatim Content Sections A to K --}}
        <div id="document-content-body" class="space-y-8 text-slate-800 text-sm md:text-base leading-relaxed font-sans">

            {{-- SECTION A --}}
            <section id="sec-a" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    A. Kehadiran, Ketidakhadiran dan Keterlambatan Peserta Didik
                </h2>
                
                <div class="pl-3 md:pl-4 space-y-3">
                    <div class="space-y-1.5">
                        <h3 class="font-semibold text-slate-900 text-sm md:text-base">A.1. Kehadiran Peserta Didik</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Peserta Didik yang bertugas sebagai piket wajib membersihkan ruang kelas sebelum pukul 07.15 WITA.</li>
                            <li>Seluruh Peserta Didik wajib berada di kelas pukul 07.15 WITA.</li>
                            <li>Pukul 07.15 WITA Peserta Didik beragama Hindu diwajibkan sembahyang Puja Trisandya, sedangkan umat lain diwajibkan berdoa sesuai dengan keyakinannya, kemudian wajib menyanyikan lagu kebangsaan Indonesia Raya yang dipimpin oleh salah satu pengurus kelas.</li>
                        </ol>
                    </div>

                    <div class="space-y-2 pt-1">
                        <h3 class="font-semibold text-slate-900 text-sm md:text-base">A.2. Ketidakhadiran Peserta Didik</h3>
                        
                        <div class="space-y-1.5 pl-2">
                            <p class="font-medium">1. Peserta Didik yang tidak hadir pada pembelajaran dan kegiatan sekolah, dikarenakan :</p>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>Sakit selama 1 hari, maka orang tua/wali wajib memberitahukan dengan bersurat kepada wali kelas/guru piket/guru BK.</li>
                                <li>Sakit selama 2 hari atau lebih, wajib melampirkan surat keterangan dokter.</li>
                                <li>Ijin selama 1 hari, peserta didik mengisi form surat ijin yang disediakan oleh sekolah H-2 sebelum ijin dan terlebih dahulu meminta tanda tangan orang tua dan dikumpulkan H-1 kepada perangkat kelas.</li>
                                <li>Ijin selama 2 hari atau lebih, maka orang tua / wali wajib datang kesekolah untuk mengurus perizinannya kepada kepala sekolah.</li>
                                <li>Peserta Didik yang tidak hadir tanpa pemberitahuan akan dinyatakan <em>alpha</em>.</li>
                            </ul>
                        </div>

                        <div class="space-y-1.5 pl-2 pt-2">
                            <p class="font-medium">2. Peserta Didik yang tidak dapat melanjutkan untuk mengikuti KBM dan kegiatan sekolah secara penuh dan meninggalkan sekolah, dikarenakan :</p>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>Sakit, harus mendapatkan izin dari guru di kelas dan/atau guru piket.</li>
                                <li>Izin, harus dijemput oleh orang tua / wali dan mendapatkan izin dari guru di kelas dan/atau piket.</li>
                                <li>Keperluan yang berkaitan dengan kegiatan sekolah, harus mendapatkan izin atau surat dispensasi dari pihak sekolah.</li>
                                <li>Izin yang bersifat sementara, harus sepengetahuan guru di kelas dan piket serta membawa surat izin permisi yang disediakan sekolah dan menunjukkan surat izin kepada satpam sekolah dan kembali pada waktu yang sudah disepakati.</li>
                                <li>Peserta didik yang tidak mengikuti KBM atau kegiatan sekolah sampai akhir tanpa pemberitahuan (bolos) akan dinyatakan <em>alpha</em>.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="space-y-1.5 pt-1">
                        <h3 class="font-semibold text-slate-900 text-sm md:text-base">A.3. Keterlambatan Peserta Didik</h3>
                        <p class="pl-2">Peserta Didik yang hadir setelah pukul 07.15 WITA dinyatakan terlambat.</p>
                    </div>
                </div>
            </section>

            {{-- SECTION B --}}
            <section id="sec-b" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    B. Kegiatan Belajar Mengajar
                </h2>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Sebelum kegiatan belajar mengajar dimulai, piket kelas menyiapkan sarana pembelajaran yang dibutuhkan dengan berkoordinasi terlebih dahulu dengan guru mata pelajaran.</li>
                    <li>Setiap mengawali pelajaran Peserta Didik wajib mengucapkan salam (Panganjali Umat) dan doa, serta mengakhiri dengan doa dan mengucapkan salam (Paramasanthi).</li>
                    <li>Mengikuti keseluruhan kegiatan belajar mengajar sejak jam pertama hingga jam terakhir, serta pulang secara bersama-sama setelah tanda bel pelajaran terakhir berbunyi.</li>
                    <li>Peserta Didik tidak diperkenankan meninggalkan kelas saat pembelajaran sedang berlangsung kecuali mendapat izin dari guru pengajar, dan tidak diperkenankan meninggalkan halaman sekolah, kecuali seizin wali kelas atau guru piket.</li>
                    <li>Jika 10 (sepuluh) menit setelah bel berbunyi guru belum hadir, maka ketua kelas atau pengurus kelas wajib menghubungi guru bersangkutan atau menanyakan kepada guru piket. Bila guru berhalangan hadir, Peserta Didik wajib mengerjakan Tugu yang diberikan sesuai dengan petunjuk.</li>
                    <li>Sesudah KBM berakhir, Peserta Didik wajib membersihkan ruang kelas serta mematikan semua alat elektronik yang ada di kelas dan meninggalkan ruang kelas dengan tertib.</li>
                </ol>
            </section>

            {{-- SECTION C --}}
            <section id="sec-c" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    C. Waktu Belajar
                </h2>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Memenuhi kehadiran di sekolah sekurang-kurangnya 90% dari hari efektif sekolah selama satu semester.</li>
                    <li>Peserta Didik tidak diperkenankan memajukan mata pelajaran yang tidak sesuai dengan jadwal.</li>
                    <li>Pelaksanaan ekstrakurikuler maksimal sampai pukul 18.30 WITA di sekolah dan apabila melebihi waktu yang telah ditentukan harus mendapat izin dari pembina ekstra atau pembina kesiswaan.</li>
                </ol>
            </section>

            {{-- SECTION D --}}
            <section id="sec-d" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    D. Tata Tertib Kerapian Peserta Didik
                </h2>
                <p class="font-medium">Peserta Didik berpenampilan dengan ketentuan :</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Rambut putra wajib dicukur dengan wajar atau standar (pendek dan rapi, tidak mengenai telinga, tidak mengenai alis, tidak dicukur modifikasi dan tidak ada kuncir).</li>
                    <li>Rambut putri wajib diikat satu tanpa poni dan tidak diperkenankan menggunakan jedai.</li>
                    <li>Semua Peserta Didik tidak diperkenankan mewarnai rambut dan kuku.</li>
                    <li>Peserta Didik putra tidak diperkenankan bertindik dan Peserta Didik putri tidak diperkenankan bertindik lebih dari sepasang.</li>
                    <li>Semua Peserta Didik tidak diperkenankan bertato.</li>
                    <li>Semua Peserta Didik tidak diperkenankan membawa atau memakai perhiasan dan aksesoris dalam bentuk apapun kecuali jam tangan, bagi Peserta Didik putri hanya diperkenankan memakai satu pasang giwang/anting yang tidak berlebihan.</li>
                    <li>Peserta Didik tidak diperkenankan menggunakan dan membawa peralatan berhias yang berlebihan, kecuali sisir.</li>
                </ul>
            </section>

            {{-- SECTION E --}}
            <section id="sec-e" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    E. Tata Tertib Berpakaian
                </h2>
                <p class="font-medium">Peserta Didik diwajibkan berpakaian dengan ketentuan :</p>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Hari Senin dan Selasa memakai baju kemeja putih dengan badge merah putih, celana/rok abu-abu, dasi, kaos kaki putih berlogo sekolah dengan tinggi minimal 10 cm diatas mata kaki dan topi pada saat upacara bendera</li>
                    <li>Hari Rabu memakai baju kemeja batik, celana/rok biru, dan kaos kaki putih berlogo sekolah dengan tinggi minimal 10 cm diatas mata kaki.</li>
                    <li>Hari Kamis memakai pakaian kemeja, kamben dan saput, memakai destar dan tidak menggunakan sandal jepit untuk putra. Dan memakai pakaian kebaya (kain sari), kamben, selendang dan tidak menggunakan sandal jepit untuk putri</li>
                    <li>Hari Jumat dan Sabtu memakai kemeja pramuka, celana/rok pramuka, dan kaos kaki hitam berlogo pramuka dengan tinggi minimal 10 cm diatas mata kaki.</li>
                    <li>
                        Hari Keagamaan Hindu dan Hari Jadi Provinsi Bali,
                        <div class="pl-4 pt-1 space-y-1">
                            <p><strong>Putra :</strong> Menggunakan pakaian kemeja putih, kamben dan saput, memakai destar putih dan tidak menggunakan sandal jepit.</p>
                            <p><strong>Putri :</strong> Menggunakan pakaian kebaya (kain sari) putih, kamben, selendang, dan tidak menggunakan sandal jepit.</p>
                        </div>
                    </li>
                </ol>

                <div class="pt-3 space-y-2">
                    <p class="font-medium">Ketentuan tambahan pada hari Senin, Selasa, Rabu, Jumat dan Sabtu yaitu :</p>
                    <ol class="list-decimal pl-6 space-y-2">
                        <li>Kemeja yang digunakan merupakan kemeja lengan pendek memakai satu saku di sebelah kiri tanpa jahitan belakang lengkap dengan badge nama Peserta Didik, badge nama sekolah, panjang bawah 25 cm-30 cm dari pinggang, lebar lengan 7 cm dari lengan dan panjang lengan 3 cm di bawah siku.</li>
                        <li>Baju kemeja dimasukkan ke dalam rok / celana panjang (kecuali kemeja pramuka putri).</li>
                        <li>
                            <div class="space-y-1">
                                <p><strong>Putra :</strong> Celana panjang ukuran standar, panjang celana sampai mata kaki dengan ukuran lingkar kaki minimal 44 cm (tidak dilipat pada bagian bawahnya).</p>
                                <p><strong>Putri :</strong> Rok dengan lipit hadap pada tengah muka, resleting di tengah belakang, panjang rok 5 cm di bawah lutut (dengan ketentuan model yang sudah ditetapkan dan tidak dilipat pada bagian bawahnya).</p>
                            </div>
                        </li>
                        <li>Memakai ikat pinggang ukuran lebar 3 cm warna hitam berlogo sekolah.</li>
                        <li>Memakai kaos dalam (singlet) berwarna putih (tidak diperkenankan menggunakan kaos bukan singlet)</li>
                        <li>Memakai sepatu hitam selain pantofel.</li>
                    </ol>
                </div>
            </section>

            {{-- SECTION F --}}
            <section id="sec-f" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    F. Kegiatan Ekstrakurikuler
                </h2>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Semua peserta didik Mengikuti minimal 1 (satu) dan maksimal 2 (dua) kegiatan Ekstrakurikuler di SMA Negeri 1 Gianyar sesuai minat dan bakat.</li>
                    <li>Jika Peserta Didik yang bersangkutan pindah kegiatan ekstrakurikuler diberi batas waktu 1 bulan dari pemilihan awal.</li>
                    <li>Setiap kegiatan kelompok ekstrakurikuler harus sepengetahuan dan didampingi Pembina/Koordinator Kelompok/Pelatih.</li>
                    <li>Kehadiran Peserta Didik kurang dari 75 % dalam kegiatan ekstrakurikuler tidak mendapat nilai ekstra.</li>
                </ol>
            </section>

            {{-- SECTION G --}}
            <section id="sec-g" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    G. Hak Peserta Didik
                </h2>
                <p class="font-medium">Setiap peserta didik mempunyai hak :</p>
                <ol class="list-decimal pl-6 space-y-1.5">
                    <li>Melaksanakan ibadah sesuai keyakinan yang dianutnya.</li>
                    <li>Memperoleh pembelajaran sebaik-baiknya.</li>
                    <li>Memperoleh layanan bidang akademik dan administrasi sesuai peraturan / ketentuan yang berlaku.</li>
                    <li>Memanfaatkan fasilitas untuk kelancaran proses belajar dan kegiatan ekstrakurikuler.</li>
                    <li>Mendapatkan beasiswa atau bantuan lainnya sesuai persyaratan.</li>
                    <li>Memperoleh laporan hasil belajar.</li>
                    <li>Mendapatkan informasi, perhatian dan perlindungan dari sekolah.</li>
                    <li>Memperoleh layanan BK dalam bidang pribadi, belajar, sosial, dan karir.</li>
                    <li>Menjadi pengurus OSIS/MPK di SMA Negeri 1 Gianyar.</li>
                    <li>Memilih kegiatan Ekstrakurikuler di SMA Negeri 1 Gianyar sesuai minat dan bakat.</li>
                    <li>Memberikan saran dan kritik yang membangun terhadap kebijakan sekolah melalui jalur pengurus kelas, OSIS/MPK, wali kelas, dan BK sesuai etika.</li>
                </ol>
            </section>

            {{-- SECTION H --}}
            <section id="sec-h" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    H. Kewajiban Peserta Didik
                </h2>
                <p class="font-medium">Setiap peserta didik mempunyai kewajiban :</p>
                <ol class="list-decimal pl-6 space-y-1.5">
                    <li>Menaati Tata Tertib yang berlaku di Sekolah.</li>
                    <li>Berperan aktif mengikuti kegiatan sekolah.</li>
                    <li>Berperan aktif menciptakan suasana tertib dan kondusif di lingkungan sekolah dan sekitarnya</li>
                    <li>Mengikuti kegiatan belajar mengajar dengan tekun, bersungguh-sungguh, disiplin, tertib, dan penuh tanggung jawab.</li>
                    <li>Menyelesaikan tugas-tugas akademik maupun non akademik yang diberikan oleh sekolah.</li>
                    <li>Mengikuti minimal 1 (satu) dan maksimal 2 (dua) kegiatan Ekstrakurikuler di SMA Negeri 1 Gianyar sesuai minat dan bakat.</li>
                    <li>Mengikuti upacara bendera pada hari-hari tertentu dan kegiatan keagamaan dengan tertib.</li>
                    <li>Menjaga dan memelihara sarana-prasarana serta kebersihan lingkungan sekolah.</li>
                    <li>Menjaga keamanan barang-barang milik pribadi dan apabila terjadi kehilangan, bukan tanggung jawab sekolah.</li>
                    <li>Menjaga nama baik sekolah, guru, keluarga dan diri sendiri baik di dalam maupun di luar sekolah.</li>
                    <li>Menjaga ketertiban dan kesopanan dalam berpakaian, bersikap, serta bertutur kata terhadap orang tua, guru, tenaga kependidikan, teman, dan masyarakat lainnya.</li>
                </ol>
            </section>

            {{-- SECTION I --}}
            <section id="sec-i" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    I. Larangan Peserta Didik
                </h2>
                <p class="font-medium">Peserta Didik dilarang :</p>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Melanggar kewajiban-kewajiban yang harus dipatuhi oleh peserta didik sebagaimana tertera pada point H.</li>
                    <li>Meninggalkan sekolah sebelum berakhirnya kegiatan belajar mengajar tanpa ijin (bolos).</li>
                    <li>Melakukan Perundungan (bullying).</li>
                    <li>Berkeliaran atau berada di luar kelas pada saat jam-jam kegiatan belajar mengajar.</li>
                    <li>Berkeliaran di luar lingkungan sekolah pada saat jam-jam kegiatan belajar mengajar maupun istirahat.</li>
                    <li>Memarkir sepeda motor di luar area parkir yang telah disepakati.</li>
                    <li>Menggunakan Knalpot Racing/bising (tidak sesuai dengan UU No 22 Tahun 2009 Pasal 285 tentang Lalu Lintas dan Penggunaan Jalan).</li>
                    <li>Mengendarai sepeda / sepeda motor pada jam pelajaran di halaman sekolah.</li>
                    <li>
                        Berpenampilan tidak sesuai dengan peraturan :
                        <div class="pl-4 pt-1 space-y-1">
                            <p><strong>a.</strong> Peserta didik Pria Berambut panjang menyentuh alis/telinga/kerah baju, memakai jelly, berkuku panjang, mengecat kuku dan rambut, bertindik, memakai anting-anting, gelang atau aksesoris wanita lainnya.</p>
                            <p><strong>b.</strong> Peserta didik Wanita: Bermake-up, berkuku panjang, mengecat kuku dan rambut, memakai perhiasan atau aksesoris yang berlebihan.</p>
                        </div>
                    </li>
                    <li>Bertingkah/berbicara teriak-teriak dan berbuat onar yang mengundang kerawanan di sekolah.</li>
                    <li>Berpacaran di lingkungan sekolah baik pada saat jam-jam sekolah maupun di luar jam sekolah.</li>
                    <li>Membawa senjata tajam, petasan, dan benda lainnya yang tidak ada hubungannya dengan kegiatan belajar mengajar.</li>
                    <li>Berkelahi dengan sesama peserta didik SMA Negeri 1 Gianyar, maupun peserta didik/orang lain dari luar SMA Negeri 1 Gianyar.</li>
                    <li>Merokok selama masih mengenakan seragam sekolah baik di sekolah maupun di luar sekolah.</li>
                    <li>Membawa/mengkonsumsi/mengedarkan obat-obat terlarang (NAPZA) maupun minuman beralkohol, baik di sekolah maupun di luar sekolah.</li>
                    <li>Berjudi atau hal-hal yang bisa diindikasikan perjudian.</li>
                    <li>Mengambil barang - barang baik milik sekolah maupun milik teman yang bukan miliknya.</li>
                    <li>Melakukan pemerasan atau sejenisnya yang bersifat atau diindikasikan Premanisme.</li>
                    <li>Melakukan pelecehan/penghinaan kehormatan martabat guru, tenaga kependidikan maupun sesama peserta didik.</li>
                    <li>Menonton foto/video yang memuat unsur pornografi.</li>
                    <li>Pelecehan seksual dan perbuatan asusila.</li>
                    <li>Meretas akun sekolah.</li>
                    <li>Memalsukan dokumen administrasi sekolah.</li>
                    <li>Melakukan tindakan kriminal.</li>
                </ol>
            </section>

            {{-- SECTION J --}}
            <section id="sec-j" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    J. Upacara Bendera
                </h2>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Peserta Didik wajib mengikuti upacara bendera pada hari senin atau hari besar nasional tertentu yang telah ditetapkan, baik yang diadakan di sekolah maupun diluar sekolah</li>
                    <li>Peserta Didik wajib mengenakan seragam upacara sesuai dengan imbauan dari dinas terkait.</li>
                    <li>Perangkat upacara adalah siswa yang ditunjuk oleh pihak sekolah.</li>
                    <li>Semua Peserta Didik wajib mengikuti dengan tertib dan khidmah seluruh rangkaian upacara bendera</li>
                </ol>
            </section>

            {{-- SECTION K --}}
            <section id="sec-k" class="doc-section space-y-3">
                <h2 class="font-bold text-slate-900 text-base md:text-lg border-b border-slate-200 pb-1">
                    K. Ketentuan Lain
                </h2>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Intisari tata tertib ini, merupakan kutipan dari tata tertib SMA Negeri 1 Gianyar yang ditetapkan berdasarkan keputusan kepala sekolah nomor B.10.400.3/1861/SMAN 1 GIANYAR/DISDIK</li>
                    <li>Foto contoh kerapian pada poin D dan E terlampir.</li>
                </ol>
            </section>

            {{-- SIGNATURES / LEMBAR PENGESAHAN --}}
            <section id="sec-pengesahan" class="pt-8 border-t border-slate-300 space-y-8">
                <div class="grid grid-cols-2 gap-8 text-center text-xs md:text-sm">
                    <div class="space-y-16">
                        <p class="font-semibold text-slate-900">Ketua MPK</p>
                        <div>
                            <p class="font-bold text-slate-900 underline">I Gd Nesta Chandra Adyatma</p>
                            <p class="text-slate-600">NIS. 15057</p>
                        </div>
                    </div>

                    <div class="space-y-16">
                        <p class="font-semibold text-slate-900">Ketua OSIS</p>
                        <div>
                            <p class="font-bold text-slate-900 underline">Putu Bhaskara Jaya Warnawa</p>
                            <p class="text-slate-600">NIS. 15175</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 text-center space-y-6">
                    <p class="font-semibold text-slate-900 uppercase tracking-wider text-xs">Mengetahui,</p>
                    
                    <div class="grid grid-cols-2 gap-8 text-center text-xs md:text-sm">
                        <div class="space-y-16">
                            <p class="font-semibold text-slate-900">Ketua Komite</p>
                            <div>
                                <p class="font-bold text-slate-900 underline">Ir. Pande Nyoman Yoharsana</p>
                            </div>
                        </div>

                        <div class="space-y-16">
                            <p class="font-semibold text-slate-900">Kepala SMA Negeri 1 Gianyar</p>
                            <div>
                                <p class="font-bold text-slate-900 underline">I Wayan Sudiarta, S.Pd., M.Pd</p>
                                <p class="text-slate-600">NIP. 19740415 199703 1 007</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>

    {{-- VIEW MODE 2: Original PDF Scan Viewer --}}
    <div id="view-pdf" class="hidden bg-white rounded-2xl shadow-xl border border-slate-200 p-4 space-y-4">
        <div class="flex items-center justify-between px-2 py-1 bg-slate-50 rounded-xl border border-slate-100">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Dokumen Asli Scanned PDF (Dengan Cap & Tanda Tangan Resmi)
            </div>
            <a href="{{ $pdfUrl }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1">
                Buka di Tab Baru ↗
            </a>
        </div>

        <div class="w-full h-[750px] md:h-[900px] rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
            <iframe src="{{ $pdfUrl }}#toolbar=1" class="w-full h-full border-none">
                <p class="p-6 text-center text-slate-500 text-sm">
                    Browser Anda tidak mendukung preview PDF. 
                    <a href="{{ $pdfUrl }}" class="text-blue-600 underline font-semibold">Klik di sini untuk mengunduh dokumen PDF</a>.
                </p>
            </iframe>
        </div>
    </div>

</div>

<script>
    function switchViewMode(mode) {
        const docView = document.getElementById('view-doc');
        const pdfView = document.getElementById('view-pdf');
        const docBtn  = document.getElementById('tab-doc-btn');
        const pdfBtn  = document.getElementById('tab-pdf-btn');

        if (mode === 'doc') {
            docView.classList.remove('hidden');
            pdfView.classList.add('hidden');
            docBtn.className = "px-4 py-2 rounded-lg transition-all flex items-center gap-2 bg-blue-600 text-white shadow-sm";
            pdfBtn.className = "px-4 py-2 rounded-lg transition-all flex items-center gap-2 text-slate-300 hover:text-white hover:bg-white/5";
        } else {
            pdfView.classList.remove('hidden');
            docView.classList.add('hidden');
            pdfBtn.className = "px-4 py-2 rounded-lg transition-all flex items-center gap-2 bg-blue-600 text-white shadow-sm";
            docBtn.className = "px-4 py-2 rounded-lg transition-all flex items-center gap-2 text-slate-300 hover:text-white hover:bg-white/5";
        }
    }

    function scrollToSection(secId) {
        if (!secId) return;
        switchViewMode('doc');
        const el = document.getElementById(secId);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function filterDocumentText(query) {
        switchViewMode('doc');
        const q = query.toLowerCase().trim();
        const sections = document.querySelectorAll('.doc-section');
        
        sections.forEach(sec => {
            if (!q) {
                sec.style.display = '';
                return;
            }
            const text = sec.textContent.toLowerCase();
            if (text.includes(q)) {
                sec.style.display = '';
            } else {
                sec.style.display = 'none';
            }
        });
    }
</script>
@endsection
