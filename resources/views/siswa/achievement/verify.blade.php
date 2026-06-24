@extends('layouts.siswa')
@section('title', 'Verifikasi Prestasi')
@section('page-title', 'Verifikasi Prestasi')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    {{-- Stats --}}
    @if($pendingCount > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-3 flex items-center gap-3">
        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center shrink-0">
            <span class="text-yellow-700 font-bold text-sm">{{ $pendingCount }}</span>
        </div>
        <p class="text-sm text-yellow-800 font-medium">prestasi menunggu verifikasi</p>
    </div>
    @endif

    {{-- Filter tabs --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $val => $label)
        <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
            class="shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                {{ $status === $val ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- List --}}
    @forelse($achievements as $achievement)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-3">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm leading-tight">{{ $achievement->title }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $achievement->student->name }}
                    @if($achievement->student->schoolClass)
                        · <span>{{ $achievement->student->schoolClass->name }}</span>
                    @endif
                </p>
            </div>
            <span class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full {{ $achievement->statusBadgeClass() }}">
                {{ $achievement->statusLabel() }}
            </span>
        </div>

        {{-- Meta --}}
        <div class="flex flex-wrap gap-2">
            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $achievement->levelBadgeClass() }}">
                {{ $achievement->levelLabel() }}
            </span>
            @if($achievement->rank)
            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                {{ $achievement->rank }}
            </span>
            @endif
            @if($achievement->category)
            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                {{ $achievement->category->name }}
            </span>
            @endif
            <span class="text-xs text-gray-400">{{ $achievement->achievement_date->translatedFormat('d M Y') }}</span>
        </div>

        {{-- Files --}}
        @if($achievement->photoUrl() || $achievement->certificateUrl())
        <div class="flex gap-2">
            @if($achievement->photoUrl())
            <a href="{{ $achievement->photoUrl() }}" target="_blank"
                class="flex-1 flex items-center gap-1.5 bg-blue-50 rounded-xl px-3 py-2 text-xs text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Foto Kegiatan
            </a>
            @endif
            @if($achievement->certificateUrl())
            <a href="{{ $achievement->certificateUrl() }}" target="_blank"
                class="flex-1 flex items-center gap-1.5 bg-green-50 rounded-xl px-3 py-2 text-xs text-green-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Piagam
            </a>
            @endif
        </div>
        @endif

        {{-- Rejection Reason --}}
        @if($achievement->rejection_reason)
        <div class="bg-red-50 rounded-xl p-3 text-xs text-red-700">
            <span class="font-semibold">Alasan ditolak:</span> {{ $achievement->rejection_reason }}
        </div>
        @endif

        {{-- Actions (pending only) --}}
        @if($achievement->status === 'pending')
        <div class="flex gap-2 pt-1">
            <form method="POST" action="{{ route('siswa.achievements.approve', $achievement) }}" class="flex-1">
                @csrf @method('PATCH')
                <button type="submit"
                    class="w-full bg-green-600 text-white text-sm font-semibold py-2.5 rounded-xl">
                    Setujui
                </button>
            </form>
            <button type="button"
                onclick="document.getElementById('reject-modal-{{ $achievement->id }}').showModal()"
                class="flex-1 bg-red-100 text-red-700 text-sm font-semibold py-2.5 rounded-xl">
                Tolak
            </button>
        </div>

        {{-- Reject Modal --}}
        <dialog id="reject-modal-{{ $achievement->id }}"
            class="rounded-2xl p-6 w-full max-w-sm shadow-xl backdrop:bg-black/50">
            <p class="font-bold text-gray-800 mb-3">Alasan Penolakan</p>
            <form method="POST" action="{{ route('siswa.achievements.reject', $achievement) }}">
                @csrf @method('PATCH')
                <textarea name="rejection_reason" required rows="3"
                    placeholder="Tuliskan alasan penolakan yang jelas untuk siswa..."
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm resize-none mb-3"></textarea>
                <div class="flex gap-2">
                    <button type="button" onclick="this.closest('dialog').close()"
                        class="flex-1 bg-gray-100 text-gray-700 font-semibold py-2.5 rounded-xl text-sm">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 bg-red-600 text-white font-semibold py-2.5 rounded-xl text-sm">
                        Tolak
                    </button>
                </div>
            </form>
        </dialog>
        @endif

    </div>
    @empty
    <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
        <p class="text-gray-500 font-medium">Tidak ada prestasi dengan status ini</p>
    </div>
    @endforelse

</div>
@endsection
