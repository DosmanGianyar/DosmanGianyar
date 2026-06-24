@extends('layouts.siswa')
@section('title', $achievement->title)
@section('page-title', 'Detail Prestasi')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    {{-- Status Banner --}}
    @php
        $bannerClass = match($achievement->status) {
            'approved' => 'bg-green-50 border-green-200 text-green-800',
            'rejected' => 'bg-red-50 border-red-200 text-red-800',
            default    => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        };
        $iconPath = match($achievement->status) {
            'approved' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'rejected' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            default    => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    @endphp
    <div class="border rounded-2xl p-4 {{ $bannerClass }} flex items-center gap-3">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
        </svg>
        <div>
            <p class="font-bold text-sm">{{ $achievement->statusLabel() }}</p>
            @if($achievement->verified_at)
                <p class="text-xs mt-0.5 opacity-75">
                    oleh {{ $achievement->verifier?->name ?? 'Admin' }} ·
                    {{ $achievement->verified_at->translatedFormat('d M Y H:i') }}
                </p>
            @endif
        </div>
    </div>

    {{-- Rejection Reason --}}
    @if($achievement->status === 'rejected' && $achievement->rejection_reason)
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
        <p class="text-xs font-semibold text-red-700 mb-1">Alasan Penolakan:</p>
        <p class="text-sm text-red-800">{{ $achievement->rejection_reason }}</p>
    </div>
    @endif

    {{-- Info Cards --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
        <div class="p-4">
            <p class="text-xs text-gray-400 mb-0.5">Judul Prestasi</p>
            <p class="font-semibold text-gray-800">{{ $achievement->title }}</p>
        </div>
        <div class="grid grid-cols-2 divide-x divide-gray-100">
            <div class="p-4">
                <p class="text-xs text-gray-400 mb-1">Tingkat</p>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $achievement->levelBadgeClass() }}">
                    {{ $achievement->levelLabel() }}
                </span>
            </div>
            <div class="p-4">
                <p class="text-xs text-gray-400 mb-0.5">Peringkat</p>
                <p class="text-sm font-semibold text-gray-700">{{ $achievement->rank ?: '—' }}</p>
            </div>
        </div>
        <div class="grid grid-cols-2 divide-x divide-gray-100">
            <div class="p-4">
                <p class="text-xs text-gray-400 mb-0.5">Kategori</p>
                <p class="text-sm text-gray-700">{{ $achievement->category?->name ?? '—' }}</p>
            </div>
            <div class="p-4">
                <p class="text-xs text-gray-400 mb-0.5">Tanggal</p>
                <p class="text-sm text-gray-700">{{ $achievement->achievement_date->translatedFormat('d M Y') }}</p>
            </div>
        </div>
        @if($achievement->description)
        <div class="p-4">
            <p class="text-xs text-gray-400 mb-1">Deskripsi</p>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $achievement->description }}</p>
        </div>
        @endif
        <div class="p-4">
            <p class="text-xs text-gray-400 mb-0.5">Siswa</p>
            <p class="text-sm font-medium text-gray-700">
                {{ $achievement->student->name }}
                @if($achievement->student->schoolClass)
                    · <span class="text-gray-500">{{ $achievement->student->schoolClass->name }}</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Foto Kegiatan --}}
    @if($achievement->photoUrl())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 pt-4 pb-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Foto Kegiatan</p>
        </div>
        <img src="{{ $achievement->photoUrl() }}" alt="Foto Kegiatan"
            class="w-full max-h-80 object-cover">
    </div>
    @endif

    {{-- Scan Piagam --}}
    @if($achievement->certificateUrl())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 pt-4 pb-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Scan Piagam / Sertifikat</p>
        </div>
        <img src="{{ $achievement->certificateUrl() }}" alt="Piagam"
            class="w-full max-h-96 object-contain p-2">
        <div class="px-4 pb-4">
            <a href="{{ $achievement->certificateUrl() }}" target="_blank"
                class="block text-center text-sm text-blue-600 font-medium py-2 border border-blue-200 rounded-xl">
                Lihat Ukuran Penuh
            </a>
        </div>
    </div>
    @endif

    <a href="{{ route('siswa.achievements.index') }}"
        class="block text-center text-sm text-gray-500 py-2">← Kembali ke Daftar Prestasi</a>

</div>
@endsection
