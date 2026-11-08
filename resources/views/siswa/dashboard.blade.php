@extends('layouts.siswa')

@section('title', 'Beranda')
@section('page-title', 'Beranda')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

{{-- ─── 1. Banner Notifikasi Belum Dibaca ──────────────────────────────── --}}
@if($unreadNotifications > 0)
<a href="{{ route('siswa.notifications.index') }}"
    class="flex items-center justify-between gap-3 bg-blue-50/80 border border-blue-200 rounded-xl px-3.5 py-2.5 hover:bg-blue-50 transition-colors group">
    <div class="flex items-center gap-2.5 min-w-0">
        <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
        <p class="text-xs font-semibold text-slate-800 truncate">
            {{ $unreadNotifications }} pemberitahuan baru belum dibaca
        </p>
    </div>
    <span class="text-xs font-semibold text-blue-600 shrink-0">Lihat →</span>
</a>
@endif

{{-- ─── 2. Kartu Identitas & Ringkasan Kehadiran Bulan Ini ─────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-4 space-y-3.5">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-medium text-slate-500">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mt-0.5">
                {{ $siswa->name }}
            </h1>
            <div class="flex items-center gap-1.5 flex-wrap mt-1 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">{{ $siswa->schoolClass?->name ?? 'Siswa SMAN 1 Gianyar' }}</span>
                @if($siswa->angkatan)
                    <span class="text-slate-300">·</span>
                    <span class="font-medium text-slate-600">Angkatan {{ $siswa->angkatan }}</span>
                @endif
                <span class="text-slate-300">·</span>
                <span class="font-mono text-[11px]">NIS {{ $siswa->nis ?? '—' }}</span>
            </div>
        </div>
        <a href="{{ route('siswa.profile') }}"
           class="shrink-0 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            Profil
        </a>
    </div>

    {{-- Strip Ringkasan Kehadiran Bulan Ini --}}
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-[11px] font-semibold text-slate-500">Rekap Bulan {{ now()->isoFormat('MMMM') }}</span>
            <a href="#kalender-kehadiran" class="text-[11px] font-medium text-blue-600 hover:underline">
                Kalender ↓
            </a>
        </div>
        <div class="border border-slate-200 rounded-lg divide-x divide-slate-200 grid grid-cols-5 text-center bg-slate-50/50 overflow-hidden">
            <div class="py-2 px-1">
                <p class="text-sm font-bold {{ $monthlySummary['terlambat'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $monthlySummary['terlambat'] }}</p>
                <p class="text-[10px] font-medium text-slate-500 mt-0.5">Telat</p>
            </div>
            <div class="py-2 px-1">
                <p class="text-sm font-bold {{ $monthlySummary['alpa'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $monthlySummary['alpa'] }}</p>
                <p class="text-[10px] font-medium text-slate-500 mt-0.5">Alpa</p>
            </div>
            <div class="py-2 px-1">
                <p class="text-sm font-bold text-slate-900">{{ $monthlySummary['izin'] }}</p>
                <p class="text-[10px] font-medium text-slate-500 mt-0.5">Izin</p>
            </div>
            <div class="py-2 px-1">
                <p class="text-sm font-bold text-slate-900">{{ $monthlySummary['sakit'] }}</p>
                <p class="text-[10px] font-medium text-slate-500 mt-0.5">Sakit</p>
            </div>
            <div class="py-2 px-1">
                <p class="text-sm font-bold text-slate-900">{{ $monthlySummary['dispensasi'] }}</p>
                <p class="text-[10px] font-medium text-slate-500 mt-0.5">Dispen</p>
            </div>
        </div>
    </div>
</div>

{{-- ─── 3. Status Presensi Hari Ini (Masuk & Pulang) ───────────────────── --}}
@php
    $statusIcon = match($todayStatus['status']) {
        'Hadir'         => ['dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'Terlambat'     => ['dot' => 'bg-amber-500',   'badge' => 'bg-amber-50 text-amber-700 border-amber-200'],
        'Izin', 'Sakit', 'Dispensasi' => ['dot' => 'bg-sky-500', 'badge' => 'bg-sky-50 text-sky-700 border-sky-200'],
        'Alpa'          => ['dot' => 'bg-rose-500',    'badge' => 'bg-rose-50 text-rose-700 border-rose-200'],
        default         => ['dot' => 'bg-slate-400',   'badge' => 'bg-slate-100 text-slate-600 border-slate-200'],
    };
    $checkinDone = $todayStatus['status'] !== 'Belum Presensi';
@endphp

<div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100 overflow-hidden">
    {{-- Baris Presensi Masuk --}}
    <div class="p-3.5 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $statusIcon['dot'] }}"></span>
                <p class="text-xs font-bold text-slate-900">Presensi Masuk</p>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md border {{ $statusIcon['badge'] }}">
                    {{ $todayStatus['status'] }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                @if(! $checkinDone)
                    Batas waktu presensi pukul 08:00 WITA
                @else
                    Tercatat pukul {{ $todayStatus['time'] }} WITA
                @endif
            </p>
        </div>
        @if(! $checkinDone)
            <a href="{{ route('siswa.attendance.show') }}"
                class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition-colors">
                Presensi
            </a>
        @elseif($todayStatus['photo'])
            <img src="{{ $todayStatus['photo'] }}"
                alt="Selfie Presensi"
                class="shrink-0 w-11 h-11 rounded-lg object-cover border border-slate-200">
        @endif
    </div>

    {{-- Baris Presensi Pulang --}}
    @if($todayStatus['checked_in'])
    @php $checkoutDone = (bool) $todayStatus['check_out_time']; @endphp
    <div class="p-3.5 flex items-center justify-between gap-3 bg-slate-50/40">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $checkoutDone ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                <p class="text-xs font-bold text-slate-900">Presensi Pulang</p>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md border {{ $checkoutDone ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                    {{ $checkoutDone ? 'Selesai' : 'Belum' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                @if($checkoutDone)
                    Tercatat pukul {{ $todayStatus['check_out_time'] }} WITA
                @else
                    Lakukan presensi saat jam kepulangan
                @endif
            </p>
        </div>
        @if($checkoutDone && $todayStatus['check_out_photo'])
            <img src="{{ $todayStatus['check_out_photo'] }}"
                alt="Selfie Pulang"
                class="shrink-0 w-11 h-11 rounded-lg object-cover border border-slate-200">
        @elseif(! $checkoutDone)
            <a href="{{ route('siswa.attendance.show') }}"
                class="shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition-colors whitespace-nowrap">
                Absen Pulang
            </a>
        @endif
    </div>
    @endif
</div>

{{-- ─── 4. Grid Menu Kotak (SIPINTER, Chat Konsultasi, Tata Tertib) ────── --}}
<div class="grid grid-cols-3 gap-2.5">
    {{-- SIPINTER --}}
    <a href="{{ route('siswa.conduct.index') }}"
       class="bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl p-3 flex flex-col justify-between min-h-[100px] transition-colors group">
        <div class="flex items-center justify-between">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <span class="text-[11px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">
                {{ $pointSummary['total'] }}
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">SIPINTER</p>
            <p class="text-[10px] text-slate-500 mt-0.5 truncate">+{{ $pointSummary['prestasi'] }} / {{ $pointSummary['pelanggaran'] }} catatan</p>
        </div>
    </a>

    {{-- Chat Konsultasi --}}
    <a href="{{ route('siswa.chat.index') }}"
       class="bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl p-3 flex flex-col justify-between min-h-[100px] transition-colors group">
        <div class="flex items-center justify-between">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <span class="text-[10px] font-medium text-slate-400">3 pihak</span>
        </div>
        <div class="mt-2.5">
            <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Ruang Chat</p>
            <p class="text-[10px] text-slate-500 mt-0.5 truncate">Wali kelas & ortu</p>
        </div>
    </a>

    {{-- Tata Tertib --}}
    <a href="{{ route('siswa.tata-tertib') }}"
       class="bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl p-3 flex flex-col justify-between min-h-[100px] transition-colors group">
        <div class="flex items-center justify-between">
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <span class="text-[10px] font-medium text-slate-400">Pedoman</span>
        </div>
        <div class="mt-2.5">
            <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Tata Tertib</p>
            <p class="text-[10px] text-slate-500 mt-0.5 truncate">Aturan sekolah</p>
        </div>
    </a>
</div>

{{-- ─── 5. Kartu Pelajar Digital ───────────────────────────────────────── --}}
<div>
    @include('siswa.partials.student-card-digital')
</div>

{{-- ─── 6. Kalender Kehadiran Bulanan ──────────────────────────────────── --}}
@php
    $calNow        = \Carbon\Carbon::now();
    $calFirst      = $calNow->copy()->startOfMonth();
    $calDays       = $calNow->daysInMonth;
    $startDow      = (int) $calFirst->dayOfWeek;
    $startOffset   = ($startDow + 6) % 7;
    $todayDate     = $calNow->toDateString();
@endphp
<div id="kalender-kehadiran" class="bg-white rounded-xl border border-slate-200 p-4">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Kalender Kehadiran</h3>
        <span class="text-xs font-medium text-slate-500">{{ $calNow->isoFormat('MMMM Y') }}</span>
    </div>

    <div class="grid grid-cols-7 gap-1 mb-1 text-center">
        @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dh)
            <div class="text-[10px] font-semibold text-slate-400 py-1">{{ $dh }}</div>
        @endforeach
    </div>

    <div class="grid grid-cols-7 gap-1 text-center">
        @for($i = 0; $i < $startOffset; $i++)
            <div class="h-7"></div>
        @endfor
        @for($day = 1; $day <= $calDays; $day++)
            @php
                $ds         = $calFirst->copy()->addDays($day - 1)->toDateString();
                $dow        = (int) \Carbon\Carbon::parse($ds)->dayOfWeek;
                $isWeekend  = ($dow === 0);
                $isHoliday  = isset($monthlyHolidays[$ds]);
                $isSpecial  = isset($monthlySpecial[$ds]);
                $isGrayDay  = ($ds > $todayDate) || ($isWeekend && ! $isSpecial) || $isHoliday;
                $isToday    = $ds === $todayDate;
                $st         = $monthlyByDate[$ds] ?? null;

                if ($isToday) {
                    $cls = 'bg-blue-600 text-white font-bold ring-2 ring-blue-200';
                } elseif ($isGrayDay) {
                    $cls = 'text-slate-300';
                } elseif ($st && in_array($st, ['hadir', 'terlambat'])) {
                    $cls = 'bg-emerald-500 text-white font-semibold';
                } elseif ($st && in_array($st, ['izin', 'sakit', 'dispensasi'])) {
                    $cls = 'bg-sky-500 text-white font-semibold';
                } elseif ($st === 'alpa') {
                    $cls = 'bg-rose-500 text-white font-semibold';
                } else {
                    $cls = 'text-slate-400';
                }
            @endphp
            <div class="flex items-center justify-center py-0.5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center {{ $cls }} text-xs">
                    {{ $day }}
                </div>
            </div>
        @endfor
    </div>

    <div class="flex items-center gap-3.5 mt-3 pt-2.5 border-t border-slate-100 flex-wrap">
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            <span class="text-[11px] text-slate-500">Hari Ini</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-[11px] text-slate-500">Hadir</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
            <span class="text-[11px] text-slate-500">Izin/Sakit</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            <span class="text-[11px] text-slate-500">Alpa</span>
        </div>
    </div>
</div>

{{-- ─── 7. Riwayat Catatan Terbaru ─────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Catatan Karakter Terbaru</h3>
        <a href="{{ route('siswa.conduct.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
            Semua →
        </a>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse($recentPoints as $point)
        <div class="flex items-center gap-3 px-4 py-2.5">
            <span class="w-2 h-2 rounded-full shrink-0 {{ $point['type'] === 'prestasi' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-800 truncate">{{ $point['desc'] }}</p>
                <p class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($point['date'])->isoFormat('D MMMM Y') }}</p>
            </div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded border shrink-0 {{ $point['type'] === 'prestasi' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                {{ $point['point'] }}
            </span>
        </div>
        @empty
        <div class="px-4 py-6 text-center text-xs text-slate-400">
            Belum ada catatan karakter.
        </div>
        @endforelse
    </div>
</div>

{{-- ─── 8. Pengumuman Sekolah ────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Pengumuman Sekolah</h3>
        <a href="{{ route('siswa.humas.announcements.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
            Semua →
        </a>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse($announcements as $ann)
        <a href="{{ route('siswa.humas.announcements.show', $ann['id']) }}"
            class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors group">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-800 group-hover:text-blue-600 transition-colors truncate">{{ $ann['title'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($ann['date'])->isoFormat('D MMMM Y') }}</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @empty
        <div class="px-4 py-6 text-center text-xs text-slate-400">
            Belum ada pengumuman terbaru.
        </div>
        @endforelse
    </div>
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

            @php
                $hasPopupHtml = $popupAnnouncement->body !== strip_tags($popupAnnouncement->body);
            @endphp
            <div class="announcement-modal-body text-xs sm:text-sm text-slate-700 leading-relaxed bg-white/95 backdrop-blur-xs p-4 rounded-2xl border border-slate-200/70 shadow-xs">
                @if($hasPopupHtml)
                    {!! $popupAnnouncement->body !!}
                @else
                    <div class="whitespace-pre-line">{!! nl2br(e($popupAnnouncement->body)) !!}</div>
                @endif
            </div>
            <style>
                .announcement-modal-body p { margin-bottom: 0.65rem; }
                .announcement-modal-body p:last-child { margin-bottom: 0; }
                .announcement-modal-body h1, .announcement-modal-body h2, .announcement-modal-body h3 { font-weight: 800; margin-top: 0.75rem; margin-bottom: 0.35rem; color: #0f172a; }
                .announcement-modal-body ul { list-style-type: disc; padding-left: 1.35rem; margin-bottom: 0.65rem; }
                .announcement-modal-body ol { list-style-type: decimal; padding-left: 1.35rem; margin-bottom: 0.65rem; }
                .announcement-modal-body li { margin-bottom: 0.3rem; }
                .announcement-modal-body blockquote { border-left: 3px solid #3b82f6; padding-left: 0.75rem; font-style: italic; color: #475569; margin: 0.5rem 0; background: #f8fafc; padding-top: 0.35rem; padding-bottom: 0.35rem; border-radius: 0 8px 8px 0; }
                .announcement-modal-body a { color: #2563eb; text-decoration: underline; font-weight: 600; }
                .announcement-modal-body table { width: 100%; border-collapse: collapse; margin-bottom: 0.65rem; font-size: 12px; }
                .announcement-modal-body th, .announcement-modal-body td { border: 1px solid #e2e8f0; padding: 6px 10px; }
                .announcement-modal-body th { background-color: #f1f5f9; font-weight: 700; }
            </style>
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
