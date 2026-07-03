@extends('layouts.guru')
@section('title', 'Detail Siswa')
@section('page-title', 'Detail Prestasi & Pelanggaran')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    {{-- Header Siswa --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                <span class="text-sm font-bold text-blue-600">{{ $student->initials }}</span>
            </div>
            <div>
                <h2 class="font-bold text-gray-800">{{ $student->name }}</h2>
                <p class="text-xs text-gray-500">{{ $student->schoolClass?->name }} · NIS {{ $student->nis }}</p>
            </div>
            <a href="{{ route('guru.conduct.create', ['student_id' => $student->id]) }}"
                class="ml-auto px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition-colors shrink-0">
                + Catat
            </a>
        </div>

        {{-- Ringkasan Prestasi & Pelanggaran --}}
        <div class="grid grid-cols-2 gap-3 text-center">
            <div class="bg-green-50 rounded-xl py-3">
                <p class="text-lg font-bold text-green-600">{{ $prestasiCount }}</p>
                <p class="text-xs text-gray-500">Prestasi</p>
            </div>
            <div class="bg-red-50 rounded-xl py-3">
                <p class="text-lg font-bold text-red-600">{{ $pelanggaranCount }}</p>
                <p class="text-xs text-gray-500">Pelanggaran</p>
            </div>
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Riwayat Catatan</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($logs as $log)
            <div class="flex items-start gap-3 px-4 py-3">
                <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center
                    {{ $log->isPrestasi() ? 'bg-green-100' : 'bg-red-100' }}">
                    <svg class="w-4 h-4 {{ $log->isPrestasi() ? 'text-green-600' : 'text-red-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($log->isPrestasi())
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        @endif
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $log->category->name }}</p>
                    @if($log->note)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $log->note }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $log->created_at->isoFormat('D MMM Y, HH:mm') }}
                        · oleh {{ $log->teacher->name }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-lg
                        {{ $log->isPrestasi() ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                        {{ $log->isPrestasi() ? 'Prestasi' : 'Pelanggaran' }}
                    </span>
                    @if($log->photo)
                    <a href="{{ Storage::url($log->photo) }}" target="_blank"
                        class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-50 text-blue-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada catatan</div>
            @endforelse
        </div>
        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </div>

    {{-- Log BK --}}
    @if($bkLogs->count())
    <div class="bg-orange-50 border border-orange-200 rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-orange-200 flex items-center gap-2">
            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="text-sm font-semibold text-orange-800">Log BK / Pembinaan</h3>
        </div>
        <div class="divide-y divide-orange-100">
            @foreach($bkLogs as $bk)
            <div class="px-4 py-3">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm text-orange-900">{{ $bk->coaching_note }}</p>
                    @if($bk->is_auto)
                    <span class="text-xs bg-orange-200 text-orange-700 px-2 py-0.5 rounded-full shrink-0">Auto</span>
                    @endif
                </div>
                <p class="text-xs text-orange-600 mt-1">
                    {{ $bk->date->isoFormat('D MMM Y') }}
                    · Konselor: {{ $bk->counselor?->name ?? '—' }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
