@extends('layouts.guru')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ─── 1. Header Branding SIMAK_DOSMAN & Foto Profil Guru ───────────── --}}
<div class="relative rounded-3xl p-5 mb-5 text-white shadow-lg shadow-blue-950/15 overflow-hidden"
     style="background: linear-gradient(135deg, #0b1e4f 0%, #123075 50%, #19439e 100%); border: 1px solid rgba(255,255,255,0.15);">
    
    {{-- Aksen Dekoratif Halus --}}
    <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-blue-400/10 blur-2xl pointer-events-none"></div>
    <div class="absolute -left-10 -top-10 w-36 h-36 rounded-full bg-indigo-400/10 blur-2xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-white/90 border border-white/20 shadow-2xs">
                    <img src="/img/logo_sekolah.png" alt="Logo" class="w-4 h-4 object-contain">
                    <span>SIMAK_DOSMAN</span>
                </span>
                <span class="text-[10px] font-extrabold tracking-wider uppercase px-2.5 py-1 rounded-full bg-amber-400/20 text-amber-300 border border-amber-300/30">
                    Portal Guru
                </span>
            </div>

            <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-tight text-white drop-shadow-xs">
                Selamat {{ now()->hour < 11 ? 'Pagi' : (now()->hour < 15 ? 'Siang' : 'Sore') }}, {{ $guru->name }}! 👋
            </h2>
            <p class="text-xs text-blue-100/90 font-medium flex items-center gap-1.5">
                <span>📅 {{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                <span class="text-white/40">·</span>
                <span>{{ $guru->subject ?? 'Guru Pengajar' }}</span>
                @if($guru->homeroomClass)
                    <span class="text-white/40">·</span>
                    <span class="text-amber-300 font-bold">Wali Kelas {{ $guru->homeroomClass->name }}</span>
                @endif
            </p>
        </div>

        {{-- Profil Singkat Guru (Quick Link) --}}
        <a href="{{ route('guru.profile') }}"
           class="flex items-center gap-3 bg-white/10 hover:bg-white/15 backdrop-blur-md border border-white/20 rounded-2xl p-2.5 px-3.5 transition-all group shrink-0 shadow-2xs">
            <div class="relative shrink-0">
                @if($guru->photo)
                    <img src="{{ $guru->photo_url }}" class="w-12 h-12 rounded-xl object-cover ring-2 ring-white/50 group-hover:ring-white shadow-xs transition-all">
                @else
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-black text-base ring-2 ring-white/50 group-hover:ring-white shadow-xs transition-all">
                        {{ $guru->initials }}
                    </div>
                @endif
                <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 rounded-full ring-2 ring-slate-900" title="Status Aktif"></span>
            </div>
            <div class="pr-1 text-left">
                <p class="text-xs font-bold text-white group-hover:text-blue-200 transition-colors truncate max-w-[140px] sm:max-w-xs">{{ $guru->name }}</p>
                <p class="text-[11px] text-blue-200 font-medium truncate mt-0.5">NIP {{ $guru->nip ?? '—' }}</p>
                <span class="inline-flex items-center gap-1 text-[10.5px] font-bold text-amber-300 mt-1">
                    Edit Profil <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                </span>
            </div>
        </a>
    </div>
</div>

{{-- ─── 2. Jadwal Mengajar Guru (Hari Ini & Perminggu) ───────────────── --}}
<div x-data="{ showWeeklyModal: false, activeTab: {{ (int) now()->dayOfWeekIso <= 6 ? (int) now()->dayOfWeekIso : 1 }} }}" class="mb-5">
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        {{-- Card Header --}}
        <div class="px-4 py-3.5 bg-slate-900 text-white flex items-center justify-between cursor-pointer border-b border-slate-800"
             @click="showWeeklyModal = true">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-white flex items-center gap-2">
                        <span>Jadwal Mengajar Hari Ini ({{ $todayDayName }})</span>
                    </h3>
                    <p class="text-[11px] text-slate-300">Ketuk untuk membuka jadwal lengkap perminggu</p>
                </div>
            </div>
            <button type="button" @click.stop="showWeeklyModal = true"
                class="px-3 py-1.5 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5">
                <span>🗓️ Jadwal Perminggu</span>
            </button>
        </div>

        {{-- Today's Schedule List --}}
        <div class="p-4 sm:p-5">
            @if(isset($todaySchedulesMerged) && $todaySchedulesMerged->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    @foreach($todaySchedulesMerged as $sch)
                        @php $isFilled = $sch['is_filled']; @endphp
                        <div class="p-4 rounded-2xl flex flex-col justify-between transition-all {{ $isFilled ? 'bg-slate-50 border border-slate-200/80 text-slate-700' : 'bg-blue-50/60 border border-blue-200 hover:border-blue-300 shadow-2xs' }}">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @foreach($sch['periods'] as $p)
                                            <span class="px-2.5 py-0.5 text-xs font-black rounded-lg {{ $isFilled ? 'bg-slate-200 text-slate-700' : 'bg-blue-600 text-white shadow-2xs' }}">
                                                Jam ke-{{ $p }}
                                            </span>
                                        @endforeach
                                        <span class="text-xs font-bold {{ $isFilled ? 'text-slate-600' : 'text-slate-800' }} ml-1">
                                            {{ $sch['start_time'] }} - {{ $sch['end_time'] }}
                                        </span>
                                    </div>
                                    @if($isFilled)
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold rounded-full flex items-center gap-1 shrink-0">
                                            ✓ Jurnal Terisi
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-bold rounded-full shrink-0">
                                            Perlu Jurnal
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <span class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-1.5">
                                        🏫 Kelas {{ $sch['class_name'] }}
                                    </span>
                                    @if(!empty($sch['room']))
                                        <span class="text-xs font-semibold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200 shadow-2xs">
                                            📍 {{ $sch['room'] }}
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs sm:text-sm font-semibold leading-tight {{ $isFilled ? 'text-slate-500' : 'text-blue-900' }}">
                                    📚 {{ $sch['subject_name'] }}
                                </p>
                            </div>

                            <div class="mt-3.5 pt-3 border-t {{ $isFilled ? 'border-slate-200' : 'border-blue-100' }} flex items-center justify-between">
                                @if($isFilled)
                                    <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                                        Tersimpan
                                    </span>
                                    <a href="{{ route('guru.journal.index') }}"
                                       class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">
                                        Lihat Jurnal →
                                    </a>
                                @else
                                    <span class="text-xs font-medium text-amber-700">
                                        Belum Tercatat
                                    </span>
                                    <a href="{{ route('guru.journal.create', ['class_id' => $sch['class_id'], 'subject_id' => $sch['subject_id'], 'period' => $sch['period_start']]) }}"
                                       class="inline-flex items-center gap-1 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-xl shadow-xs transition-colors">
                                        + Buat Jurnal
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-6 text-center">
                    <span class="text-2xl block mb-1">☕</span>
                    <p class="text-xs font-medium text-slate-500">Tidak ada jadwal mengajar pada hari {{ $todayDayName }}.</p>
                    <button type="button" @click="showWeeklyModal = true"
                        class="mt-2 text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                        Lihat Jadwal Mengajar Hari Lain (Perminggu) →
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Modal Jadwal Perminggu ───────────────────────────────────── --}}
    <template x-teleport="body">
        <div x-show="showWeeklyModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-white/20"
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
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Day Tabs --}}
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex gap-2 overflow-x-auto shrink-0">
                    @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
                        @php
                            $hasClasses = isset($weeklySchedulesMerged[$dNum]) && $weeklySchedulesMerged[$dNum]->count() > 0;
                        @endphp
                        <button type="button" @click="activeTab = {{ $dNum }}"
                            :class="activeTab === {{ $dNum }} ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-white text-slate-700 hover:bg-slate-100 font-medium'"
                            class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
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
                                <div class="overflow-x-auto border border-slate-200 rounded-2xl shadow-xs">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                                            <tr>
                                                <th class="px-4 py-3 text-center w-28">Jam Ke</th>
                                                <th class="px-4 py-3 w-36">Waktu</th>
                                                <th class="px-4 py-3">Mata Pelajaran</th>
                                                <th class="px-4 py-3">Kelas</th>
                                                <th class="px-4 py-3">Ruang</th>
                                                <th class="px-4 py-3 text-center">Status Jurnal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach($weeklySchedulesMerged[$dNum] as $sch)
                                                @php $isFilledW = $sch['is_filled']; @endphp
                                                <tr class="transition-colors {{ $isFilledW ? 'bg-slate-50/70 text-slate-500' : 'hover:bg-blue-50/40' }}">
                                                    <td class="px-4 py-3 text-center">
                                                         <div class="flex items-center justify-center gap-1 flex-wrap">
                                                             @foreach($sch['periods'] as $p)
                                                                 <span class="px-2 py-0.5 rounded-md font-extrabold text-xs {{ $isFilledW ? 'bg-slate-200 text-slate-700' : 'bg-blue-100 text-blue-800' }}">
                                                                     Jam ke-{{ $p }}
                                                                 </span>
                                                             @endforeach
                                                         </div>
                                                    </td>
                                                    <td class="px-4 py-3 font-bold text-xs {{ $isFilledW ? 'text-slate-600' : 'text-slate-900' }}">
                                                        {{ $sch['start_time'] }} - {{ $sch['end_time'] }}
                                                    </td>
                                                    <td class="px-4 py-3 font-bold {{ $isFilledW ? 'text-slate-500 line-through' : 'text-slate-900' }}">
                                                        {{ $sch['subject_name'] }}
                                                    </td>
                                                    <td class="px-4 py-3 font-black text-xs {{ $isFilledW ? 'text-slate-600' : 'text-blue-700' }}">
                                                        {{ $sch['class_name'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-500 font-medium">
                                                        {{ $sch['room'] ?? '—' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @if($isFilledW)
                                                            <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-full">✓ Terisi</span>
                                                        @elseif($dNum === (int) now()->dayOfWeekIso)
                                                            <a href="{{ route('guru.journal.create', ['class_id' => $sch['class_id'], 'subject_id' => $sch['subject_id'], 'period' => $sch['period_start']]) }}"
                                                               class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl inline-block shadow-xs transition-colors">
                                                                + Buat Jurnal
                                                            </a>
                                                        @else
                                                            <span class="text-slate-400 text-xs">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="py-12 text-center text-sm text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    <span>☕ Tidak ada jadwal mengajar pada hari {{ $dName }}.</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Modal Footer --}}
                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex justify-end shrink-0">
                    <button type="button" @click="showWeeklyModal = false"
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

{{-- ─── 3. Pembina Ekstrakurikuler & Juara Siswa Binaan Banner ─────────────────────────────── --}}
@if((isset($myExtracurriculars) && $myExtracurriculars->count() > 0) || ($stats['binaan_achievements_count'] ?? 0) > 0)
<div class="mb-5 bg-gradient-to-r from-indigo-950 via-slate-900 to-blue-950 rounded-2xl p-4 text-white shadow-xs border border-indigo-900/40">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2.5 mb-2">
                <span class="text-xl">🎗️</span>
                <div>
                    <h3 class="font-bold text-sm text-indigo-100 uppercase tracking-wide">Pembina Ekstrakurikuler & Prestasi Binaan</h3>
                    <p class="text-[11px] text-indigo-200">
                        @if(isset($myExtracurriculars) && $myExtracurriculars->count() > 0)
                            Anda bertugas sebagai Pembina pada {{ $myExtracurriculars->count() }} Ekstrakurikuler
                        @else
                            Daftar siswa yang memilih Anda sebagai Guru Pembina Lomba
                        @endif
                    </p>
                </div>
            </div>
            @if(isset($myExtracurriculars) && $myExtracurriculars->count() > 0)
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($myExtracurriculars as $ex)
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl px-3 py-1.5 text-xs font-bold flex items-center gap-2 shadow-2xs">
                            <span>🏅 {{ $ex->name }}</span>
                            <span class="text-[10px] text-blue-200 font-semibold bg-white/10 px-2 py-0.2 rounded-full">
                                {{ $ex->members_count ?? 0 }} Anggota
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="shrink-0 flex flex-wrap items-center gap-2">
            <a href="{{ route('guru.achievements.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-sm transition">
                <span>🏆 Lihat Juara Siswa Binaan</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-950/20 text-slate-950 text-[11px] font-black">
                    {{ $stats['binaan_achievements_count'] ?? 0 }}
                </span>
            </a>
        </div>
    </div>
</div>
@endif

{{-- ─── 4. Stat Cards (Grid Simetris) ─────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
    <div class="bg-white rounded-2xl p-4.5 shadow-xs border border-slate-200/80 flex items-center justify-between">
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

    <div class="bg-white rounded-2xl p-4.5 shadow-xs border border-slate-200/80 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Jurnal Mengajar Saya</span>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total_journals'] }}</p>
            <a href="{{ route('guru.journal.create') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 mt-1 flex items-center gap-1 transition-colors">
                <span>+ Buat Jurnal Baru →</span>
            </a>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xl shadow-2xs">
            📖
        </div>
    </div>
</div>

{{-- ─── 5. Smart Action Hub Guru ─────────────────────────────────────── --}}
<div class="bg-white rounded-2xl p-4.5 shadow-xs border border-slate-200/80 mb-5">
    <div class="flex items-center justify-between mb-3.5">
        <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
            <span>🚀</span>
            <span>Akses Pintar & Layanan Guru</span>
        </h3>
        <span class="text-xs font-semibold text-slate-400">SIMAK_DOSMAN</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('guru.journal.index') }}"
            class="group p-3 bg-slate-50/80 hover:bg-blue-50/80 border border-slate-200/70 hover:border-blue-300 rounded-2xl transition-all flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm font-bold shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                📖
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-black text-slate-900 group-hover:text-blue-700 truncate">Jurnal Mengajar</p>
                <p class="text-[10.5px] text-slate-500 font-medium truncate">Kelola & Cetak PDF</p>
            </div>
        </a>

        <a href="{{ route('guru.grades.index') }}"
            class="group p-3 bg-slate-50/80 hover:bg-emerald-50/80 border border-slate-200/70 hover:border-emerald-300 rounded-2xl transition-all flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                📝
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-black text-slate-900 group-hover:text-emerald-700 truncate">Input Nilai</p>
                <p class="text-[10.5px] text-slate-500 font-medium truncate">Nilai Siswa & Export</p>
            </div>
        </a>

        <a href="{{ route('guru.conduct.index') }}"
            class="group p-3 bg-slate-50/80 hover:bg-purple-50/80 border border-slate-200/70 hover:border-purple-300 rounded-2xl transition-all flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm font-bold shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                ⭐
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-black text-slate-900 group-hover:text-purple-700 truncate">Catat Karakter</p>
                <p class="text-[10.5px] text-slate-500 font-medium truncate">Prestasi & Perilaku</p>
            </div>
        </a>

        <a href="{{ route('guru.attendance.permits') }}"
            class="group p-3 bg-slate-50/80 hover:bg-amber-50/80 border border-slate-200/70 hover:border-amber-300 rounded-2xl transition-all flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-600 text-white flex items-center justify-center text-sm font-bold shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                ✉️
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-black text-slate-900 group-hover:text-amber-700 truncate">Approval Izin</p>
                <p class="text-[10.5px] text-slate-500 font-medium truncate">Surat Izin & Sakit</p>
            </div>
        </a>
    </div>
</div>

{{-- ─── 6. Histori Jurnal Mengajar Saya (Per Minggu) ─────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden mb-6">
    <div class="px-4 py-3.5 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Histori Jurnal Mengajar Saya (Per Minggu)</span>
        </h3>
        <span class="text-xs font-semibold px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full">
            Total: {{ $stats['total_journals'] ?? 0 }} Jurnal
        </span>
    </div>

    <div class="p-4 sm:p-5 space-y-4">
        @forelse($weeklyJournals as $weekRange => $journals)
            <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-2xs">
                {{-- Header Minggu --}}
                <div class="bg-slate-50/90 px-4 py-2.5 border-b border-slate-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span class="text-xs font-bold text-slate-900">{{ $weekRange }}</span>
                    </div>
                    <span class="text-xs text-blue-700 font-semibold bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                        {{ $journals->count() }} Pertemuan
                    </span>
                </div>

                {{-- List Jurnal --}}
                <div class="divide-y divide-slate-100">
                    @foreach($journals as $j)
                        <div class="p-3.5 hover:bg-slate-50/60 transition-colors">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">
                                        {{ \Illuminate\Support\Carbon::parse($j->date)->translatedFormat('l, d M Y') }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-semibold rounded-md">
                                        Jam ke-{{ $j->period_display }}
                                    </span>
                                </div>
                                <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                                    {{ $j->schoolClass?->name ?? '—' }} {{ $j->subject?->name ? '• '.$j->subject->name : '' }}
                                </span>
                            </div>

                            {{-- TP --}}
                            @if($j->tp || $j->learning_objectives)
                                <div class="mb-1 text-xs text-indigo-800 font-medium bg-indigo-50/70 border border-indigo-100 px-2.5 py-1 rounded-md">
                                    <span class="font-bold">TP:</span>
                                    @if($j->tp?->code)<span class="font-semibold">[{{ $j->tp->code }}]</span>@endif
                                    {{ $j->learning_objectives ?? $j->tp?->description }}
                                </div>
                            @endif

                            {{-- Materi & Aktivitas --}}
                            <div class="text-xs text-slate-700 space-y-0.5">
                                <p><span class="font-semibold text-slate-900">Materi:</span> {{ $j->material }}</p>
                                <p><span class="font-semibold text-slate-900">Aktivitas:</span> {{ $j->activity }}</p>
                                @if($j->notes)
                                    <p class="text-slate-500 italic"><span class="font-semibold not-italic text-slate-700">Catatan:</span> {{ $j->notes }}</p>
                                @endif
                            </div>

                            {{-- Siswa Tidak Hadir --}}
                            @if($j->absences->isNotEmpty())
                                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                                    <span class="font-semibold text-rose-600">Siswa Tidak Hadir:</span>
                                    @foreach($j->absences as $abs)
                                        <span class="px-2 py-0.5 bg-rose-50 text-rose-700 font-medium rounded-md border border-rose-100">
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
            <div class="py-8 text-center text-sm text-slate-400">
                <span class="text-2xl block mb-1">📖</span>
                Belum ada histori jurnal mengajar yang dibuat.
            </div>
        @endforelse
    </div>
</div>

@endsection
