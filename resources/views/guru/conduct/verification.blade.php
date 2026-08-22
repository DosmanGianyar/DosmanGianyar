@extends('layouts.guru')
@section('title', 'Verifikasi Pembinaan Disiplin Hari Ini')
@section('page-title', 'Piket Gerbang — Verifikasi Keterlambatan & Pembinaan Mandiri')

@section('content')
<div class="space-y-5" x-data="{ search: '', tab: 'pending' }">

    {{-- Top Banner --}}
    <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 text-white rounded-3xl p-5 shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 bg-white/20 backdrop-blur-md text-white text-[11px] font-black rounded-lg uppercase tracking-wider">⚡ Piket Gerbang</span>
                <span class="text-xs font-semibold text-amber-100">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h2 class="font-black text-xl mt-1.5">Pengajuan Pembinaan Mandiri Hari Ini</h2>
            <p class="text-xs text-amber-100 mt-1 max-w-xl">
                Halaman khusus guru piket untuk memantau & memverifikasi siswa terlambat yang mengajukan pembinaan dari HP saat tiba di gerbang sekolah.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center min-w-[90px] border border-white/20">
                <span class="text-2xl font-black block text-white">{{ $pendingLogs->count() }}</span>
                <span class="text-[10px] uppercase font-bold text-amber-200">Pending</span>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center min-w-[90px] border border-white/20">
                <span class="text-2xl font-black block text-emerald-200">{{ $verifiedLogs->count() }}</span>
                <span class="text-[10px] uppercase font-bold text-emerald-100">Diverifikasi</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-xs font-bold flex items-center gap-2.5 shadow-sm">
            <div class="w-7 h-7 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl p-4 text-xs font-bold flex items-center gap-2.5 shadow-sm">
            <div class="w-7 h-7 rounded-xl bg-blue-500 text-white flex items-center justify-center shrink-0">i</div>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    {{-- Filter & Tab Controls --}}
    <div class="bg-white rounded-2xl p-3 border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 w-full sm:w-auto bg-gray-100 p-1 rounded-xl">
            <button @click="tab = 'pending'"
                :class="tab === 'pending' ? 'bg-white text-amber-700 shadow-xs font-extrabold' : 'text-gray-500 font-semibold hover:text-gray-900'"
                class="px-4 py-2 text-xs rounded-lg transition-all flex items-center gap-2 flex-1 sm:flex-none justify-center">
                <span>⚡ Menunggu Verifikasi</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="tab === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-200 text-gray-600'">
                    {{ $pendingLogs->count() }}
                </span>
            </button>
            <button @click="tab = 'verified'"
                :class="tab === 'verified' ? 'bg-white text-emerald-700 shadow-xs font-extrabold' : 'text-gray-500 font-semibold hover:text-gray-900'"
                class="px-4 py-2 text-xs rounded-lg transition-all flex items-center gap-2 flex-1 sm:flex-none justify-center">
                <span>✓ Sudah Diverifikasi</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="tab === 'verified' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600'">
                    {{ $verifiedLogs->count() }}
                </span>
            </button>
        </div>

        <div class="relative w-full sm:w-72">
            <input type="text" x-model="search" placeholder="Cari nama siswa / kelas..."
                class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    {{-- TAB 1: Menunggu Verifikasi (Pending) --}}
    <div x-show="tab === 'pending'" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-amber-50/50">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></div>
                <h3 class="font-extrabold text-sm text-gray-900">Daftar Pengajuan Pending Hari Ini ({{ $pendingLogs->count() }})</h3>
            </div>
            <span class="text-xs text-amber-800 font-bold bg-amber-100 px-2.5 py-1 rounded-lg">Realtime Gerbang</span>
        </div>

        @if($pendingLogs->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-base text-gray-800">Tidak ada pengajuan mandiri yang pending pagi ini</h4>
                <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">Siswa terlambat yang datang ke gerbang dapat mengajukan pembinaan mandiri langsung lewat HP mereka.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($pendingLogs as $log)
                    @php
                        $studentName = strtolower($log->student?->name ?? '');
                        $className   = strtolower($log->student?->schoolClass?->name ?? '');
                        $latCount    = $log->student?->lateness_count ?? 1;
                    @endphp
                    <div x-show="search === '' || '{{ $studentName }}'.includes(search.toLowerCase()) || '{{ $className }}'.includes(search.toLowerCase())"
                        class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-amber-50/20 transition-colors">
                        
                        <div class="flex items-start gap-3.5">
                            {{-- Avatar --}}
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white font-black flex items-center justify-center text-base shrink-0 shadow-xs uppercase">
                                {{ substr($log->student?->name ?? 'S', 0, 2) }}
                            </div>

                            <div class="space-y-1">
                                {{-- Nama & Badge Kelas --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="font-black text-base text-gray-900">{{ $log->student?->name }}</h4>
                                    
                                    {{-- Badge Kelas --}}
                                    <span class="px-2.5 py-0.5 bg-blue-600 text-white font-extrabold text-xs rounded-md shadow-2xs">
                                        Kelas {{ $log->student?->schoolClass?->name ?? '—' }}
                                    </span>

                                    {{-- Badge Jumlah Keterlambatan --}}
                                    @if($latCount <= 1)
                                        <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold text-xs rounded-md border border-amber-200 flex items-center gap-1">
                                            <span>⚠️ Catatan Ke-1</span>
                                        </span>
                                    @elseif($latCount < 5)
                                        <span class="px-2.5 py-0.5 bg-orange-100 text-orange-800 font-bold text-xs rounded-md border border-orange-200 flex items-center gap-1">
                                            <span>⚠️ Total {{ $latCount }}x Pembinaan</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-red-100 text-red-800 font-black text-xs rounded-md border border-red-300 animate-pulse flex items-center gap-1">
                                            <span>🚨 Sering Terlambat: {{ $latCount }}x</span>
                                        </span>
                                    @endif
                                </div>

                                {{-- Alasan & Keterangan --}}
                                <p class="text-xs text-amber-700 font-bold flex items-center gap-1.5 mt-1">
                                    <span>⚡ {{ $log->displayCategoryName() }}</span>
                                </p>
                                @if($log->parsed_description)
                                    <p class="text-xs text-gray-600 bg-gray-50 border border-gray-100 rounded-xl p-2.5 mt-1.5">
                                        <span class="font-semibold text-gray-500">Alasan siswa:</span> "{{ $log->parsed_description }}"
                                    </p>
                                @endif

                                {{-- Jam Pengajuan --}}
                                <p class="text-[11px] text-gray-400 flex items-center gap-1 pt-1">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Diajukan pukul <span class="font-bold text-gray-700">{{ $log->created_at->format('H:i') }} WITA</span>
                                </p>
                            </div>
                        </div>

                        {{-- Tombol Akses Verifikasi --}}
                        <div class="flex items-center gap-2 shrink-0 md:self-center">
                            <form method="POST" action="{{ route('guru.conduct.verify', $log->id) }}" class="w-full md:w-auto">
                                @csrf
                                <button type="submit"
                                    class="w-full md:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-2xl shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Verifikasi & Izinkan Masuk</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- TAB 2: Sudah Diverifikasi (Verified) --}}
    <div x-show="tab === 'verified'" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-emerald-50/50">
            <h3 class="font-extrabold text-sm text-gray-900">Riwayat Diverifikasi Hari Ini ({{ $verifiedLogs->count() }})</h3>
            <span class="text-xs text-emerald-800 font-bold bg-emerald-100 px-2.5 py-1 rounded-lg">Selesai</span>
        </div>

        @if($verifiedLogs->isEmpty())
            <div class="p-8 text-center text-xs text-gray-400">
                Belum ada pengajuan pembinaan mandiri yang diverifikasi hari ini.
            </div>
        @else
            <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                @foreach($verifiedLogs as $log)
                    @php
                        $studentName = strtolower($log->student?->name ?? '');
                        $className   = strtolower($log->student?->schoolClass?->name ?? '');
                        $latCount    = $log->student?->lateness_count ?? 1;
                    @endphp
                    <div x-show="search === '' || '{{ $studentName }}'.includes(search.toLowerCase()) || '{{ $className }}'.includes(search.toLowerCase())"
                        class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs hover:bg-gray-50/50 transition-colors">
                        
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 font-black flex items-center justify-center text-sm shrink-0">
                                ✓
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h5 class="font-bold text-gray-900 text-sm">{{ $log->student?->name }}</h5>
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-bold text-[11px] rounded-md">
                                        Kelas {{ $log->student?->schoolClass?->name }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 font-semibold text-[11px] rounded-md">
                                        Total {{ $latCount }}x Pembinaan
                                    </span>
                                </div>
                                <p class="text-gray-500 mt-0.5">{{ $log->displayCategoryName() }} — <span class="text-gray-400">Diajukan {{ $log->created_at->format('H:i') }}</span></p>
                            </div>
                        </div>

                        <div class="sm:text-right shrink-0">
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-extrabold text-[11px] rounded-lg border border-emerald-200">
                                Diverifikasi {{ $log->verified_at?->format('H:i') }} WITA
                            </span>
                            <p class="text-[10px] text-gray-400 mt-1">Oleh Guru: <span class="font-semibold text-gray-600">{{ $log->verifier?->name ?? 'Guru Piket' }}</span></p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
