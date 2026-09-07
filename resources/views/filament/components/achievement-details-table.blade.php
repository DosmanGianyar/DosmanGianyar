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

    $docStandardUrl = $record->doc_standard_file 
        ? (str_starts_with($record->doc_standard_file, 'kurasi/') ? asset($record->doc_standard_file) : asset('storage/' . $record->doc_standard_file)) 
        : null;

    $selectionLevelUrl = $record->selection_level_file 
        ? (str_starts_with($record->selection_level_file, 'kurasi/') ? asset($record->selection_level_file) : asset('storage/' . $record->selection_level_file)) 
        : null;

    $frequencyConsistencyUrl = $record->frequency_consistency_file 
        ? (str_starts_with($record->frequency_consistency_file, 'kurasi/') ? asset($record->frequency_consistency_file) : asset('storage/' . $record->frequency_consistency_file)) 
        : null;

    $infrastructureUrl = $record->infrastructure_file 
        ? (str_starts_with($record->infrastructure_file, 'kurasi/') ? asset($record->infrastructure_file) : asset('storage/' . $record->infrastructure_file)) 
        : null;

    $rewardRecapUrl = $record->reward_recap_file 
        ? (str_starts_with($record->reward_recap_file, 'kurasi/') ? asset($record->reward_recap_file) : asset('storage/' . $record->reward_recap_file)) 
        : ($record->reward_certificate_file ? (str_starts_with($record->reward_certificate_file, 'kurasi/') ? asset($record->reward_certificate_file) : asset('storage/' . $record->reward_certificate_file)) : null);

    $rewardPhotoUrl = $record->reward_photo_file 
        ? (str_starts_with($record->reward_photo_file, 'kurasi/') ? asset($record->reward_photo_file) : asset('storage/' . $record->reward_photo_file)) 
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
            'style' => 'background:rgba(16,185,129,0.15); color:#6ee7b7; border:1px solid #059669;',
        ],
        'rejected' => [
            'label' => 'Ditolak / Tidak Valid',
            'style' => 'background:rgba(239,68,68,0.15); color:#fca5a5; border:1px solid #dc2626;',
        ],
        default => $record->curation_status === 'revision' ? [
            'label' => 'Perlu Revisi Berkas',
            'style' => 'background:rgba(245,158,11,0.15); color:#fcd34d; border:1px solid #d97706;',
        ] : [
            'label' => 'Menunggu Verifikasi Admin',
            'style' => 'background:rgba(59,130,246,0.15); color:#93c5fd; border:1px solid #2563eb;',
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
        border: 1px solid rgba(148, 163, 184, 0.45) !important;
        border-radius: 12px !important;
        overflow: hidden !important;
    }
    .sims-tagihan-table th {
        border: 1px solid rgba(148, 163, 184, 0.35) !important;
        padding: 11px 16px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.05em !important;
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
    }
    .sims-tagihan-table td {
        border: 1px solid rgba(148, 163, 184, 0.25) !important;
        padding: 10px 16px !important;
        vertical-align: middle !important;
        line-height: 1.45 !important;
    }
    .sims-tagihan-table tbody tr:nth-child(even) {
        background-color: rgba(30, 41, 59, 0.45) !important;
    }
    .sims-tagihan-table tbody tr:nth-child(odd) {
        background-color: rgba(15, 23, 42, 0.2) !important;
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

    /* Action buttons hover */
    .btn-verify-approve:hover { background-color: #047857 !important; transform: translateY(-1px); }
    .btn-verify-revision:hover { background-color: #b45309 !important; transform: translateY(-1px); }
    .btn-verify-reject:hover { background-color: #b91c1c !important; transform: translateY(-1px); }
    .btn-verify-reset:hover { background-color: #334155 !important; transform: translateY(-1px); }
</style>

<div class="sims-tagihan-wrap space-y-5">
    <!-- Header Status & Catatan Verifikasi -->
    <div style="padding: 14px 18px; border-radius: 14px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(71, 85, 105, 0.5);">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Status Verifikasi:</span>
                <span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-weight: 800; font-size: 0.75rem; {{ $statusConfig['style'] }}">
                    {{ $statusConfig['label'] }}
                </span>
            </div>

            <div style="font-size: 0.75rem; color: #94a3b8;">
                @if($record->verified_by)
                    Diverifikasi oleh: <strong style="color: #f1f5f9;">{{ $record->verifier?->name ?? 'Administrator' }}</strong>
                    @if($record->verified_at)
                        <span>({{ $record->verified_at->translatedFormat('d F Y, H:i') }})</span>
                    @endif
                @else
                    <span style="color: #64748b; font-style: italic;">Belum diverifikasi oleh admin</span>
                @endif
            </div>
        </div>

        @if(!empty($record->curation_note))
            <div style="margin-top: 12px; padding: 12px 14px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.8rem;">
                <div style="font-weight: 800; color: #fcd34d; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                    <span>⚠️</span> Catatan Verifikasi / Alasan Revisi / Penolakan:
                </div>
                <div style="color: #fef3c7; padding-left: 22px; font-weight: 500; white-space: pre-line;">
                    {{ $record->curation_note }}
                </div>
            </div>
        @endif
    </div>

    <!-- Tabel Rincian Tagihan Menurun (No. 1 s/d 13) -->
    <div style="overflow-x: auto; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
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
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">1</td>
                    <td style="font-weight: 700;">Judul Prestasi / Kejuaraan</td>
                    <td style="font-weight: 800; color: #f59e0b; font-size: 0.95rem;">
                        🏆 {{ $record->title }}
                    </td>
                </tr>

                <!-- 2. Nama Ajang / Event -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">2</td>
                    <td style="font-weight: 700;">Nama Ajang / Event</td>
                    <td>
                        @if($record->event_name)
                            <span style="font-weight: 600; color: #e2e8f0;">{{ $record->event_name }}</span>
                        @else
                            <span style="color: #64748b; font-style: italic;">— (Siswa tidak mengisi nama ajang)</span>
                        @endif
                    </td>
                </tr>

                <!-- 3. Penyelenggara -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">3</td>
                    <td style="font-weight: 700;">Penyelenggara Lomba</td>
                    <td>
                        @if($record->organizer)
                            <span style="font-weight: 600; color: #e2e8f0;">{{ $record->organizer }}</span>
                        @else
                            <span style="color: #64748b; font-style: italic;">— (Siswa tidak mengisi penyelenggara)</span>
                        @endif
                    </td>
                </tr>

                <!-- 4. Tingkat Kejuaraan -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">4</td>
                    <td style="font-weight: 700;">Tingkat Kejuaraan</td>
                    <td>
                        <span style="display: inline-block; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; {{ $levelStyle }}">
                            {{ $record->levelLabel() }}
                        </span>
                    </td>
                </tr>

                <!-- 5. Peringkat / Juara -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">5</td>
                    <td style="font-weight: 700;">Peringkat / Capaian (Juara)</td>
                    <td>
                        @if($record->rank)
                            <span style="display: inline-block; padding: 3px 12px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; background:#78350f; color:#fef3c7; border:1px solid #b45309;">
                                🥇 {{ $record->rank }}
                            </span>
                        @else
                            <span style="color: #64748b; font-style: italic;">—</span>
                        @endif
                    </td>
                </tr>

                <!-- 6. Rumpun Talenta -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">6</td>
                    <td style="font-weight: 700;">Rumpun Bidang / Talenta</td>
                    <td>
                        <span style="display: inline-block; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; background:#1e3a8a; color:#bfdbfe; border:1px solid #2563eb;">
                            {{ $record->fieldCategoryLabel() }}
                        </span>
                    </td>
                </tr>

                <!-- 7. Jenis Partisipasi -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">7</td>
                    <td style="font-weight: 700;">Jenis Partisipasi</td>
                    <td>
                        @if($record->isBeregu())
                            <span style="display: inline-block; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; background:#581c87; color:#f3e8ff; border:1px solid #7e22ce;">
                                👥 Beregu ({{ $record->team_members->count() }} Anggota Tim)
                            </span>
                        @else
                            <span style="display: inline-block; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; background:#334155; color:#cbd5e1; border:1px solid #475569;">
                                👤 Perorangan (Individu)
                            </span>
                        @endif
                    </td>
                </tr>

                <!-- 8. Tanggal Pelaksanaan -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">8</td>
                    <td style="font-weight: 700;">Tanggal Lomba / Capaian</td>
                    <td style="font-weight: 600;">
                        🗓️ {{ $record->achievement_date ? $record->achievement_date->translatedFormat('d F Y') : '—' }}
                    </td>
                </tr>

                <!-- 9. Website Resmi Ajang -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">9</td>
                    <td style="font-weight: 700;">Website Resmi Ajang / Berita</td>
                    <td>
                        @if($record->event_url)
                            <a href="{{ $record->event_url }}" target="_blank" style="color: #60a5fa; text-decoration: underline; font-weight: 700;">
                                🔗 {{ $record->event_url }} ↗
                            </a>
                        @else
                            <span style="color: #64748b; font-style: italic;">— (Tidak dilampirkan)</span>
                        @endif
                    </td>
                </tr>

                <!-- 10. Deskripsi / Catatan Tambahan -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">10</td>
                    <td style="font-weight: 700;">Deskripsi / Catatan Tambahan</td>
                    <td style="white-space: pre-line; color: #cbd5e1;">
                        {{ $record->description ?: '— (Tidak ada catatan tambahan)' }}
                    </td>
                </tr>

                <!-- 11. Scan Piagam / Sertifikat (Wajib) -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">11</td>
                    <td style="font-weight: 700;">
                        Scan Piagam / Sertifikat <span style="color: #f43f5e; font-weight: 800;">*Wajib</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                            @if($certUrl)
                                <a href="{{ $certUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 8px; background: #1d4ed8; color: #ffffff; font-weight: 700; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                    📄 Buka Berkas Sertifikat (PDF) ↗
                                </a>
                                <button type="button" wire:click="mountAction('edit_files')" title="Ganti berkas sertifikat" style="cursor: pointer; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.4); color: #93c5fd; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                    ✏️ Ganti Berkas
                                </button>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 6px; background: rgba(244, 63, 94, 0.15); color: #fda4af; border: 1px solid #f43f5e; font-weight: 800; font-size: 0.75rem;">
                                    ⚠️ BELUM DIISI / BELUM DIUPLOAD OLEH SISWA
                                </span>
                                <button type="button" wire:click="mountAction('edit_files')" title="Bantu upload berkas siswa" style="cursor: pointer; background: #2563eb; color: #ffffff; padding: 4px 12px; border-radius: 7px; border: none; font-size: 0.72rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                    📤 Bantu Uploadkan
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- 12. Surat Tugas / Rekomendasi Sekolah -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">12</td>
                    <td style="font-weight: 700;">
                        Surat Tugas / Rekomendasi Sekolah
                        <span style="color: #94a3b8; font-size: 0.7rem; display: block; font-weight: 500;">(Opsional)</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                            @if($letterUrl)
                                <a href="{{ $letterUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 8px; background: #4338ca; color: #ffffff; font-weight: 700; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                    📑 Buka Surat Tugas (PDF) ↗
                                </a>
                                <button type="button" wire:click="mountAction('edit_files')" title="Ganti berkas surat tugas" style="cursor: pointer; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.4); color: #c7d2fe; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                    ✏️ Ganti Berkas
                                </button>
                            @else
                                <span style="color: #64748b; font-style: italic;">— Tidak ada berkas (Opsional)</span>
                                <button type="button" wire:click="mountAction('edit_files')" title="Upload surat tugas" style="cursor: pointer; background: rgba(71, 85, 105, 0.4); border: 1px solid #475569; color: #cbd5e1; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                    📤 Upload
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- 13. Foto Kegiatan / Penyerahan Piagam -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">13</td>
                    <td style="font-weight: 700;">
                        Foto Dokumentasi Kegiatan
                        <span style="color: #94a3b8; font-size: 0.7rem; display: block; font-weight: 500;">(Opsional)</span>
                    </td>
                    <td>
                        @if($photoUrl)
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px;">
                                <a href="{{ $photoUrl }}" target="_blank" style="display: block; border-radius: 8px; overflow: hidden; border: 1px solid #475569;">
                                    <img src="{{ $photoUrl }}" alt="Foto Kegiatan" style="width: 80px; height: 55px; object-fit: cover; display: block;">
                                </a>
                                <a href="{{ $photoUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 8px; background: #047857; color: #ffffff; font-weight: 700; font-size: 0.75rem; text-decoration: none;">
                                    📷 Buka Foto Ukuran Penuh ↗
                                </a>
                                <button type="button" wire:click="mountAction('edit_files')" title="Ganti foto kegiatan" style="cursor: pointer; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #a7f3d0; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                    ✏️ Ganti Foto
                                </button>
                            </div>
                        @else
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: #64748b; font-style: italic;">— Tidak ada foto kegiatan (Opsional)</span>
                                <button type="button" wire:click="mountAction('edit_files')" title="Upload foto kegiatan" style="cursor: pointer; background: rgba(71, 85, 105, 0.4); border: 1px solid #475569; color: #cbd5e1; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                    📤 Upload Foto
                                </button>
                            </div>
                        @endif
                    </td>
                </tr>

                <!-- 14. Kurasi P1: Standar Penyelenggaraan / Juknis (Pedoman Lomba) -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">14</td>
                    <td style="font-weight: 700;">
                        Kurasi P1: Dokumen Standar Penyelenggaraan
                        <span style="color: #94a3b8; font-size: 0.7rem; display: block; font-weight: 500;">(Pedoman/Juknis resmi ajang lomba talenta - Opsional)</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                                @if($docStandardUrl)
                                    <a href="{{ $docStandardUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 8px; background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                        📄 Buka Juknis / Pedoman (PDF) ↗
                                    </a>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Ganti berkas juknis" style="cursor: pointer; background: rgba(2, 132, 199, 0.15); border: 1px solid rgba(2, 132, 199, 0.4); color: #bae6fd; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        ✏️ Ganti
                                    </button>
                                @else
                                    <span style="color: #64748b; font-style: italic;">— Tidak ada berkas (Opsional / Tidak Wajib)</span>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Upload juknis" style="cursor: pointer; background: rgba(71, 85, 105, 0.4); border: 1px solid #475569; color: #cbd5e1; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        📤 Upload
                                    </button>
                                @endif
                            </div>

                            @if(!empty($record->doc_standard_checklist))
                                <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 2px;">
                                    @foreach($record->doc_standard_checklist as $item)
                                        <span style="font-size: 0.68rem; font-weight: 600; padding: 2px 7px; border-radius: 5px; background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);">
                                            ✓ {{ ucwords(str_replace('_', ' ', $item)) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- 15. Kurasi P2: Bukti Tahapan / Jenjang Seleksi Ajang -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">15</td>
                    <td style="font-weight: 700;">
                        Kurasi P2: Bukti Jenjang Seleksi Ajang
                        <span style="color: #94a3b8; font-size: 0.7rem; display: block; font-weight: 500;">(Bukti seleksi berjenjang dari sekolah/kab/prov - Opsional)</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                                @if($selectionLevelUrl)
                                    <a href="{{ $selectionLevelUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 8px; background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                        📄 Buka Bukti Seleksi (PDF) ↗
                                    </a>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Ganti berkas seleksi" style="cursor: pointer; background: rgba(2, 132, 199, 0.15); border: 1px solid rgba(2, 132, 199, 0.4); color: #bae6fd; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        ✏️ Ganti
                                    </button>
                                @else
                                    <span style="color: #64748b; font-style: italic;">— Tidak ada berkas (Opsional / Tidak Wajib)</span>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Upload bukti seleksi" style="cursor: pointer; background: rgba(71, 85, 105, 0.4); border: 1px solid #475569; color: #cbd5e1; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        📤 Upload
                                    </button>
                                @endif
                            </div>

                            @if($record->selection_level)
                                <div style="font-size: 0.75rem; color: #cbd5e1;">
                                    Tahapan: <strong style="color: #93c5fd;">{{ ucwords(str_replace('_', ' ', $record->selection_level)) }}</strong>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- 16. Kurasi P3: Konsistensi Frekuensi Penyelenggaraan -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">16</td>
                    <td style="font-weight: 700;">
                        Kurasi P3: Konsistensi Frekuensi Penyelenggaraan
                        <span style="color: #94a3b8; font-size: 0.7rem; display: block; font-weight: 500;">(Bukti juknis lintas tahun lomba - Opsional)</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                                @if($frequencyConsistencyUrl)
                                    <a href="{{ $frequencyConsistencyUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 8px; background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                        📄 Buka Juknis Lintas Tahun (PDF) ↗
                                    </a>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Ganti berkas konsistensi" style="cursor: pointer; background: rgba(2, 132, 199, 0.15); border: 1px solid rgba(2, 132, 199, 0.4); color: #bae6fd; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        ✏️ Ganti
                                    </button>
                                @else
                                    <span style="color: #64748b; font-style: italic;">— Tidak ada berkas (Opsional / Tidak Wajib)</span>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Upload bukti konsistensi" style="cursor: pointer; background: rgba(71, 85, 105, 0.4); border: 1px solid #475569; color: #cbd5e1; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        📤 Upload
                                    </button>
                                @endif
                            </div>

                            @if($record->frequency_consistency)
                                <div style="font-size: 0.75rem; color: #cbd5e1;">
                                    Frekuensi: <strong style="color: #93c5fd;">{{ ucwords(str_replace('_', ' ', $record->frequency_consistency)) }}</strong>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- 17. Kurasi P4: Bukti Sarana & Prasarana / Venue Ajang -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">17</td>
                    <td style="font-weight: 700;">
                        Kurasi P4: Bukti Sarana & Prasarana / Venue
                        <span style="color: #94a3b8; font-size: 0.7rem; display: block; font-weight: 500;">(Dokumentasi venue & fasilitas lomba - Opsional)</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                                @if($infrastructureUrl)
                                    <a href="{{ $infrastructureUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 8px; background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                        📷 Buka Berkas Sarpras / Venue ↗
                                    </a>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Ganti berkas sarpras" style="cursor: pointer; background: rgba(2, 132, 199, 0.15); border: 1px solid rgba(2, 132, 199, 0.4); color: #bae6fd; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        ✏️ Ganti
                                    </button>
                                @else
                                    <span style="color: #64748b; font-style: italic;">— Tidak ada berkas (Opsional / Tidak Wajib)</span>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Upload dokumentasi venue" style="cursor: pointer; background: rgba(71, 85, 105, 0.4); border: 1px solid #475569; color: #cbd5e1; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        📤 Upload
                                    </button>
                                @endif
                            </div>

                            @if($record->infrastructure_type)
                                <div style="font-size: 0.75rem; color: #cbd5e1;">
                                    Fasilitas: <strong style="color: #93c5fd;">{{ ucwords(str_replace('_', ' ', $record->infrastructure_type)) }}</strong>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- 18. Kurasi P5: Rekapitulasi Hasil Lomba & Bukti Apresiasi -->
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #94a3b8;">18</td>
                    <td style="font-weight: 700;">
                        Kurasi P5: Rekapitulasi Hasil & Apresiasi
                        <span style="color: #94a3b8; font-size: 0.7rem; display: block; font-weight: 500;">(SK pemenang, rekapitulasi juara, medali, apresiasi - Opsional)</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                                @if($rewardRecapUrl)
                                    <a href="{{ $rewardRecapUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 8px; background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.75rem; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                        📑 Buka Rekap Hasil / Apresiasi ↗
                                    </a>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Ganti berkas apresiasi" style="cursor: pointer; background: rgba(2, 132, 199, 0.15); border: 1px solid rgba(2, 132, 199, 0.4); color: #bae6fd; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        ✏️ Ganti
                                    </button>
                                @else
                                    <span style="color: #64748b; font-style: italic;">— Tidak ada berkas (Opsional / Tidak Wajib)</span>
                                    <button type="button" wire:click="mountAction('edit_files')" title="Upload rekap hasil" style="cursor: pointer; background: rgba(71, 85, 105, 0.4); border: 1px solid #475569; color: #cbd5e1; padding: 4px 10px; border-radius: 7px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        📤 Upload
                                    </button>
                                @endif
                            </div>

                            @if(!empty($record->reward_types))
                                <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 2px;">
                                    @foreach($record->reward_types as $rew)
                                        <span style="font-size: 0.68rem; font-weight: 600; padding: 2px 7px; border-radius: 5px; background: rgba(16, 185, 129, 0.15); color: #a7f3d0; border: 1px solid rgba(16, 185, 129, 0.3);">
                                            🎁 {{ ucwords(str_replace('_', ' ', $rew)) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ─── TOMBOL AKSI VERIFIKASI LANGSUNG DI BAWAH TABEL ──────────── -->
    <div style="padding: 16px 20px; border-radius: 14px; background: #0b1329; border: 1px solid #334155; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
        <div>
            <div style="font-weight: 800; font-size: 0.85rem; color: #f8fafc; display: flex; align-items: center; gap: 6px;">
                <span>⚡</span> Aksi Verifikasi & Bantuan Edit Admin
            </div>
            <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">
                Verifikasi ajuan atau bantu perbaiki data & berkas siswa langsung di bawah ini:
            </p>
        </div>

        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px;">
            <!-- 0. Tombol Bantu Edit Berkas & Data Siswa (Biru) -->
            <button type="button" 
                    wire:click="mountAction('edit_files')" 
                    class="btn-verify-edit"
                    style="cursor: pointer; background: #2563eb; color: #ffffff; font-weight: 700; font-size: 0.8rem; padding: 9px 18px; border-radius: 10px; border: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.25); transition: all 0.15s ease;">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Bantu Edit Berkas & Data
            </button>

            <!-- 1. Tombol Setujui & Sahkan (Hijau) -->
            <button type="button" 
                    wire:click="mountAction('approve_achievement')" 
                    class="btn-verify-approve"
                    style="cursor: pointer; background: #059669; color: #ffffff; font-weight: 700; font-size: 0.8rem; padding: 9px 18px; border-radius: 10px; border: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.25); transition: all 0.15s ease;">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Setujui & Sahkan Prestasi
            </button>

            <!-- 2. Tombol Minta Revisi (Kuning/Amber) -->
            <button type="button" 
                    wire:click="mountAction('revision')" 
                    class="btn-verify-revision"
                    style="cursor: pointer; background: #d97706; color: #ffffff; font-weight: 700; font-size: 0.8rem; padding: 9px 18px; border-radius: 10px; border: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.25); transition: all 0.15s ease;">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Minta Revisi Berkas
            </button>

            <!-- 3. Tombol Tolak (Merah/Rose) -->
            <button type="button" 
                    wire:click="mountAction('reject')" 
                    class="btn-verify-reject"
                    style="cursor: pointer; background: #dc2626; color: #ffffff; font-weight: 700; font-size: 0.8rem; padding: 9px 18px; border-radius: 10px; border: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.25); transition: all 0.15s ease;">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Tolak / Tidak Valid
            </button>

            <!-- 4. Tombol Reset Status Ke Menunggu (Abu-abu) -->
            <button type="button" 
                    wire:click="mountAction('reset_pending')" 
                    class="btn-verify-reset"
                    style="cursor: pointer; background: #334155; color: #f1f5f9; font-weight: 600; font-size: 0.75rem; padding: 9px 14px; border-radius: 10px; border: 1px solid #475569; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s ease;">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                Reset Status
            </button>
        </div>
    </div>
</div>
