@extends('layouts.siswa')
@section('title', 'Kurikulum')
@section('page-title', 'Kurikulum')

@section('content')

{{-- ─── Header ───────────────────────────────────────────────────────── --}}
<div class="bg-linear-to-br from-emerald-500 to-teal-600 rounded-2xl p-4 mb-4 text-white">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <div>
            <p class="text-emerald-100 text-xs">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h2 class="text-lg font-bold leading-tight">Kurikulum</h2>
            <p class="text-emerald-100 text-xs mt-0.5">{{ $siswa->schoolClass?->name ?? 'SMA Negeri 1 Gianyar' }}</p>
        </div>
    </div>
</div>

{{-- ─── Absensi Guru Shortcut ───────────────────────────────────────── --}}
<a href="{{ route('siswa.teacher-attendance.index') }}"
    class="flex items-center justify-between bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 hover:border-blue-200 transition-colors">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-800">Absensi Guru Mengajar</p>
            <p class="text-xs text-gray-500">Lihat kehadiran guru di kelas kamu</p>
        </div>
    </div>
    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>

{{-- ─── Bimbingan Wali Kelas Shortcut ───────────────────────────────── --}}
<a href="{{ route('siswa.homeroom-consultation.index') }}"
    class="flex items-center justify-between bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 hover:border-indigo-200 transition-colors">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-800">Bimbingan Wali Kelas</p>
            <p class="text-xs text-gray-500">Ajukan dan lihat riwayat bimbingan</p>
        </div>
    </div>
    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>

{{-- ─── Tab Nav ──────────────────────────────────────────────────────── --}}
<div class="flex gap-2 mb-4 overflow-x-auto pb-1 scrollbar-none">
    @foreach([['jadwal','Jadwal Hari Ini'],['mingguan','Jadwal Mingguan'],['kalender','Kalender'],['nilai','Nilai']] as [$id,$label])
    <button onclick="switchKTab('{{ $id }}')" id="ktab-btn-{{ $id }}"
        class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors shrink-0
            {{ $id === 'jadwal' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600' }}">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- ─── Tab: Jadwal Hari Ini ─────────────────────────────────────────── --}}
<div id="ktab-jadwal">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-50 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-700">
                {{ $isWeekday ? $dayNames[$todayIso] . ', ' . now()->isoFormat('D MMM Y') : now()->isoFormat('dddd, D MMM Y') }}
            </p>
            @if($isWeekday)
                <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full">
                    {{ $todaySchedule->count() }} Pelajaran
                </span>
            @endif
        </div>

        @if(!$isWeekday)
        <div class="py-12 text-center">
            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-gray-600">Hari Libur</p>
            <p class="text-xs text-gray-400 mt-1">Tidak ada jadwal pelajaran hari ini</p>
        </div>
        @elseif($todaySchedule->isEmpty())
        <div class="py-12 text-center">
            <p class="text-sm text-gray-400">Belum ada jadwal untuk hari ini</p>
        </div>
        @else
        @foreach($todaySchedule as $i => $sch)
        @php $isNow = now()->between(\Carbon\Carbon::parse($sch->start_time), \Carbon\Carbon::parse($sch->end_time)); @endphp
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0
            {{ $isNow ? 'bg-emerald-50' : '' }}">
            {{-- Jam ke- --}}
            <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center text-xs font-bold
                {{ $isNow ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ $sch->period }}
            </div>
            {{-- Waktu --}}
            <div class="w-20 shrink-0">
                <p class="text-[11px] font-semibold text-gray-700">{{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }}</p>
                <p class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($sch->end_time)->format('H:i') }}</p>
            </div>
            {{-- Mapel --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $sch->subject->name }}</p>
                <p class="text-xs text-gray-400 truncate">
                    {{ $sch->teacher?->name ?? '—' }}
                    @if($sch->room) · {{ $sch->room }} @endif
                </p>
            </div>
            @if($isNow)
            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full shrink-0 animate-pulse">
                Sekarang
            </span>
            @endif
        </div>
        @endforeach
        @endif
    </div>
</div>

