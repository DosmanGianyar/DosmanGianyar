@extends('layouts.piket')
@section('title', 'Dashboard Piket')
@section('page-title', 'Dashboard Meja Piket')

@section('content')
<div class="space-y-6">

    {{-- Banner Petugas Aktif & Ringkasan Hari Ini --}}
    <div class="rounded-2xl bg-gradient-to-r from-[#182433] via-[#1f3147] to-[#206bc4] p-6 sm:p-7 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/30 text-amber-200 text-xs font-bold uppercase tracking-wider">
                Petugas Meja Piket Aktif
            </span>
            <h2 class="text-2xl sm:text-3xl font-black mt-2">{{ $teacher->name }}</h2>
            <p class="text-sm text-slate-200 mt-1">
                Semua verifikasi izin siswa, keterlambatan, dan dispensasi akan otomatis tercatat atas nama Anda.
            </p>
        </div>
        <div class="flex flex-wrap gap-2.5 shrink-0">
            <a href="{{ route('piket.permits.index') }}"
               class="px-4 py-2.5 rounded-xl bg-white text-slate-900 font-bold text-sm hover:bg-slate-100 transition">
                Verifikasi Izin ({{ $pendingPermits }})
            </a>
            <a href="{{ route('piket.dispensation.create') }}"
               class="px-4 py-2.5 rounded-xl bg-amber-500 text-white font-bold text-sm hover:bg-amber-600 transition">
                + Input Dispensasi
            </a>
            <a href="{{ route('piket.select-teacher') }}"
               class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-white font-bold text-sm transition">
                Ganti Guru Piket
            </a>
        </div>
    </div>

    {{-- 5 Kartu Antrean Verifikasi --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <a href="{{ route('piket.permits.index') }}"
           class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 hover:border-amber-400 transition">
            <p class="text-xs font-bold text-slate-500 uppercase">Izin Tidak Sekolah</p>
            <p class="text-3xl font-black text-amber-600 mt-1.5">{{ $pendingPermits }}</p>
            <p class="text-sm text-slate-500 mt-1">Menunggu verifikasi</p>
        </a>

        <a href="{{ route('piket.early-checkouts.index') }}"
           class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 hover:border-orange-400 transition">
            <p class="text-xs font-bold text-slate-500 uppercase">Izin Pulang Awal</p>
            <p class="text-3xl font-black text-orange-600 mt-1.5">{{ $pendingEarlyCheckouts }}</p>
            <p class="text-sm text-slate-500 mt-1">Menunggu verifikasi</p>
        </a>

        <a href="{{ route('piket.forgot-attendance.index') }}"
           class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 hover:border-sky-400 transition">
            <p class="text-xs font-bold text-slate-500 uppercase">Lupa Presensi</p>
            <p class="text-3xl font-black text-sky-600 mt-1.5">{{ $pendingForgotAttendance }}</p>
            <p class="text-sm text-slate-500 mt-1">Menunggu verifikasi</p>
        </a>

        <a href="{{ route('piket.late-verification.index') }}"
           class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 hover:border-rose-400 transition">
            <p class="text-xs font-bold text-slate-500 uppercase">Terlambat Hari Ini</p>
            <p class="text-3xl font-black text-rose-600 mt-1.5">{{ $pendingLateReports }}</p>
            <p class="text-sm text-slate-500 mt-1">Belum diverifikasi</p>
        </a>

        <a href="{{ route('piket.exit-passes.index') }}"
           class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 hover:border-emerald-400 transition">
            <p class="text-xs font-bold text-slate-500 uppercase">Sedang Keluar Kelas</p>
            <p class="text-3xl font-black text-emerald-600 mt-1.5">{{ $activeExitPasses }}</p>
            <p class="text-sm text-slate-500 mt-1">Izin keluar aktif</p>
        </a>
    </div>

    {{-- Rekap Kehadiran Siswa Hari Ini --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
        <div class="flex items-center justify-between mb-3.5">
            <h3 class="text-lg font-extrabold text-slate-900">Ringkasan Presensi Siswa Hari Ini</h3>
            <span class="text-sm text-slate-500">{{ now()->locale('id')->translatedFormat('d M Y') }}</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200">
                <p class="text-2xl font-black text-emerald-700">{{ $todayAttendance['hadir'] }}</p>
                <p class="text-sm font-bold text-emerald-800 mt-0.5">Hadir</p>
            </div>
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200">
                <p class="text-2xl font-black text-amber-700">{{ $todayAttendance['terlambat'] }}</p>
                <p class="text-sm font-bold text-amber-800 mt-0.5">Terlambat</p>
            </div>
            <div class="p-3.5 rounded-xl bg-sky-50 border border-sky-200">
                <p class="text-2xl font-black text-sky-700">{{ $todayAttendance['izin'] }}</p>
                <p class="text-sm font-bold text-sky-800 mt-0.5">Izin</p>
            </div>
            <div class="p-3.5 rounded-xl bg-purple-50 border border-purple-200">
                <p class="text-2xl font-black text-purple-700">{{ $todayAttendance['sakit'] }}</p>
                <p class="text-sm font-bold text-purple-800 mt-0.5">Sakit</p>
            </div>
            <div class="p-3.5 rounded-xl bg-orange-50 border border-orange-200">
                <p class="text-2xl font-black text-orange-700">{{ $todayAttendance['dispensasi'] }}</p>
                <p class="text-sm font-bold text-orange-800 mt-0.5">Dispensasi</p>
            </div>
        </div>
    </div>

    {{-- Grid Antrean Izin Terbaru & Aktivitas Piket --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Antrean Izin Tidak Sekolah --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
            <div class="flex items-center justify-between mb-3.5">
                <h3 class="text-lg font-extrabold text-slate-900">Antrean Izin Tidak Sekolah</h3>
                <a href="{{ route('piket.permits.index') }}" class="text-sm font-bold text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentPermits as $p)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-base font-bold text-slate-900 truncate">{{ $p->student?->name ?? '—' }}</p>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $p->typeBadgeClass() }}">
                                    {{ $p->typeLabel() }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">
                                {{ $p->student?->schoolClass?->name ?? '—' }} • {{ $p->start_date->format('d/m') }} – {{ $p->end_date->format('d/m/Y') }}
                            </p>
                            <p class="text-sm text-slate-600 truncate mt-0.5">{{ $p->reason }}</p>
                        </div>
                        <form method="POST" action="{{ route('piket.permits.approve', $p) }}" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
                                Setujui
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-6">Tidak ada pengajuan izin yang menunggu.</p>
                @endforelse
            </div>
        </div>

        {{-- Shift & Log Verifikasi Hari Ini --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-4">
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <h3 class="text-lg font-extrabold text-slate-900">Shift Guru Piket Hari Ini</h3>
                    <a href="{{ route('piket.logs.index') }}" class="text-sm font-bold text-blue-600 hover:underline">Log lengkap →</a>
                </div>
                <div class="flex flex-wrap gap-2">
                    @forelse($todayShifts as $shift)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold {{ $shift->ended_at ? 'bg-slate-100 text-slate-600' : 'bg-emerald-50 border border-emerald-200 text-emerald-800' }}">
                            <span>{{ $shift->teacher?->name ?? '—' }}</span>
                            <span class="text-xs opacity-75">({{ $shift->started_at?->format('H:i') }}{{ $shift->ended_at ? '–' . $shift->ended_at->format('H:i') : '–aktif' }})</span>
                        </span>
                    @empty
                        <span class="text-sm text-slate-400">Belum ada shift tercatat hari ini.</span>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3.5">
                <h4 class="text-sm font-extrabold text-slate-700 uppercase tracking-wider mb-2.5">Aktivitas Piket Terbaru</h4>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @forelse($recentLogs as $log)
                        <div class="text-sm flex items-start justify-between gap-2 py-2 border-b border-slate-100 last:border-0">
                            <div>
                                <span class="font-bold text-slate-800">{{ $log->changes['piket_teacher_name'] ?? $log->changes['teacher_name'] ?? 'Guru Piket' }}</span>
                                <span class="text-slate-500">• {{ str_replace('_', ' ', $log->action) }}</span>
                            </div>
                            <span class="text-xs text-slate-400 shrink-0">{{ $log->created_at?->format('H:i') }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 py-3 text-center">Belum ada aktivitas piket hari ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
