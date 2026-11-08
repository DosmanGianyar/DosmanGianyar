@extends('layouts.orangtua')
@section('title', 'Beranda Orang Tua')
@section('page-title', 'Beranda Orang Tua')

@section('content')
<div class="max-w-xl mx-auto space-y-4 pb-8">

    {{-- ─── 1. Header Sapaan Ringkas ───────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-medium text-slate-500">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h1 class="text-base font-bold text-slate-900 truncate mt-0.5">
                {{ auth()->user()->name }}
            </h1>
        </div>
        <a href="{{ route('orangtua.profile') }}"
           class="shrink-0 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Profil</span>
        </a>
    </div>

    {{-- ─── 2. Daftar Siswa (Anak) Terhubung ────────────────────────────── --}}
    @forelse($childrenData as $data)
    @php
        $child    = $data['student'];
        $summary  = $data['monthly_summary'];
        $pct      = $data['percentage'];
        $today    = $data['today_attendance'];
        $pctBadge = $pct >= 90
            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
            : ($pct >= 80 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');

        $trioRoom = app(\App\Services\ChatService::class)->getOrCreateRoomForStudent($child, 'trio');
        $trioUnread = $trioRoom ? $trioRoom->unreadCountFor(auth()->id()) : 0;

        $privateRoom = app(\App\Services\ChatService::class)->getOrCreateRoomForStudent($child, 'parent_teacher');
        $privateUnread = $privateRoom ? $privateRoom->unreadCountFor(auth()->id()) : 0;
    @endphp

    <div class="space-y-3">

        {{-- Kartu Identitas & Kehadiran Hari Ini (Tabler + Filament Minimalist) --}}
        <div class="bg-white rounded-xl border border-slate-200 border-t-[3px] border-t-[#206bc4] shadow-2xs p-4 space-y-3.5">
            {{-- Baris Identitas Siswa --}}
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    @if($child->photo)
                        <img src="{{ $child->photo_url }}" class="w-11 h-11 rounded-lg object-cover border border-slate-200 shrink-0">
                    @else
                        <div class="w-11 h-11 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0 border border-slate-200">
                            {{ $child->initials }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-900 text-sm sm:text-base truncate">{{ $child->name }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Kelas {{ $child->schoolClass?->name ?? '—' }}
                            @if($child->nis)
                                <span class="text-slate-300 mx-1">·</span>
                                <span class="font-mono text-[11px]">NIS {{ $child->nis }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <span class="shrink-0 text-[11px] px-2.5 py-1 rounded-md border font-semibold {{ $pctBadge }}">
                    {{ $pct }}% Hadir
                </span>
            </div>

            {{-- Status Presensi Hari Ini --}}
            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-600">Presensi Hari Ini</span>
                    @if($today)
                        @php
                            $st = match($today->status) {
                                'hadir'      => ['cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500', 'label' => 'Hadir'],
                                'terlambat'  => ['cls' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500', 'label' => 'Terlambat'],
                                'izin'       => ['cls' => 'bg-sky-50 text-sky-700 border-sky-200', 'dot' => 'bg-sky-500', 'label' => 'Izin'],
                                'sakit'      => ['cls' => 'bg-purple-50 text-purple-700 border-purple-200', 'dot' => 'bg-purple-500', 'label' => 'Sakit'],
                                'dispensasi' => ['cls' => 'bg-teal-50 text-teal-700 border-teal-200', 'dot' => 'bg-teal-500', 'label' => 'Dispensasi'],
                                default      => ['cls' => 'bg-rose-50 text-rose-700 border-rose-200', 'dot' => 'bg-rose-500', 'label' => 'Alpa'],
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2 py-0.5 rounded-md border {{ $st['cls'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>
                            {{ $st['label'] }}
                        </span>
                    @elseif(!empty($data['today_holiday']))
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 truncate max-w-[200px]">
                            Libur: {{ $data['today_holiday']->description }}
                        </span>
                    @elseif(!empty($data['is_weekend']))
                        <span class="text-[11px] font-medium text-slate-500 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                            Akhir Pekan
                        </span>
                    @else
                        <span class="text-[11px] font-medium text-slate-500 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                            Belum Tercatat
                        </span>
                    @endif
                </div>

                @if($today)
                    <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                        <div class="bg-white rounded-md px-3 py-2 border border-slate-200/80 flex items-center justify-between">
                            <span class="text-slate-500">Masuk</span>
                            <span class="font-semibold text-slate-800">{{ $today->check_in_time ? \Carbon\Carbon::parse($today->check_in_time)->format('H:i') : '—' }}</span>
                        </div>
                        <div class="bg-white rounded-md px-3 py-2 border border-slate-200/80 flex items-center justify-between">
                            <span class="text-slate-500">Pulang</span>
                            <span class="font-semibold text-slate-800">{{ $today->check_out_time ? \Carbon\Carbon::parse($today->check_out_time)->format('H:i') : 'Belum' }}</span>
                        </div>
                    </div>

                    @if($today->photo_url || $today->check_out_photo_url)
                    <div class="flex items-center gap-3 pt-2 border-t border-slate-200/60">
                        @if($today->photo_url)
                        <a href="{{ $today->photo_url }}" target="_blank" class="flex items-center gap-2 group">
                            <img src="{{ $today->photo_url }}" alt="Foto Masuk" class="w-10 h-10 rounded-md object-cover border border-slate-200 group-hover:opacity-90">
                            <div class="text-[11px] leading-tight">
                                <p class="font-semibold text-slate-700">Foto Masuk</p>
                                <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($today->check_in_time)->format('H:i') }} WITA</p>
                            </div>
                        </a>
                        @endif

                        @if($today->check_out_photo_url)
                        <a href="{{ $today->check_out_photo_url }}" target="_blank" class="flex items-center gap-2 group">
                            <img src="{{ $today->check_out_photo_url }}" alt="Foto Pulang" class="w-10 h-10 rounded-md object-cover border border-slate-200 group-hover:opacity-90">
                            <div class="text-[11px] leading-tight">
                                <p class="font-semibold text-slate-700">Foto Pulang</p>
                                <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($today->check_out_time)->format('H:i') }} WITA</p>
                            </div>
                        </a>
                        @endif
                    </div>
                    @endif
                @endif
            </div>

            {{-- Strip Ringkasan Kehadiran Bulan Ini (Clean, Tanpa Kotak Pelangi) --}}
            <div class="border border-slate-200 rounded-lg divide-x divide-slate-200 grid grid-cols-6 text-center bg-white overflow-hidden">
                <div class="py-2 px-1">
                    <p class="text-sm font-bold text-slate-900">{{ $summary['hadir'] }}</p>
                    <p class="text-[10px] font-medium text-slate-500 mt-0.5">Hadir</p>
                </div>
                <div class="py-2 px-1">
                    <p class="text-sm font-bold {{ $summary['terlambat'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $summary['terlambat'] }}</p>
                    <p class="text-[10px] font-medium text-slate-500 mt-0.5">Telat</p>
                </div>
                <div class="py-2 px-1">
                    <p class="text-sm font-bold {{ $summary['alpa'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $summary['alpa'] }}</p>
                    <p class="text-[10px] font-medium text-slate-500 mt-0.5">Alpa</p>
                </div>
                <div class="py-2 px-1">
                    <p class="text-sm font-bold text-slate-900">{{ $summary['izin'] }}</p>
                    <p class="text-[10px] font-medium text-slate-500 mt-0.5">Izin</p>
                </div>
                <div class="py-2 px-1">
                    <p class="text-sm font-bold text-slate-900">{{ $summary['sakit'] }}</p>
                    <p class="text-[10px] font-medium text-slate-500 mt-0.5">Sakit</p>
                </div>
                <div class="py-2 px-1">
                    <p class="text-sm font-bold text-slate-900">{{ $summary['dispensasi'] }}</p>
                    <p class="text-[10px] font-medium text-slate-500 mt-0.5">Dispen</p>
                </div>
            </div>

            {{-- Peringatan Ringkas Jika Ada Alpa / Terlambat Berulang --}}
            @if($summary['alpa'] > 0 || $summary['terlambat'] >= 3)
            <div class="px-3 py-2 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800 flex items-center justify-between gap-2">
                <span>
                    Perhatian: Bulan ini tercatat
                    @if($summary['alpa'] > 0) <strong>{{ $summary['alpa'] }}x Alpa</strong>@endif
                    @if($summary['alpa'] > 0 && $summary['terlambat'] >= 3) dan @endif
                    @if($summary['terlambat'] >= 3) <strong>{{ $summary['terlambat'] }}x Terlambat</strong>@endif.
                </span>
                <a href="{{ route('orangtua.attendance.history', $child->id) }}" class="font-semibold underline shrink-0">Cek</a>
            </div>
            @endif
        </div>

        {{-- ─── 3. Grid Card Kecil Kotak (Menu Akses Cepat Orang Tua) ─────── --}}
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-0.5 mb-2">
                Menu Pemantauan Anak
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">

                {{-- Card 1: Riwayat Absensi --}}
                <a href="{{ route('orangtua.attendance.history', $child->id) }}"
                   class="bg-white hover:bg-slate-50 border {{ !empty($data['badges']['attendance']['has_unread']) ? 'border-blue-400 ring-2 ring-blue-100' : 'border-slate-200 hover:border-slate-300' }} rounded-xl p-3.5 flex flex-col justify-between min-h-[112px] transition-colors group relative">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            @if(!empty($data['badges']['attendance']['has_unread']))
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-blue-600 rounded-full ring-2 ring-white"></span>
                            @endif
                        </div>
                        @if(!empty($data['badges']['attendance']['has_unread']))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-600 text-white text-[10px] font-bold rounded-md shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                Baru
                            </span>
                        @else
                            <span class="text-[11px] font-semibold text-slate-500">
                                Lupa: {{ $summary['lupa_absen'] ?? 0 }}/{{ $summary['lupa_absen_limit'] ?? 10 }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Riwayat Absensi</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Rekap harian & foto</p>
                    </div>
                </a>

                {{-- Card 2: Catatan SIPINTER --}}
                <a href="{{ route('orangtua.conduct.index', $child->id) }}"
                   class="bg-white hover:bg-slate-50 border {{ !empty($data['badges']['conduct']['has_unread']) ? 'border-rose-400 ring-2 ring-rose-100' : 'border-slate-200 hover:border-amber-300' }} rounded-xl p-3.5 flex flex-col justify-between min-h-[112px] transition-colors group relative">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            @if(!empty($data['badges']['conduct']['has_unread']))
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-rose-600 rounded-full ring-2 ring-white"></span>
                            @endif
                        </div>
                        @if(!empty($data['badges']['conduct']['has_unread']))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-600 text-white text-[10px] font-bold rounded-md shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                {{ $data['badges']['conduct']['count'] > 0 ? $data['badges']['conduct']['count'] . ' Baru' : 'Baru' }}
                            </span>
                        @else
                            <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                                {{ $data['violation_count'] ?? $data['violation_points'] }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <p class="text-xs font-bold text-slate-900 group-hover:text-amber-600 transition-colors">Catatan Karakter</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">SIPINTER & disiplin</p>
                    </div>
                </a>

                {{-- Card 3: Prestasi Siswa --}}
                <a href="{{ route('orangtua.achievements.index', $child->id) }}"
                   class="bg-white hover:bg-slate-50 border {{ !empty($data['badges']['achievement']['has_unread']) ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-slate-200 hover:border-slate-300' }} rounded-xl p-3.5 flex flex-col justify-between min-h-[112px] transition-colors group relative">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                            @if(!empty($data['badges']['achievement']['has_unread']))
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-600 rounded-full ring-2 ring-white"></span>
                            @endif
                        </div>
                        @if(!empty($data['badges']['achievement']['has_unread']))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-600 text-white text-[10px] font-bold rounded-md shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                {{ $data['badges']['achievement']['count'] > 0 ? $data['badges']['achievement']['count'] . ' Baru' : 'Baru' }}
                            </span>
                        @else
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                {{ $data['achievement_count'] }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Prestasi Siswa</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Rekap penghargaan</p>
                    </div>
                </a>

                {{-- Card 4: Chat Ruang Bersama (Wali, Ortu & Siswa) --}}
                @if($trioRoom)
                <a href="{{ route('orangtua.chat.show', $trioRoom->id) }}"
                   class="bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl p-3.5 flex flex-col justify-between min-h-[112px] transition-colors group">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        @if($trioUnread > 0)
                            <span class="px-2 py-0.5 bg-rose-600 text-white text-[10px] font-bold rounded-full">
                                {{ $trioUnread }} baru
                            </span>
                        @else
                            <span class="text-[10px] font-medium text-slate-400">3 pihak</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Ruang Bersama</p>
                        <p class="text-[11px] text-slate-500 mt-0.5 truncate">Wali, Ortu & {{ Str::words($child->name, 1, '') }}</p>
                    </div>
                </a>
                @endif

                {{-- Card 5: Chat Ruang Privat (Wali & Ortu) --}}
                @if($privateRoom)
                <a href="{{ route('orangtua.chat.show', $privateRoom->id) }}"
                   class="bg-white hover:bg-slate-50 border border-slate-200 hover:border-indigo-300 rounded-xl p-3.5 flex flex-col justify-between min-h-[112px] transition-colors group">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        @if($privateUnread > 0)
                            <span class="px-2 py-0.5 bg-rose-600 text-white text-[10px] font-bold rounded-full">
                                {{ $privateUnread }} baru
                            </span>
                        @else
                            <span class="text-[10px] font-medium text-slate-400">Privat</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <p class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Chat Wali Kelas</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Khusus Ortu & Wali</p>
                    </div>
                </a>
                @endif

                {{-- Card 6: Pengaturan Profil Orang Tua --}}
                <a href="{{ route('orangtua.profile') }}"
                   class="bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl p-3.5 flex flex-col justify-between min-h-[112px] transition-colors group">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-slate-400">Akun</span>
                    </div>
                    <div class="mt-3">
                        <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Profil Orang Tua</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Data diri & kontak</p>
                    </div>
                </a>

            </div>
        </div>

        {{-- Jadwal Libur Bulan Ini (Ringkas, jika ada) --}}
        @if(!empty($data['monthly_holidays']) && count($data['monthly_holidays']) > 0)
        <div class="bg-white rounded-xl p-3.5 border border-slate-200 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-800">Hari Libur Bulan Ini</span>
                <span class="text-[11px] font-medium text-slate-500">{{ count($data['monthly_holidays']) }} hari</span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($data['monthly_holidays'] as $h)
                <div class="py-2 flex items-center justify-between gap-2 text-xs">
                    <div class="min-w-0">
                        <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($h['date'])->isoFormat('D MMM Y') }}</span>
                        <span class="text-slate-400 mx-1">·</span>
                        <span class="text-slate-600">{{ $h['description'] }}</span>
                    </div>
                    <span class="text-[11px] text-slate-400 shrink-0">
                        {{ \Carbon\Carbon::parse($h['date'])->isoFormat('dddd') }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
    @empty
    <div class="bg-white rounded-xl p-8 text-center border border-slate-200">
        <p class="text-slate-800 font-bold text-sm">Belum ada data anak yang terhubung</p>
        <p class="text-slate-500 text-xs mt-1">Hubungi pihak sekolah untuk menghubungkan akun dengan data siswa.</p>
    </div>
    @endforelse

</div>
@endsection
