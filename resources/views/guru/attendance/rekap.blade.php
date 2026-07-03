@extends('layouts.guru')
@section('title', 'Rekap Absensi')
@section('page-title', 'Rekap Absensi Bulanan')

@section('content')
<div class="space-y-4">

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
                    @foreach($schoolDays as $day)
                    <th class="px-1 py-2.5 font-semibold text-gray-500 text-center min-w-[28px]
                        {{ $day->isToday() ? 'bg-blue-50 text-blue-600' : '' }}">
                        {{ $day->format('d') }}
                        <div class="text-[9px] font-normal text-gray-400">{{ $day->isoFormat('ddd') }}</div>
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
                        {{ $row['student']->name }}
                    </td>
                    @foreach($schoolDays as $day)
                    @php
                        $status = $row['effective_statuses'][$day->format('Y-m-d')] ?? 'alpa';
                        $cell   = match($status) {
                            'hadir'       => ['bg-green-500','H'],
                            'terlambat'   => ['bg-yellow-400','T'],
                            'izin'        => ['bg-blue-400','I'],
                            'sakit'       => ['bg-purple-400','S'],
                            'dispensasi'  => ['bg-teal-400','D'],
                            default       => ['bg-red-400','A'],
                        };
                    @endphp
                    <td class="text-center py-2 px-0.5">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded text-[9px] font-bold text-white {{ $cell[0] }}">
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

</div>
@endsection
