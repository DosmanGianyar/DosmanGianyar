@extends('layouts.guru')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Header Branding SIMAK_DOSMAN & Foto Profil Guru --}}
<div class="mb-6 bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="space-y-1">
        <div class="flex items-center gap-2 mb-1.5">
            <img src="/img/logo_sekolah.png" alt="Logo" class="w-6 h-6 object-contain">
            <span class="text-sm font-black text-indigo-950 tracking-wider">SIMAK_DOSMAN</span>
            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700">Portal Guru</span>
        </div>
        <h2 class="text-xl font-bold text-gray-800">
            Selamat {{ now()->hour < 11 ? 'Pagi' : (now()->hour < 15 ? 'Siang' : 'Sore') }},
            {{ $guru->name }} 👋
        </h2>
        <p class="text-xs text-gray-500 font-medium">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
    </div>

    {{-- Tempat Foto Profil Guru Estetik --}}
    <a href="{{ route('guru.profile') }}" class="flex items-center gap-3.5 bg-slate-50 hover:bg-blue-50/60 border border-slate-200/80 rounded-2xl p-2.5 transition-all group shrink-0">
        <div class="relative shrink-0">
            @if($guru->photo)
                <img src="{{ $guru->photo_url }}" class="w-12 h-12 rounded-xl object-cover ring-2 ring-blue-500/40 group-hover:ring-blue-600 shadow-xs transition-all">
            @else
                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-black text-base ring-2 ring-blue-500/40 group-hover:ring-blue-600 shadow-xs transition-all">
                    {{ $guru->initials }}
                </div>
            @endif
            <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 rounded-full ring-2 ring-white" title="Status Aktif"></span>
        </div>
        <div class="pr-2">
            <p class="text-xs font-black text-slate-900 group-hover:text-blue-600 transition-colors">{{ $guru->name }}</p>
            <p class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $guru->subject ?? 'Guru Pengajar' }}</p>
            <span class="inline-flex items-center gap-1 text-[10.5px] font-bold text-blue-600 mt-1">
                Edit Profil
                <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        </div>
    </a>
</div>