{{-- ─── Tab: Jadwal Mingguan ────────────────────────────────────────── --}}
<div id="ktab-mingguan" class="hidden">
    @if($weekSchedule->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center">
        <p class="text-sm text-gray-400">Belum ada jadwal pelajaran</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach(range(1,5) as $day)
        @php $daySchedules = $weekSchedule->get($day, collect()); @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 py-2.5 flex items-center justify-between
                {{ $day === $todayIso ? 'bg-emerald-50 border-b border-emerald-100' : 'border-b border-gray-50' }}">
                <p class="text-sm font-bold {{ $day === $todayIso ? 'text-emerald-700' : 'text-gray-700' }}">
                    {{ $dayNames[$day] }}
                    @if($day === $todayIso)
                        <span class="ml-1.5 text-[10px] font-semibold bg-emerald-500 text-white px-1.5 py-0.5 rounded-full">Hari ini</span>
                    @endif
                </p>
                <span class="text-xs text-gray-400">{{ $daySchedules->count() }} mapel</span>
            </div>
            @forelse($daySchedules as $sch)
            <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-50 last:border-0">
                <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center text-xs font-bold text-gray-500 shrink-0">
                    {{ $sch->period }}
                </div>
                <div class="w-16 shrink-0">
                    <p class="text-[11px] text-gray-600 font-medium">{{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($sch->end_time)->format('H:i') }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $sch->subject->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $sch->teacher?->name ?? '—' }}{{ $sch->room ? ' · ' . $sch->room : '' }}</p>
                </div>
            </div>
            @empty
            <div class="px-4 py-3">
                <p class="text-xs text-gray-400">Tidak ada pelajaran</p>
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
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center">
        <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-sm text-gray-400">Belum ada jadwal akademik</p>
    </div>
    @else
    @php $grouped = $upcomingEvents->groupBy(fn($e) => $e->start_date->isoFormat('MMMM Y')); @endphp
    <div class="space-y-4">
        @foreach($grouped as $month => $events)
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide px-1 mb-2">{{ $month }}</p>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                @foreach($events as $event)
                @php $isToday = $event->start_date->isToday() || ($event->start_date->lte(today()) && $event->end_date->gte(today())); @endphp
                <div class="flex items-start gap-3 px-4 py-3 border-b border-gray-50 last:border-0
                    {{ $isToday ? 'bg-amber-50' : '' }}">
                    {{-- Tanggal --}}
                    <div class="w-10 shrink-0 text-center">
                        <p class="text-lg font-extrabold leading-none {{ $isToday ? 'text-amber-500' : 'text-gray-700' }}">
                            {{ $event->start_date->format('d') }}
                        </p>
                        <p class="text-[10px] text-gray-400 uppercase">{{ $event->start_date->isoFormat('MMM') }}</p>
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="text-sm font-semibold text-gray-800 leading-tight">{{ $event->title }}</p>
                        @if(!$event->start_date->isSameDay($event->end_date))
                        <p class="text-[11px] text-gray-400 mt-0.5">
                            s.d {{ $event->end_date->isoFormat('D MMM Y') }}
                        </p>
                        @endif
                        @if($event->description)
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $event->description }}</p>
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

