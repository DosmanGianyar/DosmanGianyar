@extends('layouts.siswa')

@section('title', 'Absensi Guru')
@section('page-title', 'Absensi Guru')

@section('content')
<div class="space-y-4 mt-2">

    {{-- Today's Attendance --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
            Hari Ini — {{ now()->isoFormat('dddd, D MMMM Y') }}
        </p>

        @if($today->isEmpty())
        <div class="text-center py-6 text-gray-400 text-sm">
            Belum ada catatan absensi guru hari ini.
        </div>
        @else
        <div class="space-y-2">
            @foreach($today as $r)
            @php
                $badge = match($r->status) {
                    'hadir'       => 'bg-green-100 text-green-700',
                    'tidak_hadir' => 'bg-red-100 text-red-700',
                    'izin'        => 'bg-blue-100 text-blue-700',
                    'sakit'       => 'bg-purple-100 text-purple-700',
                    default       => 'bg-gray-100 text-gray-700',
                };
                $icon = match($r->status) {
                    'hadir'       => '✓',
                    'tidak_hadir' => '✗',
                    'izin'        => 'I',
                    'sakit'       => 'S',
                    default       => '?',
                };
            @endphp
            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                        {{ $r->period }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $r->teacher?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $r->subject?->name ?? '—' }}
                            @if($r->start_time) · {{ substr($r->start_time, 0, 5) }}–{{ substr($r->end_time, 0, 5) }} @endif
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badge }}">
                    {{ $icon }} {{ $r->statusLabel() }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Month Filter --}}
    <form method="GET" action="{{ route('siswa.teacher-attendance.index') }}" class="flex gap-2">
        <input type="month" name="month" value="{{ $month }}"
            class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
            Tampilkan
        </button>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-4 gap-2">
        @foreach([['Hadir', $summary['hadir'], 'bg-green-50 text-green-700'], ['Tdk Hadir', $summary['tidak_hadir'], 'bg-red-50 text-red-700'], ['Izin', $summary['izin'], 'bg-blue-50 text-blue-700'], ['Sakit', $summary['sakit'], 'bg-purple-50 text-purple-700']] as [$label, $count, $cls])
        <div class="rounded-xl p-3 text-center {{ $cls }}">
            <p class="text-lg font-bold">{{ $count }}</p>
            <p class="text-xs">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Records by Date --}}
    @if($byDate->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400 text-sm">
        Belum ada data absensi guru untuk bulan ini.
    </div>
    @else
    <div class="space-y-3">
        @foreach($byDate as $dateStr => $dayRecords)
        @php $carbon = \Carbon\Carbon::parse($dateStr); @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                <p class="text-xs font-semibold text-gray-600">{{ $carbon->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($dayRecords as $r)
                @php
                    $badge = match($r->status) {
                        'hadir'       => 'bg-green-100 text-green-700',
                        'tidak_hadir' => 'bg-red-100 text-red-700',
                        'izin'        => 'bg-blue-100 text-blue-700',
                        'sakit'       => 'bg-purple-100 text-purple-700',
                        default       => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">
                            {{ $r->period }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $r->teacher?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $r->subject?->name ?? '—' }}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badge }}">
                        {{ $r->statusLabel() }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