{{-- ─── Jadwal Mengajar Guru (Hari Ini & Perminggu) ───────────────── --}}
<div x-data="{ showWeeklyModal: false, activeTab: {{ (int) now()->dayOfWeekIso <= 6 ? (int) now()->dayOfWeekIso : 1 }} }}" class="mb-5">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Card Header --}}
        <div class="px-4 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between cursor-pointer"
             @click="showWeeklyModal = true">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-white flex items-center gap-2">
                        Jadwal Mengajar Hari Ini ({{ $todayDayName }})
                    </h3>
                    <p class="text-[11px] text-blue-100">Klik untuk melihat jadwal lengkap perminggu</p>
                </div>
            </div>
            <button type="button" @click.stop="showWeeklyModal = true"
                class="px-3 py-1.5 bg-white/20 hover:bg-white/30 backdrop-blur-md border border-white/30 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5">
                <span>🗓️ Jadwal Perminggu</span>
            </button>
        </div>

        {{-- Today's Schedule List --}}
        <div class="p-4">
            @if(isset($todaySchedulesMerged) && $todaySchedulesMerged->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    @foreach($todaySchedulesMerged as $sch)
                        @php $isFilled = $sch['is_filled']; @endphp
                        <div class="p-4 rounded-2xl flex flex-col justify-between transition-all {{ $isFilled ? 'bg-slate-100 border-2 border-slate-200 text-slate-700 opacity-90' : 'bg-blue-50/90 border-2 border-blue-200 hover:border-blue-400 shadow-xs' }}">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @foreach($sch['periods'] as $p)
                                            <span class="px-2.5 py-1 text-xs font-black rounded-lg {{ $isFilled ? 'bg-slate-500 text-white' : 'bg-blue-600 text-white' }}">
                                                Jam ke-{{ $p }}
                                            </span>
                                        @endforeach
                                        <span class="text-sm font-black {{ $isFilled ? 'text-slate-600' : 'text-gray-900' }} ml-1">
                                            {{ $sch['start_time'] }} - {{ $sch['end_time'] }}
                                        </span>
                                    </div>
                                    @if($isFilled)
                                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold rounded-lg flex items-center gap-1 shrink-0">
                                            ✓ Jurnal Terisi
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 border border-amber-300 text-xs font-bold rounded-lg shrink-0">
                                            Belum Terisi
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <span class="text-base sm:text-lg font-black text-indigo-950 flex items-center gap-1.5">
                                        🏫 Kelas {{ $sch['class_name'] }}
                                    </span>
                                    @if(!empty($sch['room']))
                                        <span class="text-xs font-bold text-gray-600 bg-white/80 px-2 py-0.5 rounded-md border border-gray-200">
                                            📍 {{ $sch['room'] }}
                                        </span>
                                    @endif
                                </div>

                                <p class="text-sm font-bold leading-tight {{ $isFilled ? 'text-slate-700 line-through decoration-slate-400' : 'text-blue-900' }}">
                                    📚 {{ $sch['subject_name'] }}
                                </p>
                            </div>

                            <div class="mt-3.5 pt-3 border-t {{ $isFilled ? 'border-slate-200' : 'border-blue-200' }} flex items-center justify-between">
                                @if($isFilled)
                                    <span class="text-xs font-bold text-emerald-700 flex items-center gap-1">
                                        ✓ Jurnal Terisi
                                    </span>
                                    <a href="{{ route('guru.journal.index') }}"
                                       class="text-xs font-bold text-slate-700 hover:text-slate-900 underline">
                                        Lihat Jurnal →
                                    </a>
                                @else
                                    <span class="text-xs font-semibold text-amber-700">
                                        Perlu Jurnal
                                    </span>
                                    <a href="{{ route('guru.journal.create', ['class_id' => $sch['class_id'], 'subject_id' => $sch['subject_id'], 'period' => $sch['period_start']]) }}"
                                       class="inline-flex items-center gap-1 text-xs font-bold text-blue-800 bg-blue-100 hover:bg-blue-200 px-3 py-1.5 rounded-xl transition-colors shadow-xs">
                                        + Buat Jurnal
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-4 text-center">
                    <p class="text-xs font-medium text-gray-500">Tidak ada jadwal mengajar pada hari {{ $todayDayName }}.</p>
                    <button type="button" @click="showWeeklyModal = true"
                        class="mt-2 text-xs font-bold text-blue-600 hover:text-blue-700 underline">
                        Lihat Jadwal Mengajar Hari Lain (Perminggu) →
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Modal / Full View Jadwal Perminggu ───────────────────────── --}}
    <template x-teleport="body">
        <div x-show="showWeeklyModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-100"
                @click.outside="showWeeklyModal = false">

                {{-- Modal Header --}}
                <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xl">📅</span>
                        <div>
                            <h3 class="font-bold text-base text-white">Jadwal Mengajar Perminggu</h3>
                            <p class="text-xs text-slate-300">Tahun Ajaran {{ $todaySchedules->first()?->academic_year ?? '2025/2026' }}</p>
                        </div>
                    </div>
                    <button @click="showWeeklyModal = false"
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
                        ✕
                    </button>
                </div>

                {{-- Day Tabs --}}
                <div class="px-5 py-3 bg-slate-50 border-b border-gray-200 flex gap-2 overflow-x-auto shrink-0">
                    @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
                        @php
                            $hasClasses = isset($weeklySchedulesMerged[$dNum]) && $weeklySchedulesMerged[$dNum]->count() > 0;
                        @endphp
                        <button type="button" @click="activeTab = {{ $dNum }}"
                            :class="activeTab === {{ $dNum }} ? 'bg-blue-600 text-white shadow-sm font-bold' : 'bg-white text-gray-700 hover:bg-gray-100 font-medium'"
                            class="px-3.5 py-2 text-xs rounded-xl border border-gray-200 transition-all flex items-center gap-1.5 shrink-0">
                            <span>{{ $dName }}</span>
                            @if($hasClasses)
                                <span class="w-5 h-5 rounded-full text-[10px] flex items-center justify-center"
                                    :class="activeTab === {{ $dNum }} ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-700'">
                                    {{ $weeklySchedulesMerged[$dNum]->count() }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>

                {{-- Modal Body / Content per Tab --}}
                <div class="p-5 overflow-y-auto flex-1 space-y-4">
                    @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
                        <div x-show="activeTab === {{ $dNum }}">
                            @if(isset($weeklySchedulesMerged[$dNum]) && $weeklySchedulesMerged[$dNum]->isNotEmpty())
                                <div class="overflow-x-auto border border-gray-200 rounded-xl shadow-sm">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                                            <tr>
                                                <th class="px-4 py-3 text-center w-28">Jam Ke</th>
                                                <th class="px-4 py-3 w-36">Waktu</th>
                                                <th class="px-4 py-3">Mata Pelajaran</th>
                                                <th class="px-4 py-3">Kelas</th>
                                                <th class="px-4 py-3">Ruang</th>
                                                <th class="px-4 py-3 text-center">Status Jurnal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @foreach($weeklySchedulesMerged[$dNum] as $sch)
                                                @php $isFilledW = $sch['is_filled']; @endphp
                                                <tr class="transition-colors {{ $isFilledW ? 'bg-slate-100/80 text-slate-500' : 'hover:bg-blue-50/40' }}">
                                                    <td class="px-4 py-3 text-center">
                                                         <div class="flex items-center justify-center gap-1 flex-wrap">
                                                             @foreach($sch['periods'] as $p)
                                                                 <span class="px-2 py-0.5 rounded-md font-extrabold text-xs {{ $isFilledW ? 'bg-slate-300 text-slate-700' : 'bg-blue-100 text-blue-800' }}">
                                                                     Jam ke-{{ $p }}
                                                                 </span>
                                                             @endforeach
                                                         </div>
                                                    </td>
                                                    <td class="px-4 py-3 font-bold text-sm {{ $isFilledW ? 'text-slate-600' : 'text-gray-900' }}">
                                                        {{ $sch['start_time'] }} - {{ $sch['end_time'] }}
                                                    </td>
                                                    <td class="px-4 py-3 font-bold {{ $isFilledW ? 'text-slate-700 line-through' : 'text-gray-900' }}">
                                                        {{ $sch['subject_name'] }}
                                                    </td>
                                                    <td class="px-4 py-3 font-black text-sm {{ $isFilledW ? 'text-slate-600' : 'text-blue-700' }}">
                                                        {{ $sch['class_name'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-500 font-medium">
                                                        {{ $sch['room'] ?? '—' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @if($isFilledW)
                                                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-md">✓ Terisi</span>
                                                        @elseif($dNum === (int) now()->dayOfWeekIso)
                                                            <a href="{{ route('guru.journal.create', ['class_id' => $sch['class_id'], 'subject_id' => $sch['subject_id'], 'period' => $sch['period_start']]) }}"
                                                               class="px-2.5 py-1 bg-blue-600 text-white hover:bg-blue-700 text-xs font-bold rounded-md inline-block">
                                                                + Buat Jurnal
                                                            </a>
                                                        @else
                                                            <span class="text-gray-400 text-xs">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="py-12 text-center text-sm text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                    <span>☕ Tidak ada jadwal mengajar pada hari {{ $dName }}.</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Modal Footer --}}
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end shrink-0">
                    <button type="button" @click="showWeeklyModal = false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold rounded-xl transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

{{-- ─── Pembina Ekstrakurikuler Banner ─────────────────────────────── --}}
@if(isset($myExtracurriculars) && $myExtracurriculars->count() > 0)
<div class="mb-5 bg-gradient-to-r from-indigo-900 via-indigo-800 to-blue-900 rounded-2xl p-4 text-white shadow-md">
    <div class="flex items-center gap-2.5 mb-2">
        <span class="text-xl">🎗️</span>
        <div>
            <h3 class="font-bold text-sm text-indigo-100 uppercase tracking-wide">Pembina Ekstrakurikuler</h3>
            <p class="text-[11px] text-indigo-200">Anda bertugas sebagai Pembina pada {{ $myExtracurriculars->count() }} Ekstrakurikuler:</p>
        </div>
    </div>
    <div class="flex flex-wrap gap-2 mt-2.5">
        @foreach($myExtracurriculars as $ex)
            <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-xl px-3 py-1.5 text-xs font-bold flex items-center gap-2">
                <span>🏆 {{ $ex->name }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ─── Stat Cards & Smart Hub ───────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">

    <div class="bg-white rounded-2xl p-4.5 shadow-2xs border border-slate-200/80 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Siswa Bimbingan</span>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total_students'] }}</p>
            <p class="text-xs font-bold text-blue-600 mt-1 flex items-center gap-1">
                <span>🏫</span>
                <span>{{ $guru->homeroomClass?->name ? 'Wali Kelas ' . $guru->homeroomClass->name : 'Guru Pengajar' }}</span>
            </p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xl shadow-2xs">
            👥
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4.5 shadow-2xs border border-slate-200/80 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Jurnal Mengajar Saya</span>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total_journals'] }}</p>
            <a href="{{ route('guru.journal.create') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 mt-1 flex items-center gap-1">
                <span>+ Buat Jurnal Baru →</span>
            </a>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xl shadow-2xs">
            📖
        </div>
    </div>

</div>

{{-- ─── Smart Action Hub Guru ────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl p-4.5 shadow-2xs border border-slate-200/80 mb-6">
    <div class="flex items-center justify-between mb-3.5">
        <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
            <span>🚀</span>
            <span>Akses Pintar & Layanan Guru</span>
        </h3>
        <span class="text-xs font-bold text-slate-400">SIMAK_DOSMAN</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('guru.journal.index') }}"
            class="group p-3 bg-slate-50 hover:bg-blue-50/80 border border-slate-200/70 hover:border-blue-300 rounded-xl transition-all flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center text-sm font-bold shrink-0 group-hover:scale-105 transition-transform">
                📖
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-black text-slate-900 group-hover:text-blue-700 truncate">Jurnal Mengajar</p>
                <p class="text-[10.5px] text-slate-500 font-medium truncate">Kelola & Cetak PDF</p>
            </div>
        </a>

        <a href="{{ route('guru.grades.index') }}"
            class="group p-3 bg-slate-50 hover:bg-emerald-50/80 border border-slate-200/70 hover:border-emerald-300 rounded-xl transition-all flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shrink-0 group-hover:scale-105 transition-transform">
                📝
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-black text-slate-900 group-hover:text-emerald-700 truncate">Input Nilai</p>
                <p class="text-[10.5px] text-slate-500 font-medium truncate">Nilai Siswa & Export</p>
            </div>
        </a>

        <a href="{{ route('guru.conduct.index') }}"
            class="group p-3 bg-slate-50 hover:bg-purple-50/80 border border-slate-200/70 hover:border-purple-300 rounded-xl transition-all flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-purple-600 text-white flex items-center justify-center text-sm font-bold shrink-0 group-hover:scale-105 transition-transform">
                ⭐
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-black text-slate-900 group-hover:text-purple-700 truncate">Catat Karakter</p>
                <p class="text-[10.5px] text-slate-500 font-medium truncate">Prestasi & Perilaku</p>
            </div>
        </a>

        <a href="{{ route('guru.attendance.permits') }}"
            class="group p-3 bg-slate-50 hover:bg-amber-50/80 border border-slate-200/70 hover:border-amber-300 rounded-xl transition-all flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-amber-600 text-white flex items-center justify-center text-sm font-bold shrink-0 group-hover:scale-105 transition-transform">
                ✉️
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-black text-slate-900 group-hover:text-amber-700 truncate">Approval Izin</p>
                <p class="text-[10.5px] text-slate-500 font-medium truncate">Surat Izin & Sakit</p>
            </div>
        </a>
    </div>
</div>

    {{-- ─── Histori Jurnal Mengajar Saya (Per Minggu) ───────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-gray-100 bg-slate-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Histori Jurnal Mengajar Saya (Per Minggu)
            </h3>
            <span class="text-xs font-semibold px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full">
                Total: {{ $stats['total_journals'] ?? 0 }} Jurnal
            </span>
        </div>

        <div class="p-4 space-y-4">
            @forelse($weeklyJournals as $weekRange => $journals)
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                    {{-- Header Minggu --}}
                    <div class="bg-blue-50/70 px-4 py-2.5 border-b border-blue-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span class="text-xs font-bold text-blue-900">{{ $weekRange }}</span>
                        </div>
                        <span class="text-xs text-blue-700 font-medium bg-blue-100/80 px-2 py-0.5 rounded-md">
                            {{ $journals->count() }} Pertemuan
                        </span>
                    </div>

                    {{-- List Jurnal --}}
                    <div class="divide-y divide-gray-100">
                        @foreach($journals as $j)
                            <div class="p-3.5 hover:bg-gray-50/80 transition-colors">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-gray-900">
                                            {{ \Illuminate\Support\Carbon::parse($j->date)->translatedFormat('l, d M Y') }}
                                        </span>
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-[11px] font-semibold rounded-md">
                                            Jam ke-{{ $j->period_display }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">
                                        {{ $j->schoolClass?->name ?? '—' }} {{ $j->subject?->name ? '• '.$j->subject->name : '' }}
                                    </span>
                                </div>

                                {{-- TP --}}
                                @if($j->tp || $j->learning_objectives)
                                    <div class="mb-1 text-xs text-indigo-700 font-medium bg-indigo-50/70 px-2.5 py-1 rounded-md">
                                        <span class="font-bold">TP:</span>
                                        @if($j->tp?->code)<span class="font-semibold">[{{ $j->tp->code }}]</span>@endif
                                        {{ $j->learning_objectives ?? $j->tp?->description }}
                                    </div>
                                @endif

                                {{-- Materi & Aktivitas --}}
                                <div class="text-xs text-gray-700 space-y-0.5">
                                    <p><span class="font-semibold text-gray-900">Materi:</span> {{ $j->material }}</p>
                                    <p><span class="font-semibold text-gray-900">Aktivitas:</span> {{ $j->activity }}</p>
                                    @if($j->notes)
                                        <p class="text-gray-500 italic"><span class="font-semibold not-italic text-gray-700">Catatan:</span> {{ $j->notes }}</p>
                                    @endif
                                </div>

                                {{-- Siswa Tidak Hadir --}}
                                @if($j->absences->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                                        <span class="font-semibold text-red-600">Siswa Tidak Hadir:</span>
                                        @foreach($j->absences as $abs)
                                            <span class="px-2 py-0.5 bg-red-50 text-red-700 font-medium rounded border border-red-100">
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
                <div class="py-8 text-center text-sm text-gray-400">
                    Belum ada histori jurnal mengajar yang dibuat.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
