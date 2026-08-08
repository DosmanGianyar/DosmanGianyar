@extends('layouts.guru')
@section('title', 'Rekap Absensi')
@section('page-title', 'Rekap Absensi Bulanan')

@section('content')
<div class="space-y-4" x-data="{
    showModal: false,
    loading: false,
    detail: null,
    filter: 'all',
    fetchDetail(studentId) {
        this.showModal = true;
        this.loading = true;
        this.detail = null;
        fetch('{{ route('guru.attendance.student-detail', ':id') }}'.replace(':id', studentId) + '?month={{ $month }}&year={{ $year }}')
            .then(res => res.json())
            .then(data => {
                this.detail = data;
                this.loading = false;
            })
            .catch(() => { this.loading = false; });
    }
}">

{{-- ─── Filter Bar ──────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <form method="GET" action="{{ route('guru.attendance.rekap') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
        <div class="flex-1">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Kelas</label>
            <select name="class_id"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Bulan</label>
            <select name="month"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->isoFormat('MMMM') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-24">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun</label>
            <select name="year"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                @foreach(range(now()->year - 2, now()->year) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
            class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors sm:w-auto shrink-0">
            Tampilkan
        </button>
    </form>
</div>

{{-- ─── Judul Periode ───────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between px-1">
    <p class="text-sm font-bold text-gray-700">
        Rekap {{ $start->isoFormat('MMMM Y') }} · {{ $schoolDays->count() }} hari sekolah
    </p>
    <span class="text-xs text-gray-400">{{ $studentData->count() }} siswa</span>
</div>

{{-- ─── Legend ──────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap gap-2 text-[10px] font-semibold">
    @foreach(['H'=>['bg-green-500','Hadir'],'T'=>['bg-yellow-400','Terlambat'],'I'=>['bg-blue-400','Izin'],'S'=>['bg-purple-400','Sakit'],'A'=>['bg-red-400','Alpa'],'D'=>['bg-teal-400','Dispensasi']] as $k=>[$color,$label])
    <span class="flex items-center gap-1"><span class="inline-block w-3.5 h-3.5 rounded {{ $color }}"></span>{{ $label }}</span>
    @endforeach
</div>

{{-- ─── Grid Table ──────────────────────────────────────────────────────── --}}
@if($studentData->isEmpty())
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center">
    <p class="text-sm text-gray-400">Tidak ada siswa di kelas ini</p>
</div>
@else
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-3 py-2.5 font-semibold text-gray-600 sticky left-0 bg-gray-50 min-w-[140px]">
                        Nama Siswa
                    </th>
                    @foreach($allDays as $day)
                    @php $isOff = isset($offDays[$day->format('Y-m-d')]); @endphp
                    <th class="px-1 py-2.5 font-semibold text-center min-w-[28px]
                        {{ $isOff ? 'bg-gray-100 text-gray-300' : ($day->isToday() ? 'bg-blue-50 text-blue-600' : 'text-gray-500') }}">
                        {{ $day->format('d') }}
                        <div class="text-[9px] font-normal {{ $isOff ? 'text-gray-300' : 'text-gray-400' }}">
                            {{ $day->isoFormat('ddd') }}
                        </div>
                    </th>
                    @endforeach
                    <th class="px-2 py-2.5 font-semibold text-green-600 text-center min-w-[28px]">H</th>
                    <th class="px-2 py-2.5 font-semibold text-yellow-500 text-center min-w-[28px]">T</th>
                    <th class="px-2 py-2.5 font-semibold text-blue-500 text-center min-w-[28px]">I</th>
                    <th class="px-2 py-2.5 font-semibold text-purple-500 text-center min-w-[28px]">S</th>
                    <th class="px-2 py-2.5 font-semibold text-red-500 text-center min-w-[28px]">A</th>
                    <th class="px-2 py-2.5 font-semibold text-teal-500 text-center min-w-[28px]">D</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($studentData as $row)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-3 py-2 sticky left-0 bg-white font-medium text-gray-800 truncate max-w-[140px]">
                        <button type="button" @click="fetchDetail({{ $row['student']->id }})"
                            class="hover:underline text-blue-600 font-semibold cursor-pointer text-left truncate max-w-[130px]">
                            {{ $row['student']->name }}
                        </button>
                    </td>
                    @foreach($allDays as $day)
                    @php
                        $dateStr = $day->format('Y-m-d');
                        $isOff   = isset($offDays[$dateStr]);
                        $isFuture = $day->gt($today);
                        $status  = $row['effective_statuses'][$dateStr] ?? null;
                        $cell = match(true) {
                            $isOff                            => ['bg-gray-100 border border-gray-200', 'L', 'text-gray-300'],
                            $isFuture || $status === 'future' => ['bg-gray-50',  '·',  'text-gray-200'],
                            $status === 'hadir'               => ['bg-green-500','H',  'text-white'],
                            $status === 'terlambat'           => ['bg-yellow-400','T', 'text-white'],
                            $status === 'izin'                => ['bg-blue-400', 'I',  'text-white'],
                            $status === 'sakit'               => ['bg-purple-400','S', 'text-white'],
                            $status === 'dispensasi'          => ['bg-teal-400', 'D',  'text-white'],
                            default                           => ['bg-red-400',  'A',  'text-white'],
                        };
                    @endphp
                    <td class="text-center py-2 px-0.5 {{ $isOff ? 'bg-gray-50' : '' }}">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded text-[9px] font-bold {{ $cell[0] }} {{ $cell[2] }}">
                            {{ $cell[1] }}
                        </span>
                    </td>
                    @endforeach
                    <td class="text-center py-2 px-2 font-bold text-green-700">{{ $row['counts']['hadir'] }}</td>
                    <td class="text-center py-2 px-2 font-bold text-yellow-600">{{ $row['counts']['terlambat'] }}</td>
                    <td class="text-center py-2 px-2 font-bold text-blue-600">{{ $row['counts']['izin'] }}</td>
                    <td class="text-center py-2 px-2 font-bold text-purple-600">{{ $row['counts']['sakit'] }}</td>
                    <td class="text-center py-2 px-2 font-bold text-red-600">{{ $row['counts']['alpa'] }}</td>
                    <td class="text-center py-2 px-2 font-bold text-teal-600">{{ $row['counts']['dispensasi'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ─── Back Nav ────────────────────────────────────────────────────────── --}}
<div class="pt-1">
    <a href="{{ route('guru.attendance.index') }}"
        class="text-sm text-blue-600 font-medium hover:underline">← Kembali ke Absensi Harian</a>
</div>

{{-- ─── Modal Detail Presensi Siswa ─────────────────────────────────────── --}}
<div x-show="showModal" x-cloak
    @click.self="showModal = false"
    @keydown.escape.window="showModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
    <div class="bg-white rounded-2xl max-w-xl w-full max-h-[85vh] overflow-hidden flex flex-col shadow-2xl border border-gray-100" @click.stop>
        {{-- Header --}}
        <div class="p-4 border-b border-gray-100 flex items-start justify-between bg-gray-50">
            <div>
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2" x-text="detail?.student?.name || 'Detail Presensi'"></h3>
                <p class="text-xs text-gray-500 mt-0.5" x-text="'NIS: ' + (detail?.student?.nis || '—') + ' • Kelas: ' + (detail?.student?.class_name || '—') + ' • ' + (detail?.month_name || '')"></p>
            </div>
            <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <template x-if="loading">
            <div class="p-12 text-center text-gray-400 text-sm flex flex-col items-center gap-2">
                <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Memuat rincian presensi siswa...
            </div>
        </template>

        <template x-if="!loading && detail">
            <div class="flex-1 overflow-hidden flex flex-col">
                {{-- Summary Cards --}}
                <div class="p-3 bg-gray-50 border-b border-gray-100 grid grid-cols-6 gap-1.5 text-center">
                    <div class="bg-white p-1.5 rounded-xl border border-gray-100"><p class="text-[9px] font-bold text-green-600 uppercase">Hadir</p><p class="text-sm font-extrabold text-gray-800" x-text="detail.counts.hadir"></p></div>
                    <div class="bg-white p-1.5 rounded-xl border border-gray-100"><p class="text-[9px] font-bold text-yellow-600 uppercase">Terlambat</p><p class="text-sm font-extrabold text-gray-800" x-text="detail.counts.terlambat"></p></div>
                    <div class="bg-white p-1.5 rounded-xl border border-gray-100"><p class="text-[9px] font-bold text-blue-600 uppercase">Izin</p><p class="text-sm font-extrabold text-gray-800" x-text="detail.counts.izin"></p></div>
                    <div class="bg-white p-1.5 rounded-xl border border-gray-100"><p class="text-[9px] font-bold text-purple-600 uppercase">Sakit</p><p class="text-sm font-extrabold text-gray-800" x-text="detail.counts.sakit"></p></div>
                    <div class="bg-white p-1.5 rounded-xl border border-gray-100"><p class="text-[9px] font-bold text-teal-600 uppercase">Dispensasi</p><p class="text-sm font-extrabold text-gray-800" x-text="detail.counts.dispensasi"></p></div>
                    <div class="bg-white p-1.5 rounded-xl border border-gray-100"><p class="text-[9px] font-bold text-red-600 uppercase">Alpa</p><p class="text-sm font-extrabold text-gray-800" x-text="detail.counts.alpa"></p></div>
                </div>

                {{-- Filter Tabs --}}
                <div class="px-4 pt-2.5 flex gap-2 border-b border-gray-100 bg-white">
                    <button type="button" @click="filter = 'all'" :class="filter === 'all' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500'" class="pb-2 text-xs border-b-2">Semua</button>
                    <button type="button" @click="filter = 'alpa'" :class="filter === 'alpa' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-gray-500'" class="pb-2 text-xs border-b-2">🔴 Alpa (<span x-text="detail.counts.alpa"></span>)</button>
                    <button type="button" @click="filter = 'izin_sakit'" :class="filter === 'izin_sakit' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500'" class="pb-2 text-xs border-b-2">🔵 Izin / Sakit</button>
                </div>

                {{-- Logs --}}
                <div class="p-4 overflow-y-auto flex-1 space-y-2">
                    <template x-for="log in detail.logs" :key="log.date">
                        <div x-show="filter === 'all' || (filter === 'alpa' && log.status === 'alpa') || (filter === 'izin_sakit' && ['izin','sakit','dispensasi'].includes(log.status))"
                            class="p-2.5 rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="{
                                    'bg-green-500': log.status === 'hadir',
                                    'bg-yellow-400': log.status === 'terlambat',
                                    'bg-blue-400': log.status === 'izin',
                                    'bg-purple-400': log.status === 'sakit',
                                    'bg-teal-400': log.status === 'dispensasi',
                                    'bg-red-500': log.status === 'alpa'
                                }"></span>
                                <div>
                                    <p class="font-bold text-gray-800" x-text="log.date_formatted"></p>
                                    <p class="text-[11px] text-gray-500 mt-0.5" x-text="log.reason || ''" x-show="log.reason"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-right shrink-0">
                                <template x-if="log.via_lupa_absen">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Lupa Absen</span>
                                </template>
                                <template x-if="!log.via_lupa_absen">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="{
                                        'bg-green-100 text-green-700': log.status === 'hadir',
                                        'bg-yellow-100 text-yellow-700': log.status === 'terlambat',
                                        'bg-blue-100 text-blue-700': log.status === 'izin',
                                        'bg-purple-100 text-purple-700': log.status === 'sakit',
                                        'bg-teal-100 text-teal-700': log.status === 'dispensasi',
                                        'bg-red-100 text-red-700': log.status === 'alpa'
                                    }" x-text="log.status"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

</div>
@endsection
