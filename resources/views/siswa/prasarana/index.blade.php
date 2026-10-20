@extends('layouts.siswa')

@section('title', 'Prasarana')
@section('page-title', 'Prasarana')

@section('content')

{{-- ─── Header Sarana & Prasarana ────────────────────────────────────── --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 p-4 text-white shadow-sm mb-4">
    <div class="relative z-10 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/15 text-white backdrop-blur-xs">
                        {{ $siswa->schoolClass?->name ?? 'SMAN 1 Gianyar' }}
                    </span>
                    <span class="text-[11px] text-white/90">{{ now()->isoFormat('D MMMM Y') }}</span>
                </div>
                <h1 class="text-base font-bold text-white mt-0.5">Fasilitas, Sarana & Prasarana</h1>
            </div>
        </div>

        <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/10 text-slate-300 border border-white/10">
            SIMS Sarpras
        </span>
    </div>
</div>

{{-- ─── Metric Grid: Ringkasan Status ────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-4">
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-3.5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pinjam Aktif</span>
            <div class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-2">
            <p class="text-2xl font-black text-indigo-700 leading-none">{{ $stats['active_loans'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Aset digunakan</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-3.5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kembali</span>
            <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
        <div class="mt-2">
            <p class="text-2xl font-black text-emerald-600 leading-none">{{ $stats['returned_loans'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Selesai pinjam</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-3.5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Diproses</span>
            <div class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
        <div class="mt-2">
            <p class="text-2xl font-black text-amber-600 leading-none">{{ $stats['damage_pending'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Lapor kerusakan</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-3.5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Lapor</span>
            <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <div class="mt-2">
            <p class="text-2xl font-black text-blue-600 leading-none">{{ $stats['damage_total'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Semua riwayat</p>
        </div>
    </div>
</div>

{{-- ─── Aksi Cepat Sarpras & Perpustakaan (4 Tiles) ───────────────────── --}}
<div class="grid grid-cols-2 gap-2.5 mb-4">
    {{-- Scan Aset --}}
    <a href="{{ route('siswa.sarpras.scan') }}"
        class="group bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] rounded-2xl p-3.5 flex items-center gap-3 text-white shadow-xs transition-all">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8H3a2 2 0 00-2 2v6a2 2 0 002 2h2m14-8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2M9 4H7a2 2 0 00-2 2v4a2 2 0 002 2h2m0 0h4m0 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold leading-tight">Scan QR Aset</p>
            <p class="text-[11px] text-indigo-100 truncate mt-0.5">Pinjam via kamera</p>
        </div>
    </a>

    {{-- Riwayat Pinjaman --}}
    <a href="{{ route('siswa.sarpras.loans') }}"
        class="group bg-white hover:bg-slate-50 active:scale-[0.98] rounded-2xl shadow-xs border border-slate-200/80 p-3.5 flex items-center gap-3 transition-all">
        <div class="w-10 h-10 bg-slate-100 group-hover:bg-slate-200/70 text-slate-700 rounded-xl flex items-center justify-center shrink-0 transition-colors">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-slate-800 leading-tight">Riwayat Pinjam</p>
            <p class="text-[11px] text-slate-400 truncate mt-0.5">Daftar semua aset</p>
        </div>
    </a>

    {{-- Perpustakaan --}}
    <a href="{{ route('siswa.library.index') }}"
        class="group bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 active:scale-[0.98] text-white rounded-2xl shadow-xs p-3.5 flex items-center gap-3 transition-all">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
            <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold leading-tight">Perpustakaan</p>
            <p class="text-[11px] text-blue-100 truncate mt-0.5">Buku & Kartu Bebas</p>
        </div>
    </a>

    {{-- Katalog Aset --}}
    <a href="{{ route('siswa.sarpras.catalog') }}"
        class="group bg-white hover:bg-slate-50 active:scale-[0.98] rounded-2xl shadow-xs border border-slate-200/80 p-3.5 flex items-center gap-3 transition-all">
        <div class="w-10 h-10 bg-indigo-50 group-hover:bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center shrink-0 transition-colors">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-slate-800 leading-tight">Katalog Aset</p>
            <p class="text-[11px] text-slate-400 truncate mt-0.5">Daftar sarana sekolah</p>
        </div>
    </a>
</div>

{{-- ─── Section: Pinjaman Aktif ──────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div>
            <h2 class="text-xs font-bold text-slate-800">Pinjaman Aset Aktif</h2>
            <p class="text-[11px] text-slate-400">Aset yang saat ini sedang kamu pinjam</p>
        </div>
        @if($stats['active_loans'] > 0)
        <a href="{{ route('siswa.sarpras.loans') }}" class="text-[11px] text-indigo-600 hover:text-indigo-700 font-semibold inline-flex items-center gap-0.5">
            <span>Semua</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif
    </div>

    @if($activeLoans->isEmpty())
    <div class="py-10 text-center">
        <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <p class="text-xs text-slate-500 font-bold">Tidak Ada Pinjaman Aktif</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Gunakan tombol Scan QR untuk meminjam sarana sekolah</p>
        <a href="{{ route('siswa.sarpras.scan') }}"
            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-bold hover:bg-indigo-100 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8H3a2 2 0 00-2 2v6a2 2 0 002 2h2m14-8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2M9 4H7a2 2 0 00-2 2v4a2 2 0 002 2h2m0 0h4m0 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2"/>
            </svg>
            <span>Scan QR Sekarang</span>
        </a>
    </div>
    @else
    <div class="divide-y divide-slate-100">
        @foreach($activeLoans as $loan)
        @php
            $statusBadge = match($loan->status) {
                'pending'  => 'bg-amber-50 text-amber-700 border-amber-200/80',
                'approved' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                'active'   => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                default    => 'bg-slate-50 text-slate-600 border-slate-200/80',
            };
        @endphp
        <div class="px-4 py-3 flex items-center gap-3 hover:bg-slate-50/50 transition-colors">
            <div class="w-9 h-9 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-slate-800 truncate">{{ $loan->asset->name }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    {{ $loan->start_date->format('d M') }} – {{ $loan->end_date->format('d M Y') }}
                </p>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border shrink-0 {{ $statusBadge }}">
                {{ $loan->statusLabel() }}
            </span>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ─── Section: Laporan Kerusakan Saya ──────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden mb-6">
    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
        <h2 class="text-xs font-bold text-slate-800">Laporan Kerusakan Fasilitas</h2>
        <p class="text-[11px] text-slate-400">Riwayat laporan kerusakan sarana yang kamu ajukan</p>
    </div>

    @if($recentDamage->isEmpty())
    <div class="py-10 text-center">
        <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-xs text-slate-400 font-medium">Belum ada laporan kerusakan yang diajukan</p>
    </div>
    @else
    <div class="divide-y divide-slate-100">
        @foreach($recentDamage as $report)
        @php
            $damageBadge = match($report->status) {
                'pending'     => 'bg-amber-50 text-amber-700 border-amber-200/80',
                'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                'resolved'    => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                default       => 'bg-slate-50 text-slate-600 border-slate-200/80',
            };
        @endphp
        <div class="px-4 py-3 flex items-center gap-3 hover:bg-slate-50/50 transition-colors">
            @if($report->photo)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($report->photo) }}"
                alt="Foto Kerusakan"
                class="w-10 h-10 rounded-xl object-cover shrink-0 border border-slate-200/80">
            @else
            <div class="w-10 h-10 bg-amber-50 border border-amber-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-slate-800 truncate">{{ $report->asset->name }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $report->created_at->isoFormat('D MMMM Y') }}</p>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border shrink-0 {{ $damageBadge }}">
                {{ $report->statusLabel() }}
            </span>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection
