<x-filament-panels::page>
    <style>
        .pres-banner {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            background: linear-gradient(135deg, #0a3880 0%, #1e3a8a 50%, #0f172a 100%);
            padding: 1.75rem 2rem;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            margin-bottom: 1.5rem;
        }
        .pres-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(147, 197, 253, 0.3);
            color: #bfdbfe;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
        .pres-title {
            font-size: 1.75rem;
            font-weight: 900;
            letter-spacing: -0.025em;
            color: #ffffff;
            margin: 0;
            line-height: 1.2;
        }
        .pres-sub {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #cbd5e1;
            line-height: 1.5;
            max-width: 50rem;
        }
        .pres-chips {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .pres-chip {
            padding: 0.25rem 0.75rem;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #f8fafc;
        }
        .pres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.25rem;
        }
        .pres-card {
            background: #1e293b;
            border-radius: 0.75rem;
            padding: 1.25rem;
            border: 1px solid #334155;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }
        .pres-card-hdr {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .pres-icon-box {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pres-icon-blue { background: rgba(37, 99, 235, 0.25); color: #60a5fa; }
        .pres-icon-amber { background: rgba(217, 119, 6, 0.25); color: #fbbf24; }
        .pres-icon-purple { background: rgba(147, 51, 234, 0.25); color: #c084fc; }
        .pres-icon-indigo { background: rgba(79, 70, 229, 0.25); color: #818cf8; }
        .pres-icon-teal { background: rgba(13, 148, 136, 0.25); color: #2dd4bf; }
        .pres-icon-slate { background: rgba(71, 85, 105, 0.35); color: #94a3b8; }
        .pres-card-title {
            font-size: 1rem;
            font-weight: 800;
            color: #f8fafc;
            margin: 0;
        }
        .pres-card-sub {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.125rem;
        }
        .pres-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.6pt;
            font-size: 0.8rem;
            color: #cbd5e1;
            line-height: 1.45;
        }
        .pres-list li {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.4rem;
        }
        .pres-bullet {
            font-weight: 800;
            flex-shrink: 0;
        }
        .b-blue { color: #60a5fa; }
        .b-amber { color: #fbbf24; }
        .b-purple { color: #c084fc; }
        .b-indigo { color: #818cf8; }
        .b-teal { color: #2dd4bf; }
        .b-emerald { color: #34d399; }
        .pres-footer-note {
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #334155;
            font-size: 0.725rem;
            font-weight: 700;
        }
    </style>

    {{-- Banner Utama Presentasi --}}
    <div class="pres-banner">
        <div class="pres-badge">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Panduan & Cheatsheet Presentasi SIMS
        </div>
        <h1 class="pres-title">SIMS SMAN 1 GIANYAR</h1>
        <p class="pres-sub">
            Ringkasan eksekutif seluruh modul dan fitur aplikasi sebagai pengingat utama saat mempresentasikan sistem di hadapan Kepala Sekolah, Dinas Pendidikan, Guru, dan Orang Tua Siswa.
        </p>

        <div class="pres-chips">
            <span class="pres-chip">✓ Web PWA & Mobile Flutter Native</span>
            <span class="pres-chip">✓ Multi-Role 4 Portal</span>
            <span class="pres-chip">✓ QR Code Token UUID Unguessable</span>
            <span class="pres-chip">✓ Realtime Geofencing GPS & Face Cam</span>
        </div>
    </div>

    {{-- Grid 6 Modul Utama SIMS --}}
    <div class="pres-grid">

        {{-- Modul 1: Presensi Pintar & Pengajuan --}}
        <div class="pres-card">
            <div>
                <div class="pres-card-hdr">
                    <div class="pres-icon-box pres-icon-blue">
                        <svg style="width:1.5rem; height:1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="pres-card-title">1. Presensi Pintar & Pengajuan</h3>
                        <p class="pres-card-sub">Kehadiran GPS & Manajemen Izin</p>
                    </div>
                </div>

                <ul class="pres-list">
                    <li>
                        <span class="pres-bullet b-blue">•</span>
                        <span><strong>Geofencing GPS & Foto Selfie</strong>: Presensi radius sekolah + verifikasi foto kamera live.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-blue">•</span>
                        <span><strong>Status Otomatis</strong>: Toleransi terlambat, alpa otomatis jika lewat batas jam KBM.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-blue">•</span>
                        <span><strong>Izin, Sakit, & Dispensasi</strong>: Pengajuan online lengkap dengan bukti berkas/surat dokter.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-emerald">•</span>
                        <span><strong>Izin Pulang Awal (*Early Checkout*)</strong>: Disetujui Guru Piket + Barcode Pass Keluar Satpam Gerbang.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-blue">•</span>
                        <span><strong>Lupa Absen</strong>: Pengajuan koreksi jam absen dengan verifikasi admin.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-blue">•</span>
                        <span><strong>Ekspor Laporan</strong>: Cetak rekap presensi Grid Harian, Excel, & PDF.</span>
                    </li>
                </ul>
            </div>

            <div class="pres-footer-note" style="color: #60a5fa;">
                💡 Poin Presentasi: Menjamin kejujuran absensi 100% tanpa bisanya manipulasi lokasi GPS mock.
            </div>
        </div>

        {{-- Modul 2: Poin Kedisiplinan & Prestasi (SIPINTER) --}}
        <div class="pres-card">
            <div>
                <div class="pres-card-hdr">
                    <div class="pres-icon-box pres-icon-amber">
                        <svg style="width:1.5rem; height:1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="pres-card-title">2. Kedisiplinan & Prestasi</h3>
                        <p class="pres-card-sub">SIPINTER & Pembentukan Karakter</p>
                    </div>
                </div>

                <ul class="pres-list">
                    <li>
                        <span class="pres-bullet b-amber">•</span>
                        <span><strong>Pencatatan Pelanggaran</strong>: Guru/Piket mencatat indikasi pelanggaran berdasar Kategori Poin.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-emerald">•</span>
                        <span><strong>Kurasi & Pencatatan Prestasi</strong>: Siswa mengajukan kejuaraan (Lomba/Sertifikat) + Verifikasi Kesiswaan + Poin Positif.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-amber">•</span>
                        <span><strong>Penerbitan SP Otomatis</strong>: Akumulasi poin pelanggaran otomatis memicu SP1, SP2, SP3.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-amber">•</span>
                        <span><strong>Layanan Bimbingan Konseling (BK)</strong>: Sesi konseling terintegrasi wali kelas & guru BK.</span>
                    </li>
                </ul>
            </div>

            <div class="pres-footer-note" style="color: #fbbf24;">
                💡 Poin Presentasi: Memadukan kurasi apresiasi prestasi dan penegakan tata tertib secara obyektif.
            </div>
        </div>

        {{-- Modul 3: Kartu Pelajar Digital & Barcode Verification --}}
        <div class="pres-card">
            <div>
                <div class="pres-card-hdr">
                    <div class="pres-icon-box pres-icon-purple">
                        <svg style="width:1.5rem; height:1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 114 0v1m-4 0a2 104 0m-5 8a2 100-4 2 2 0 000 4zm0 0c0 1.657 1.343 3 3 3s3-1.343 3-3"/></svg>
                    </div>
                    <div>
                        <h3 class="pres-card-title">3. Kartu Pelajar & Barcode Scan</h3>
                        <p class="pres-card-sub">Kartu Pelajar & Otentikasi Publik</p>
                    </div>
                </div>

                <ul class="pres-list">
                    <li>
                        <span class="pres-bullet b-purple">•</span>
                        <span><strong>Kartu Pelajar Digital 2 Sisi</strong>: Animasi Flip 3D KTP Aspect Ratio di Web & Mobile App.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-purple">•</span>
                        <span><strong>Unduh PDF 2 Halaman Presisi</strong>: Dilengkapi TTD & Stempel Resmi Kepsek + Header Aksara Bali.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-purple">•</span>
                        <span><strong>QR Code Verification Publik (Tanpa Login)</strong>: Scan QR membuka Keabsahan Siswa dengan **Token UUID Acak** (*Unguessable Hash*).</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-purple">•</span>
                        <span><strong>Barcode Event Scanner</strong>: Presensi kilat kegiatan/acara sekolah via scan barcode.</span>
                    </li>
                </ul>
            </div>

            <div class="pres-footer-note" style="color: #c084fc;">
                💡 Poin Presentasi: Keabsahan kartu siswa dapat diverifikasi siapa saja tanpa login namun 100% bebas peretasan URL.
            </div>
        </div>

        {{-- Modul 4: Akademik & Jurnal Mengajar Guru --}}
        <div class="pres-card">
            <div>
                <div class="pres-card-hdr">
                    <div class="pres-icon-box pres-icon-indigo">
                        <svg style="width:1.5rem; height:1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <h3 class="pres-card-title">4. Akademik & Jurnal Mengajar</h3>
                        <p class="pres-card-sub">Pembelajaran & Jurnal Guru</p>
                    </div>
                </div>

                <ul class="pres-list">
                    <li>
                        <span class="pres-bullet b-indigo">•</span>
                        <span><strong>Jadwal Pelajaran</strong>: Pemetaan KBM harian per kelas, mata pelajaran, dan guru pengampu.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-indigo">•</span>
                        <span><strong>Jurnal Harian Mengajar Guru</strong>: Pencatatan topik pembahasan, catatan kelas, & absensi jam KBM.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-indigo">•</span>
                        <span><strong>Tujuan Pembelajaran (TP) & Nilai</strong>: Pemantauan capaian kompetensi siswa.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-indigo">•</span>
                        <span><strong>Kalender Akademik & Hari Libur</strong>: Sinkronisasi agenda kegiatan sekolah.</span>
                    </li>
                </ul>
            </div>

            <div class="pres-footer-note" style="color: #818cf8;">
                💡 Poin Presentasi: Memudahkan guru mendokumentasikan KBM dan absensi siswa per jam pelajaran.
            </div>
        </div>

        {{-- Modul 5: Sarpras, Ekskul, & E-Voting OSIS --}}
        <div class="pres-card">
            <div>
                <div class="pres-card-hdr">
                    <div class="pres-icon-box pres-icon-teal">
                        <svg style="width:1.5rem; height:1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h3 class="pres-card-title">5. Sarpras, Ekskul, & E-Voting</h3>
                        <p class="pres-card-sub">Layanan Pendukung Sekolah</p>
                    </div>
                </div>

                <ul class="pres-list">
                    <li>
                        <span class="pres-bullet b-teal">•</span>
                        <span><strong>Inventaris Sarpras & Peminjaman</strong>: Manajemen aset sekolah, peminjaman barang, & laporan kerusakan.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-teal">•</span>
                        <span><strong>Ekstrakurikuler</strong>: Pendaftaran anggota, instruktur, & presensi kegiatan ekskul.</span>
                    </li>
                    <li>
                        <span class="pres-bullet b-teal">•</span>
                        <span><strong>E-Voting Pemilihan OSIS/MPK</strong>: Voting digital transparan (1 pemilih 1 token suara + *Quick Count Chart* real-time).</span>
                    </li>
                </ul>
            </div>

            <div class="pres-footer-note" style="color: #2dd4bf;">
                💡 Poin Presentasi: Digitalisasi sarpras dan pemilihan OSIS secara transparan tanpa kertas.
            </div>
        </div>

        {{-- Modul 6: Multi-Role, Security, PWA & Mobile Native --}}
        <div class="pres-card">
            <div>
                <div class="pres-card-hdr">
                    <div class="pres-icon-box pres-icon-slate">
                        <svg style="width:1.5rem; height:1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="pres-card-title">6. Multi-Role & Mobile Flutter</h3>
                        <p class="pres-card-sub">Keamanan & Portal Pengguna</p>
                    </div>
                </div>

                <ul class="pres-list">
                    <li>
                        <span class="pres-bullet" style="color:#94a3b8;">•</span>
                        <span><strong>Multi-Role 4 Portal</strong>: Portal Admin, Portal Guru, Portal Siswa, & Portal Orang Tua / Wali.</span>
                    </li>
                    <li>
                        <span class="pres-bullet" style="color:#94a3b8;">•</span>
                        <span><strong>Web PWA & Mobile Native Flutter App</strong>: Push Notifikasi FCM & Biometric / Device Lock.</span>
                    </li>
                    <li>
                        <span class="pres-bullet" style="color:#94a3b8;">•</span>
                        <span><strong>Lupa Password & Reset Mandiri</strong>: Pengajuan reset password aman bagi siswa/guru yang lupa kata sandi.</span>
                    </li>
                </ul>
            </div>

            <div class="pres-footer-note" style="color: #94a3b8;">
                💡 Poin Presentasi: Memastikan seluruh pemangku kepentingan (Sekolah & Orang Tua) terhubung secara real-time.
            </div>
        </div>

    </div>
</x-filament-panels::page>
