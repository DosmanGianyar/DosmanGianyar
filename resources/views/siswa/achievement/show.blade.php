@extends('layouts.siswa')
@section('title', $achievement->title)
@section('page-title', 'Detail Prestasi')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Status Banner --}}
    @php
        $bannerClass = match($achievement->status) {
            'approved' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'rejected' => 'bg-rose-50 border-rose-200 text-rose-800',
            default    => 'bg-amber-50 border-amber-200 text-amber-800',
        };
    @endphp

    <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold px-3 py-1 rounded-full {{ $achievement->is_curation ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-700' }}">
                {{ $achievement->is_curation ? '🎖️ Kurasi Kemendikdasmen' : '🏆 Prestasi Internal Sekolah' }}
            </span>
            <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $achievement->statusBadgeClass() }}">
                Status: {{ $achievement->statusLabel() }}
            </span>
        </div>

        <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ $achievement->title }}</h3>

        @if($achievement->verified_at)
            <p class="text-xs text-gray-500">
                Diverifikasi oleh <strong>{{ $achievement->verifier?->name ?? 'Pengelola' }}</strong> pada {{ $achievement->verified_at->translatedFormat('d M Y H:i') }}
            </p>
        @endif

        @if($achievement->status === 'rejected' && $achievement->rejection_reason)
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-3 text-xs text-rose-800 space-y-1">
                <p class="font-bold text-rose-900">Catatan Penolakan / Revisi:</p>
                <p>{{ $achievement->rejection_reason }}</p>
            </div>
        @endif
    </div>

    {{-- Info Rincian Prestasi --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 space-y-4">
        <h4 class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-2">Informasi Umum</h4>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
            <div>
                <span class="text-gray-400 block mb-0.5">Tingkat Ajang</span>
                <span class="font-semibold px-2 py-0.5 rounded-md text-[11px] {{ $achievement->levelBadgeClass() }}">
                    {{ $achievement->levelLabel() }}
                </span>
            </div>
            <div>
                <span class="text-gray-400 block mb-0.5">Peringkat</span>
                <span class="font-bold text-gray-800">{{ $achievement->rank ?: '—' }}</span>
            </div>
            <div>
                <span class="text-gray-400 block mb-0.5">Rumpun Talenta</span>
                <span class="font-medium text-gray-800">{{ $achievement->fieldCategoryLabel() }}</span>
            </div>
            <div>
                <span class="text-gray-400 block mb-0.5">Penyelenggara</span>
                <span class="font-medium text-gray-800">{{ $achievement->organizer ?: '—' }}</span>
            </div>
            <div>
                <span class="text-gray-400 block mb-0.5">Kategori SIMS</span>
                <span class="font-medium text-gray-800">{{ $achievement->category?->name ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-400 block mb-0.5">Tanggal Prestasi</span>
                <span class="font-medium text-gray-800">{{ $achievement->achievement_date->translatedFormat('d M Y') }}</span>
            </div>
        </div>

        @if($achievement->description)
            <div class="pt-2 border-t border-gray-100 text-xs">
                <span class="text-gray-400 block mb-1">Deskripsi</span>
                <p class="text-gray-700 leading-relaxed">{{ $achievement->description }}</p>
            </div>
        @endif
    </div>

    {{-- RINCIAN 5 POIN KURASI (JIKA ADA) --}}
    @if($achievement->is_curation)
        <div class="bg-indigo-50/60 border border-indigo-100 rounded-3xl p-5 space-y-4">
            <h4 class="font-bold text-indigo-950 text-sm flex items-center gap-2 border-b border-indigo-100 pb-2">
                <span>🎖️</span> Rincian Berkas 5 Poin Kurasi Kemendikdasmen
            </h4>

            <div class="space-y-3 text-xs">
                {{-- Poin 1 --}}
                <div class="bg-white p-3.5 rounded-2xl border border-indigo-100/80 space-y-1.5">
                    <span class="font-bold text-indigo-900 block">P1. Dokumen Standar Penyelenggaraan:</span>
                    @if(!empty($achievement->doc_standard_checklist))
                        <div class="flex flex-wrap gap-1">
                            @foreach($achievement->doc_standard_checklist as $chk)
                                <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[10px] font-medium border border-indigo-100">
                                    ✓ {{ ucwords(str_replace('_', ' ', $chk)) }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    @if($achievement->doc_standard_file)
                        <div class="pt-1">
                            <a href="{{ asset('storage/' . $achievement->doc_standard_file) }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 font-semibold hover:underline">
                                📎 Lihat Dokumen Juknis/Pedoman (P1)
                            </a>
                        </div>
                    @endif
                    @if($achievement->doc_standard_url)
                        <p class="text-gray-500">Tautan: <a href="{{ $achievement->doc_standard_url }}" target="_blank" class="text-blue-600 underline">{{ $achievement->doc_standard_url }}</a></p>
                    @endif
                </div>

                {{-- Poin 2 --}}
                <div class="bg-white p-3.5 rounded-2xl border border-indigo-100/80 space-y-1.5">
                    <span class="font-bold text-indigo-900 block">P2. Tingkatan Seleksi Ajang:</span>
                    <p class="text-gray-700 font-medium">Opsi Selected: <span class="uppercase font-bold text-indigo-800">{{ str_replace('_', ' ', $achievement->selection_level ?? '—') }}</span></p>
                    @if($achievement->selection_level_file)
                        <div class="pt-1">
                            <a href="{{ asset('storage/' . $achievement->selection_level_file) }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 font-semibold hover:underline">
                                📎 Lihat Berkas Bukti Seleksi (P2)
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Poin 3 --}}
                <div class="bg-white p-3.5 rounded-2xl border border-indigo-100/80 space-y-1.5">
                    <span class="font-bold text-indigo-900 block">P3. Konsistensi Frekuensi Penyelenggaraan:</span>
                    <p class="text-gray-700 font-medium">Kekerapatan: <span class="uppercase font-bold text-indigo-800">{{ str_replace('_', ' ', $achievement->frequency_consistency ?? '—') }}</span></p>
                    @if($achievement->frequency_consistency_file)
                        <div class="pt-1">
                            <a href="{{ asset('storage/' . $achievement->frequency_consistency_file) }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 font-semibold hover:underline">
                                📎 Lihat Berkas Juknis Lintas Tahun (P3)
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Poin 4 --}}
                <div class="bg-white p-3.5 rounded-2xl border border-indigo-100/80 space-y-1.5">
                    <span class="font-bold text-indigo-900 block">P4. Sarana dan Prasarana Ajang:</span>
                    <p class="text-gray-700 font-medium">Status Sarpras: <span class="uppercase font-bold text-indigo-800">{{ str_replace('_', ' ', $achievement->infrastructure_type ?? '—') }}</span></p>
                    @if($achievement->infrastructure_file)
                        <div class="pt-1">
                            <a href="{{ asset('storage/' . $achievement->infrastructure_file) }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 font-semibold hover:underline">
                                📎 Lihat Dokumentasi Sarpras/Foto Venue (P4)
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Poin 5 --}}
                <div class="bg-white p-3.5 rounded-2xl border border-indigo-100/80 space-y-1.5">
                    <span class="font-bold text-indigo-900 block">P5. Penghargaan dan Apresiasi:</span>
                    @if(!empty($achievement->reward_types))
                        <div class="flex flex-wrap gap-1 mb-1">
                            @foreach($achievement->reward_types as $rew)
                                <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded text-[10px] font-medium border border-emerald-100">
                                    🎁 {{ ucwords(str_replace('_', ' ', $rew)) }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    <div class="flex flex-wrap gap-3 pt-1">
                        @if($achievement->reward_certificate_file)
                            <a href="{{ asset('storage/' . $achievement->reward_certificate_file) }}" target="_blank" class="text-indigo-600 font-semibold hover:underline">📎 Scan Piagam</a>
                        @endif
                        @if($achievement->reward_photo_file)
                            <a href="{{ asset('storage/' . $achievement->reward_photo_file) }}" target="_blank" class="text-indigo-600 font-semibold hover:underline">📷 Foto Penyerahan</a>
                        @endif
                        @if($achievement->reward_recap_file)
                            <a href="{{ asset('storage/' . $achievement->reward_recap_file) }}" target="_blank" class="text-indigo-600 font-semibold hover:underline">📄 SK Rekap Pemenang</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Lampiran Utama Foto & Sertifikat --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($achievement->photoUrl())
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-4 space-y-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Foto Kegiatan</span>
                <img src="{{ $achievement->photoUrl() }}" alt="Foto Kegiatan" class="w-full h-48 object-cover rounded-2xl">
            </div>
        @endif

        @if($achievement->certificateUrl())
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-4 space-y-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Scan Piagam Utama</span>
                <div class="h-48 flex items-center justify-center bg-gray-50 rounded-2xl overflow-hidden border border-gray-100">
                    <img src="{{ $achievement->certificateUrl() }}" alt="Piagam" class="max-h-full max-w-full object-contain">
                </div>
                <a href="{{ $achievement->certificateUrl() }}" target="_blank" class="block text-center text-xs text-blue-600 font-semibold hover:underline pt-1">
                    Buka Ukuran Penuh
                </a>
            </div>
        @endif
    </div>

    <a href="{{ route('siswa.achievements.index') }}" class="block text-center text-xs text-gray-500 py-3 hover:underline">
        ← Kembali ke Daftar Prestasi
    </a>

</div>
@endsection
