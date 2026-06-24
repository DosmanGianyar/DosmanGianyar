@extends('layouts.siswa')

@section('title', 'Prasarana')
@section('page-title', 'Prasarana')

@section('content')

{{-- ─── Header ───────────────────────────────────────────────────────── --}}
<div class="bg-linear-to-br from-violet-500 to-purple-600 rounded-2xl p-4 mb-4 text-white">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <p class="text-violet-100 text-xs">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h2 class="text-lg font-bold leading-tight">Sarana & Prasarana</h2>
            <p class="text-violet-100 text-xs mt-0.5">{{ $siswa->schoolClass?->name ?? 'SMA Negeri 1 Gianyar' }}</p>
        </div>
    </div>
</div>

{{-- ─── Stats ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
        <p class="text-2xl font-bold text-violet-700">{{ $stats['active_loans'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Pinjaman Aktif</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
        <p class="text-2xl font-bold text-gray-600">{{ $stats['returned_loans'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Sudah Dikembalikan</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
        <p class="text-2xl font-bold text-orange-600">{{ $stats['damage_pending'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Laporan Diproses</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $stats['damage_total'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Total Laporan</p>
    </div>
</div>

{{-- ─── Aksi Cepat ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-3 mb-4">
    <a href="{{ route('siswa.sarpras.scan') }}"
        class="bg-violet-600 rounded-2xl p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8H3a2 2 0 00-2 2v6a2 2 0 002 2h2m14-8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2M9 4H7a2 2 0 00-2 2v4a2 2 0 002 2h2m0 0h4m0 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2"/>
            </svg>
        </div>
        <div>
            <p class="text-white text-xs font-bold">Scan Aset</p>
            <p class="text-violet-200 text-[11px]">Scan QR untuk pinjam</p>
        </div>
    </a>
    <a href="{{ route('siswa.sarpras.loans') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <p class="text-gray-700 text-xs font-bold">Semua Pinjaman</p>
            <p class="text-gray-400 text-[11px]">Riwayat lengkap</p>
        </div>
    </a>
    <a href="{{ route('siswa.sarpras.catalog') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div>
            <p class="text-gray-700 text-xs font-bold">Katalog Aset</p>
            <p class="text-gray-400 text-[11px]">Browse tanpa scan QR</p>
        </div>
    </a>
</div>

{{-- ─── Pinjaman Aktif ──────────────────────────────────────────────── --}}
<div class="mb-4">
    <div class="flex items-center justify-between mb-2 px-1">
        <p class="text-sm font-bold text-gray-700">Pinjaman Aktif</p>
        @if($stats['active_loans'] > 5)
        <a href="{{ route('siswa.sarpras.loans') }}" class="text-xs text-violet-500 font-semibold">Lihat semua</a>
        @endif
    </div>

    @if($activeLoans->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-8 text-center">
        <p class="text-sm text-gray-400">Tidak ada pinjaman aktif</p>
        <a href="{{ route('siswa.sarpras.scan') }}"
            class="mt-3 inline-block text-xs text-violet-600 font-semibold">
            Scan aset untuk meminjam →
        </a>
    </div>
    @else
    <div class="space-y-2">
        @foreach($activeLoans as $loan)
        @php
            $statusColors = [
                'pending'  => 'bg-yellow-100 text-yellow-700',
                'approved' => 'bg-blue-100 text-blue-700',
                'active'   => 'bg-green-100 text-green-700',
            ];
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $loan->asset->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $loan->start_date->format('d M') }} – {{ $loan->end_date->format('d M Y') }}
                </p>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0
                {{ $statusColors[$loan->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $loan->statusLabel() }}
            </span>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ─── Laporan Kerusakan Saya ──────────────────────────────────────── --}}
<div>
    <div class="flex items-center justify-between mb-2 px-1">
        <p class="text-sm font-bold text-gray-700">Laporan Kerusakan Saya</p>
    </div>

    @if($recentDamage->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-8 text-center">
        <p class="text-sm text-gray-400">Belum ada laporan kerusakan</p>
    </div>
    @else
    <div class="space-y-2">
        @foreach($recentDamage as $report)
        @php
            $damageColors = [
                'pending'     => 'bg-yellow-100 text-yellow-700',
                'in_progress' => 'bg-blue-100 text-blue-700',
                'resolved'    => 'bg-green-100 text-green-700',
            ];
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex items-center gap-3">
            @if($report->photo)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($report->photo) }}"
                class="w-10 h-10 rounded-xl object-cover shrink-0">
            @else
            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $report->asset->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $report->created_at->isoFormat('D MMM Y') }}</p>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0
                {{ $damageColors[$report->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $report->statusLabel() }}
            </span>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection
