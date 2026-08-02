@extends('layouts.siswa')

@section('title', 'Beranda')
@section('page-title', 'Beranda')

@section('content')

{{-- ─── Sapaan & Hero Header (High Contrast & Large Font Design) ───────── --}}
<div class="relative overflow-hidden bg-linear-to-r from-blue-800 via-indigo-900 to-blue-900 rounded-2xl p-4 mb-3.5 text-white shadow-lg border border-blue-600/30">
    {{-- Decorative Background Watermark Logo --}}
    <div class="absolute -right-6 -bottom-6 w-36 h-36 opacity-15 pointer-events-none">
        <img src="{{ asset('img/logo_sekolah.png') }}" class="w-full h-full object-contain">
    </div>

    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/20 backdrop-blur-md text-xs font-bold text-white border border-white/30 shadow-2xs">
                    📅 {{ now()->isoFormat('dddd, D MMMM Y') }}
                </span>
            </div>
            <h2 class="text-lg sm:text-xl font-black tracking-tight leading-tight truncate text-white mt-1">
                Selamat {{ match(true) { now()->hour < 11 => 'Pagi', now()->hour < 15 => 'Siang', now()->hour < 18 => 'Sore', default => 'Malam' } }}, {{ explode(' ', trim($siswa->name))[0] }}! 👋
            </h2>
            <p class="text-xs sm:text-sm font-semibold text-blue-50 mt-1 truncate">
                {{ $siswa->schoolClass?->name ?? 'Siswa SMAN 1 Gianyar' }} @if($siswa->angkatan) · <span class="font-extrabold text-amber-300 bg-amber-400/25 px-1.5 py-0.5 rounded border border-amber-300/40 text-xs">{{ $siswa->angkatan }}</span>@endif · NIS {{ $siswa->nis ?? '—' }}
            </p>
        </div>

        {{-- Ringkasan Kehadiran Bulan Ini --}}
        <div class="shrink-0 bg-white/15 backdrop-blur-md rounded-xl p-2.5 border border-white/25 shadow-xs">
            <p class="text-[11px] font-extrabold text-amber-300 uppercase tracking-wider mb-1.5 text-center">Rekap Bulan Ini</p>
            <div class="flex items-center justify-center gap-1.5 text-xs font-black">
                <span class="inline-flex items-center gap-1 bg-yellow-400/20 px-1.5 py-0.5 rounded text-yellow-200 border border-yellow-400/30" title="Terlambat"><span class="w-2 h-2 rounded-full bg-yellow-400"></span>{{ $monthlySummary['terlambat'] }} TL</span>
                <span class="inline-flex items-center gap-1 bg-red-500/20 px-1.5 py-0.5 rounded text-red-200 border border-red-400/30" title="Alpa"><span class="w-2 h-2 rounded-full bg-red-400"></span>{{ $monthlySummary['alpa'] }} A</span>
                <span class="inline-flex items-center gap-1 bg-sky-400/20 px-1.5 py-0.5 rounded text-sky-200 border border-sky-400/30" title="Izin"><span class="w-2 h-2 rounded-full bg-sky-400"></span>{{ $monthlySummary['izin'] }} I</span>
                <span class="inline-flex items-center gap-1 bg-purple-400/20 px-1.5 py-0.5 rounded text-purple-200 border border-purple-400/30" title="Sakit"><span class="w-2 h-2 rounded-full bg-purple-400"></span>{{ $monthlySummary['sakit'] }} S</span>
                <span class="inline-flex items-center gap-1 bg-orange-400/20 px-1.5 py-0.5 rounded text-orange-200 border border-orange-400/30" title="Dispensasi"><span class="w-2 h-2 rounded-full bg-orange-400"></span>{{ $monthlySummary['dispensasi'] }} D</span>
            </div>
        </div>
    </div>
</div>

{{-- ─── Kartu Pelajar Digital ───────────────────────────────────────── --}}
@include('siswa.partials.student-card-digital')

