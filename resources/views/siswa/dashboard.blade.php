@extends('layouts.siswa')

@section('title', 'Beranda')
@section('page-title', 'Beranda')

@section('content')

{{-- ─── 1. Card Notifikasi (Compact & Clean Inline Banner) ────────────── --}}
@if($unreadNotifications > 0)
<a href="{{ route('siswa.notifications.index') }}"
    class="flex items-center justify-between gap-3 bg-gradient-to-r from-blue-50/90 via-indigo-50/80 to-blue-50/90 border border-blue-200/80 rounded-2xl px-4 py-3 mb-4 shadow-xs hover:border-blue-400 hover:shadow-sm transition-all group">
    <div class="flex items-center gap-3 min-w-0">
        <div class="w-9 h-9 bg-blue-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5 truncate">
                <span>{{ $unreadNotifications }} notifikasi belum dibaca</span>
                <span class="inline-block w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
            </p>
            <p class="text-[11px] text-slate-500 truncate">Ketuk untuk membaca pemberitahuan terbaru sekolah</p>
        </div>
    </div>
    <div class="shrink-0 flex items-center gap-1 text-xs font-semibold text-blue-600">
        <span class="hidden sm:inline">Lihat</span>
        <svg class="w-4 h-4 text-blue-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
</a>
@endif