{{-- ─── Tab: Nilai ──────────────────────────────────────────────────── --}}
<div id="ktab-nilai" class="hidden">
    {{-- Info semester --}}
    <div class="flex items-center justify-between px-1 mb-3">
        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">
            Semester {{ $semester }} · TA {{ $academicYear }}
        </p>
        <div class="flex items-center gap-2">
            @if($grades->isNotEmpty())
            <span class="text-xs text-gray-400">{{ $grades->count() }} mata pelajaran</span>
            <a href="{{ route('siswa.kurikulum.rapor') }}"
                class="flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg hover:bg-emerald-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Rapor PDF
            </a>
            @endif
        </div>
    </div>

    @if($grades->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center">
        <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-gray-600">Belum Ada Nilai</p>
        <p class="text-xs text-gray-400 mt-1">Nilai untuk semester ini belum tersedia</p>
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
            $avgColor     = $finalAvg === null ? 'text-gray-400'
                          : ($finalAvg >= 80 ? 'text-green-600' : ($finalAvg >= 65 ? 'text-yellow-600' : 'text-red-600'));
            $avgBg        = $finalAvg === null ? 'bg-gray-50'
                          : ($finalAvg >= 80 ? 'bg-green-50' : ($finalAvg >= 65 ? 'bg-yellow-50' : 'bg-red-50'));
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Subject header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-50 {{ $avgBg }}">
                <p class="text-sm font-bold text-gray-800">{{ $subjectName }}</p>
                @if($finalAvg !== null)
                <span class="text-sm font-extrabold {{ $avgColor }}">{{ $finalAvg }}</span>
                @else
                <span class="text-xs text-gray-400">—</span>
                @endif
            </div>
            {{-- Score rows --}}
            <div class="divide-y divide-gray-50">
                {{-- UH rows --}}
                @foreach($subjectGrades->where('type', 'UH')->values() as $idx => $g)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <span class="w-20 text-xs text-gray-500 shrink-0">UH {{ $idx + 1 }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $g->score >= 80 ? 'bg-green-500' : ($g->score >= 65 ? 'bg-yellow-400' : 'bg-red-400') }}"
                            style="width: {{ min(100, $g->score) }}%"></div>
                    </div>
                    <span class="w-10 text-right text-sm font-bold {{ $g->scoreColor() }} shrink-0">
                        {{ number_format($g->score, 0) }}
                    </span>
                </div>
                @endforeach
                {{-- UH average if multiple --}}
                @if($uhScores->count() > 1)
                <div class="flex items-center gap-3 px-4 py-2 bg-gray-50/60">
                    <span class="w-20 text-[11px] text-gray-400 shrink-0 italic">Rata UH</span>
                    <div class="flex-1"></div>
                    <span class="w-10 text-right text-xs font-semibold text-gray-600 shrink-0">{{ $uhAvg }}</span>
                </div>
                @endif
                {{-- UTS --}}
                @if($utsScore !== null)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <span class="w-20 text-xs text-gray-500 shrink-0">UTS</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $utsScore >= 80 ? 'bg-green-500' : ($utsScore >= 65 ? 'bg-yellow-400' : 'bg-red-400') }}"
                            style="width: {{ min(100, $utsScore) }}%"></div>
                    </div>
                    <span class="w-10 text-right text-sm font-bold {{ $subjectGrades->firstWhere('type','UTS')->scoreColor() }} shrink-0">
                        {{ number_format($utsScore, 0) }}
                    </span>
                </div>
                @endif
                {{-- UAS --}}
                @if($uasScore !== null)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <span class="w-20 text-xs text-gray-500 shrink-0">UAS</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $uasScore >= 80 ? 'bg-green-500' : ($uasScore >= 65 ? 'bg-yellow-400' : 'bg-red-400') }}"
                            style="width: {{ min(100, $uasScore) }}%"></div>
                    </div>
                    <span class="w-10 text-right text-sm font-bold {{ $subjectGrades->firstWhere('type','UAS')->scoreColor() }} shrink-0">
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
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-700">Rata-rata Keseluruhan</p>
            <span class="text-lg font-extrabold {{ $overallAvg >= 80 ? 'text-green-600' : ($overallAvg >= 65 ? 'text-yellow-600' : 'text-red-600') }}">
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
        document.getElementById('ktab-' + t).classList.add('hidden');
        var btn = document.getElementById('ktab-btn-' + t);
        btn.className = btn.className
            .replace('bg-emerald-600 text-white shadow-sm', '')
            .replace('bg-white border border-gray-200 text-gray-600', '')
            .trim() + ' bg-white border border-gray-200 text-gray-600';
    });
    document.getElementById('ktab-' + name).classList.remove('hidden');
    var active = document.getElementById('ktab-btn-' + name);
    active.className = active.className
        .replace('bg-white border border-gray-200 text-gray-600', '')
        .trim() + ' bg-emerald-600 text-white shadow-sm';
}
</script>
@endsection
