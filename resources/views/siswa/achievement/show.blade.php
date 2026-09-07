@extends('layouts.siswa')
@section('title', $achievement->title)
@section('page-title', 'Detail & Status Prestasi')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Status Banner & Catatan Verifikasi --}}
    @php
        $statusConfig = match($achievement->status) {
            'approved' => [
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'label' => 'Disetujui / Valid (Masuk Rekap Sekolah)',
                'card'  => 'bg-emerald-50/70 border-emerald-200',
            ],
            'rejected' => [
                'badge' => 'bg-rose-100 text-rose-800 border-rose-300',
                'label' => 'Ditolak / Tidak Valid',
                'card'  => 'bg-rose-50/70 border-rose-200',
            ],
            default => $achievement->curation_status === 'revision' ? [
                'badge' => 'bg-amber-100 text-amber-900 border-amber-300',
                'label' => 'Perlu Revisi Berkas',
                'card'  => 'bg-amber-50/80 border-amber-200',
            ] : [
                'badge' => 'bg-blue-100 text-blue-800 border-blue-300',
                'label' => 'Menunggu Verifikasi Admin',
                'card'  => 'bg-blue-50/70 border-blue-200',
            ],
        };

        $certUrl = $achievement->certificate 
            ? (str_starts_with($achievement->certificate, 'kurasi/') ? asset($achievement->certificate) : asset('storage/' . $achievement->certificate)) 
            : null;

        $letterUrl = $achievement->assignment_letter 
            ? (str_starts_with($achievement->assignment_letter, 'kurasi/') ? asset($achievement->assignment_letter) : asset('storage/' . $achievement->assignment_letter)) 
            : null;

        $photoUrl = $achievement->photo 
            ? (str_starts_with($achievement->photo, 'kurasi/') ? asset($achievement->photo) : asset('storage/' . $achievement->photo)) 
            : null;

        $docStandardUrl = $achievement->doc_standard_file 
            ? (str_starts_with($achievement->doc_standard_file, 'kurasi/') ? asset($achievement->doc_standard_file) : asset('storage/' . $achievement->doc_standard_file)) 
            : null;

        $selectionLevelUrl = $achievement->selection_level_file 
            ? (str_starts_with($achievement->selection_level_file, 'kurasi/') ? asset($achievement->selection_level_file) : asset('storage/' . $achievement->selection_level_file)) 
            : null;

        $frequencyConsistencyUrl = $achievement->frequency_consistency_file 
            ? (str_starts_with($achievement->frequency_consistency_file, 'kurasi/') ? asset($achievement->frequency_consistency_file) : asset('storage/' . $achievement->frequency_consistency_file)) 
            : null;

        $infrastructureUrl = $achievement->infrastructure_file 
            ? (str_starts_with($achievement->infrastructure_file, 'kurasi/') ? asset($achievement->infrastructure_file) : asset('storage/' . $achievement->infrastructure_file)) 
            : null;

        $rewardRecapUrl = $achievement->reward_recap_file 
            ? (str_starts_with($achievement->reward_recap_file, 'kurasi/') ? asset($achievement->reward_recap_file) : asset('storage/' . $achievement->reward_recap_file)) 
            : ($achievement->reward_certificate_file ? (str_starts_with($achievement->reward_certificate_file, 'kurasi/') ? asset($achievement->reward_certificate_file) : asset('storage/' . $achievement->reward_certificate_file)) : null);
    @endphp

    <!-- Card Status Verifikasi -->
    <div class="bg-white border border-gray-200 rounded-3xl p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <span class="text-xs font-bold px-3 py-1 rounded-full border {{ $statusConfig['badge'] }}">
                {{ $statusConfig['label'] }}
            </span>
            <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $achievement->levelBadgeClass() }}">
                Tingkat: {{ $achievement->levelLabel() }}
            </span>
        </div>

        <h3 class="font-extrabold text-gray-900 text-lg leading-tight flex items-center gap-2">
            <span>🏆</span> {{ $achievement->title }}
        </h3>

        @if($achievement->verified_at)
            <p class="text-xs text-gray-500">
                Diverifikasi oleh <strong>{{ $achievement->verifier?->name ?? 'Admin Kesiswaan' }}</strong> pada {{ $achievement->verified_at->translatedFormat('d F Y, H:i') }}
            </p>
        @else
            <p class="text-xs text-gray-400 italic">
                Menunggu peninjauan oleh Admin Kesiswaan / Tim Kurasi Prestasi.
            </p>
        @endif

        {{-- Catatan Revisi / Penolakan Admin --}}
        @php
            $note = $achievement->curation_note ?: $achievement->rejection_reason;
        @endphp
        @if($note)
            <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 text-xs space-y-1 mt-2">
                <div class="font-extrabold text-amber-900 flex items-center gap-1.5">
                    <span>⚠️</span> Catatan Verifikasi / Tagihan yang Perlu Diperbaiki:
                </div>
                <p class="text-amber-800 font-medium whitespace-pre-line pl-5">{{ $note }}</p>
            </div>
        @endif
    </div>

    <!-- Tabel Rincian Tagihan Prestasi Menurun -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-5 space-y-3">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h4 class="font-extrabold text-gray-900 text-sm flex items-center gap-2">
                <span>📋</span> Rincian Tagihan & Data Isian Anda
            </h4>
            <span class="text-xs text-gray-400">Gunakan nomor tagihan sebagai acuan konsultasi</span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-200">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 border-b border-gray-200">
                        <th class="py-3 px-3 w-12 text-center font-extrabold uppercase border-r border-gray-200">No.</th>
                        <th class="py-3 px-4 w-52 sm:w-60 text-left font-extrabold uppercase border-r border-gray-200">Tagihan</th>
                        <th class="py-3 px-4 text-left font-extrabold uppercase">Isian / Data Anda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-800">
                    <!-- 1. Judul Prestasi -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">1</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Judul Prestasi / Kejuaraan</td>
                        <td class="py-2.5 px-4 font-bold text-amber-700 text-sm">🏆 {{ $achievement->title }}</td>
                    </tr>

                    <!-- 2. Nama Ajang / Event -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">2</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Nama Ajang / Event</td>
                        <td class="py-2.5 px-4">
                            {{ $achievement->event_name ?: '—' }}
                        </td>
                    </tr>

                    <!-- 3. Penyelenggara Lomba -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">3</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Penyelenggara Lomba</td>
                        <td class="py-2.5 px-4">
                            @if($achievement->organizer)
                                <span class="font-medium text-gray-900">{{ $achievement->organizer }}</span>
                            @else
                                <span class="text-amber-600 italic">⚠️ Belum diisi</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 4. Tingkat Kejuaraan -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">4</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Tingkat Kejuaraan</td>
                        <td class="py-2.5 px-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-md font-bold text-xs {{ $achievement->levelBadgeClass() }}">
                                {{ $achievement->levelLabel() }}
                            </span>
                        </td>
                    </tr>

                    <!-- 5. Peringkat / Juara -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">5</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Peringkat / Capaian (Juara)</td>
                        <td class="py-2.5 px-4 font-bold text-gray-900">
                            @if($achievement->rank)
                                <span class="inline-block px-2.5 py-0.5 rounded-md font-extrabold text-xs bg-amber-100 text-amber-900 border border-amber-300">
                                    🥇 {{ $achievement->rank }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 6. Rumpun Talenta -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">6</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Rumpun Bidang / Talenta</td>
                        <td class="py-2.5 px-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-md font-bold text-xs bg-blue-50 text-blue-800 border border-blue-200">
                                {{ $achievement->fieldCategoryLabel() }}
                            </span>
                        </td>
                    </tr>

                    <!-- 7. Jenis Partisipasi -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">7</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Jenis Partisipasi</td>
                        <td class="py-2.5 px-4">
                            @if($achievement->isBeregu())
                                <span class="inline-block px-2.5 py-0.5 rounded-md font-bold text-xs bg-purple-50 text-purple-800 border border-purple-200">
                                    👥 Beregu ({{ $achievement->team_members->count() }} Anggota Tim)
                                </span>
                            @else
                                <span class="inline-block px-2.5 py-0.5 rounded-md font-bold text-xs bg-gray-100 text-gray-700 border border-gray-200">
                                    👤 Perorangan (Individu)
                                </span>
                            @endif
                        </td>
                    </tr>

                    <!-- 8. Tanggal Pelaksanaan -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">8</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Tanggal Pelaksanaan / Capaian</td>
                        <td class="py-2.5 px-4 font-medium">
                            🗓️ {{ $achievement->achievement_date ? $achievement->achievement_date->translatedFormat('d F Y') : '—' }}
                        </td>
                    </tr>

                    <!-- 9. Website Resmi Ajang -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">9</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Website Resmi Ajang / Berita</td>
                        <td class="py-2.5 px-4">
                            @if($achievement->event_url)
                                <a href="{{ $achievement->event_url }}" target="_blank" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                                    🔗 {{ $achievement->event_url }} ↗
                                </a>
                            @else
                                <span class="text-gray-400 italic">— Tidak ada tautan</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 10. Deskripsi / Catatan Tambahan -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">10</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Deskripsi / Catatan Tambahan</td>
                        <td class="py-2.5 px-4 text-gray-700 whitespace-pre-line">
                            {{ $achievement->description ?: '—' }}
                        </td>
                    </tr>

                    <!-- 11. Scan Piagam / Sertifikat -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">11</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">
                            Scan Piagam / Sertifikat <span class="text-red-500 font-extrabold">*Wajib</span>
                        </td>
                        <td class="py-2.5 px-4">
                            @if($certUrl)
                                <a href="{{ $certUrl }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-bold text-xs hover:bg-blue-100">
                                    📄 Buka File Sertifikat (PDF) ↗
                                </a>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold text-xs">
                                    ⚠️ Belum Diupload
                                </span>
                            @endif
                        </td>
                    </tr>

                    <!-- 12. Surat Tugas / Rekomendasi -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">12</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Surat Tugas / Rekomendasi Sekolah</td>
                        <td class="py-2.5 px-4">
                            @if($letterUrl)
                                <a href="{{ $letterUrl }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold text-xs hover:bg-indigo-100">
                                    📑 Buka Surat Tugas (PDF) ↗
                                </a>
                            @else
                                <span class="text-gray-400 italic">Tidak dilampirkan</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 13. Foto Kegiatan / Penyerahan Piagam -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">13</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">Foto Kegiatan / Penyerahan Piagam</td>
                        <td class="py-2.5 px-4">
                            @if($photoUrl)
                                <a href="{{ $photoUrl }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-xs hover:bg-emerald-100">
                                    📷 Buka Foto Kegiatan ↗
                                </a>
                            @else
                                <span class="text-gray-400 italic">Belum ada foto</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 14. Kurasi P1: Standar Penyelenggaraan / Juknis (Pedoman) -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">14</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">
                            Kurasi P1: Dokumen Standar Penyelenggaraan
                            <span class="text-[10px] text-gray-400 font-normal block">(Pedoman / Juknis resmi lomba - Opsional)</span>
                        </td>
                        <td class="py-2.5 px-4">
                            @if($docStandardUrl)
                                <div class="space-y-1">
                                    <a href="{{ $docStandardUrl }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 border border-sky-200 font-bold text-xs hover:bg-sky-100">
                                        📄 Buka Juknis / Pedoman (PDF) ↗
                                    </a>
                                    @if(!empty($achievement->doc_standard_checklist))
                                        <div class="flex flex-wrap gap-1 pt-0.5">
                                            @foreach($achievement->doc_standard_checklist as $item)
                                                <span class="bg-gray-100 text-gray-600 text-[10px] px-1.5 py-0.5 rounded">✓ {{ ucwords(str_replace('_', ' ', $item)) }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 italic">— Tidak ada berkas (Opsional)</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 15. Kurasi P2: Bukti Tahapan / Jenjang Seleksi Ajang -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">15</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">
                            Kurasi P2: Bukti Jenjang Seleksi Ajang
                            <span class="text-[10px] text-gray-400 font-normal block">(Bukti seleksi berjenjang - Opsional)</span>
                        </td>
                        <td class="py-2.5 px-4">
                            @if($selectionLevelUrl)
                                <div class="space-y-1">
                                    <a href="{{ $selectionLevelUrl }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 border border-sky-200 font-bold text-xs hover:bg-sky-100">
                                        📄 Buka Bukti Seleksi (PDF) ↗
                                    </a>
                                    @if($achievement->selection_level)
                                        <span class="text-[11px] text-gray-600 block">Tahapan: <strong>{{ ucwords(str_replace('_', ' ', $achievement->selection_level)) }}</strong></span>
                                    @endif
                                </div>
                            @elseif($achievement->selection_level)
                                <span class="text-gray-700 font-semibold">{{ ucwords(str_replace('_', ' ', $achievement->selection_level)) }}</span>
                                <span class="text-gray-400 italic text-[11px]"> (Berkas tidak dilampirkan)</span>
                            @else
                                <span class="text-gray-400 italic">— Tidak ada berkas (Opsional)</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 16. Kurasi P3: Konsistensi Frekuensi Penyelenggaraan -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">16</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">
                            Kurasi P3: Konsistensi Frekuensi Penyelenggaraan
                            <span class="text-[10px] text-gray-400 font-normal block">(Arsip / Juknis lintas tahun - Opsional)</span>
                        </td>
                        <td class="py-2.5 px-4">
                            @if($frequencyConsistencyUrl)
                                <div class="space-y-1">
                                    <a href="{{ $frequencyConsistencyUrl }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 border border-sky-200 font-bold text-xs hover:bg-sky-100">
                                        📄 Buka Juknis Lintas Tahun (PDF) ↗
                                    </a>
                                    @if($achievement->frequency_consistency)
                                        <span class="text-[11px] text-gray-600 block">Frekuensi: <strong>{{ ucwords(str_replace('_', ' ', $achievement->frequency_consistency)) }}</strong></span>
                                    @endif
                                </div>
                            @elseif($achievement->frequency_consistency)
                                <span class="text-gray-700 font-semibold">{{ ucwords(str_replace('_', ' ', $achievement->frequency_consistency)) }}</span>
                                <span class="text-gray-400 italic text-[11px]"> (Berkas tidak dilampirkan)</span>
                            @else
                                <span class="text-gray-400 italic">— Tidak ada berkas (Opsional)</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 17. Kurasi P4: Bukti Sarana & Prasarana / Venue Ajang -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">17</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">
                            Kurasi P4: Bukti Sarana & Prasarana / Venue
                            <span class="text-[10px] text-gray-400 font-normal block">(Dokumentasi venue lomba - Opsional)</span>
                        </td>
                        <td class="py-2.5 px-4">
                            @if($infrastructureUrl)
                                <div class="space-y-1">
                                    <a href="{{ $infrastructureUrl }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 border border-sky-200 font-bold text-xs hover:bg-sky-100">
                                        📷 Buka Bukti Sarpras / Venue ↗
                                    </a>
                                    @if($achievement->infrastructure_type)
                                        <span class="text-[11px] text-gray-600 block">Fasilitas: <strong>{{ ucwords(str_replace('_', ' ', $achievement->infrastructure_type)) }}</strong></span>
                                    @endif
                                </div>
                            @elseif($achievement->infrastructure_type)
                                <span class="text-gray-700 font-semibold">{{ ucwords(str_replace('_', ' ', $achievement->infrastructure_type)) }}</span>
                                <span class="text-gray-400 italic text-[11px]"> (Dokumentasi tidak dilampirkan)</span>
                            @else
                                <span class="text-gray-400 italic">— Tidak ada berkas (Opsional)</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 18. Kurasi P5: Rekapitulasi Hasil Lomba & Bukti Apresiasi -->
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-2.5 px-3 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50/60">18</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800 border-r border-gray-200 bg-gray-50/30">
                            Kurasi P5: Rekapitulasi Hasil & Bukti Apresiasi
                            <span class="text-[10px] text-gray-400 font-normal block">(SK Pemenang, rekap juara, uang pembinaan - Opsional)</span>
                        </td>
                        <td class="py-2.5 px-4">
                            @if($rewardRecapUrl)
                                <div class="space-y-1">
                                    <a href="{{ $rewardRecapUrl }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 border border-sky-200 font-bold text-xs hover:bg-sky-100">
                                        📑 Buka Rekap Hasil / Apresiasi ↗
                                    </a>
                                    @if(!empty($achievement->reward_types))
                                        <div class="flex flex-wrap gap-1 pt-0.5">
                                            @foreach($achievement->reward_types as $rew)
                                                <span class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-0.5 rounded border border-emerald-200">🎁 {{ ucwords(str_replace('_', ' ', $rew)) }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @elseif(!empty($achievement->reward_types))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($achievement->reward_types as $rew)
                                        <span class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-0.5 rounded border border-emerald-200">🎁 {{ ucwords(str_replace('_', ' ', $rew)) }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 italic">— Tidak ada berkas (Opsional)</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── BUKTI FISIK DI BAWAHNYA (SESUAI REQUEST USER) ──────────── --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-5 space-y-4">
        <h4 class="font-extrabold text-gray-900 text-sm flex items-center gap-2 border-b border-gray-100 pb-3">
            <span>📎</span> Lampiran Bukti Fisik & Dokumentasi (Tagihan #11, #12, #13)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Bukti 1: Foto Kegiatan -->
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700">Tagihan #13: Foto Kegiatan</span>
                    @if($photoUrl)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Terunggah</span>
                    @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-gray-200 text-gray-500">Kosong</span>
                    @endif
                </div>

                @if($photoUrl)
                    <a href="{{ $photoUrl }}" target="_blank" class="block rounded-xl overflow-hidden border border-gray-200 group">
                        <img src="{{ $photoUrl }}" alt="Foto Kegiatan" class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-200">
                    </a>
                    <a href="{{ $photoUrl }}" target="_blank" class="block text-center text-xs font-bold text-blue-600 hover:underline pt-1">
                        Buka Foto Ukuran Penuh ↗
                    </a>
                @else
                    <div class="h-32 flex items-center justify-center rounded-xl bg-gray-100 border border-dashed border-gray-300 text-gray-400 text-xs italic">
                        Belum ada foto kegiatan diunggah
                    </div>
                @endif
            </div>

            <!-- Bukti 2: Scan Piagam Utama -->
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700">Tagihan #11: Scan Piagam (Wajib)</span>
                    @if($certUrl)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Terunggah</span>
                    @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-700">Belum Diupload</span>
                    @endif
                </div>

                @if($certUrl)
                    <div class="h-44 flex flex-col items-center justify-center bg-white rounded-xl border border-gray-200 p-4 text-center space-y-2">
                        <span class="text-4xl">📄</span>
                        <p class="text-xs font-bold text-gray-800">Berkas Scan Piagam / Sertifikat</p>
                        <a href="{{ $certUrl }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs transition shadow-sm inline-flex items-center gap-1.5">
                            Buka / Unduh File Sertifikat ↗
                        </a>
                    </div>
                @else
                    <div class="h-44 flex flex-col items-center justify-center rounded-xl bg-rose-50 border border-dashed border-rose-200 text-rose-600 p-4 text-center space-y-1">
                        <span class="text-3xl">⚠️</span>
                        <p class="text-xs font-extrabold">Berkas Piagam Belum Diunggah</p>
                        <p class="text-[11px] text-rose-500">Unggah berkas sertifikat agar ajuan dapat disahkan oleh verifikator.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>



    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('siswa.achievements.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-gray-500 hover:text-gray-800 transition">
            ← Kembali ke Daftar Prestasi
        </a>
    </div>

</div>
@endsection
