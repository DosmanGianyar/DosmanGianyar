@extends('layouts.siswa')
@section('title', 'Kurikulum')
@section('page-title', 'Kurikulum')

@section('content')

{{-- ─── Header Kurikulum & Akademik ──────────────────────────────────── --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 p-4 text-white shadow-sm mb-4">
    <div class="relative z-10 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/15 text-white backdrop-blur-xs">
                        {{ $siswa->schoolClass?->name ?? 'SMAN 1 Gianyar' }}
                    </span>
                    <span class="text-[11px] text-white/90">{{ now()->isoFormat('D MMMM Y') }}</span>
                </div>
                <h1 class="text-base font-bold text-white mt-0.5">Kurikulum & Pembelajaran</h1>
            </div>
        </div>

        <div class="hidden sm:block text-right">
            <span class="text-[11px] text-slate-300">Semester Genap</span>
            <p class="text-xs font-bold text-white">T.A. 2025/2026</p>
        </div>
    </div>
</div>

{{-- ─── Hub Cepat Guru Wali & Absensi Guru ─────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-4">
    {{-- Guru Wali Card --}}
    @if($guruWali)
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-3.5 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 font-bold text-sm">
                {{ substr($guruWali->name, 0, 1) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Guru Wali</span>
                </div>
                <p class="text-xs font-bold text-slate-800 truncate">{{ $guruWali->name }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ $guruWali->subject ?? 'Wali Kelas' }}</p>
            </div>
        </div>
        <a href="{{ route('siswa.homeroom-consultation.index') }}"
            class="shrink-0 px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[11px] font-bold inline-flex items-center gap-1 transition-colors">
            <span>Bimbingan</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-3.5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Guru Wali</p>
            <p class="text-xs text-slate-500">Belum memiliki Guru Wali aktif</p>
        </div>
    </div>
    @endif

    {{-- Shortcut Absensi Guru Mengajar --}}
    <a href="{{ route('siswa.teacher-attendance.index') }}"
        class="group bg-white rounded-2xl shadow-xs border border-slate-200/80 p-3.5 flex items-center justify-between gap-3 hover:border-blue-300 transition-all">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-slate-800">Absensi Guru Mengajar</p>
                <p class="text-[11px] text-blue-600 font-medium truncate">Pantau kehadiran guru di kelas</p>
            </div>
        </div>
        <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
</div>

{{-- ─── Segmented Pill Switcher Nav ──────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-1.5 mb-4">
    <div class="flex gap-1 bg-slate-100/70 p-1 rounded-xl overflow-x-auto scrollbar-none">
        @foreach([['jadwal','Jadwal Hari Ini'],['mingguan','Jadwal Mingguan'],['kalender','Kalender'],['nilai','Rapor & Nilai']] as [$id,$label])
        <button onclick="switchKTab('{{ $id }}')" id="ktab-btn-{{ $id }}"
            class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold whitespace-nowrap transition-all text-center shrink-0
                {{ $id === 'jadwal' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>
</div>

{{-- ─── Tab: Jadwal Hari Ini ─────────────────────────────────────────── --}}
<div id="ktab-jadwal">
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <p class="text-xs font-bold text-slate-800">
                    {{ $isWeekday ? $dayNames[$todayIso] . ', ' . now()->isoFormat('D MMMM Y') : now()->isoFormat('dddd, D MMMM Y') }}
                </p>
                <p class="text-[11px] text-slate-400">Jadwal kegiatan belajar mengajar</p>
            </div>
            @if($isWeekday)
                <span class="text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-0.5 rounded-full">
                    {{ $todaySchedule->count() }} Mata Pelajaran
                </span>
            @endif
        </div>

        @if(!$isWeekday)
        <div class="py-12 text-center">
            <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </div>
            <p class="text-xs font-bold text-slate-700">Hari Libur Sekolah</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada jadwal KBM pada hari ini</p>
        </div>
        @elseif($todaySchedule->isEmpty())
        <div class="py-12 text-center">
            <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs text-slate-400 font-medium">Belum ada jadwal KBM untuk hari ini</p>
        </div>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($todaySchedule as $i => $sch)
            @php $isNow = now()->between(\Carbon\Carbon::parse($sch->start_time), \Carbon\Carbon::parse($sch->end_time)); @endphp
            <div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50/60 transition-colors {{ $isNow ? 'bg-indigo-50/50' : '' }}">
                {{-- Jam ke- --}}
                <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center text-xs font-bold
                    {{ $isNow ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600' }}">
                    {{ $sch->period }}
                </div>
                {{-- Waktu --}}
                <div class="w-20 shrink-0">
                    <p class="text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }}</p>
                    <p class="text-[10px] text-slate-400">s.d {{ \Carbon\Carbon::parse($sch->end_time)->format('H:i') }}</p>
                </div>
                {{-- Mapel --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ $sch->subject->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">
                        {{ $sch->teacher?->name ?? '—' }}
                        @if($sch->room) · R. {{ $sch->room }} @endif
                    </p>
                </div>
                @if($isNow)
                <span class="text-[10px] font-bold text-indigo-700 bg-indigo-100 border border-indigo-200 px-2 py-0.5 rounded-full shrink-0 animate-pulse">
                    Sekarang
                </span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ─── Tab: Jadwal Mingguan ────────────────────────────────────────── --}}
<div id="ktab-mingguan" class="hidden">
    @if($weekSchedule->isEmpty())
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 py-12 text-center">
        <p class="text-xs text-slate-400">Belum ada data jadwal mingguan yang disusun</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach(range(1,6) as $day)
        @php $daySchedules = $weekSchedule->get($day, collect()); @endphp
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="px-4 py-2.5 flex items-center justify-between border-b border-slate-100
                {{ $day === $todayIso ? 'bg-indigo-50/60' : 'bg-slate-50/50' }}">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-bold {{ $day === $todayIso ? 'text-indigo-800' : 'text-slate-800' }}">
                        {{ $dayNames[$day] }}
                    </p>
                    @if($day === $todayIso)
                        <span class="text-[10px] font-bold bg-indigo-600 text-white px-1.5 py-0.2 rounded-md">Hari ini</span>
                    @endif
                </div>
                <span class="text-[11px] text-slate-400 font-medium">{{ $daySchedules->count() }} mapel</span>
            </div>
            @forelse($daySchedules as $sch)
            <div class="flex items-center gap-3 px-4 py-2.5 border-b border-slate-50 last:border-0 hover:bg-slate-50/40 transition-colors">
                <div class="w-6 h-6 bg-slate-100 rounded-lg flex items-center justify-center text-[11px] font-bold text-slate-600 shrink-0">
                    {{ $sch->period }}
                </div>
                <div class="w-16 shrink-0">
                    <p class="text-[11px] text-slate-600 font-semibold">{{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }}</p>
                    <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($sch->end_time)->format('H:i') }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ $sch->subject->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $sch->teacher?->name ?? '—' }}{{ $sch->room ? ' · R. ' . $sch->room : '' }}</p>
                </div>
            </div>
            @empty
            <div class="px-4 py-3">
                <p class="text-xs text-slate-400">Tidak ada jadwal KBM</p>
            </div>
            @endforelse
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ─── Tab: Kalender Akademik ─────────────────────────────────────── --}}
<div id="ktab-kalender" class="hidden">
    @if($upcomingEvents->isEmpty())
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 py-12 text-center">
        <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-xs text-slate-400 font-medium">Belum ada agenda akademik yang terdaftar</p>
    </div>
    @else
    @php $grouped = $upcomingEvents->groupBy(fn($e) => $e->start_date->isoFormat('MMMM Y')); @endphp
    <div class="space-y-4">
        @foreach($grouped as $month => $events)
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1 mb-2">{{ $month }}</p>
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden divide-y divide-slate-50">
                @foreach($events as $event)
                @php $isToday = $event->start_date->isToday() || ($event->start_date->lte(today()) && $event->end_date->gte(today())); @endphp
                <div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50/50 transition-colors {{ $isToday ? 'bg-amber-50/60' : '' }}">
                    {{-- Tanggal --}}
                    <div class="w-10 shrink-0 text-center bg-slate-50 border border-slate-100 rounded-xl py-1">
                        <p class="text-base font-black leading-none {{ $isToday ? 'text-amber-600' : 'text-slate-800' }}">
                            {{ $event->start_date->format('d') }}
                        </p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">{{ $event->start_date->isoFormat('MMM') }}</p>
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="text-xs font-bold text-slate-800 leading-tight">{{ $event->title }}</p>
                        @if(!$event->start_date->isSameDay($event->end_date))
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            s.d {{ $event->end_date->isoFormat('D MMM Y') }}
                        </p>
                        @endif
                        @if($event->description)
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $event->description }}</p>
                        @endif
                    </div>
                    {{-- Badge tipe --}}
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 mt-0.5 {{ $event->colorClass() }}">
                        {{ $event->typeLabel() }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ─── Tab: Rapor & Nilai ───────────────────────────────────────────── --}}
<div id="ktab-nilai" class="hidden">
    {{-- Info semester & Rapor PDF --}}
    <div class="flex items-center justify-between px-1 mb-3">
        <div>
            <p class="text-xs font-bold text-slate-800 uppercase tracking-wide">
                Semester {{ $semester }} · TA {{ $academicYear }}
            </p>
            @if($grades->isNotEmpty())
            <p class="text-[11px] text-slate-400">{{ $grades->count() }} mata pelajaran terdaftar</p>
            @endif
        </div>

        @if($grades->isNotEmpty())
        <a href="{{ route('siswa.kurikulum.rapor') }}"
            class="flex items-center gap-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200/80 px-3 py-1.5 rounded-xl hover:bg-indigo-100 transition-colors shadow-2xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Unduh Rapor PDF</span>
        </a>
        @endif
    </div>

    @if($grades->isEmpty())
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 py-12 text-center">
        <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <p class="text-xs font-bold text-slate-700">Belum Ada Catatan Nilai</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Nilai KBM semester ini belum dipublikasikan oleh guru pengampu</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($grades as $subjectId => $subjectGrades)
        @php
            $subjectName  = $subjectGrades->first()->subject->name;
            $uhScores     = $subjectGrades->where('type', 'UH')->pluck('score');
            $utsScore     = $subjectGrades->firstWhere('type', 'UTS')?->score;
            $uasScore     = $subjectGrades->firstWhere('type', 'UAS')?->score;
            $uhAvg        = $uhScores->isNotEmpty() ? round($uhScores->average(), 1) : null;
            // Weighted average: UH 40%, UTS 30%, UAS 30%
            $parts        = [];
            if ($uhAvg  !== null) $parts[] = $uhAvg  * 0.4;
            if ($utsScore !== null) $parts[] = $utsScore * 0.3;
            if ($uasScore !== null) $parts[] = $uasScore * 0.3;
            $totalWeight  = ($uhAvg !== null ? 0.4 : 0) + ($utsScore !== null ? 0.3 : 0) + ($uasScore !== null ? 0.3 : 0);
            $finalAvg     = $totalWeight > 0 ? round(array_sum($parts) / $totalWeight, 1) : null;
            $avgColor     = $finalAvg === null ? 'text-slate-400'
                          : ($finalAvg >= 80 ? 'text-emerald-600' : ($finalAvg >= 65 ? 'text-amber-600' : 'text-rose-600'));
            $avgBg        = $finalAvg === null ? 'bg-slate-50'
                          : ($finalAvg >= 80 ? 'bg-emerald-50/50' : ($finalAvg >= 65 ? 'bg-amber-50/50' : 'bg-rose-50/50'));
        @endphp
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            {{-- Subject header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 {{ $avgBg }}">
                <p class="text-xs font-bold text-slate-800">{{ $subjectName }}</p>
                @if($finalAvg !== null)
                <div class="flex items-baseline gap-1">
                    <span class="text-[10px] text-slate-400 font-medium">Rata-rata:</span>
                    <span class="text-sm font-black {{ $avgColor }}">{{ $finalAvg }}</span>
                </div>
                @else
                <span class="text-xs text-slate-400">—</span>
                @endif
            </div>

            {{-- Score rows --}}
            <div class="divide-y divide-slate-50">
                {{-- UH rows --}}
                @foreach($subjectGrades->where('type', 'UH')->values() as $idx => $g)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <span class="w-16 text-xs text-slate-500 shrink-0 font-medium">UH {{ $idx + 1 }}</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="h-1.5 rounded-full {{ $g->score >= 80 ? 'bg-emerald-500' : ($g->score >= 65 ? 'bg-amber-400' : 'bg-rose-400') }}"
                            style="width: {{ min(100, $g->score) }}%"></div>
                    </div>
                    <span class="w-10 text-right text-xs font-bold {{ $g->scoreColor() }} shrink-0">
                        {{ number_format($g->score, 0) }}
                    </span>
                </div>
                @endforeach

                {{-- UH average if multiple --}}
                @if($uhScores->count() > 1)
                <div class="flex items-center gap-3 px-4 py-1.5 bg-slate-50/60">
                    <span class="w-16 text-[10px] text-slate-400 shrink-0 italic">Rata UH</span>
                    <div class="flex-1"></div>
                    <span class="w-10 text-right text-[11px] font-bold text-slate-600 shrink-0">{{ $uhAvg }}</span>
                </div>
                @endif

                {{-- UTS --}}
                @if($utsScore !== null)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <span class="w-16 text-xs text-slate-500 shrink-0 font-medium">UTS</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="h-1.5 rounded-full {{ $utsScore >= 80 ? 'bg-emerald-500' : ($utsScore >= 65 ? 'bg-amber-400' : 'bg-rose-400') }}"
                            style="width: {{ min(100, $utsScore) }}%"></div>
                    </div>
                    <span class="w-10 text-right text-xs font-bold {{ $subjectGrades->firstWhere('type','UTS')->scoreColor() }} shrink-0">
                        {{ number_format($utsScore, 0) }}
                    </span>
                </div>
                @endif

                {{-- UAS --}}
                @if($uasScore !== null)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <span class="w-16 text-xs text-slate-500 shrink-0 font-medium">UAS</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="h-1.5 rounded-full {{ $uasScore >= 80 ? 'bg-emerald-500' : ($uasScore >= 65 ? 'bg-amber-400' : 'bg-rose-400') }}"
                            style="width: {{ min(100, $uasScore) }}%"></div>
                    </div>
                    <span class="w-10 text-right text-xs font-bold {{ $subjectGrades->firstWhere('type','UAS')->scoreColor() }} shrink-0">
                        {{ number_format($uasScore, 0) }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        {{-- Overall summary --}}
        @php
            $allFinals = $grades->map(function($sg) {
                $uhA = $sg->where('type','UH')->pluck('score');
                $uh  = $uhA->isNotEmpty() ? $uhA->average() : null;
                $uts = $sg->firstWhere('type','UTS')?->score;
                $uas = $sg->firstWhere('type','UAS')?->score;
                $w   = ($uh!==null?0.4:0)+($uts!==null?0.3:0)+($uas!==null?0.3:0);
                if ($w === 0) return null;
                return (($uh??0)*($uh!==null?0.4:0) + ($uts??0)*($uts!==null?0.3:0) + ($uas??0)*($uas!==null?0.3:0)) / $w;
            })->filter()->values();
            $overallAvg = $allFinals->isNotEmpty() ? round($allFinals->average(), 1) : null;
        @endphp
        @if($overallAvg !== null)
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 px-4 py-3.5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-800">Rata-rata Keseluruhan Mata Pelajaran</p>
                <p class="text-[11px] text-slate-400">Akumulasi bobot UH (40%), UTS (30%), UAS (30%)</p>
            </div>
            <span class="text-xl font-black {{ $overallAvg >= 80 ? 'text-emerald-600' : ($overallAvg >= 65 ? 'text-amber-600' : 'text-rose-600') }}">
                {{ $overallAvg }}
            </span>
        </div>
        @endif
    </div>
    @endif
</div>

<script>
function switchKTab(name) {
    ['jadwal','mingguan','kalender','nilai'].forEach(function(t) {
        var el = document.getElementById('ktab-' + t);
        if (el) el.classList.add('hidden');
        var btn = document.getElementById('ktab-btn-' + t);
        if (btn) {
            btn.classList.remove('bg-white', 'text-indigo-700', 'shadow-xs');
            btn.classList.add('text-slate-600');
        }
    });
    var activeTab = document.getElementById('ktab-' + name);
    if (activeTab) activeTab.classList.remove('hidden');
    var activeBtn = document.getElementById('ktab-btn-' + name);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-600');
        activeBtn.classList.add('bg-white', 'text-indigo-700', 'shadow-xs');
    }
}
</script>
@endsection
