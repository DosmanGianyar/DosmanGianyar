@php
    $record = $getRecord();
    if (!$record) return;

    $certUrl = $record->certificate 
        ? (str_starts_with($record->certificate, 'kurasi/') ? asset($record->certificate) : asset('storage/' . $record->certificate)) 
        : null;

    $letterUrl = $record->assignment_letter 
        ? (str_starts_with($record->assignment_letter, 'kurasi/') ? asset($record->assignment_letter) : asset('storage/' . $record->assignment_letter)) 
        : null;

    $photoUrl = $record->photo 
        ? (str_starts_with($record->photo, 'kurasi/') ? asset($record->photo) : asset('storage/' . $record->photo)) 
        : null;

    $levelBadges = [
        'sekolah'       => 'background:#334155; color:#cbd5e1; border:1px solid #475569;',
        'kabupaten'     => 'background:#0369a1; color:#e0f2fe; border:1px solid #0284c7;',
        'provinsi'      => 'background:#b45309; color:#fef3c7; border:1px solid #d97706;',
        'nasional'      => 'background:#047857; color:#d1fae5; border:1px solid #059669;',
        'internasional' => 'background:#be123c; color:#ffe4e6; border:1px solid #e11d48;',
    ];
    $levelStyle = $levelBadges[$record->level] ?? 'background:#334155; color:#cbd5e1; border:1px solid #475569;';

    $statusConfig = match ($record->status) {
        'approved' => [
            'label' => 'Disetujui / Valid (Masuk Rekap Sekolah)',
            'class' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
        ],
        'rejected' => [
            'label' => 'Ditolak / Tidak Valid',
            'class' => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
        ],
        default => $record->curation_status === 'revision' ? [
            'label' => 'Perlu Revisi Berkas',
            'class' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
        ] : [
            'label' => 'Menunggu Verifikasi Admin',
            'class' => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
        ],
    };
@endphp

<style>
    .sims-tagihan-wrap {
        width: 100%;
        margin-bottom: 1.5rem;
    }
    .sims-tagihan-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        font-size: 0.85rem !important;
        border: 1px solid rgba(148, 163, 184, 0.4) !important;
        border-radius: 12px !important;
        overflow: hidden !important;
    }
    .sims-tagihan-table th {
        border: 1px solid rgba(148, 163, 184, 0.35) !important;
        padding: 10px 14px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.05em !important;
        background-color: rgba(30, 41, 59, 0.85) !important;
        color: #f1f5f9 !important;
    }
    .sims-tagihan-table td {
        border: 1px solid rgba(148, 163, 184, 0.25) !important;
        padding: 9px 14px !important;
        vertical-align: middle !important;
        line-height: 1.45 !important;
    }
    .sims-tagihan-table tbody tr:nth-child(even) {
        background-color: rgba(30, 41, 59, 0.35) !important;
    }
    .sims-tagihan-table tbody tr:nth-child(odd) {
        background-color: rgba(15, 23, 42, 0.15) !important;
    }
    .sims-tagihan-table tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.12) !important;
    }

    /* Light mode */
    :root:not(.dark) .sims-tagihan-table {
        border: 1px solid #cbd5e1 !important;
    }
    :root:not(.dark) .sims-tagihan-table th {
        border: 1px solid #cbd5e1 !important;
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
    }
    :root:not(.dark) .sims-tagihan-table td {
        border: 1px solid #e2e8f0 !important;
    }
    :root:not(.dark) .sims-tagihan-table tbody tr:nth-child(even) {
        background-color: #f8fafc !important;
    }
    :root:not(.dark) .sims-tagihan-table tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }
    :root:not(.dark) .sims-tagihan-table tbody tr:hover {
        background-color: #eff6ff !important;
    }
</style>

