@extends('layouts.guru')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ─── 1. Ringkasan Profil Guru & Statistik Utama ────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
    {{-- Kartu Identitas Guru --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5 min-w-0">
            @if($guru->photo)
                <img src="{{ $guru->photo_url }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shrink-0">
            @else
                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 font-bold text-base shrink-0">
                    {{ $guru->initials }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-[11px] font-medium text-slate-500">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 truncate mt-0.5">
                    {{ $guru->name }}
                </h2>
                <div class="flex items-center gap-1.5 flex-wrap mt-1 text-xs text-slate-600">
                    <span>{{ $guru->subject ?? 'Guru Pengajar' }}</span>
                    @if($guru->nip)
                        <span class="text-slate-300">·</span>
                        <span class="text-slate-500 font-mono text-[11px]">NIP {{ $guru->nip }}</span>
                    @endif
                    @if($guru->homeroomClass)
                        <span class="text-slate-300">·</span>
                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-semibold text-[11px]">
                            Wali Kelas {{ $guru->homeroomClass->name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <a href="{{ route('guru.profile') }}"
           class="self-start sm:self-center shrink-0 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            Edit Profil
        </a>
    </div>

    {{-- Statistik Ringkas (2 Kolom) --}}
    <div class="bg-white rounded-xl border border-slate-200 divide-x divide-slate-200 grid grid-cols-2 overflow-hidden">
        <div class="p-4 flex flex-col justify-between">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Siswa Bimbingan</span>
            <div class="mt-2">
                <p class="text-2xl font-bold text-slate-900 leading-none">{{ $stats['total_students'] }}</p>
                <p class="text-[11px] text-slate-500 mt-1 truncate">
                    {{ $guru->homeroomClass?->name ? 'Kelas ' . $guru->homeroomClass->name : 'Seluruh Kelas' }}
                </p>
            </div>
        </div>
        <div class="p-4 flex flex-col justify-between">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Jurnal</span>
            <div class="mt-2">
                <p class="text-2xl font-bold text-slate-900 leading-none">{{ $stats['total_journals'] }}</p>
                <a href="{{ route('guru.journal.create') }}" class="inline-block text-[11px] font-semibold text-blue-600 hover:text-blue-700 mt-1">
                    + Buat Jurnal →
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ─── 2. Jadwal Mengajar Guru (Hari Ini & Perminggu) ───────────────── --}}
<div x-data="{ showWeeklyModal: false, activeTab: {{ (int) now()->dayOfWeekIso <= 6 ? (int) now()->dayOfWeekIso : 1 }} }" class="mb-5">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        {{-- Card Header --}}
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="font-bold text-xs sm:text-sm text-slate-900">
                    Jadwal Mengajar Hari Ini ({{ $todayDayName }})
                </h3>
            </div>
            <button type="button" @click="showWeeklyModal = true"
                class="px-3 py-1.5 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                Jadwal Perminggu
            </button>
        </div>

        {{-- Today's Schedule List --}}
        <div class="p-4">
            @if(isset($todaySchedulesMerged) && $todaySchedulesMerged->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($todaySchedulesMerged as $sch)
                        @php $isFilled = $sch['is_filled']; @endphp
                        <div class="p-3.5 rounded-xl flex flex-col justify-between border transition-colors {{ $isFilled ? 'bg-slate-50/70 border-slate-200 text-slate-600' : 'bg-white border-slate-200 hover:border-blue-300' }}">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @foreach($sch['periods'] as $p)
                                            <span class="px-2 py-0.5 text-[11px] font-bold rounded {{ $isFilled ? 'bg-slate-200 text-slate-700' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                                Jam {{ $p }}
                                            </span>
                                        @endforeach
                                        <span class="text-xs font-medium text-slate-500">
                                            {{ $sch['start_time'] }}–{{ $sch['end_time'] }}
                                        </span>
                                    </div>
                                    @if($isFilled)
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-semibold rounded-md shrink-0">
                                            Terisi
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-semibold rounded-md shrink-0">
                                            Belum Isi
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between gap-2 pt-0.5">
                                    <span class="text-sm font-bold text-slate-900">
                                        Kelas {{ $sch['class_name'] }}
                                    </span>
                                    @if(!empty($sch['room']))
                                        <span class="text-[11px] font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                                            {{ $sch['room'] }}
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs font-medium text-slate-600">
                                    {{ $sch['subject_name'] }}
                                </p>
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                @if($isFilled)
                                    <span class="text-[11px] text-slate-400">Tersimpan</span>
                                    <a href="{{ route('guru.journal.index') }}"
                                       class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                                        Lihat Jurnal →
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-500">Jurnal mengajar</span>
                                    <a href="{{ route('guru.journal.create', ['class_id' => $sch['class_id'], 'subject_id' => $sch['subject_id'], 'period' => $sch['period_start']]) }}"
                                       class="inline-flex items-center gap-1 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg transition-colors">
                                        + Isi Jurnal
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-6 text-center">
                    <p class="text-xs text-slate-500">Tidak ada jadwal mengajar pada hari {{ $todayDayName }}.</p>
                    <button type="button" @click="showWeeklyModal = true"
                        class="mt-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 cursor-pointer">
                        Lihat Jadwal Perminggu →
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Modal Jadwal Perminggu ───────────────────────────────────── --}}
    <template x-teleport="body">
        <div x-show="showWeeklyModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-slate-200"
                @click.outside="showWeeklyModal = false">

                {{-- Modal Header --}}
                <div class="px-5 py-3.5 bg-white border-b border-slate-200 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-slate-900">Jadwal Mengajar Perminggu</h3>
                        <p class="text-xs text-slate-500">Tahun Ajaran {{ $todaySchedules->first()?->academic_year ?? '2025/2026' }}</p>
                    </div>
                    <button @click="showWeeklyModal = false"
                        class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 transition-colors cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Day Tabs --}}
                <div class="px-5 py-2.5 bg-slate-50 border-b border-slate-200 flex gap-2 overflow-x-auto shrink-0">
                    @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
                        @php
                            $hasClasses = isset($weeklySchedulesMerged[$dNum]) && $weeklySchedulesMerged[$dNum]->count() > 0;
                        @endphp
                        <button type="button" @click="activeTab = {{ $dNum }}"
                            :class="activeTab === {{ $dNum }} ? 'bg-blue-600 text-white border-blue-600 font-semibold' : 'bg-white text-slate-700 hover:bg-slate-100 border-slate-200 font-medium'"
                            class="px-3 py-1.5 text-xs rounded-lg border transition-colors flex items-center gap-1.5 shrink-0 cursor-pointer">
                            <span>{{ $dName }}</span>
                            @if($hasClasses)
                                <span class="px-1.5 py-0.2 rounded text-[10px]"
                                    :class="activeTab === {{ $dNum }} ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                                    {{ $weeklySchedulesMerged[$dNum]->count() }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>

                {{-- Modal Body / Content per Tab --}}
                <div class="p-5 overflow-y-auto flex-1">
                    @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
                        <div x-show="activeTab === {{ $dNum }}">
                            @if(isset($weeklySchedulesMerged[$dNum]) && $weeklySchedulesMerged[$dNum]->isNotEmpty())
                                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold">
                                            <tr>
                                                <th class="px-4 py-2.5 text-center w-28">Jam Ke</th>
                                                <th class="px-4 py-2.5 w-36">Waktu</th>
                                                <th class="px-4 py-2.5">Mata Pelajaran</th>
                                                <th class="px-4 py-2.5">Kelas</th>
                                                <th class="px-4 py-2.5">Ruang</th>
                                                <th class="px-4 py-2.5 text-center">Jurnal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach($weeklySchedulesMerged[$dNum] as $sch)
                                                @php $isFilledW = $sch['is_filled']; @endphp
                                                <tr class="{{ $isFilledW ? 'bg-slate-50/60 text-slate-500' : 'hover:bg-slate-50' }}">
                                                    <td class="px-4 py-2.5 text-center">
                                                        <div class="flex items-center justify-center gap-1 flex-wrap">
                                                            @foreach($sch['periods'] as $p)
                                                                <span class="px-2 py-0.5 rounded font-semibold text-[11px] {{ $isFilledW ? 'bg-slate-200 text-slate-700' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                                                    Jam {{ $p }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-2.5 font-medium text-slate-700">
                                                        {{ $sch['start_time'] }} - {{ $sch['end_time'] }}
                                                    </td>
                                                    <td class="px-4 py-2.5 font-semibold text-slate-900">
                                                        {{ $sch['subject_name'] }}
                                                    </td>
                                                    <td class="px-4 py-2.5 font-bold text-slate-800">
                                                        {{ $sch['class_name'] }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-slate-500">
                                                        {{ $sch['room'] ?? '—' }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-center">
                                                        @if($isFilledW)
                                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold rounded">Terisi</span>
                                                        @elseif($dNum === (int) now()->dayOfWeekIso)
                                                            <a href="{{ route('guru.journal.create', ['class_id' => $sch['class_id'], 'subject_id' => $sch['subject_id'], 'period' => $sch['period_start']]) }}"
                                                               class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg inline-block transition-colors">
                                                                + Isi Jurnal
                                                            </a>
                                                        @else
                                                            <span class="text-slate-400">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="py-10 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-slate-200">
                                    Tidak ada jadwal mengajar pada hari {{ $dName }}.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Modal Footer --}}
                <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end shrink-0">
                    <button type="button" @click="showWeeklyModal = false"
                        class="px-4 py-1.5 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

{{-- ─── 3. Pembina Ekstrakurikuler & Prestasi Binaan (Jika Ada) ────────── --}}
@if((isset($myExtracurriculars) && $myExtracurriculars->count() > 0) || ($stats['binaan_achievements_count'] ?? 0) > 0)
<div class="mb-5 bg-white rounded-xl border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="space-y-1.5">
        <h3 class="font-bold text-xs sm:text-sm text-slate-900">Pembina Ekstrakurikuler & Prestasi Binaan</h3>
        @if(isset($myExtracurriculars) && $myExtracurriculars->count() > 0)
            <div class="flex flex-wrap gap-1.5">
                @foreach($myExtracurriculars as $ex)
                    <span class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-700 inline-flex items-center gap-1.5">
                        <span>{{ $ex->name }}</span>
                        <span class="text-[10px] text-slate-500 bg-white px-1.5 py-0.2 rounded border border-slate-200">
                            {{ $ex->members_count ?? 0 }} siswa
                        </span>
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-500">Daftar siswa yang mencantumkan Anda sebagai Guru Pembina Lomba.</p>
        @endif
    </div>

    <div class="shrink-0">
        <a href="{{ route('guru.achievements.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-colors">
            <span>Prestasi Siswa Binaan</span>
            <span class="px-1.5 py-0.2 rounded bg-white/20 text-white text-[11px] font-bold">
                {{ $stats['binaan_achievements_count'] ?? 0 }}
            </span>
        </a>
    </div>
</div>
@endif

{{-- ─── 4. Menu Akses Cepat Guru (Grid Kotak Bersih) ───────────────────── --}}
<div class="mb-5">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 px-0.5">Menu Utama Guru</p>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('guru.journal.index') }}"
            class="group p-3.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl transition-colors flex flex-col justify-between min-h-[96px]">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="mt-2.5">
                <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Jurnal Mengajar</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Kelola & cetak PDF</p>
            </div>
        </a>

        <a href="{{ route('guru.grades.index') }}"
            class="group p-3.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl transition-colors flex flex-col justify-between min-h-[96px]">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div class="mt-2.5">
                <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Input Nilai</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Nilai siswa & ekspor</p>
            </div>
        </a>

        <a href="{{ route('guru.conduct.index') }}"
            class="group p-3.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl transition-colors flex flex-col justify-between min-h-[96px]">
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="mt-2.5">
                <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Catat Karakter</p>
                <p class="text-[11px] text-slate-500 mt-0.5">SIPINTER & perilaku</p>
            </div>
        </a>

        <a href="{{ route('guru.attendance.permits') }}"
            class="group p-3.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl transition-colors flex flex-col justify-between min-h-[96px]">
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="mt-2.5">
                <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Persetujuan Izin</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Surat izin & sakit</p>
            </div>
        </a>
    </div>
</div>

{{-- ─── 5. Histori Jurnal Mengajar Saya (Per Minggu) ─────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
    <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
        <h3 class="text-xs sm:text-sm font-bold text-slate-900">
            Histori Jurnal Mengajar (Per Minggu)
        </h3>
        <span class="text-xs font-medium text-slate-500">
            Total: {{ $stats['total_journals'] ?? 0 }} Jurnal
        </span>
    </div>

    <div class="p-4 space-y-3.5">
        @forelse($weeklyJournals as $weekRange => $journals)
            <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                {{-- Header Minggu --}}
                <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800">{{ $weekRange }}</span>
                    <span class="text-[11px] text-slate-600 font-semibold">
                        {{ $journals->count() }} Pertemuan
                    </span>
                </div>

                {{-- List Jurnal --}}
                <div class="divide-y divide-slate-100">
                    @foreach($journals as $j)
                        <div class="p-3.5 hover:bg-slate-50/50 transition-colors">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">
                                        {{ \Illuminate\Support\Carbon::parse($j->date)->translatedFormat('l, d M Y') }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-semibold rounded">
                                        Jam ke-{{ $j->period_display }}
                                    </span>
                                </div>
                                <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                    {{ $j->schoolClass?->name ?? '—' }} {{ $j->subject?->name ? '· '.$j->subject->name : '' }}
                                </span>
                            </div>

                            {{-- TP --}}
                            @if($j->tp || $j->learning_objectives)
                                <div class="mb-1.5 text-xs text-slate-700 bg-slate-50 border border-slate-200/80 px-2.5 py-1 rounded">
                                    <span class="font-semibold">TP:</span>
                                    @if($j->tp?->code)<span class="font-semibold">[{{ $j->tp->code }}]</span>@endif
                                    {{ $j->learning_objectives ?? $j->tp?->description }}
                                </div>
                            @endif

                            {{-- Materi & Aktivitas --}}
                            <div class="text-xs text-slate-600 space-y-0.5">
                                <p><span class="font-semibold text-slate-800">Materi:</span> {{ $j->material }}</p>
                                <p><span class="font-semibold text-slate-800">Aktivitas:</span> {{ $j->activity }}</p>
                                @if($j->notes)
                                    <p class="text-slate-500"><span class="font-semibold text-slate-700">Catatan:</span> {{ $j->notes }}</p>
                                @endif
                            </div>

                            {{-- Siswa Tidak Hadir --}}
                            @if($j->absences->isNotEmpty())
                                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                                    <span class="font-semibold text-rose-600">Tidak Hadir:</span>
                                    @foreach($j->absences as $abs)
                                        <span class="px-2 py-0.5 bg-rose-50 text-rose-700 font-medium rounded border border-rose-200">
                                            {{ $abs->student?->name ?? '—' }} ({{ strtoupper($abs->status) }})
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="py-8 text-center text-xs text-slate-400">
                Belum ada histori jurnal mengajar yang dibuat.
            </div>
        @endforelse
    </div>
</div>

@endsection
