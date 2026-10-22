@extends('layouts.siswa')

@section('title', 'Kesiswaan')
@section('page-title', 'Kesiswaan')

@section('content')

{{-- ─── Header Info Kesiswaan ────────────────────────────────────────── --}}
<div class="mb-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-slate-800">Pusat Layanan Kesiswaan</h1>
            <p class="text-xs text-slate-500">Prestasi, perizinan mandiri & aktivitas kesiswaan</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100/80">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
            SIMAK DOSMAN Kesiswaan
        </span>
    </div>
</div>

{{-- ─── Spotlight Card: Prestasi Siswa ───────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 mb-4">
    <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-50 border border-amber-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Capaian & Prestasi</p>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <span class="text-2xl font-black text-slate-800 leading-none">{{ $achievementSummary['approved'] }}</span>
                    <span class="text-xs font-medium text-slate-500">Prestasi Terverifikasi</span>
                </div>
            </div>
        </div>

        @if($achievementSummary['pending'] > 0)
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200/80 px-2.5 py-1 rounded-full shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                {{ $achievementSummary['pending'] }} Menunggu
            </span>
        @endif
    </div>

    @if($lastAchievement)
        <div class="bg-slate-50/80 rounded-xl p-2.5 mb-3 border border-slate-100 flex items-center gap-2">
            <span class="text-[10px] font-bold text-amber-700 bg-amber-100/70 px-1.5 py-0.5 rounded shrink-0">Terbaru</span>
            <p class="text-xs text-slate-600 truncate font-medium">{{ $lastAchievement->title }}</p>
        </div>
    @endif

    {{-- Action Buttons Prestasi --}}
    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100">
        <a href="{{ route('siswa.achievements.create') }}"
            class="flex items-center justify-center gap-2 py-2.5 px-3 bg-amber-500 hover:bg-amber-600 active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>+ Laporkan Prestasi</span>
        </a>
        <a href="{{ route('siswa.achievements.index') }}"
            class="flex items-center justify-center gap-2 py-2.5 px-3 bg-slate-100 hover:bg-slate-200/80 active:scale-[0.98] text-slate-700 text-xs font-semibold rounded-xl transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>Daftar Prestasi</span>
        </a>
    </div>

    <div class="mt-2.5 text-center">
        <a href="{{ route('siswa.achievements.report') }}" class="text-[11px] text-blue-600 hover:text-blue-700 font-medium inline-flex items-center gap-1">
            <span>Lihat Rekapitulasi Prestasi Sekolah</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>

{{-- ─── 3 Menu Pintas Kesiswaan (Ekstra, Tatib, BK) ───────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-4">
    {{-- Ekstrakurikuler --}}
    <a href="{{ route('siswa.extracurricular.index') }}"
        class="group flex items-center justify-between p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/80 hover:border-violet-300 transition-all">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 bg-violet-50 text-violet-600 rounded-xl flex items-center justify-center shrink-0 border border-violet-100 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-slate-800">Ekstrakurikuler</p>
                @if(isset($myExtracurricularRoles) && $myExtracurricularRoles->count() > 0)
                    <p class="text-[11px] text-violet-600 font-semibold truncate">{{ $myExtracurricularRoles->count() }} Kegiatan Aktif</p>
                @else
                    <p class="text-[11px] text-slate-400 truncate">Daftar & info ekstra</p>
                @endif
            </div>
        </div>
        <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-500 group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

    {{-- Tata Tertib Sekolah --}}
    <a href="{{ route('siswa.tata-tertib') }}"
        class="group flex items-center justify-between p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/80 hover:border-blue-300 transition-all">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shrink-0 border border-blue-100 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-slate-800">Tata Tertib</p>
                <p class="text-[11px] text-blue-600 font-semibold truncate">Aturan & Seragam</p>
            </div>
        </div>
        <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

    {{-- Layanan BK --}}
    <a href="{{ route('siswa.bk-consultation.index') }}"
        class="group flex items-center justify-between p-3.5 bg-white rounded-2xl shadow-xs border border-slate-200/80 hover:border-purple-300 transition-all">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center shrink-0 border border-purple-100 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-slate-800">Bimbingan BK</p>
                <p class="text-[11px] text-purple-600 font-semibold truncate">Konseling Siswa</p>
            </div>
        </div>
        <svg class="w-4 h-4 text-slate-300 group-hover:text-purple-500 group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
</div>

{{-- ─── Hub Layanan Perizinan & Presensi Mandiri ──────────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 mb-4">
    <div class="flex items-center justify-between mb-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-800 leading-tight">Layanan Perizinan & Presensi</h2>
                <p class="text-[11px] text-slate-400">Pengajuan izin, sakit, lupa absen & pass kelas</p>
            </div>
        </div>

        @if($activePermit)
            <span class="text-[11px] font-semibold text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-full">
                {{ $activePermit->typeLabel() }} s.d {{ $activePermit->end_date->format('d M') }}
            </span>
        @elseif($permitPending > 0)
            <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                {{ $permitPending }} Menunggu
            </span>
        @endif
    </div>

    {{-- Baris Tombol Izin, Dispensasi, Riwayat --}}
    <div class="grid grid-cols-3 gap-2 mb-3">
        <a href="{{ route('siswa.permit.create') }}?type=izin"
            class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-xl bg-sky-50/70 hover:bg-sky-100/80 border border-sky-100 text-sky-800 transition-colors">
            <div class="w-7 h-7 rounded-lg bg-white shadow-2xs flex items-center justify-center text-sky-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="text-xs font-bold">Ajukan Izin</span>
        </a>

        <a href="{{ route('siswa.permit.create') }}?type=dispensasi"
            class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-xl bg-orange-50/70 hover:bg-orange-100/80 border border-orange-100 text-orange-800 transition-colors">
            <div class="w-7 h-7 rounded-lg bg-white shadow-2xs flex items-center justify-center text-orange-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="text-xs font-bold">Dispensasi</span>
        </a>

        <a href="{{ route('siswa.permit.index') }}"
            class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/80 text-slate-700 transition-colors">
            <div class="w-7 h-7 rounded-lg bg-white shadow-2xs flex items-center justify-center text-slate-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <span class="text-xs font-bold">Riwayat Izin</span>
        </a>
    </div>

    {{-- Sub-list Akses Mandiri (Izin Pulang Awal, Lupa Absen, Izin Keluar, E-Voting) --}}
    <div class="space-y-2 pt-2 border-t border-slate-100">
        {{-- Izin Pulang Lebih Awal --}}
        <a href="{{ route('siswa.early-checkout.index') }}"
            class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 leading-tight">Izin Pulang Lebih Awal</p>
                    <p class="text-[11px] text-slate-400 truncate">
                        {{ $earlyCheckoutPending > 0 ? $earlyCheckoutPending . ' pengajuan menunggu persetujuan' : 'Izin pulang sebelum jam sekolah berakhir' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                @if($earlyCheckoutPending > 0)
                    <span class="text-[10px] font-bold bg-amber-500 text-white rounded-full px-2 py-0.5">
                        {{ $earlyCheckoutPending }}
                    </span>
                @endif
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        {{-- Lupa Absen --}}
        <a href="{{ route('siswa.forgot-attendance.index') }}"
            class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 leading-tight">Koreksi Lupa Absen</p>
                    <p class="text-[11px] text-slate-400 truncate">
                        {{ $forgotAttendancePending > 0 ? $forgotAttendancePending . ' pengajuan menunggu respon' : 'Ajukan klaim lupa tap presensi' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                @if($forgotAttendancePending > 0)
                    <span class="text-[10px] font-bold bg-amber-500 text-white rounded-full px-2 py-0.5">
                        {{ $forgotAttendancePending }}
                    </span>
                @endif
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        {{-- Izin Keluar Kelas (Exit Pass) --}}
        <a href="{{ route('siswa.exit-pass.show') }}"
            class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg {{ $activeExitPass ? 'bg-rose-50 text-rose-600' : 'bg-teal-50 text-teal-600' }} flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 leading-tight">Izin Keluar Kelas</p>
                    <p class="text-[11px] {{ $activeExitPass ? 'text-rose-600 font-semibold' : 'text-slate-400' }} truncate">
                        {{ $activeExitPass ? 'Sedang keluar sejak ' . \Carbon\Carbon::parse($activeExitPass->out_time)->format('H:i') : 'Tap untuk buat izin keluar kelas' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                @if($activeExitPass)
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full animate-pulse">
                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                        Aktif
                    </span>
                @else
                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @endif
            </div>
        </a>

        {{-- E-Voting jika aktif --}}
        @if(\App\Models\AppSetting::isEvotingActive())
        <a href="{{ route('siswa.voting.index') }}"
            class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 leading-tight">E-Voting Pemilihan</p>
                    <p class="text-[11px] {{ $unvotedCount > 0 ? 'text-indigo-600 font-medium' : 'text-slate-400' }} truncate">
                        {{ $unvotedCount > 0 ? $unvotedCount . ' sesi pemilihan belum dipilih' : 'Semua suara telah diberikan' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                @if($unvotedCount > 0)
                    <span class="text-[10px] font-bold bg-indigo-600 text-white rounded-full px-2 py-0.5">
                        {{ $unvotedCount }} Baru
                    </span>
                @endif
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
        @endif
    </div>
</div>

{{-- ─── Verifikasi Prestasi (Khusus Pengelola) ───────────────────────── --}}
@if(auth()->user()->role === 'pengelola')
<a href="{{ route('siswa.achievements.verify') }}"
    class="block bg-blue-50/70 border border-blue-200/80 rounded-2xl p-4 mb-4 hover:bg-blue-100/60 transition-colors">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-blue-900">Verifikasi Prestasi Siswa</p>
            <p class="text-[11px] {{ $pendingVerify > 0 ? 'text-blue-700 font-semibold' : 'text-blue-500' }} truncate">
                {{ $pendingVerify > 0 ? $pendingVerify . ' pengajuan menunggu persetujuanmu' : 'Seluruh pengajuan telah diverifikasi' }}
            </p>
        </div>
        @if($pendingVerify > 0)
            <span class="text-xs font-bold bg-blue-600 text-white rounded-full px-2.5 py-1 shrink-0">
                {{ $pendingVerify }}
            </span>
        @endif
        <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
</a>
@endif

{{-- ─── Tab Log Aktivitas (Presensi, Catatan, Prestasi Lomba) ─────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden mb-6">

    {{-- Segmented Header Pill Switcher --}}
    <div class="p-2 border-b border-slate-100 bg-slate-50/60">
        <div class="flex gap-1 bg-slate-200/60 p-1 rounded-xl">
            @foreach([['presensi','Presensi'],['catatan','Catatan Sikap'],['prestasi','Prestasi Lomba']] as [$id,$label])
            <button onclick="switchTab('{{ $id }}')" id="tab-btn-{{ $id }}"
                class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all text-center
                    {{ $id === 'presensi' ? 'bg-white shadow-xs text-blue-600' : 'text-slate-600 hover:text-slate-800' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Tab: Presensi --}}
    <div id="tab-presensi">
        @forelse($tabPresensi as $att)
        @php
            $statusMeta = match($att->status) {
                'hadir'      => ['dot' => 'bg-emerald-500', 'pill' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/70',   'text' => 'Hadir'],
                'terlambat'  => ['dot' => 'bg-amber-500',   'pill' => 'bg-amber-50 text-amber-700 border border-amber-200/70',     'text' => 'Terlambat'],
                'izin'       => ['dot' => 'bg-sky-500',     'pill' => 'bg-sky-50 text-sky-700 border border-sky-200/70',           'text' => 'Izin'],
                'sakit'      => ['dot' => 'bg-purple-500',  'pill' => 'bg-purple-50 text-purple-700 border border-purple-200/70', 'text' => 'Sakit'],
                'dispensasi' => ['dot' => 'bg-orange-500',  'pill' => 'bg-orange-50 text-orange-700 border border-orange-200/70', 'text' => 'Dispensasi'],
                default      => ['dot' => 'bg-rose-500',    'pill' => 'bg-rose-50 text-rose-700 border border-rose-200/70',       'text' => 'Alpa'],
            };
        @endphp
        <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
            {{-- Dot status --}}
            <div class="w-2.5 h-2.5 rounded-full shrink-0 {{ $statusMeta['dot'] }}"></div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-slate-800 leading-tight">
                        {{ $att->date->isoFormat('dddd, D MMMM Y') }}
                    </p>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md shrink-0 {{ $statusMeta['pill'] }}">
                        {{ $statusMeta['text'] }}
                    </span>
                </div>
                <div class="mt-1 flex flex-col gap-0.5">
                    @if($att->check_in_time)
                        <p class="text-[11px] text-slate-500">
                            Masuk {{ \Carbon\Carbon::parse($att->check_in_time)->format('H:i') }}
                            @if($att->check_out_time)
                                · Pulang {{ \Carbon\Carbon::parse($att->check_out_time)->format('H:i') }}
                            @endif
                        </p>
                        @if(!$att->check_out_time)
                            <p class="text-[11px] text-amber-600 font-medium">Belum absen pulang</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="py-12 text-center">
            <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-xs text-slate-400 font-medium">Belum ada data presensi tercatat</p>
        </div>
        @endforelse
    </div>

    {{-- Tab: Catatan Sikap --}}
    <div id="tab-catatan" class="hidden">
        {{-- Filter Semua / Negatif / Positif --}}
        <div class="flex gap-1.5 px-4 pt-3 pb-2 bg-slate-50/40 border-b border-slate-100">
            @foreach([['semua','Semua'],['negatif','Catatan Perhatian'],['positif','Catatan Positif']] as [$fid,$flabel])
            <button onclick="filterCatatan('{{ $fid }}')" id="filter-btn-{{ $fid }}"
                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all
                    {{ $fid === 'semua' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white border border-slate-200/80 text-slate-600 hover:bg-slate-100' }}">
                {{ $flabel }}
            </button>
            @endforeach
        </div>

        @forelse($tabCatatan as $item)
        @php $isNegatif = $item['type'] === 'negatif'; @endphp
        <div data-type="{{ $item['type'] }}" class="flex items-start gap-3 px-4 py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
            <div class="w-8 h-8 {{ $isNegatif ? 'bg-rose-50 text-rose-500 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }} rounded-xl shrink-0 flex items-center justify-center mt-0.5">
                @if($isNegatif)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2v10z"/>
                </svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-800 leading-snug">{{ $item['title'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    @if($item['note']){{ $item['note'] }} · @endif{{ $item['date']->isoFormat('D MMM Y') }}
                </p>
            </div>
        </div>
        @empty
        <div class="py-12 text-center">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs text-slate-400 font-medium">Belum ada catatan sikap tercatat</p>
        </div>
        @endforelse
    </div>

    {{-- Tab: Prestasi Lomba --}}
    <div id="tab-prestasi" class="hidden">
        @forelse($tabPrestasi as $ach)
        <div class="flex items-start gap-3 px-4 py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
            <div class="w-8 h-8 bg-amber-50 text-amber-500 border border-amber-100 rounded-xl shrink-0 flex items-center justify-center mt-0.5">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-semibold text-slate-800 leading-snug">{{ $ach->title }}</p>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $ach->levelBadgeClass() }} shrink-0">
                        {{ $ach->levelLabel() }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    {{ $ach->category?->name }} · {{ $ach->achievement_date->isoFormat('D MMM Y') }}
                </p>
            </div>
        </div>
        @empty
        <div class="py-12 text-center">
            <div class="w-12 h-12 bg-amber-50 text-amber-300 rounded-full flex items-center justify-center mx-auto mb-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <p class="text-xs text-slate-400 font-medium">Belum ada prestasi lomba yang disetujui</p>
        </div>
        @endforelse
    </div>

</div>

<script>
function switchTab(name) {
    ['presensi','catatan','prestasi'].forEach(function(t) {
        var el = document.getElementById('tab-' + t);
        if (el) el.classList.add('hidden');
        var btn = document.getElementById('tab-btn-' + t);
        if (btn) {
            btn.classList.remove('bg-white', 'shadow-xs', 'text-blue-600');
            btn.classList.add('text-slate-600');
        }
    });
    var activeTab = document.getElementById('tab-' + name);
    if (activeTab) activeTab.classList.remove('hidden');
    var activeBtn = document.getElementById('tab-btn-' + name);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-600');
        activeBtn.classList.add('bg-white', 'shadow-xs', 'text-blue-600');
    }
}

function filterCatatan(type) {
    document.querySelectorAll('#tab-catatan [data-type]').forEach(function(el) {
        el.style.display = (type === 'semua' || el.dataset.type === type) ? '' : 'none';
    });
    ['semua','negatif','positif'].forEach(function(t) {
        var btn = document.getElementById('filter-btn-' + t);
        if (btn) {
            btn.classList.remove('bg-blue-600', 'text-white', 'shadow-2xs');
            btn.classList.add('bg-white', 'border', 'border-slate-200/80', 'text-slate-600');
        }
    });
    var active = document.getElementById('filter-btn-' + type);
    if (active) {
        active.classList.remove('bg-white', 'border', 'border-slate-200/80', 'text-slate-600');
        active.classList.add('bg-blue-600', 'text-white', 'shadow-2xs');
    }
}
</script>

@endsection
