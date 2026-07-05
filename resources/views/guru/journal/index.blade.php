@extends('layouts.guru')
@section('title', 'Jurnal Mengajar')
@section('page-title', 'Jurnal Mengajar')

@section('content')
@php
    $months = ['', 'Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
@endphp
<div class="space-y-4">

    {{-- ─── Filter Bar --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" action="{{ route('guru.journal.index') }}"
            class="flex flex-wrap gap-3 items-end">

            <div class="w-28">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Bulan</label>
                <select name="month" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
                    @endfor
                </select>
            </div>

            <div class="w-24">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun</label>
                <select name="year" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Kelas</label>
                <select name="class_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Semua Kelas —</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <a href="{{ route('guru.journal.create') }}"
                class="flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Jurnal
            </a>
        </form>
    </div>

    {{-- ─── Summary --}}
    <div class="flex items-center gap-2 px-1">
        <span class="text-sm text-gray-500">
            <span class="font-semibold text-gray-800">{{ $total }}</span> jurnal
            di {{ $months[$month] }} {{ $year }}
        </span>
    </div>

    {{-- ─── Journal List --}}
    @forelse($journals as $journal)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-blue-50 border-b border-blue-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $journal->schoolClass?->name ?? '—' }}</p>
                    <div class="flex items-center gap-2 flex-wrap mt-0.5">
                        <span class="text-xs text-gray-500">
                            {{ $journal->date?->isoFormat('ddd, D MMM Y') }}
                        </span>
                        @if($journal->period)
                        <span class="text-xs text-blue-600 font-medium">
                            Jam ke-{{ $journal->period }}{{ $journal->period_end && $journal->period_end > $journal->period ? '–'.$journal->period_end : '' }}
                        </span>
                        @endif
                        @if($journal->subject)
                        <span class="text-xs text-gray-500">· {{ $journal->subject->name }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($journal->absences->count() > 0)
                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-100 text-red-600">
                    {{ $journal->absences->count() }} absen
                </span>
                @endif
                <form method="POST" action="{{ route('guru.journal.destroy', $journal) }}"
                    onsubmit="return confirm('Hapus jurnal ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-red-100 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-4 py-3.5 space-y-2.5">
            @if($journal->tp)
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Tujuan Pembelajaran</p>
                <p class="text-sm text-gray-700">
                    @if($journal->tp->code)
                    <span class="inline-block px-1.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 mr-1">{{ $journal->tp->code }}</span>
                    @endif
                    {{ $journal->tp->description }}
                </p>
            </div>
            @endif
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Materi</p>
                <p class="text-sm text-gray-700">{{ $journal->material }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Aktivitas Pembelajaran</p>
                <p class="text-sm text-gray-700">{{ $journal->activity }}</p>
            </div>
            @if($journal->notes)
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Catatan</p>
                <p class="text-sm text-gray-700">{{ $journal->notes }}</p>
            </div>
            @endif
            @if($journal->absences->count() > 0)
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Siswa Tidak Hadir</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($journal->absences as $abs)
                    @php
                        $statusConfig = [
                            'tidak_hadir' => ['label' => 'A', 'bg' => 'bg-red-100', 'text' => 'text-red-600'],
                            'izin'        => ['label' => 'I', 'bg' => 'bg-sky-100',  'text' => 'text-sky-600'],
                            'sakit'       => ['label' => 'S', 'bg' => 'bg-purple-100','text' => 'text-purple-600'],
                        ];
                        $cfg = $statusConfig[$abs->status] ?? ['label' => '?', 'bg' => 'bg-gray-100', 'text' => 'text-gray-500'];
                    @endphp
                    <span class="flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs {{ $cfg['bg'] }} {{ $cfg['text'] }} font-medium">
                        <span class="font-bold">{{ $cfg['label'] }}</span>
                        {{ $abs->student?->name ?? '—' }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <p class="text-gray-400 text-sm">Belum ada jurnal di {{ $months[$month] }} {{ $year }}.</p>
        <a href="{{ route('guru.journal.create') }}"
            class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Jurnal Pertama
        </a>
    </div>
    @endforelse

</div>
@endsection
