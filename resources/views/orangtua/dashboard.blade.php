@extends('layouts.orangtua')
@section('title', 'Beranda Orang Tua')
@section('page-title', 'Beranda Orang Tua')

@section('content')
<div class="max-w-lg mx-auto space-y-4 pb-8">

    {{-- ─── 1. Welcome Card (Modern Deep Navy Glassmorphism) ───────────── --}}
    <div class="relative rounded-3xl p-5 text-white shadow-lg shadow-blue-950/15 overflow-hidden"
         style="background: linear-gradient(135deg, #0b1e4f 0%, #123075 50%, #19439e 100%); border: 1px solid rgba(255,255,255,0.15);">
        
        {{-- Aksen Dekoratif Halus --}}
        <div class="absolute -right-8 -bottom-8 w-40 h-40 rounded-full bg-blue-400/10 blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-32 h-32 rounded-full bg-indigo-400/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col gap-3">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-medium text-white/90 border border-white/20 shadow-2xs">
                    <span>📅</span>
                    <span>{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                </span>
                <span class="text-[10px] font-extrabold tracking-wider uppercase px-2.5 py-1 rounded-full bg-amber-400/20 text-amber-300 border border-amber-300/30">
                    Portal Orang Tua
                </span>
            </div>

            <div class="flex items-center justify-between gap-3 pt-1">
                <div>
                    <h1 class="text-xl font-black tracking-tight leading-tight text-white drop-shadow-xs">
                        Selamat datang, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="text-xs text-blue-100/90 font-medium mt-1">
                        Pantau kehadiran, catatan kedisiplinan & prestasi putra/putri Anda secara langsung.
                    </p>
                </div>
                <a href="{{ route('orangtua.profile') }}"
                   class="shrink-0 px-3 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 active:scale-95 text-white text-xs font-bold border border-white/20 transition-all flex items-center gap-1.5 shadow-2xs"
                   title="Buka & Edit Profil Orang Tua">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span>Profil</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ─── 2. Daftar Siswa (Anak) Terhubung ────────────────────────────── --}}
    @forelse($childrenData as $data)
    @php
        $child    = $data['student'];
        $summary  = $data['monthly_summary'];
        $pct      = $data['percentage'];
        $today    = $data['today_attendance'];
        $pctColor = $pct >= 90 ? 'text-emerald-700' : ($pct >= 80 ? 'text-amber-700' : 'text-rose-700');
        $pctBg    = $pct >= 90 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($pct >= 80 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');

        $trioRoom = app(\App\Services\ChatService::class)->getOrCreateRoomForStudent($child, 'trio');
        $trioUnread = $trioRoom ? $trioRoom->unreadCountFor(auth()->id()) : 0;

        $privateRoom = app(\App\Services\ChatService::class)->getOrCreateRoomForStudent($child, 'parent_teacher');
        $privateUnread = $privateRoom ? $privateRoom->unreadCountFor(auth()->id()) : 0;
    @endphp

    <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 p-4 sm:p-5 space-y-4">
        
        {{-- Profile Header Siswa --}}
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
            <div class="flex items-center gap-3 min-w-0">
                @if($child->photo)
                    <img src="{{ $child->photo_url }}" class="w-12 h-12 rounded-2xl object-cover ring-2 ring-blue-100/80 shadow-2xs shrink-0">
                @else
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-600 flex items-center justify-center font-black text-base shrink-0 border border-blue-100/80 shadow-2xs">
                        {{ $child->initials }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h2 class="font-bold text-slate-900 text-base truncate">{{ $child->name }}</h2>
                    <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                        <span class="text-xs text-slate-500 font-semibold">Kelas {{ $child->schoolClass?->name ?? '—' }}</span>
                        @if($child->nis)
                            <span class="text-slate-300">·</span>
                            <span class="text-[11px] text-slate-400 font-mono">NIS {{ $child->nis }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="shrink-0 text-right">
                <span class="inline-block text-[10px] px-2.5 py-1 rounded-full border font-bold {{ $pctBg }} shadow-2xs">
                    Kehadiran {{ $pct }}%
                </span>
            </div>
        </div>

        {{-- Status Absensi Hari Ini --}}
        <div class="bg-slate-50/80 rounded-2xl p-3.5 border border-slate-200/70 space-y-2.5">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <span>📅</span>
                    <span>Status Hari Ini</span>
                </span>
                @if($today)
                    @php
                        $st = match($today->status) {
                            'hadir'      => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'label' => 'Hadir'],
                            'terlambat'  => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'label' => 'Terlambat'],
                            'izin'       => ['bg' => 'bg-sky-100 text-sky-800 border-sky-200', 'label' => 'Izin'],
                            'sakit'      => ['bg' => 'bg-purple-100 text-purple-800 border-purple-200', 'label' => 'Sakit'],
                            'dispensasi' => ['bg' => 'bg-teal-100 text-teal-800 border-teal-200', 'label' => 'Dispen'],
                            default      => ['bg' => 'bg-rose-100 text-rose-800 border-rose-200', 'label' => 'Alpa'],
                        };
                    @endphp
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border {{ $st['bg'] }} shadow-2xs">
                        {{ $st['label'] }}
                    </span>
                @elseif(!empty($data['today_holiday']))
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200 shadow-2xs truncate max-w-[190px]">
                        🏖️ Libur: {{ $data['today_holiday']->description }}
                    </span>
                @elseif(!empty($data['is_weekend']))
                    <span class="text-[11px] font-semibold text-slate-500 bg-slate-200/70 px-2.5 py-0.5 rounded-full shadow-2xs">
                        🏖️ Libur Akhir Pekan
                    </span>
                @else
                    <span class="text-[11px] font-semibold text-slate-400 bg-slate-200/60 px-2.5 py-0.5 rounded-full shadow-2xs">
                        Belum Absen
                    </span>
                @endif
            </div>

            @if($today)
                {{-- Jam Presensi Masuk & Pulang --}}
                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    <div class="bg-white rounded-xl p-2.5 border border-slate-200/60 flex items-center justify-between">
                        <span class="text-slate-400 font-medium">Jam Masuk</span>
                        <span class="font-bold text-slate-800">{{ $today->check_in_time ? \Carbon\Carbon::parse($today->check_in_time)->format('H:i') . ' WITA' : '—' }}</span>
                    </div>
                    <div class="bg-white rounded-xl p-2.5 border border-slate-200/60 flex items-center justify-between">
                        <span class="text-slate-400 font-medium">Jam Pulang</span>
                        <span class="font-bold text-slate-800">{{ $today->check_out_time ? \Carbon\Carbon::parse($today->check_out_time)->format('H:i') . ' WITA' : 'Belum' }}</span>
                    </div>
                </div>

                {{-- Foto Selfie Masuk & Pulang --}}
                @if($today->photo_url || $today->check_out_photo_url)
                <div class="flex items-center gap-3 pt-2 border-t border-slate-200/60">
                    @if($today->photo_url)
                    <div class="flex items-center gap-2">
                        <a href="{{ $today->photo_url }}" target="_blank" class="shrink-0 group">
                            <img src="{{ $today->photo_url }}" alt="Foto Masuk" class="w-12 h-12 rounded-xl object-cover border border-slate-200 group-hover:opacity-90 shadow-2xs transition-opacity">
                        </a>
                        <div class="text-[11px] leading-tight min-w-0">
                            <p class="font-bold text-slate-800">Foto Masuk</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($today->check_in_time)->format('H:i') }} WITA</p>
                        </div>
                    </div>
                    @endif

                    @if($today->check_out_photo_url)
                    <div class="flex items-center gap-2">
                        <a href="{{ $today->check_out_photo_url }}" target="_blank" class="shrink-0 group">
                            <img src="{{ $today->check_out_photo_url }}" alt="Foto Pulang" class="w-12 h-12 rounded-xl object-cover border border-slate-200 group-hover:opacity-90 shadow-2xs transition-opacity">
                        </a>
                        <div class="text-[11px] leading-tight min-w-0">
                            <p class="font-bold text-slate-800">Foto Pulang</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($today->check_out_time)->format('H:i') }} WITA</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            @else
                @if(!empty($data['today_holiday']))
                    <div class="mt-1 bg-blue-50/90 border border-blue-200/70 rounded-xl p-3 text-xs text-blue-900 flex items-start gap-2.5">
                        <span class="text-base shrink-0">🏖️</span>
                        <div>
                            <p class="font-bold">Hari ini Libur: {{ $data['today_holiday']->description }}</p>
                            <p class="text-[11px] text-blue-700 mt-0.5">
                                {{ $data['today_holiday']->applies_to === 'kelas_tertentu' ? 'Libur khusus untuk kelas ini.' : 'Hari libur resmi seluruh sekolah.' }} Siswa tidak ada kegiatan belajar mengajar.
                            </p>
                        </div>
                    </div>
                @elseif(!empty($data['is_weekend']))
                    <p class="text-xs text-slate-500 italic mt-1 flex items-center gap-1.5">
                        <span>ℹ️</span>
                        <span>Hari ini hari Minggu (akhir pekan), kegiatan belajar mengajar libur.</span>
                    </p>
                @else
                    <p class="text-xs text-slate-400 italic mt-1 flex items-center gap-1.5">
                        <span>⏳</span>
                        <span>Ananda belum tercatat melakukan absensi hari ini.</span>
                    </p>
                @endif
            @endif
        </div>

        {{-- Jadwal Hari Libur Bulan Ini (Jika Ada) --}}
        @if(!empty($data['monthly_holidays']) && count($data['monthly_holidays']) > 0)
        <div class="bg-blue-50/60 rounded-2xl p-3.5 border border-blue-100/90 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-blue-950 flex items-center gap-1.5">
                    <span>🏖️</span>
                    <span>Jadwal Libur Bulan Ini ({{ count($data['monthly_holidays']) }})</span>
                </span>
                <span class="text-[10px] font-semibold text-blue-700 bg-blue-100/80 px-2 py-0.5 rounded-full">Resmi</span>
            </div>
            <div class="space-y-1.5 max-h-36 overflow-y-auto pr-0.5">
                @foreach($data['monthly_holidays'] as $h)
                <div class="bg-white rounded-xl p-2.5 px-3 border border-blue-100/70 flex items-center justify-between gap-2 text-xs shadow-2xs">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($h['date'])->isoFormat('D MMM Y') }}</span>
                            @if(!empty($h['is_special']))
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 border border-purple-200">Khusus</span>
                            @else
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 border border-blue-200">Sekolah</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-600 truncate mt-0.5 font-medium">📌 {{ $h['description'] }}</p>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium shrink-0">
                        {{ \Carbon\Carbon::parse($h['date'])->isoFormat('dddd') }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Ringkasan Absen Bulan Ini --}}
        <div onclick="window.location='{{ route('orangtua.attendance.history', $child->id) }}'"
             class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs cursor-pointer hover:border-blue-300 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                    <span>📊</span>
                    <span>Ringkasan Kehadiran {{ now()->isoFormat('MMMM Y') }}</span>
                </p>
                <span class="text-xs font-semibold text-blue-600 group-hover:text-blue-700 flex items-center gap-0.5">
                    Detail <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                </span>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 text-center">
                <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl p-2.5">
                    <p class="text-lg font-black text-emerald-700 leading-none">{{ $summary['hadir'] }}</p>
                    <p class="text-[10px] font-bold text-emerald-700/80 uppercase mt-1">Hadir</p>
                </div>
                <div class="bg-amber-50/70 border border-amber-100 rounded-xl p-2.5">
                    <p class="text-lg font-black text-amber-700 leading-none">{{ $summary['terlambat'] }}</p>
                    <p class="text-[10px] font-bold text-amber-700/80 uppercase mt-1">Telat</p>
                </div>
                <div class="bg-rose-50/70 border border-rose-100 rounded-xl p-2.5">
                    <p class="text-lg font-black text-rose-700 leading-none">{{ $summary['alpa'] }}</p>
                    <p class="text-[10px] font-bold text-rose-700/80 uppercase mt-1">Alpa</p>
                </div>
                <div class="bg-sky-50/70 border border-sky-100 rounded-xl p-2.5">
                    <p class="text-lg font-black text-sky-700 leading-none">{{ $summary['izin'] }}</p>
                    <p class="text-[10px] font-bold text-sky-700/80 uppercase mt-1">Izin</p>
                </div>
                <div class="bg-purple-50/70 border border-purple-100 rounded-xl p-2.5">
                    <p class="text-lg font-black text-purple-700 leading-none">{{ $summary['sakit'] }}</p>
                    <p class="text-[10px] font-bold text-purple-700/80 uppercase mt-1">Sakit</p>
                </div>
                <div class="bg-teal-50/70 border border-teal-100 rounded-xl p-2.5">
                    <p class="text-lg font-black text-teal-700 leading-none">{{ $summary['dispensasi'] }}</p>
                    <p class="text-[10px] font-bold text-teal-700/80 uppercase mt-1">Disp.</p>
                </div>
            </div>

            {{-- Pengajuan Lupa Absen Info --}}
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between bg-amber-50/60 border border-amber-200/60 rounded-xl px-3 py-2 text-xs">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold shrink-0">📝</span>
                    <div class="min-w-0 truncate">
                        <span class="font-bold text-slate-800">Lupa Absen:</span>
                        <span class="text-slate-600 ml-1">
                            @if(($summary['lupa_absen'] ?? 0) > 0)
                                <strong class="text-amber-800">{{ $summary['lupa_absen'] }} kali</strong>
                                <span class="text-[11px] text-slate-500">({{ $summary['lupa_absen_approved'] ?? 0 }} disetujui{{ ($summary['lupa_absen_pending'] ?? 0) > 0 ? ', ' . $summary['lupa_absen_pending'] . ' proses' : '' }})</span>
                            @else
                                <span class="text-slate-500 font-medium">0 kali</span>
                            @endif
                        </span>
                    </div>
                </div>
                <span class="text-[11px] font-semibold text-amber-700 shrink-0">
                    Detail →
                </span>
            </div>

            {{-- Alert Peringatan Alpa atau Terlambat --}}
            @if($summary['alpa'] > 0 || $summary['terlambat'] >= 3)
            <div class="mt-2.5 p-3 bg-rose-50/90 border border-rose-200 rounded-xl flex items-start gap-2.5 text-xs text-rose-900">
                <span class="text-base shrink-0">⚠️</span>
                <div>
                    <p class="font-bold">Perhatian Untuk Orang Tua:</p>
                    <p class="text-[11px] text-rose-800 mt-0.5 leading-relaxed">
                        @if($summary['alpa'] > 0)
                            Ananda memiliki <strong>{{ $summary['alpa'] }} kali Alpa</strong> bulan ini.
                        @endif
                        @if($summary['terlambat'] >= 3)
                            Tercatat <strong>{{ $summary['terlambat'] }} kali Terlambat</strong>. Mohon bimbingan waktu keberangkatan.
                        @endif
                    </p>
                </div>
            </div>
            @endif
        </div>

        {{-- Akses Cepat SIPINTER & Prestasi (2 Kolom Seimbang) --}}
        <div class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-100">
            <a href="{{ route('orangtua.conduct.index', $child->id) }}"
               class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-3 flex items-center justify-between hover:bg-rose-50/60 hover:border-rose-200 transition-all group">
                <div class="min-w-0">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kedisiplinan</p>
                    <p class="text-base font-black text-slate-800 mt-0.5 truncate">
                        {{ $data['violation_count'] ?? $data['violation_points'] }} <span class="text-xs font-normal text-slate-500">Catatan</span>
                    </p>
                </div>
                <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm shrink-0 group-hover:scale-105 transition-transform">⚠️</span>
            </a>

            <a href="{{ route('orangtua.achievements.index', $child->id) }}"
               class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-3 flex items-center justify-between hover:bg-emerald-50/60 hover:border-emerald-200 transition-all group">
                <div class="min-w-0">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Prestasi Siswa</p>
                    <p class="text-base font-black text-slate-800 mt-0.5 truncate">
                        {{ $data['achievement_count'] }} <span class="text-xs font-normal text-slate-500">Prestasi</span>
                    </p>
                </div>
                <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm shrink-0 group-hover:scale-105 transition-transform">🏆</span>
            </a>
        </div>

        {{-- ─── Chat Hub Orang Tua: Pilihan Ruang Bersama & Ruang Privat ────── --}}
        <div class="space-y-2 pt-1 border-t border-slate-100">
            <div class="flex items-center justify-between px-0.5">
                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <span>💬</span>
                    <span>Konsultasi Wali Kelas</span>
                </span>
                <span class="text-[10px] text-slate-400 font-medium">Pilih Ruang Obrolan</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                {{-- 1. Ruang Bersama (Siswa + Ortu + Wali Kelas) --}}
                @if($trioRoom)
                <a href="{{ route('orangtua.chat.show', $trioRoom->id) }}"
                   class="flex items-center justify-between p-3 rounded-2xl bg-white border border-emerald-100 hover:border-emerald-300 shadow-2xs hover:shadow-xs transition-all group">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center text-sm shrink-0 group-hover:scale-105 transition-transform">
                            👥
                        </div>
                        <div class="min-w-0 text-left">
                            <div class="flex items-center gap-1.5">
                                <p class="font-bold text-xs text-slate-900 group-hover:text-emerald-700 transition-colors">Ruang Bersama</p>
                                <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    +Siswa
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 truncate mt-0.5">Wali, Ortu & {{ Str::words($child->name, 1, '') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        @if($trioUnread > 0)
                            <span class="px-1.5 py-0.2 bg-emerald-600 text-white text-[9px] font-black rounded-full shadow-2xs">
                                {{ $trioUnread }}
                            </span>
                        @endif
                        <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
                @endif

                {{-- 2. Ruang Privat (Hanya Ortu & Wali Kelas) --}}
                @if($privateRoom)
                <a href="{{ route('orangtua.chat.show', $privateRoom->id) }}"
                   class="flex items-center justify-between p-3 rounded-2xl bg-white border border-indigo-100 hover:border-indigo-300 shadow-2xs hover:shadow-xs transition-all group">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center justify-center text-sm shrink-0 group-hover:scale-105 transition-transform">
                            🔒
                        </div>
                        <div class="min-w-0 text-left">
                            <div class="flex items-center gap-1.5">
                                <p class="font-bold text-xs text-slate-900 group-hover:text-indigo-700 transition-colors">Ruang Privat</p>
                                <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Privat
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 truncate mt-0.5">Khusus Wali & Orang Tua</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        @if($privateUnread > 0)
                            <span class="px-1.5 py-0.2 bg-indigo-600 text-white text-[9px] font-black rounded-full shadow-2xs">
                                {{ $privateUnread }}
                            </span>
                        @endif
                        <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
                @endif
            </div>
        </div>

    </div>
    @empty
    <div class="bg-white rounded-3xl p-8 text-center shadow-xs border border-slate-200/80">
        <span class="text-3xl block mb-2">👨‍👩‍👧‍👦</span>
        <p class="text-slate-800 font-bold text-sm">Belum ada data anak yang terhubung</p>
        <p class="text-slate-400 text-xs mt-1">Hubungi admin sekolah untuk menghubungkan akun putra/putri Anda.</p>
    </div>
    @endforelse

</div>
@endsection
