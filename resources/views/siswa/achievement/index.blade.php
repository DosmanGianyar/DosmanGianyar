@extends('layouts.siswa')
@section('title', 'Prestasi Saya')
@section('page-title', 'Prestasi Saya')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-yellow-50 rounded-2xl p-3 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
            <p class="text-xs text-yellow-700 mt-0.5">Menunggu</p>
        </div>
        <div class="bg-green-50 rounded-2xl p-3 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
            <p class="text-xs text-green-700 mt-0.5">Disetujui</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-3 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
            <p class="text-xs text-red-700 mt-0.5">Ditolak</p>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="flex gap-2">
        <a href="{{ route('siswa.achievements.create') }}"
            class="flex-1 bg-blue-600 text-white text-sm font-semibold py-3 rounded-xl text-center">
            + Laporkan Prestasi
        </a>
        <a href="{{ route('siswa.achievements.report') }}"
            class="px-4 bg-gray-100 text-gray-700 text-sm font-semibold py-3 rounded-xl text-center">
            Laporan Sekolah
        </a>
    </div>

    {{-- List --}}
    @forelse($achievements as $achievement)
    <a href="{{ route('siswa.achievements.show', $achievement) }}"
        class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:border-blue-200 transition-colors">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ $achievement->title }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $achievement->category?->name ?? '—' }} ·
                    {{ $achievement->achievement_date->translatedFormat('d M Y') }}
                </p>
                @if($achievement->rank)
                    <p class="text-xs text-blue-600 font-medium mt-1">{{ $achievement->rank }}</p>
                @endif
            </div>
            <div class="flex flex-col items-end gap-1.5 shrink-0">
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $achievement->statusBadgeClass() }}">
                    {{ $achievement->statusLabel() }}
                </span>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $achievement->levelBadgeClass() }}">
                    {{ $achievement->levelLabel() }}
                </span>
            </div>
        </div>
    </a>
    @empty
    <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
        </div>
        <p class="text-gray-700 font-semibold">Belum ada prestasi dilaporkan</p>
        <p class="text-gray-400 text-xs mt-1">Raih prestasi dan laporkan di sini</p>
        <a href="{{ route('siswa.achievements.create') }}"
            class="inline-block mt-4 px-5 py-2 bg-blue-600 text-white text-sm rounded-xl font-medium">
            Laporkan Sekarang
        </a>
    </div>
    @endforelse

</div>
@endsection
