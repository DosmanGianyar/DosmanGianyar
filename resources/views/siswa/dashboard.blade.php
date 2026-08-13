@extends('layouts.siswa')

@section('title', 'Beranda')
@section('page-title', 'Beranda')

@section('content')

{{-- ─── 1. Card Notifikasi (Paling Atas Halaman) ─────────────────────── --}}
@if($unreadNotifications > 0)
<a href="{{ route('siswa.notifications.index') }}"
    class="flex items-center gap-3 bg-blue-50 border border-blue-100 rounded-2xl px-4 py-3 mb-3.5 shadow-xs hover:border-blue-300 transition-all">
    <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
    </div>
    <div class="flex-1">
        <p class="text-sm font-bold text-blue-900">
            {{ $unreadNotifications }} notifikasi belum dibaca
        </p>
        <p class="text-xs text-blue-600">Klik untuk melihat detail notifikasi</p>
    </div>
    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
@endif

{{-- ─── 2. Sapaan & Hero Header ───────────────────────────────────────── --}}
<div class="relative rounded-2xl p-4 mb-3.5 text-white shadow-lg overflow-hidden"
     style="background: linear-gradient(135deg, #0a3880 0%, #1565c0 50%, #1d4ed8 100%); border: 1px solid rgba(255,255,255,0.2);">
    
    <div style="position: absolute; right: -20px; bottom: -20px; width: 140px; height: 140px; border-radius: 50%; background: rgba(255,255,255,0.06); pointer-events: none;"></div>

    <div class="relative z-10 flex flex-col gap-3">
        <div class="flex items-center justify-between gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold text-white border border-white/30 shadow-2xs">
                📅 {{ now()->isoFormat('dddd, D MMMM Y') }}
            </span>
            <span class="text-[11px] font-extrabold text-amber-300 bg-black/20 px-2.5 py-1 rounded-full border border-amber-300/30 uppercase tracking-wider">
                Rekap Kehadiran Bulan Ini
            </span>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-tight text-white drop-shadow-xs">
                Selamat {{ match(true) { now()->hour < 11 => 'Pagi', now()->hour < 15 => 'Siang', now()->hour < 18 => 'Sore', default => 'Malam' } }}, {{ $siswa->name }}! 👋
            </h2>
            <p class="text-xs sm:text-sm font-semibold text-blue-100 mt-1 flex items-center gap-1.5 flex-wrap">
                <span>{{ $siswa->schoolClass?->name ?? 'Siswa SMAN 1 Gianyar' }}</span>
                @if($siswa->angkatan)
                    <span class="font-extrabold text-amber-300 bg-amber-400/30 px-2 py-0.5 rounded-md border border-amber-300/40 text-xs">{{ $siswa->angkatan }}</span>
                @endif
                <span>· NIS {{ $siswa->nis ?? '—' }}</span>
            </p>
        </div>

        <div class="grid grid-cols-5 gap-1.5 pt-1 text-center font-black text-xs">
            <div class="bg-yellow-400/25 border border-yellow-300/40 rounded-lg p-1.5 text-yellow-200">
                <span class="block text-[10px] opacity-90 font-bold uppercase">Terlambat</span>
                <span class="text-sm font-black">{{ $monthlySummary['terlambat'] }}</span>
            </div>
            <div class="bg-red-500/25 border border-red-400/40 rounded-lg p-1.5 text-red-200">
                <span class="block text-[10px] opacity-90 font-bold uppercase">Alpa</span>
                <span class="text-sm font-black">{{ $monthlySummary['alpa'] }}</span>
            </div>
            <div class="bg-sky-400/25 border border-sky-300/40 rounded-lg p-1.5 text-sky-200">
                <span class="block text-[10px] opacity-90 font-bold uppercase">Izin</span>
                <span class="text-sm font-black">{{ $monthlySummary['izin'] }}</span>
            </div>
            <div class="bg-purple-400/25 border border-purple-300/40 rounded-lg p-1.5 text-purple-200">
                <span class="block text-[10px] opacity-90 font-bold uppercase">Sakit</span>
                <span class="text-sm font-black">{{ $monthlySummary['sakit'] }}</span>
            </div>
            <div class="bg-orange-400/25 border border-orange-300/40 rounded-lg p-1.5 text-orange-200">
                <span class="block text-[10px] opacity-90 font-bold uppercase">Disp.</span>
                <span class="text-sm font-black">{{ $monthlySummary['dispensasi'] }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ─── 3. Kartu Pelajar Digital ───────────────────────────────────────── --}}
@include('siswa.partials.student-card-digital')