{{-- ─── 2. Sapaan & Hero Header (Deep Navy Modern Glassmorphism) ───────── --}}
<div class="relative rounded-3xl p-5 mb-4 text-white shadow-lg shadow-blue-950/15 overflow-hidden"
     style="background: linear-gradient(135deg, #0b1e4f 0%, #123075 50%, #19439e 100%); border: 1px solid rgba(255,255,255,0.15);">
    
    {{-- Aksen Dekoratif Halus --}}
    <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-blue-400/10 blur-2xl pointer-events-none"></div>
    <div class="absolute -left-10 -top-10 w-36 h-36 rounded-full bg-indigo-400/10 blur-2xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col gap-4">
        {{-- Tanggal & Badge --}}
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-medium text-white/90 border border-white/20 shadow-2xs">
                <span>📅</span>
                <span>{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
            </span>
            <span class="text-[10px] font-extrabold tracking-wider uppercase px-2.5 py-1 rounded-full bg-amber-400/20 text-amber-300 border border-amber-300/30">
                Bulan Ini
            </span>
        </div>

        {{-- Sapaan & Info Siswa --}}
        <div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-tight text-white drop-shadow-xs">
                Selamat {{ match(true) { now()->hour < 11 => 'Pagi', now()->hour < 15 => 'Siang', now()->hour < 18 => 'Sore', default => 'Malam' } }}, {{ $siswa->name }}! 👋
            </h2>
            <div class="flex items-center gap-2 flex-wrap mt-1.5 text-xs text-blue-100/90 font-medium">
                <span class="font-semibold text-white">{{ $siswa->schoolClass?->name ?? 'Siswa SMAN 1 Gianyar' }}</span>
                <span class="text-white/40">·</span>
                @if($siswa->angkatan)
                    <span class="font-extrabold text-[11px] text-amber-300 bg-amber-400/20 px-2 py-0.5 rounded-md border border-amber-300/30">
                        {{ $siswa->angkatan }}
                    </span>
                    <span class="text-white/40">·</span>
                @endif
                <span class="font-mono text-white/80">NIS {{ $siswa->nis ?? '—' }}</span>
            </div>
        </div>

        {{-- Clean Metric Grid: Rekap Kehadiran Bulan Ini --}}
        <div class="pt-1 border-t border-white/10">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[11px] font-bold tracking-wider uppercase text-blue-200/80">
                    Rekap Kehadiran Bulan {{ now()->isoFormat('MMMM') }}
                </p>
                <a href="#kalender-kehadiran" class="text-[10px] text-blue-200 hover:text-white transition-colors underline decoration-blue-300/40">
                    Lihat Kalender ↓
                </a>
            </div>
            <div class="grid grid-cols-5 gap-1.5 sm:gap-2 text-center">
                {{-- Terlambat --}}
                <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md border border-amber-300/30 rounded-2xl py-2 px-1 transition-all">
                    <span class="block text-[10px] font-extrabold text-amber-300 uppercase tracking-wider">Telat</span>
                    <span class="text-base sm:text-lg font-black text-amber-200 mt-0.5 block">{{ $monthlySummary['terlambat'] }}</span>
                </div>
                {{-- Alpa --}}
                <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md border border-rose-300/30 rounded-2xl py-2 px-1 transition-all">
                    <span class="block text-[10px] font-extrabold text-rose-300 uppercase tracking-wider">Alpa</span>
                    <span class="text-base sm:text-lg font-black text-rose-200 mt-0.5 block">{{ $monthlySummary['alpa'] }}</span>
                </div>
                {{-- Izin --}}
                <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md border border-sky-300/30 rounded-2xl py-2 px-1 transition-all">
                    <span class="block text-[10px] font-extrabold text-sky-300 uppercase tracking-wider">Izin</span>
                    <span class="text-base sm:text-lg font-black text-sky-200 mt-0.5 block">{{ $monthlySummary['izin'] }}</span>
                </div>
                {{-- Sakit --}}
                <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md border border-purple-300/30 rounded-2xl py-2 px-1 transition-all">
                    <span class="block text-[10px] font-extrabold text-purple-300 uppercase tracking-wider">Sakit</span>
                    <span class="text-base sm:text-lg font-black text-purple-200 mt-0.5 block">{{ $monthlySummary['sakit'] }}</span>
                </div>
                {{-- Dispensasi --}}
                <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md border border-orange-300/30 rounded-2xl py-2 px-1 transition-all">
                    <span class="block text-[10px] font-extrabold text-orange-300 uppercase tracking-wider">Disp.</span>
                    <span class="text-base sm:text-lg font-black text-orange-200 mt-0.5 block">{{ $monthlySummary['dispensasi'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── 3. Kartu Pelajar Digital ───────────────────────────────────────── --}}
<div class="mb-4">
    @include('siswa.partials.student-card-digital')
</div>

{{-- ─── 4. Quick Actions Hub: Chat Konsultasi & Tata Tertib (2 Kolom Seimbang) ─── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
    {{-- Card Chat Konsultasi --}}
    <a href="{{ route('siswa.chat.index') }}"
       class="group relative bg-white rounded-2xl p-4 border border-emerald-100 hover:border-emerald-300 shadow-xs hover:shadow-md transition-all flex items-center justify-between gap-3 overflow-hidden">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                💬
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-emerald-700 transition-colors">Chat Konsultasi</h3>
                    <span class="text-[9px] font-extrabold px-2 py-0.2 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Wali & Ortu
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 truncate">
                    Obrolan siswa, wali kelas & ortu
                </p>
            </div>
        </div>
        <div class="w-7 h-7 rounded-full bg-slate-50 group-hover:bg-emerald-600 group-hover:text-white text-slate-400 flex items-center justify-center shrink-0 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>

    {{-- Card Tata Tertib Sekolah --}}
    <a href="{{ route('siswa.tata-tertib') }}"
       class="group relative bg-white rounded-2xl p-4 border border-blue-100 hover:border-blue-300 shadow-xs hover:shadow-md transition-all flex items-center justify-between gap-3 overflow-hidden">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-blue-700 transition-colors">Tata Tertib</h3>
                    <span class="text-[9px] font-extrabold px-2 py-0.2 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                        Resmi
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 truncate">
                    Aturan, hak, seragam & kedisiplinan
                </p>
            </div>
        </div>
        <div class="w-7 h-7 rounded-full bg-slate-50 group-hover:bg-blue-600 group-hover:text-white text-slate-400 flex items-center justify-center shrink-0 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>
</div>

{{-- ─── 5. Card SIPINTER (Pendidikan Karakter) ─────────────────────────── --}}
<div onclick="window.location='{{ route('siswa.conduct.index') }}'"
     class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 mb-4 cursor-pointer hover:border-emerald-300 hover:shadow-md transition-all group">
    <div class="flex items-center justify-between mb-3.5">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center text-xs">🛡️</span>
            <span>SIPINTER (Pendidikan Karakter)</span>
        </h3>
        <span class="text-xs font-semibold text-blue-600 group-hover:text-blue-700 flex items-center gap-0.5">
            Detail Catatan <span class="group-hover:translate-x-0.5 transition-transform">→</span>
        </span>
    </div>
    <div class="grid grid-cols-3 gap-2 sm:gap-3 text-center">
        <div class="bg-slate-50 border border-slate-100 rounded-xl py-2.5 px-1 group-hover:bg-blue-50/50 transition-colors">
            <p class="text-lg font-black text-slate-800">{{ $pointSummary['total'] }}</p>
            <p class="text-[11px] font-semibold text-slate-500 mt-0.5">Total Catatan</p>
        </div>
        <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl py-2.5 px-1">
            <p class="text-lg font-black text-emerald-700">+{{ $pointSummary['prestasi'] }}</p>
            <p class="text-[11px] font-semibold text-emerald-800 mt-0.5">Apresiasi Karakter</p>
        </div>
        <div class="bg-rose-50/70 border border-rose-100 rounded-xl py-2.5 px-1">
            <p class="text-lg font-black text-rose-700">{{ $pointSummary['pelanggaran'] }}</p>
            <p class="text-[11px] font-semibold text-rose-800 mt-0.5">Kedisiplinan</p>
        </div>
    </div>
</div>

{{-- ─── 6. Bukti Presensi Hari Ini (Masuk & Pulang) ─────────────────────── --}}
@php
    $statusIcon = match($todayStatus['status']) {
        'Hadir'         => ['bg' => 'bg-emerald-500 text-white', 'icon' => 'M5 13l4 4L19 7', 'card_bg' => 'bg-emerald-50/60 border-emerald-200/80'],
        'Terlambat'     => ['bg' => 'bg-amber-500 text-white',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'card_bg' => 'bg-amber-50/60 border-amber-200/80'],
        'Izin', 'Sakit', 'Dispensasi' => ['bg' => 'bg-sky-500 text-white', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'card_bg' => 'bg-sky-50/60 border-sky-200/80'],
        'Alpa'          => ['bg' => 'bg-rose-500 text-white',    'icon' => 'M6 18L18 6M6 6l12 12', 'card_bg' => 'bg-rose-50/60 border-rose-200/80'],
        default         => ['bg' => 'bg-slate-300 text-slate-600', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'card_bg' => 'bg-slate-50 border-slate-200'],
    };
    $checkinDone = $todayStatus['status'] !== 'Belum Presensi';
@endphp

<div class="space-y-2.5 mb-4">
    {{-- Card Presensi Masuk --}}
    <div class="{{ $statusIcon['card_bg'] }} rounded-2xl shadow-xs border p-3.5 flex items-center gap-3 transition-all">
        <div class="w-11 h-11 rounded-2xl {{ $statusIcon['bg'] }} flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon['icon'] }}"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <p class="{{ $checkinDone ? 'text-slate-900' : 'text-slate-600' }} font-bold text-sm">
                    {{ $todayStatus['status'] }}
                </p>
                @if($checkinDone)
                    <span class="text-[10px] font-extrabold px-2 py-0.2 rounded-full bg-white/80 text-slate-700 border border-slate-200 shadow-2xs">
                        Masuk
                    </span>
                @endif
            </div>
            <p class="{{ $checkinDone ? 'text-slate-600' : 'text-slate-400' }} text-xs mt-0.5">
                @if(! $checkinDone)
                    Batas presensi jam 08:00 WITA
                @else
                    Tercatat pukul {{ $todayStatus['time'] }} WITA
                @endif
            </p>
        </div>
        @if(! $checkinDone)
            <a href="{{ route('siswa.attendance.show') }}"
                class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-xs transition-colors">
                Presensi Sekarang
            </a>
        @elseif($todayStatus['photo'])
            <img src="{{ $todayStatus['photo'] }}"
                alt="Selfie Presensi"
                class="shrink-0 w-13 h-13 rounded-xl object-cover border border-white shadow-xs">
        @else
            <div class="shrink-0 w-12 h-12 rounded-xl border border-dashed border-slate-300 bg-white/60 flex flex-col items-center justify-center text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
                <span class="text-[8px] font-medium">Tanpa Foto</span>
            </div>
        @endif
    </div>

    {{-- Card Presensi Pulang --}}
    @if($todayStatus['checked_in'])
    @php $checkoutDone = (bool) $todayStatus['check_out_time']; @endphp
    <div class="{{ $checkoutDone ? 'bg-emerald-50/60 border-emerald-200/80' : 'bg-slate-50 border-slate-200' }} rounded-2xl shadow-xs border p-3.5 flex items-center gap-3 transition-all">
        <div class="w-11 h-11 rounded-2xl {{ $checkoutDone ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($checkoutDone)
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @endif
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <p class="{{ $checkoutDone ? 'text-slate-900' : 'text-slate-600' }} font-bold text-sm">
                    Absen Pulang
                </p>
                <span class="text-[10px] font-extrabold px-2 py-0.2 rounded-full {{ $checkoutDone ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-200 text-slate-700' }}">
                    {{ $checkoutDone ? 'Selesai' : 'Belum' }}
                </span>
            </div>
            <p class="{{ $checkoutDone ? 'text-slate-600' : 'text-slate-400' }} text-xs mt-0.5">
                @if($checkoutDone)
                    Tercatat pukul {{ $todayStatus['check_out_time'] }} WITA
                @else
                    Lakukan absen ketika jam kepulangan tiba
                @endif
            </p>
        </div>
        @if($checkoutDone && $todayStatus['check_out_photo'])
            <img src="{{ $todayStatus['check_out_photo'] }}"
                alt="Selfie Pulang"
                class="shrink-0 w-13 h-13 rounded-xl object-cover border border-white shadow-xs">
        @elseif($checkoutDone)
            <div class="shrink-0 w-12 h-12 rounded-xl border border-dashed border-slate-300 bg-white/60 flex flex-col items-center justify-center text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
                <span class="text-[8px] font-medium">Tanpa Foto</span>
            </div>
        @else
            <a href="{{ route('siswa.attendance.show') }}"
                class="shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-xs transition-colors whitespace-nowrap">
                Absen Pulang
            </a>
        @endif
    </div>
    @endif
</div>

{{-- ─── 7. Kalender Kehadiran Bulanan (Clean & Soft Grid) ───────────────── --}}
@php
    $calNow        = \Carbon\Carbon::now();
    $calFirst      = $calNow->copy()->startOfMonth();
    $calDays       = $calNow->daysInMonth;
    $startDow      = (int) $calFirst->dayOfWeek;            // 0=Sun,1=Mon…6=Sat
    $startOffset   = ($startDow + 6) % 7;                   // Monday-first offset
    $todayDate     = $calNow->toDateString();
    $hadir_set     = ['hadir', 'terlambat', 'izin', 'sakit', 'dispensasi'];
@endphp
<div id="kalender-kehadiran" class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 mb-4">
    <div class="flex items-center justify-between mb-3.5">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-xs">📆</span>
            <span>Kalender Kehadiran</span>
        </h3>
        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">
            {{ $calNow->isoFormat('MMMM Y') }}
        </span>
    </div>

    {{-- Hari Nama --}}
    <div class="grid grid-cols-7 gap-1 mb-1.5 text-center">
        @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dh)
            <div class="text-[11px] font-bold text-slate-400 py-1">{{ $dh }}</div>
        @endforeach
    </div>

    {{-- Grid Tanggal --}}
    <div class="grid grid-cols-7 gap-1 text-center">
        @for($i = 0; $i < $startOffset; $i++)
            <div class="h-8"></div>
        @endfor
        @for($day = 1; $day <= $calDays; $day++)
            @php
                $ds         = $calFirst->copy()->addDays($day - 1)->toDateString();
                $dow        = (int) \Carbon\Carbon::parse($ds)->dayOfWeek;   // 0=Sun,6=Sat
                $isWeekend  = ($dow === 0);                                 // Hanya Minggu libur rutin
                $isHoliday  = isset($monthlyHolidays[$ds]);
                $isSpecial  = isset($monthlySpecial[$ds]);
                $isGrayDay  = $isFuture = ($ds > $todayDate);
                $isGrayDay  = $isGrayDay || ($isWeekend && ! $isSpecial) || $isHoliday;
                $isToday    = $ds === $todayDate;
                $st         = $monthlyByDate[$ds] ?? null;

                if ($isToday) {
                    $cls = 'bg-blue-600 text-white font-black shadow-xs ring-2 ring-blue-300 ring-offset-1';
                } elseif ($isGrayDay) {
                    $cls = 'text-slate-300 font-normal';
                } elseif ($st && in_array($st, ['hadir', 'terlambat'])) {
                    $cls = 'bg-emerald-500 text-white font-bold';
                } elseif ($st && in_array($st, ['izin', 'sakit', 'dispensasi'])) {
                    $cls = 'bg-sky-500 text-white font-bold';
                } elseif ($st === 'alpa') {
                    $cls = 'bg-rose-500 text-white font-bold';
                } else {
                    $cls = 'text-slate-300 font-normal';
                }
            @endphp
            <div class="flex items-center justify-center py-0.5">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center {{ $cls }} text-xs transition-transform hover:scale-105">
                    {{ $day }}
                </div>
            </div>
        @endfor
    </div>

    {{-- Keterangan Warna (Legend) --}}
    <div class="flex items-center gap-3.5 mt-4 pt-3 border-t border-slate-100 flex-wrap justify-center sm:justify-start">
        <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 ring-1 ring-blue-300"></span>
            <span class="text-[11px] font-medium text-slate-500">Hari Ini</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <span class="text-[11px] font-medium text-slate-500">Hadir</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
            <span class="text-[11px] font-medium text-slate-500">Izin/Sakit/Disp</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            <span class="text-[11px] font-medium text-slate-500">Alpa</span>
        </div>
    </div>
</div>

{{-- ─── 8. Riwayat Catatan Terbaru ─────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-4 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3.5 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center text-xs">📝</span>
            <span>Riwayat Catatan Terbaru</span>
        </h3>
        <a href="{{ route('siswa.conduct.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-0.5">
            Lihat Semua →
        </a>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse($recentPoints as $point)
        <div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50/60 transition-colors">
            <div class="w-9 h-9 rounded-2xl shrink-0 flex items-center justify-center
                {{ $point['type'] === 'prestasi' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }}">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($point['type'] === 'prestasi')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    @endif
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-semibold text-slate-800 truncate">{{ $point['desc'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($point['date'])->isoFormat('D MMMM Y') }}</p>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-xl shrink-0 {{ $point['type'] === 'prestasi' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                {{ $point['point'] }}
            </span>
        </div>
        @empty
        <div class="px-4 py-8 text-center text-xs sm:text-sm text-slate-400">
            <span class="text-2xl block mb-1">✨</span>
            Belum ada riwayat catatan siswa
        </div>
        @endforelse
    </div>
</div>

{{-- ─── 9. Pengumuman Sekolah ────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-4 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3.5 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xs">📢</span>
            <span>Pengumuman Sekolah</span>
        </h3>
        <a href="{{ route('siswa.humas.announcements.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-0.5">
            Lihat Semua →
        </a>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse($announcements as $ann)
        <a href="{{ route('siswa.humas.announcements.show', $ann['id']) }}"
            class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50/60 transition-colors group">
            <div class="w-9 h-9 bg-blue-50 text-blue-600 border border-blue-100 rounded-2xl shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-slate-800 group-hover:text-blue-600 transition-colors truncate">{{ $ann['title'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($ann['date'])->isoFormat('D MMMM Y') }}</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @empty
        <div class="px-4 py-8 text-center text-xs sm:text-sm text-slate-400">
            <span class="text-2xl block mb-1">📭</span>
            Tidak ada pengumuman baru
        </div>
        @endforelse
    </div>
</div>

@if(isset($popupAnnouncement) && $popupAnnouncement)
{{-- ─── Modal Pop-Up Pengumuman Premium (Tinggi 80% Layar, Auto-close 15 detik) ────── --}}
<div id="announcement-popup-modal" class="fixed inset-0 z-[9999] hidden bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6 w-full h-full box-border overflow-hidden">
    <div class="bg-white w-[92%] sm:w-[85%] max-w-xl h-[80vh] min-h-[480px] max-h-[82vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col mx-auto my-auto relative border border-white/20 animate-in fade-in zoom-in-95 duration-300">
        
        {{-- Header Modal Gradient & Progress Bar --}}
        <div class="relative bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white p-4 sm:p-5 shrink-0 overflow-hidden">
            {{-- Background Accent Glow --}}
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-500/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -left-10 w-28 h-28 bg-indigo-500/20 rounded-full blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/20 shadow-inner">
                        <span class="text-xl sm:text-2xl animate-bounce">📢</span>
                    </div>
                    <div class="min-w-0">
                        <span class="inline-block text-[10px] sm:text-xs font-extrabold tracking-widest uppercase text-blue-200/90">Pengumuman Sekolah</span>
                        <h2 class="text-sm sm:text-base font-bold text-white truncate">SMAN 1 Gianyar</h2>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{-- Timer Badge --}}
                    <div id="announcement-timer-badge" class="bg-amber-400 text-amber-950 text-xs font-black px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow-md border border-amber-300/80">
                        <span>⏳</span>
                        <span id="announcement-timer-count" class="font-mono">15</span>s
                    </div>
                    {{-- Close X --}}
                    <button type="button" onclick="closeAnnouncementPopup()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center text-lg font-bold transition-all cursor-pointer">
                        ✕
                    </button>
                </div>
            </div>

            {{-- Visual Progress Bar 15 Detik --}}
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-white/10 overflow-hidden">
                <div id="announcement-progress-bar" class="h-full bg-gradient-to-r from-amber-400 to-amber-300 w-full transition-all duration-1000 ease-linear"></div>
            </div>
        </div>

        {{-- Body Modal (Scrollable Content, 80% height container) --}}
        <div class="p-4 sm:p-6 overflow-y-auto space-y-4 flex-1 bg-gradient-to-b from-slate-50 via-gray-50 to-blue-50/30">
            @if($popupAnnouncement->image)
                <div class="rounded-2xl overflow-hidden shadow-md border border-gray-200/80 bg-slate-950 flex justify-center items-center group">
                    <img src="{{ $popupAnnouncement->image_url }}" alt="{{ $popupAnnouncement->title }}" class="max-h-72 w-full object-contain group-hover:scale-102 transition-transform duration-300">
                </div>
            @endif

            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    @if($popupAnnouncement->is_pinned)
                        <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-900 text-[11px] font-bold px-2.5 py-0.5 rounded-full border border-amber-200">
                            📌 Pinned
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-[11px] font-semibold px-2.5 py-0.5 rounded-full border border-blue-100">
                        🗓️ {{ $popupAnnouncement->published_at?->isoFormat('D MMMM Y, HH:mm') }} WITA
                    </span>
                </div>

                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 leading-snug tracking-tight">
                    {{ $popupAnnouncement->title }}
                </h3>
            </div>

            <div class="text-xs sm:text-sm text-slate-700 whitespace-pre-line leading-relaxed bg-white/90 backdrop-blur-xs p-4 rounded-2xl border border-slate-200/70 shadow-xs">
                {!! nl2br(e($popupAnnouncement->body)) !!}
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="px-4 py-3 sm:px-6 sm:py-4 bg-white/95 backdrop-blur-md border-t border-slate-100 flex items-center justify-between gap-3 shrink-0">
            <a href="{{ route('siswa.humas.announcements.index') }}" class="text-xs sm:text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-colors">
                <span>Daftar Pengumuman</span> →
            </a>
            <button type="button" onclick="closeAnnouncementPopup()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl text-xs sm:text-sm font-bold shadow-md shadow-blue-500/25 active:scale-95 transition-all cursor-pointer flex items-center gap-1.5">
                <span>Mengerti & Tutup</span>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const annId = '{{ $popupAnnouncement->id }}';
    const storageKey = 'dismissed_announcement_' + annId;
    const modal = document.getElementById('announcement-popup-modal');
    const timerCount = document.getElementById('announcement-timer-count');
    const progressBar = document.getElementById('announcement-progress-bar');

    if (!modal) return;

    if (!sessionStorage.getItem(storageKey)) {
        modal.classList.remove('hidden');

        let secondsLeft = 15;
        const totalSeconds = 15;

        const countdownInterval = setInterval(function() {
            secondsLeft--;
            if (timerCount) timerCount.textContent = secondsLeft;

            if (progressBar) {
                const pct = (secondsLeft / totalSeconds) * 100;
                progressBar.style.width = pct + '%';
            }

            if (secondsLeft <= 0) {
                clearInterval(countdownInterval);
                closeAnnouncementPopup();
            }
        }, 1000);

        window.closeAnnouncementPopup = function() {
            clearInterval(countdownInterval);
            modal.classList.add('hidden');
            sessionStorage.setItem(storageKey, '1');
        };
    }
});
</script>
@endif

@endsection