<div class="sims-tagihan-wrap space-y-5">
    <!-- Header Status & Catatan Verifikasi -->
    <div class="p-4 rounded-2xl border border-slate-700/60 bg-slate-800/60 dark:bg-slate-900/60 shadow-sm space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status Verifikasi:</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $statusConfig['class'] }}">
                    {{ $statusConfig['label'] }}
                </span>
            </div>

            <div class="text-xs text-slate-400">
                @if($record->verified_by)
                    Diverifikasi oleh: <span class="font-bold text-slate-200">{{ $record->verifier?->name ?? 'Administrator' }}</span>
                    @if($record->verified_at)
                        <span>({{ $record->verified_at->translatedFormat('d F Y, H:i') }})</span>
                    @endif
                @else
                    <span class="italic text-slate-500">Belum diverifikasi oleh admin</span>
                @endif
            </div>
        </div>

        @if(!empty($record->curation_note))
            <div class="p-3.5 rounded-xl border border-amber-500/50 bg-amber-500/10 text-xs">
                <div class="font-bold text-amber-300 flex items-center gap-1.5 mb-1 text-xs">
                    <span>⚠️</span> Catatan Verifikasi / Alasan Revisi / Penolakan:
                </div>
                <div class="text-amber-200 pl-5 font-semibold whitespace-pre-line text-xs">
                    {{ $record->curation_note }}
                </div>
            </div>
        @endif
    </div>

    <!-- Tabel Rincian Tagihan Menurun -->
    <div class="overflow-x-auto rounded-2xl shadow-sm border border-slate-700/50">
        <table class="sims-tagihan-table">
            <thead>
                <tr>
                    <th style="width: 55px; text-align: center;">No.</th>
                    <th style="width: 270px; text-align: left;">Tagihan</th>
                    <th style="text-align: left;">Input Siswa</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. Judul Prestasi -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">1</td>
                    <td style="font-weight: 600;">Judul Prestasi / Kejuaraan</td>
                    <td style="font-weight: 700; color: #f59e0b; font-size: 0.95rem;">
                        🏆 {{ $record->title }}
                    </td>
                </tr>

                <!-- 2. Nama Ajang / Event -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">2</td>
                    <td style="font-weight: 600;">Nama Ajang / Event</td>
                    <td>
                        @if($record->event_name)
                            <span class="font-medium text-slate-200">{{ $record->event_name }}</span>
                        @else
                            <span class="text-slate-500 italic">— (Siswa tidak mengisi nama ajang)</span>
                        @endif
                    </td>
                </tr>

                <!-- 3. Penyelenggara -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">3</td>
                    <td style="font-weight: 600;">Penyelenggara Lomba</td>
                    <td>
                        @if($record->organizer)
                            <span class="font-medium text-slate-200">{{ $record->organizer }}</span>
                        @else
                            <span class="text-slate-500 italic">— (Siswa tidak mengisi penyelenggara)</span>
                        @endif
                    </td>
                </tr>

                <!-- 4. Tingkat Kejuaraan -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">4</td>
                    <td style="font-weight: 600;">Tingkat Kejuaraan</td>
                    <td>
                        <span style="display: inline-block; padding: 2px 10px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; {{ $levelStyle }}">
                            {{ $record->levelLabel() }}
                        </span>
                    </td>
                </tr>

                <!-- 5. Peringkat / Juara -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">5</td>
                    <td style="font-weight: 600;">Peringkat / Capaian (Juara)</td>
                    <td>
                        @if($record->rank)
                            <span style="display: inline-block; padding: 2px 10px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; background:#78350f; color:#fef3c7; border:1px solid #b45309;">
                                🥇 {{ $record->rank }}
                            </span>
                        @else
                            <span class="text-slate-500 italic">—</span>
                        @endif
                    </td>
                </tr>

                <!-- 6. Rumpun Talenta -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">6</td>
                    <td style="font-weight: 600;">Rumpun Bidang / Talenta</td>
                    <td>
                        <span style="display: inline-block; padding: 2px 10px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; background:#1e3a8a; color:#bfdbfe; border:1px solid #2563eb;">
                            {{ $record->fieldCategoryLabel() }}
                        </span>
                    </td>
                </tr>

                <!-- 7. Jenis Partisipasi -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">7</td>
                    <td style="font-weight: 600;">Jenis Partisipasi</td>
                    <td>
                        @if($record->isBeregu())
                            <span style="display: inline-block; padding: 2px 10px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; background:#581c87; color:#f3e8ff; border:1px solid #7e22ce;">
                                👥 Beregu ({{ $record->team_members->count() }} Anggota Tim)
                            </span>
                        @else
                            <span style="display: inline-block; padding: 2px 10px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; background:#334155; color:#cbd5e1; border:1px solid #475569;">
                                👤 Perorangan (Individu)
                            </span>
                        @endif
                    </td>
                </tr>

                <!-- 8. Tanggal Pelaksanaan -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">8</td>
                    <td style="font-weight: 600;">Tanggal Lomba / Capaian</td>
                    <td style="font-weight: 600;">
                        🗓️ {{ $record->achievement_date ? $record->achievement_date->translatedFormat('d F Y') : '—' }}
                    </td>
                </tr>

                <!-- 9. Website Resmi Ajang -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">9</td>
                    <td style="font-weight: 600;">Website Resmi Ajang / Berita</td>
                    <td>
                        @if($record->event_url)
                            <a href="{{ $record->event_url }}" target="_blank" style="color: #60a5fa; text-decoration: underline; font-weight: 600;">
                                🔗 {{ $record->event_url }} ↗
                            </a>
                        @else
                            <span class="text-slate-500 italic">— (Tidak dilampirkan)</span>
                        @endif
                    </td>
                </tr>

                <!-- 10. Deskripsi / Catatan Tambahan -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">10</td>
                    <td style="font-weight: 600;">Deskripsi / Catatan Tambahan</td>
                    <td style="white-space: pre-line;">
                        {{ $record->description ?: '— (Tidak ada catatan tambahan)' }}
                    </td>
                </tr>

                <!-- 11. Scan Piagam / Sertifikat (Wajib) -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">11</td>
                    <td style="font-weight: 600;">
                        Scan Piagam / Sertifikat <span style="color: #f43f5e; font-weight: 800;">*Wajib</span>
                    </td>
                    <td>
                        @if($certUrl)
                            <a href="{{ $certUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 8px; background: rgba(37, 99, 235, 0.2); color: #93c5fd; border: 1px solid #3b82f6; font-weight: 700; font-size: 0.75rem; text-decoration: none;">
                                📄 Buka File Sertifikat (PDF) ↗
                            </a>
                        @else
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 6px; background: rgba(244, 63, 94, 0.15); color: #fda4af; border: 1px solid #f43f5e; font-weight: 700; font-size: 0.75rem;">
                                ⚠️ BELUM DIISI / BELUM DIUPLOAD OLEH SISWA
                            </span>
                        @endif
                    </td>
                </tr>

                <!-- 12. Surat Tugas / Rekomendasi Sekolah -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">12</td>
                    <td style="font-weight: 600;">Surat Tugas / Rekomendasi Sekolah</td>
                    <td>
                        @if($letterUrl)
                            <a href="{{ $letterUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 8px; background: rgba(99, 102, 241, 0.2); color: #c7d2fe; border: 1px solid #6366f1; font-weight: 700; font-size: 0.75rem; text-decoration: none;">
                                📑 Buka Surat Tugas (PDF) ↗
                            </a>
                        @else
                            <span class="text-slate-500 italic">— Tidak dilampirkan</span>
                        @endif
                    </td>
                </tr>

                <!-- 13. Foto Kegiatan / Penyerahan Piagam -->
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">13</td>
                    <td style="font-weight: 600;">Foto Dokumentasi Kegiatan</td>
                    <td>
                        @if($photoUrl)
                            <a href="{{ $photoUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 8px; background: rgba(16, 185, 129, 0.2); color: #a7f3d0; border: 1px solid #10b981; font-weight: 700; font-size: 0.75rem; text-decoration: none;">
                                📷 Buka Foto Kegiatan (Ukuran Penuh) ↗
                            </a>
                        @else
                            <span class="text-slate-500 italic">— Belum ada foto kegiatan</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ─── BUKTI FISIK DI BAWAHNYA (SESUAI REQUEST USER) ──────────── -->
    <div class="p-5 rounded-2xl border border-slate-700/60 bg-slate-800/40 dark:bg-slate-900/50 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-700 pb-3">
            <h4 class="font-bold text-sm text-slate-100 flex items-center gap-2">
                <span class="text-base">📎</span> Lampiran Bukti Fisik & Dokumentasi (Tagihan #11, #12, #13)
            </h4>
            <span class="text-xs text-slate-400">Klik berkas untuk melihat ukuran penuh</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Bukti 1: Piagam / Sertifikat (Tagihan #11) -->
            <div class="p-4 rounded-xl border border-slate-700 bg-slate-800/80 flex flex-col justify-between space-y-3">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                        <span class="text-xs font-bold text-slate-300">Tagihan #11: Piagam / Sertifikat</span>
                        @if($certUrl)
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Ada</span>
                        @else
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 border border-rose-500/30">Kosong</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400">Scan sertifikat atau piagam kejuaraan siswa.</p>
                </div>

                @if($certUrl)
                    <a href="{{ $certUrl }}" target="_blank" class="w-full text-center py-2 px-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                        📄 Buka Sertifikat (PDF) ↗
                    </a>
                @else
                    <div class="p-2.5 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs text-center font-semibold">
                        ⚠️ Belum diunggah oleh siswa
                    </div>
                @endif
            </div>

            <!-- Bukti 2: Surat Tugas (Tagihan #12) -->
            <div class="p-4 rounded-xl border border-slate-700 bg-slate-800/80 flex flex-col justify-between space-y-3">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                        <span class="text-xs font-bold text-slate-300">Tagihan #12: Surat Tugas</span>
                        @if($letterUrl)
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Ada</span>
                        @else
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-slate-600/40 text-slate-400 border border-slate-600">Tidak ada</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400">Surat tugas / rekomendasi dari pihak sekolah.</p>
                </div>

                @if($letterUrl)
                    <a href="{{ $letterUrl }}" target="_blank" class="w-full text-center py-2 px-3 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                        📑 Buka Surat Tugas (PDF) ↗
                    </a>
                @else
                    <div class="p-2.5 rounded-lg bg-slate-700/30 border border-slate-700 text-slate-400 text-xs text-center italic">
                        Tidak dilampirkan
                    </div>
                @endif
            </div>

            <!-- Bukti 3: Foto Kegiatan (Tagihan #13) -->
            <div class="p-4 rounded-xl border border-slate-700 bg-slate-800/80 flex flex-col justify-between space-y-3">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                        <span class="text-xs font-bold text-slate-300">Tagihan #13: Foto Kegiatan</span>
                        @if($photoUrl)
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Ada</span>
                        @else
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-slate-600/40 text-slate-400 border border-slate-600">Tidak ada</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400">Dokumentasi penyerahan piagam / panggung ajang.</p>
                </div>

                @if($photoUrl)
                    <div class="space-y-2">
                        <a href="{{ $photoUrl }}" target="_blank" class="block rounded-lg overflow-hidden border border-slate-600 group relative">
                            <img src="{{ $photoUrl }}" alt="Foto Kegiatan" class="w-full h-28 object-cover group-hover:scale-105 transition-transform duration-200">
                        </a>
                        <a href="{{ $photoUrl }}" target="_blank" class="w-full text-center py-1.5 px-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 font-bold text-xs transition block">
                            📷 Lihat Ukuran Penuh ↗
                        </a>
                    </div>
                @else
                    <div class="p-2.5 rounded-lg bg-slate-700/30 border border-slate-700 text-slate-400 text-xs text-center italic">
                        Belum ada foto kegiatan
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