{{-- ─── Card Absen Masuk ────────────────────────────────────────────── --}}
@php
    $statusIcon = match($todayStatus['status']) {
        'Hadir'         => ['bg' => 'bg-green-500',  'icon' => 'M5 13l4 4L19 7'],
        'Terlambat'     => ['bg' => 'bg-yellow-500', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'Izin', 'Sakit' => ['bg' => 'bg-blue-500',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'Alpa'          => ['bg' => 'bg-red-500',    'icon' => 'M6 18L18 6M6 6l12 12'],
        default         => ['bg' => 'bg-gray-400',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    };
@endphp
@php
    $checkinDone = $todayStatus['status'] !== 'Belum Presensi';
@endphp
<div class="{{ $checkinDone ? 'bg-green-100 border-green-300' : 'bg-gray-100 border-gray-200' }} rounded-2xl shadow-sm border p-3 flex items-center gap-3 mb-3">
    <div class="w-11 h-11 rounded-full {{ $checkinDone ? $statusIcon['bg'] : 'bg-gray-300' }} flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon['icon'] }}"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="{{ $checkinDone ? 'text-green-900' : 'text-gray-500' }} font-semibold text-sm">{{ $todayStatus['status'] }}</p>
        <p class="{{ $checkinDone ? 'text-green-700' : 'text-gray-400' }} text-xs">
            @if(! $checkinDone)
                Segera lakukan presensi sebelum jam 08:00
            @else
                Tercatat jam {{ $todayStatus['time'] }}
            @endif
        </p>
    </div>
    @if(! $checkinDone)
        <a href="{{ route('siswa.attendance.show') }}"
            class="shrink-0 bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg">
            Presensi
        </a>
    @elseif($todayStatus['photo'])
        <img src="{{ $todayStatus['photo'] }}"
            alt="Selfie"
            class="shrink-0 w-14 h-14 rounded-xl object-cover border border-green-100 shadow-sm">
    @else
        <div class="shrink-0 w-14 h-14 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex flex-col items-center justify-center gap-0.5">
            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
            <span class="text-[9px] text-gray-300 font-medium">No Foto</span>
        </div>
    @endif
</div>

{{-- ─── Card Absen Pulang ───────────────────────────────────────────── --}}
@if($todayStatus['checked_in'])
@php $checkoutDone = (bool) $todayStatus['check_out_time']; @endphp
<div class="{{ $checkoutDone ? 'bg-emerald-100 border-emerald-300' : 'bg-gray-100 border-gray-200' }} rounded-2xl shadow-sm border p-3 flex items-center gap-3 mb-3">
    <div class="w-11 h-11 rounded-full {{ $checkoutDone ? 'bg-emerald-500' : 'bg-gray-300' }} flex items-center justify-center shrink-0">
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
        <p class="{{ $checkoutDone ? 'text-emerald-900' : 'text-gray-500' }} font-semibold text-sm">Absen Pulang</p>
        <p class="{{ $checkoutDone ? 'text-emerald-700' : 'text-gray-400' }} text-xs">
            @if($checkoutDone)
                Tercatat jam {{ $todayStatus['check_out_time'] }}
            @else
                Belum melakukan absen pulang
            @endif
        </p>
    </div>
    @if($checkoutDone && $todayStatus['check_out_photo'])
        <img src="{{ $todayStatus['check_out_photo'] }}"
            alt="Selfie Pulang"
            class="shrink-0 w-14 h-14 rounded-xl object-cover border border-emerald-100 shadow-sm">
    @elseif($checkoutDone)
        <div class="shrink-0 w-14 h-14 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex flex-col items-center justify-center gap-0.5">
            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
            <span class="text-[9px] text-gray-300 font-medium">No Foto</span>
        </div>
    @else
        <a href="{{ route('siswa.attendance.show') }}"
            class="shrink-0 bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg whitespace-nowrap">
            Absen Pulang
        </a>
    @endif
</div>
@endif

{{-- [SISTEM POIN DISABLED] — untuk mengaktifkan kembali: ganti @if(false) menjadi @if(true) pada dua blok di bawah --}}

@if(false) {{-- SISTEM POIN: Ringkasan Poin --}}
{{-- ─── Ringkasan Poin ──────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Ringkasan Poin</h3>
    <div class="grid grid-cols-3 gap-2 text-center">
        <div class="bg-blue-50 rounded-xl py-3">
            <p class="text-xl font-bold text-blue-700">{{ $pointSummary['total'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Poin</p>
        </div>
        <div class="bg-green-50 rounded-xl py-3">
            <p class="text-xl font-bold text-green-700">+{{ $pointSummary['prestasi'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Prestasi</p>
        </div>
        <div class="bg-red-50 rounded-xl py-3">
            <p class="text-xl font-bold text-red-700">-{{ $pointSummary['pelanggaran'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Catatan Negatif</p>
        </div>
    </div>
</div>
@endif

{{-- ─── Kalender Kehadiran Bulanan ──────────────────────────────── --}}
@php
    $calNow        = \Carbon\Carbon::now();
    $calFirst      = $calNow->copy()->startOfMonth();
    $calDays       = $calNow->daysInMonth;
    $startDow      = (int) $calFirst->dayOfWeek;            // 0=Sun,1=Mon…6=Sat
    $startOffset   = ($startDow + 6) % 7;                   // Monday-first offset
    $todayDate     = $calNow->toDateString();
    $hadir_set     = ['hadir', 'terlambat', 'izin', 'sakit', 'dispensasi'];
@endphp
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-700">Kalender Kehadiran</h3>
        <span class="text-xs text-gray-400">{{ $calNow->isoFormat('MMMM Y') }}</span>
    </div>
    {{-- Day headers --}}
    <div class="grid grid-cols-7 gap-0.5 mb-1">
        @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dh)
            <div class="text-center text-[10px] font-semibold text-gray-400">{{ $dh }}</div>
        @endforeach
    </div>
    {{-- Calendar cells --}}
    <div class="grid grid-cols-7 gap-0.5">
        @for($i = 0; $i < $startOffset; $i++)
            <div></div>
        @endfor
        @for($day = 1; $day <= $calDays; $day++)
            @php
                $ds         = $calFirst->copy()->addDays($day - 1)->toDateString();
                $dow        = (int) \Carbon\Carbon::parse($ds)->dayOfWeek;   // 0=Sun,6=Sat
                $isWeekend  = in_array($dow, [0, 6]);
                $isHoliday  = isset($monthlyHolidays[$ds]);
                $isSpecial  = isset($monthlySpecial[$ds]);
                // Gray if: future day, normal weekend (not special school day), or holiday
                $isGrayDay  = $isFuture = ($ds > $todayDate);
                $isGrayDay  = $isGrayDay || ($isWeekend && ! $isSpecial) || $isHoliday;
                $isToday    = $ds === $todayDate;
                $st         = $monthlyByDate[$ds] ?? null;

                if ($isToday) {
                    $cls = 'bg-blue-500 text-white font-bold';
                } elseif ($isGrayDay) {
                    $cls = 'text-gray-300';
                } elseif ($st && in_array($st, $hadir_set)) {
                    $cls = 'bg-green-500 text-white';
                } elseif ($st === 'alpa') {
                    $cls = 'bg-red-500 text-white';
                } else {
                    $cls = 'text-gray-300';
                }
            @endphp
            <div class="flex items-center justify-center py-0.5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center {{ $cls }} text-[11px]">
                    {{ $day }}
                </div>
            </div>
        @endfor
    </div>
    {{-- Legend --}}
    <div class="flex items-center gap-4 mt-3 flex-wrap">
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 rounded-full bg-blue-500 shrink-0"></div>
            <span class="text-[10px] text-gray-400">Hari Ini</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 rounded-full bg-green-500 shrink-0"></div>
            <span class="text-[10px] text-gray-400">Hadir</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 rounded-full bg-red-500 shrink-0"></div>
            <span class="text-[10px] text-gray-400">Tidak Hadir</span>
        </div>
    </div>
</div>

@if(false) {{-- SISTEM POIN: Riwayat Poin Terbaru --}}
{{-- ─── Riwayat Poin Terbaru ────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-4 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700">Riwayat Poin</h3>
        <a href="{{ route('siswa.conduct.index') }}" class="text-xs text-blue-600">Lihat Semua</a>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($recentPoints as $point)
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center
                {{ $point['type'] === 'prestasi' ? 'bg-green-100' : 'bg-red-100' }}">
                <svg class="w-4 h-4 {{ $point['type'] === 'prestasi' ? 'text-green-600' : 'text-red-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <p class="text-sm font-medium text-gray-800 truncate">{{ $point['desc'] }}</p>
                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($point['date'])->isoFormat('D MMM Y') }}</p>
            </div>
            <span class="text-sm font-bold {{ str_starts_with($point['point'], '+') ? 'text-green-600' : 'text-red-600' }}">
                {{ $point['point'] }}
            </span>
        </div>
        @empty
        <div class="px-4 py-6 text-center text-sm text-gray-400">Belum ada riwayat poin</div>
        @endforelse
    </div>
</div>
@endif


{{-- ─── Notifikasi Quick-Link (if unread) ───────────────────────────── --}}
@if($unreadNotifications > 0)
<a href="{{ route('siswa.notifications.index') }}"
    class="flex items-center gap-3 bg-blue-50 border border-blue-100 rounded-2xl px-4 py-3 mb-4">
    <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
    </div>
    <div class="flex-1">
        <p class="text-sm font-semibold text-blue-800">
            {{ $unreadNotifications }} notifikasi belum dibaca
        </p>
        <p class="text-xs text-blue-500">Tap untuk melihat</p>
    </div>
    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
@endif

{{-- ─── Pengumuman ──────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700">Pengumuman</h3>
        <a href="{{ route('siswa.announcements.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($announcements as $ann)
        <a href="{{ route('siswa.announcements.show', $ann['id']) }}"
            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
            <div class="w-9 h-9 bg-blue-100 rounded-full shrink-0 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ $ann['title'] }}</p>
                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($ann['date'])->isoFormat('D MMM Y') }}</p>
            </div>
        </a>
        @empty
        <div class="px-4 py-6 text-center text-sm text-gray-400">Tidak ada pengumuman</div>
        @endforelse
    </div>
</div>

@endsection
