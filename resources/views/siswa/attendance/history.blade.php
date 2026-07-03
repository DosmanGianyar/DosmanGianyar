@extends('layouts.siswa')
@section('title', 'Riwayat Presensi')
@section('page-title', 'Riwayat Presensi')

@section('content')

{{-- ─── Header & Navigasi Bulan ─────────────────────────────────────── --}}
<div class="bg-linear-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 mb-4 text-white">
    <div class="flex items-center justify-between mb-3">
        <a href="{{ route('siswa.attendance.history', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
            class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-colors">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <p class="text-lg font-bold">{{ $start->isoFormat('MMMM Y') }}</p>
            <p class="text-blue-200 text-xs mt-0.5">{{ $siswa->name }}</p>
        </div>
        @if($canNext)
        <a href="{{ route('siswa.attendance.history', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
            class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-colors">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @else
        <div class="w-8 h-8"></div>
        @endif
    </div>

    {{-- Ringkasan status bulan ini --}}
    <div class="grid grid-cols-3 gap-2">
        <div class="bg-white/15 rounded-xl py-2.5 text-center">
            <p class="text-green-300 font-bold text-xl leading-none">{{ $summary['hadir'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1 font-medium">Hadir</p>
        </div>
        <div class="bg-white/15 rounded-xl py-2.5 text-center">
            <p class="text-yellow-300 font-bold text-xl leading-none">{{ $summary['terlambat'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1 font-medium">Terlambat</p>
        </div>
        <div class="bg-white/15 rounded-xl py-2.5 text-center">
            <p class="text-red-300 font-bold text-xl leading-none">{{ $summary['alpa'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1 font-medium">Alpa</p>
        </div>
        <div class="bg-white/15 rounded-xl py-2.5 text-center">
            <p class="text-sky-300 font-bold text-xl leading-none">{{ $summary['izin'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1 font-medium">Izin</p>
        </div>
        <div class="bg-white/15 rounded-xl py-2.5 text-center">
            <p class="text-purple-300 font-bold text-xl leading-none">{{ $summary['sakit'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1 font-medium">Sakit</p>
        </div>
        <div class="bg-white/15 rounded-xl py-2.5 text-center">
            <p class="text-orange-300 font-bold text-xl leading-none">{{ $summary['dispensasi'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1 font-medium">Dispensasi</p>
        </div>
    </div>
</div>

{{-- ─── Tren Kehadiran 6 Bulan ──────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
    <p class="text-sm font-semibold text-gray-700 mb-3">Tren Kehadiran (6 Bulan Terakhir)</p>
    @php $maxCount = max($trend->pluck('count')->max(), 1); @endphp
    <div class="flex items-end gap-2 h-20">
        @foreach($trend as $t)
        @php $h = max(4, round(($t['count'] / $maxCount) * 64)); @endphp
        <div class="flex-1 flex flex-col items-center gap-1">
            <span class="text-[10px] font-bold text-blue-600">{{ $t['count'] }}</span>
            <div class="w-full bg-blue-500 rounded-t-md transition-all" style="height: {{ $h }}px"></div>
            <span class="text-[9px] text-gray-400 font-medium truncate w-full text-center">{{ $t['label'] }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ─── Daftar Presensi ─────────────────────────────────────────────── --}}
<div class="space-y-2">
    <p class="text-sm font-semibold text-gray-700 px-1">Detail Presensi</p>

    @forelse($records as $rec)
    @php
        $effStatus = $effectiveStatuses[$rec->date->format('Y-m-d')] ?? $rec->status;
        $color = match($effStatus) {
            'hadir'      => ['dot' => 'bg-green-500', 'badge' => 'bg-green-100 text-green-700'],
            'terlambat'  => ['dot' => 'bg-yellow-500', 'badge' => 'bg-yellow-100 text-yellow-700'],
            'izin'       => ['dot' => 'bg-blue-500',  'badge' => 'bg-blue-100 text-blue-700'],
            'sakit'      => ['dot' => 'bg-purple-500','badge' => 'bg-purple-100 text-purple-700'],
            'dispensasi' => ['dot' => 'bg-indigo-500','badge' => 'bg-indigo-100 text-indigo-700'],
            default      => ['dot' => 'bg-red-500',   'badge' => 'bg-red-100 text-red-700'],
        };
        $effLabel = match($effStatus) {
            'hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin',
            'sakit' => 'Sakit', 'dispensasi' => 'Dispensasi', default => 'Alpa',
        };
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex items-center gap-3">
        <div class="w-2 h-2 rounded-full shrink-0 {{ $color['dot'] }}"></div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-semibold text-gray-800">
                    {{ $rec->date->isoFormat('dddd, D MMM Y') }}
                </p>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full shrink-0 {{ $color['badge'] }}">
                    {{ $effLabel }}
                </span>
            </div>
            <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                @if($rec->check_in_time)
                <span>Masuk {{ \Carbon\Carbon::parse($rec->check_in_time)->format('H:i') }}</span>
                @endif
                @if($rec->check_out_time)
                <span>· Pulang {{ \Carbon\Carbon::parse($rec->check_out_time)->format('H:i') }}</span>
                @endif
                @if($rec->is_fake_gps)
                <span class="text-red-500 font-semibold">· Fake GPS</span>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-sm text-gray-400">Tidak ada data presensi bulan ini</p>
    </div>
    @endforelse
</div>

@endsection