{{-- ─── 4. Card SIPINTER (Diantara Kartu Siswa & Kalender Kehadiran) ────── --}}
<div onclick="window.location='{{ route('siswa.conduct.index') }}'" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3.5 cursor-pointer hover:border-emerald-300 hover:shadow-md transition-all">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
            <span class="p-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs">🛡️</span> SIPINTER (Pendidikan Karakter)
        </h3>
        <span class="text-xs font-semibold text-blue-600">
            Detail Catatan →
        </span>
    </div>
    <div class="grid grid-cols-3 gap-2 text-center">
        <div class="bg-blue-50/80 border border-blue-100 rounded-xl py-2.5 px-1">
            <p class="text-lg font-black text-blue-700">{{ $pointSummary['total'] }}</p>
            <p class="text-[11px] font-semibold text-gray-500 mt-0.5">Total Catatan</p>
        </div>
        <div class="bg-emerald-50/80 border border-emerald-100 rounded-xl py-2.5 px-1">
            <p class="text-lg font-black text-emerald-700">+{{ $pointSummary['prestasi'] }}</p>
            <p class="text-[11px] font-semibold text-emerald-800 mt-0.5">Apresiasi Karakter</p>
        </div>
        <div class="bg-rose-50/80 border border-rose-100 rounded-xl py-2.5 px-1">
            <p class="text-lg font-black text-rose-700">{{ $pointSummary['pelanggaran'] }}</p>
            <p class="text-[11px] font-semibold text-rose-800 mt-0.5">Kedisiplinan Karakter</p>
        </div>
    </div>
</div>

{{-- ─── 4b. Card Tata Tertib Sekolah (Akses Cepat) ──────────────────────── --}}
<a href="{{ route('siswa.tata-tertib') }}"
    class="flex items-center justify-between bg-white rounded-2xl shadow-sm border border-blue-100 p-4 mb-3.5 hover:border-blue-300 transition-all group">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <p class="text-sm font-bold text-gray-800">Tata Tertib Sekolah</p>
                <span class="text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full">Resmi</span>
            </div>
            <p class="text-xs text-gray-500 mt-0.5 truncate">Aturan, Hak, Kewajiban & Panduan Seragam SMAN 1 Gianyar</p>
        </div>
    </div>
    <div class="flex items-center gap-1 shrink-0">
        <span class="text-xs font-bold text-blue-600 group-hover:translate-x-0.5 transition-transform">Buka →</span>
    </div>
</a>

{{-- ─── 5. Bukti Presensi (Di Atas Kalender Kehadiran) ────────────────── --}}
@php
    $statusIcon = match($todayStatus['status']) {
        'Hadir'         => ['bg' => 'bg-green-500',  'icon' => 'M5 13l4 4L19 7'],
        'Terlambat'     => ['bg' => 'bg-yellow-500', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'Izin', 'Sakit' => ['bg' => 'bg-blue-500',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'Alpa'          => ['bg' => 'bg-red-500',    'icon' => 'M6 18L18 6M6 6l12 12'],
        default         => ['bg' => 'bg-gray-400',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    };
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

@if($todayStatus['checked_in'])
@php $checkoutDone = (bool) $todayStatus['check_out_time']; @endphp
<div class="{{ $checkoutDone ? 'bg-emerald-100 border-emerald-300' : 'bg-gray-100 border-gray-200' }} rounded-2xl shadow-sm border p-3 flex items-center gap-3 mb-3.5">
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

{{-- ─── 6. Kalender Kehadiran Bulanan ──────────────────────────────── --}}
@php
    $calNow        = \Carbon\Carbon::now();
    $calFirst      = $calNow->copy()->startOfMonth();
    $calDays       = $calNow->daysInMonth;
    $startDow      = (int) $calFirst->dayOfWeek;            // 0=Sun,1=Mon…6=Sat
    $startOffset   = ($startDow + 6) % 7;                   // Monday-first offset
    $todayDate     = $calNow->toDateString();
    $hadir_set     = ['hadir', 'terlambat', 'izin', 'sakit', 'dispensasi'];
@endphp
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3.5">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-700">Kalender Kehadiran</h3>
        <span class="text-xs text-gray-400">{{ $calNow->isoFormat('MMMM Y') }}</span>
    </div>
    <div class="grid grid-cols-7 gap-0.5 mb-1">
        @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dh)
            <div class="text-center text-[10px] font-semibold text-gray-400">{{ $dh }}</div>
        @endforeach
    </div>
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

{{-- ─── 7. Riwayat Catatan Terbaru ──────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-3.5 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <h3 class="text-sm font-bold text-gray-800">Riwayat Catatan Terbaru</h3>
        <a href="{{ route('siswa.conduct.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">Lihat Semua</a>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($recentPoints as $point)
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center
                {{ $point['type'] === 'prestasi' ? 'bg-emerald-100' : 'bg-rose-100' }}">
                <svg class="w-4 h-4 {{ $point['type'] === 'prestasi' ? 'text-emerald-600' : 'text-rose-600' }}"
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
            <span class="text-xs font-bold px-2 py-0.5 rounded-md {{ $point['type'] === 'prestasi' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                {{ $point['point'] }}
            </span>
        </div>
        @empty
        <div class="px-4 py-6 text-center text-sm text-gray-400">Belum ada riwayat catatan siswa</div>
        @endforelse
    </div>
</div>

{{-- ─── 8. Pengumuman ────────────────────────────────────────────────── --}}
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
