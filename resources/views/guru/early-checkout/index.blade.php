@extends('layouts.guru')
@section('title', 'Izin Pulang Awal Siswa')
@section('page-title', 'Izin Pulang Lebih Awal')

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-3">
        <p class="text-sm text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Filter --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $val => $label)
        <a href="{{ route('guru.early-checkout.index', ['status' => $val]) }}"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors
                {{ $status === $val
                    ? 'bg-emerald-600 text-white shadow-sm'
                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
            @if($val === 'pending' && $pendingCount > 0)
            <span class="ml-1 bg-white/30 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full
                {{ $status === 'pending' ? '' : 'bg-emerald-100 text-emerald-700' }}">
                {{ $pendingCount }}
            </span>
            @endif
        </a>
        @endforeach
    </div>

    @forelse($requests as $req)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">

        {{-- Siswa & Kelas --}}
        <div class="flex items-start justify-between gap-2 mb-3">
            <div>
                <p class="font-semibold text-gray-800 text-sm">{{ $req->student->name }}</p>
                <p class="text-xs text-gray-400">
                    {{ $req->student->schoolClass?->name ?? '—' }}
                    @if($req->student->nis) · NIS {{ $req->student->nis }} @endif
                </p>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $req->statusBadgeClass() }}">
                {{ $req->statusLabel() }}
            </span>
        </div>

        {{-- Detail --}}
        <div class="bg-gray-50 rounded-xl p-3 mb-3 space-y-1.5">
            <div class="flex items-center gap-2 text-xs">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-gray-500">Tanggal:</span>
                <span class="font-semibold text-gray-700">{{ $req->date->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-gray-500">Rencana pulang:</span>
                <span class="font-bold text-emerald-700">Pukul {{ $req->requestedTimeFormatted() }}</span>
            </div>
            <div class="flex items-start gap-2 text-xs">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="text-gray-500 shrink-0">Alasan:</span>
                <span class="text-gray-700 leading-snug">{{ $req->reason }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-gray-500">Diajukan:</span>
                <span class="text-gray-600">{{ $req->created_at->isoFormat('D MMM Y, HH:mm') }}</span>
            </div>
        </div>

        @if($req->reviewer_note && ! $req->isPending())
        <div class="bg-gray-50 rounded-xl px-3 py-2 mb-3">
            <p class="text-xs text-gray-500">
                <span class="font-semibold">Catatan Anda:</span> {{ $req->reviewer_note }}
            </p>
            <p class="text-[11px] text-gray-400 mt-0.5">
                {{ $req->reviewed_at?->isoFormat('D MMM Y, HH:mm') }}
            </p>
        </div>
        @endif

        {{-- Actions --}}
        @php
            $canReview = auth()->user()->role === 'admin'
                || auth()->user()->isBk()
                || (auth()->user()->homeroomClass?->id && auth()->user()->homeroomClass->id === $req->student->class_id);
        @endphp
        @if($req->isPending() && $canReview)
        <div class="space-y-2 mt-1" x-data="{ showReject: false }">

            {{-- Approve --}}
            <form action="{{ route('guru.early-checkout.approve', $req) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="flex gap-2">
                    <input type="text" name="reviewer_note"
                        placeholder="Catatan (opsional)"
                        maxlength="255"
                        class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400">
                    <button type="submit"
                        onclick="return confirm('Setujui izin pulang lebih awal ini?')"
                        class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded-xl transition-colors shrink-0">
                        Setujui
                    </button>
                </div>
            </form>

            {{-- Reject --}}
            <div>
                <button type="button" x-on:click="showReject = !showReject"
                    class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Tolak Pengajuan
                </button>
                <div x-show="showReject" x-cloak class="mt-2">
                    <form action="{{ route('guru.early-checkout.reject', $req) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="flex gap-2">
                            <input type="text" name="reviewer_note"
                                placeholder="Alasan penolakan (wajib)"
                                maxlength="255"
                                required
                                class="flex-1 border border-red-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-red-400 focus:ring-1 focus:ring-red-400">
                            <button type="submit"
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-xl transition-colors shrink-0">
                                Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-14 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <p class="text-sm font-medium text-gray-400">Tidak ada pengajuan izin pulang lebih awal</p>
    </div>
    @endforelse

    {{ $requests->links() }}

</div>
@endsection
