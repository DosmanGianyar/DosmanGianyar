@extends('layouts.guru')
@section('title', 'Verifikasi Pembinaan Disiplin Mandiri')
@section('page-title', 'Verifikasi Pembinaan Gerbang (Sipinter)')

@section('content')
<div class="space-y-4">

    {{-- Banner Alert --}}
    <div class="bg-amber-500 text-white rounded-2xl p-4 shadow-sm flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-amber-600 text-white text-[11px] font-extrabold rounded-md uppercase tracking-wider">Piket Gerbang</span>
                <h2 class="font-bold text-base">Verifikasi Pembinaan Mandiri Hari Ini</h2>
            </div>
            <p class="text-xs text-amber-100 mt-1">Siswa terlambat yang telah mengajukan pembinaan mandiri melalui HP.</p>
        </div>
        <div class="text-right">
            <span class="text-2xl font-black">{{ $pendingLogs->count() }}</span>
            <p class="text-[10px] uppercase font-bold text-amber-200">Pending</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-3 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-3 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    {{-- Section 1: Menunggu Verifikasi --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-amber-50/50">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></div>
                <h3 class="font-bold text-sm text-gray-800">Menunggu Verifikasi Guru ({{ $pendingLogs->count() }})</h3>
            </div>
            <span class="text-xs text-gray-500 font-medium">{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>

        @if($pendingLogs->isEmpty())
            <div class="p-8 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-gray-600">Tidak ada pengajuan mandiri yang pending pagi ini.</p>
                <p class="text-xs text-gray-400 mt-1">Siswa terlambat dapat langsung mengajukan via akun siswa di HP mereka.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($pendingLogs as $log)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 font-extrabold flex items-center justify-center text-sm shrink-0 uppercase">
                                {{ substr($log->student?->name ?? 'S', 0, 2) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-sm text-gray-900">{{ $log->student?->name }}</h4>
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold text-[10px] rounded-md">
                                        {{ $log->student?->schoolClass?->name ?? '—' }}
                                    </span>
                                </div>
                                <p class="text-xs text-amber-700 font-semibold mt-0.5">
                                    {{ $log->displayCategoryName() }}
                                </p>
                                @if($log->parsed_description)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $log->parsed_description }}</p>
                                @endif
                                <p class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Diajukan pukul {{ $log->created_at->format('H:i') }} WITA
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('guru.conduct.verify', $log->id) }}" class="shrink-0">
                            @csrf
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Verifikasi & Izinkan Masuk</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Section 2: Riwayat Diverifikasi Hari Ini --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-gray-800">Sudah Diverifikasi Hari Ini ({{ $verifiedLogs->count() }})</h3>
        </div>

        @if($verifiedLogs->isEmpty())
            <div class="p-6 text-center text-xs text-gray-400">
                Belum ada pengajuan mandiri yang diverifikasi hari ini.
            </div>
        @else
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                @foreach($verifiedLogs as $log)
                    <div class="p-3.5 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs shrink-0">
                                ✓
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">{{ $log->student?->name }} <span class="text-gray-400 font-normal">({{ $log->student?->schoolClass?->name }})</span></p>
                                <p class="text-gray-500 text-[11px]">{{ $log->displayCategoryName() }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold text-[10px] rounded-md">Diverifikasi {{ $log->verified_at?->format('H:i') }}</span>
                            <p class="text-[10px] text-gray-400 mt-0.5">Oleh: {{ $log->verifier?->name ?? 'Guru' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
