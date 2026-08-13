<x-filament-panels::page>
    <div class="space-y-6">
        
        {{-- Banner Pengingat Presentasi --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 p-6 md:p-8 text-white shadow-xl">
            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
                <svg class="w-96 h-96" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>

            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-semibold uppercase tracking-wider mb-3">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Panduan & Cheatsheet Presentasi SIMS
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">SIMS SMAN 1 GIANYAR</h1>
                <p class="mt-2 text-sm text-slate-300 max-w-3xl leading-relaxed">
                    Ringkasan eksekutif seluruh modul dan fitur aplikasi sebagai pengingat utama saat mempresentasikan sistem di hadapan Kepala Sekolah, Dinas Pendidikan, Guru, dan Orang Tua Siswa.
                </p>

                <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="px-3 py-1 bg-white/10 rounded-lg border border-white/10">✓ Web PWA & Mobile Flutter Native</span>
                    <span class="px-3 py-1 bg-white/10 rounded-lg border border-white/10">✓ Multi-Role 4 Portal</span>
                    <span class="px-3 py-1 bg-white/10 rounded-lg border border-white/10">✓ QR Code Token UUID Unguessable</span>
                    <span class="px-3 py-1 bg-white/10 rounded-lg border border-white/10">✓ Realtime Geofencing GPS & Face Cam</span>
                </div>
            </div>
        </div>

        {{-- Grid 6 Modul Fitur Utama --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Modul 1: Presensi Pintar & Pengajuan --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">1. Presensi Pintar & Pengajuan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Kehadiran GPS & Manajemen Izin</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 font-bold">•</span>
                            <span><strong>Geofencing GPS & Foto Selfie</strong>: Presensi radius sekolah + verifikasi foto kamera live.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 font-bold">•</span>
                            <span><strong>Status Otomatis</strong>: Toleransi terlambat, alpa otomatis jika lewat batas jam KBM.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 font-bold">•</span>
                            <span><strong>Izin, Sakit, & Dispensasi</strong>: Pengajuan online lengkap dengan bukti berkas/surat dokter.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">•</span>
                            <span><strong>Izin Pulang Lebih Awal (*Early Checkout*)</strong>: Disetujui Guru Piket + Generasi Barcode Pass Keluar Satpam Gerbang.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 font-bold">•</span>
                            <span><strong>Lupa Absen</strong>: Pengajuan koreksi jam absen dengan verifikasi admin.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 font-bold">•</span>
                            <span><strong>Ekspor Laporan</strong>: Cetak rekap presensi Grid Harian, Excel, & PDF.</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] text-blue-600 dark:text-blue-400 font-semibold">
                    💡 Poin Presentasi: Menjamin kejujuran absensi 100% tanpa bisanya manipulasi lokasi GPS mock.
                </div>
            </div>

            {{-- Modul 2: Poin Kedisiplinan & Prestasi (SIPINTER) --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">2. Kedisiplinan & Prestasi</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">SIPINTER & Pembentukan Karakter</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="text-amber-500 font-bold">•</span>
                            <span><strong>Pencatatan Pelanggaran</strong>: Guru/Piket mencatat indikasi pelanggaran berdasar Kategori Poin.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">•</span>
                            <span><strong>Kurasi & Pencatatan Prestasi</strong>: Siswa mengajukan kejuaraan (Lomba/Sertifikat) + Verifikasi Kesiswaan + Bonus Poin Positif.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-500 font-bold">•</span>
                            <span><strong>Penerbitan SP Otomatis</strong>: Akumulasi poin pelanggaran otomatis memicu SP1, SP2, SP3.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-500 font-bold">•</span>
                            <span><strong>Layanan Bimbingan Konseling (BK)</strong>: Sesi konseling terintegrasi wali kelas & guru BK.</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] text-amber-600 dark:text-amber-400 font-semibold">
                    💡 Poin Presentasi: Memadukan kurasi apresiasi prestasi dan penegakan tata tertib secara obyektif.
                </div>
            </div>

            {{-- Modul 3: Kartu Pelajar Digital & Barcode Verification --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 114 0v1m-4 0a2 104 0m-5 8a2 100-4 2 2 0 000 4zm0 0c0 1.657 1.343 3 3 3s3-1.343 3-3"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">3. Kartu Pelajar & Barcode Scan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Kartu Pelajar & Otentikasi Publik</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500 font-bold">•</span>
                            <span><strong>Kartu Pelajar Digital 2 Sisi</strong>: Animasi Flip 3D KTP Aspect Ratio di Web & Mobile App.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500 font-bold">•</span>
                            <span><strong>Unduh PDF 2 Halaman Presisi</strong>: Dilengkapi TTD & Stempel Resmi Kepsek + Header Aksara Bali.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500 font-bold">•</span>
                            <span><strong>QR Code Verification Publik (Tanpa Login)</strong>: Scan QR membuka Bukti Keabsahan Siswa dengan **Token UUID Acak** (*Unguessable Hash*).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500 font-bold">•</span>
                            <span><strong>Barcode Event Scanner</strong>: Presensi kilat kegiatan/acara sekolah via scan barcode.</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] text-purple-600 dark:text-purple-400 font-semibold">
                    💡 Poin Presentasi: Keabsahan kartu siswa dapat diverifikasi siapa saja tanpa login namun 100% bebas peretasan URL.
                </div>
            </div>

            {{-- Modul 4: Akademik & Jurnal Mengajar Guru --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">4. Akademik & Jurnal Mengajar</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pembelajaran & Jurnal Guru</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-500 font-bold">•</span>
                            <span><strong>Jadwal Pelajaran</strong>: Pemetaan KBM harian per kelas, mata pelajaran, dan guru pengampu.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-500 font-bold">•</span>
                            <span><strong>Jurnal Harian Mengajar Guru</strong>: Pencatatan topik pembahasan, catatan kelas, & absensi jam KBM.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-500 font-bold">•</span>
                            <span><strong>Tujuan Pembelajaran (TP) & Nilai</strong>: Pemantauan capaian kompetensi siswa.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-500 font-bold">•</span>
                            <span><strong>Kalender Akademik & Hari Libur</strong>: Sinkronisasi agenda kegiatan sekolah.</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold">
                    💡 Poin Presentasi: Memudahkan guru mendokumentasikan KBM dan absensi siswa per jam pelajaran.
                </div>
            </div>

            {{-- Modul 5: Sarpras, Ekskul, & E-Voting OSIS --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">5. Sarpras, Ekskul, & E-Voting</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Layanan Pendukung Sekolah</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="text-teal-500 font-bold">•</span>
                            <span><strong>Inventaris Sarpras & Peminjaman</strong>: Manajemen aset sekolah, peminjaman barang, & laporan kerusakan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-teal-500 font-bold">•</span>
                            <span><strong>Ekstrakurikuler</strong>: Pendaftaran anggota, instruktur, & presensi kegiatan ekskul.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-teal-500 font-bold">•</span>
                            <span><strong>E-Voting Pemilihan OSIS/MPK</strong>: Voting digital transparan (1 pemilih 1 token suara + *Quick Count Chart* real-time).</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] text-teal-600 dark:text-teal-400 font-semibold">
                    💡 Poin Presentasi: Digitalisasi sarpras dan pemilihan OSIS secara transparan tanpa kertas.
                </div>
            </div>

            {{-- Modul 6: Multi-Role, Security, PWA & Mobile Native --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">6. Multi-Role & Mobile Flutter</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Keamanan & Portal Pengguna</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="text-slate-500 font-bold">•</span>
                            <span><strong>Multi-Role 4 Portal</strong>: Portal Admin, Portal Guru, Portal Siswa, & Portal Orang Tua / Wali.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-slate-500 font-bold">•</span>
                            <span><strong>Web PWA & Mobile Native Flutter App</strong>: Push Notifikasi FCM & Biometric / Device Lock.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-slate-500 font-bold">•</span>
                            <span><strong>Lupa Password & Reset Mandiri</strong>: Pengajuan reset password aman bagi siswa/guru yang lupa kata sandi.</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] text-slate-600 dark:text-slate-400 font-semibold">
                    💡 Poin Presentasi: Memastikan seluruh pemangku kepentingan (Sekolah & Orang Tua) terhubung secara real-time.
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
