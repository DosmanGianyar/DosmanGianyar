@extends('layouts.guru')

@section('title', 'Layanan & Direktori Sekolah')
@section('page-title', 'Layanan & Direktori Sekolah')

@section('content')
<div class="px-4 sm:px-6 py-6 max-w-7xl mx-auto space-y-6" x-data="{ tab: 'walikelas', search: '' }">

    {{-- Banner Mode Read-Only --}}
    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-2xl p-5 shadow-md relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border border-blue-800">
        <div class="space-y-1 z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30 text-xs font-bold uppercase tracking-wider">
                ℹ️ Informasi Umum & Direktori Sekolah
            </span>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">Layanan & Informasi Direktori Sekolah</h2>
            <p class="text-xs sm:text-sm text-blue-200">
                Pusat informasi publik internal guru: Daftar Wali Kelas, Pembina Ekstrakurikuler, Guru Piket & Direktori Pengajar.
            </p>
        </div>
        <div class="bg-white/10 backdrop-blur-md px-3.5 py-2 rounded-xl border border-white/20 text-xs text-blue-100 font-medium shrink-0 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Mode Hanya Lihat (Read-Only)</span>
        </div>
    </div>

    {{-- Search & Tab Bar Navigation --}}
    <div class="bg-white rounded-2xl p-3 shadow-xs border border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        
        {{-- Navigation Tabs --}}
        <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
            <button @click="tab = 'walikelas'"
                :class="tab === 'walikelas' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5">
                <span>🏫</span> Daftar Wali Kelas ({{ $waliKelas->count() }})
            </button>

            <button @click="tab = 'pembina'"
                :class="tab === 'pembina' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5">
                <span>📌</span> Pembina Ekstra ({{ $extracurriculars->count() }})
            </button>

            <button @click="tab = 'piket'"
                :class="tab === 'piket' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5">
                <span>🛡️</span> Guru Piket / Jadwal
            </button>

            <button @click="tab = 'direktori'"
                :class="tab === 'direktori' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5">
                <span>👨‍🏫</span> Direktori Guru ({{ $gurus->count() }})
            </button>
        </div>

        {{-- Live Search Input --}}
        <div class="relative min-w-[220px]">
            <input type="text" x-model="search" placeholder="Cari nama, kelas, mapel..."
                class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    {{-- TAB 1: DAFTAR WALI KELAS --}}
    <div x-show="tab === 'walikelas'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($waliKelas as $kelas)
            @php
                $wali = $kelas->homeroomTeacher;
            @endphp
            <div x-show="!search || '{{ strtolower($kelas->name . ' ' . ($wali?->name ?? '')) }}'.includes(search.toLowerCase())"
                 class="bg-white rounded-2xl p-4 shadow-2xs border border-slate-200/80 hover:border-blue-300 transition-all flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                            Kelas {{ $kelas->grade }}
                        </span>
                        <h3 class="text-base font-black text-slate-900 mt-1">Kelas {{ $kelas->name }}</h3>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-xs">
                        🏫
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex items-center gap-3">
                    @if($wali?->photo)
                        <img src="{{ $wali->photo_url }}" class="w-10 h-10 rounded-xl object-cover ring-2 ring-blue-500/20 shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center shrink-0">
                            {{ $wali ? $wali->initials : '?' }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ $wali?->name ?? 'Belum Ditentukan' }}</p>
                        <p class="text-[11px] text-slate-500 font-medium truncate">NIP: {{ $wali?->nip ?? '—' }}</p>
                        @if($wali?->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $wali->phone) }}" target="_blank"
                               class="text-[10.5px] text-emerald-600 font-semibold hover:underline inline-flex items-center gap-1 mt-0.5">
                                💬 {{ $wali->phone }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-2xl p-8 text-center text-slate-400 text-sm">
                Belum ada data wali kelas.
            </div>
            @endforelse
        </div>
    </div>

    {{-- TAB 2: PEMBINA EKSTRAKURIKULER --}}
    <div x-show="tab === 'pembina'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($extracurriculars as $extra)
            <div x-show="!search || '{{ strtolower($extra->name . ' ' . $extra->pembina_names) }}'.includes(search.toLowerCase())"
                 class="bg-white rounded-2xl p-4 shadow-2xs border border-slate-200/80 hover:border-blue-300 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200">
                            Ekstrakurikuler
                        </span>
                        <span class="text-[11px] text-slate-400 font-medium">
                            👥 {{ $extra->members_count ?? $extra->activeMembers()->count() }} Anggota
                        </span>
                    </div>
                    <h3 class="text-base font-black text-slate-900 leading-tight mb-2">{{ $extra->name }}</h3>
                </div>

                <div class="space-y-2 mt-3 pt-3 border-t border-slate-100">
                    <div class="flex items-start gap-2">
                        <span class="text-xs">🧑‍🏫</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Pembina</p>
                            <p class="text-xs font-bold text-slate-800 leading-snug">{{ $extra->pembina_names }}</p>
                        </div>
                    </div>

                    @if($extra->contact_person)
                    <div class="flex items-start gap-2">
                        <span class="text-xs">📞</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Kontak / Kontak Person</p>
                            <p class="text-xs font-medium text-slate-700">{{ $extra->contact_person }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-2xl p-8 text-center text-slate-400 text-sm">
                Belum ada data ekstrakurikuler.
            </div>
            @endforelse
        </div>
    </div>

    {{-- TAB 3: GURU PIKET / JADWAL MENGAJAR --}}
    <div x-show="tab === 'piket'" class="space-y-4">
        @php
            $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($dayNames as $dayNum => $dayLabel)
            @php
                $schedules = $piketSchedule[$dayNum] ?? collect();
            @endphp
            <div class="bg-white rounded-2xl p-4 shadow-2xs border border-slate-200 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-1.5">
                        <span>🗓️</span> Hari {{ $dayLabel }}
                    </h3>
                    <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                        {{ $schedules->count() }} Sesi Mengajar
                    </span>
                </div>

                <div class="space-y-2 max-h-80 overflow-y-auto pr-1 text-xs">
                    @forelse($schedules->take(10) as $sch)
                    <div x-show="!search || '{{ strtolower(($sch->teacher?->name ?? '') . ' ' . ($sch->subject?->name ?? '') . ' ' . ($sch->schoolClass?->name ?? '')) }}'.includes(search.toLowerCase())"
                         class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center justify-between gap-2">
                        <div>
                            <p class="font-bold text-slate-800 leading-tight">{{ $sch->teacher?->name ?? '—' }}</p>
                            <p class="text-[10.5px] text-slate-500">{{ $sch->subject?->name ?? 'Mapel' }} · {{ $sch->schoolClass?->name ?? 'Kelas' }}</p>
                        </div>
                        <span class="text-[10px] font-extrabold px-1.5 py-0.5 bg-slate-200 text-slate-700 rounded-md shrink-0">
                            Jam {{ $sch->period }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">Belum ada jadwal mengajar/piket tercatat</p>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 4: DIREKTORI SELURUH GURU --}}
    <div x-show="tab === 'direktori'" class="space-y-4">
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Direktori Guru & Pengajar SMAN 1 Gianyar</h3>
                <span class="text-xs text-slate-500 font-medium">Total {{ $gurus->count() }} Guru</span>
            </div>
            <div class="divide-y divide-slate-100 max-h-[600px] overflow-y-auto">
                @forelse($gurus as $guru)
                <div x-show="!search || '{{ strtolower($guru->name . ' ' . ($guru->subject ?? '') . ' ' . ($guru->nip ?? '')) }}'.includes(search.toLowerCase())"
                     class="p-4 hover:bg-slate-50 transition-colors flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @if($guru->photo)
                            <img src="{{ $guru->photo_url }}" class="w-10 h-10 rounded-xl object-cover ring-2 ring-blue-500/20 shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-xs">
                                {{ $guru->initials }}
                            </div>
                        @endif
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-tight">{{ $guru->name }}</h4>
                            <p class="text-[11px] text-slate-500 font-medium">NIP: {{ $guru->nip ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                            {{ $guru->subject ?? 'Guru Pengajar' }}
                        </span>
                        @if($guru->phone)
                            <p class="text-[10.5px] text-slate-400 mt-1">📱 {{ $guru->phone }}</p>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-sm">
                    Belum ada data guru tercatat.
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
