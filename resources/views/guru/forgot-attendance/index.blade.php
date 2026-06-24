@extends('layouts.guru')
@section('title', 'Lupa Absen Siswa')
@section('page-title', 'Persetujuan Lupa Absen')

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-3">
        <p class="text-sm text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    @if(! $homeroomClass)
    {{-- Bukan wali kelas --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
        <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="font-semibold text-amber-800 mb-1">Anda Bukan Wali Kelas</p>
        <p class="text-sm text-amber-600">Fitur ini hanya dapat diakses oleh wali kelas yang ditugaskan pada suatu kelas.</p>
    </div>
    @else
    {{-- Info kelas --}}
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-3 flex items-center gap-3">
        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-blue-800">Wali Kelas {{ $homeroomClass->name }}</p>
            <p class="text-xs text-blue-600">Pengajuan lupa absen dari siswa kelas Anda</p>
        </div>
    </div>

    @forelse($requests as $req)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">

        {{-- Header siswa --}}
        <div class="flex items-start justify-between gap-2 mb-3">
            <div>
                <p class="font-semibold text-gray-800 text-sm">{{ $req->student->name }}</p>
                <p class="text-xs text-gray-400">NIS: {{ $req->student->nis ?? '—' }}</p>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $req->statusBadgeClass() }}">
                {{ $req->statusLabel() }}
            </span>
        </div>

        {{-- Detail pengajuan --}}
        <div class="bg-gray-50 rounded-xl p-3 mb-3 space-y-1.5">
            <div class="flex items-center gap-2 text-xs">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-gray-500">Tanggal:</span>
                <span class="font-semibold text-gray-700">{{ $req->date->isoFormat('dddd, D MMMM Y') }}</span>
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

        @if($req->teacher_note && ! $req->isPending())
        <div class="bg-gray-50 rounded-xl px-3 py-2 mb-3">
            <p class="text-xs text-gray-500">
                <span class="font-semibold">Catatan Anda:</span> {{ $req->teacher_note }}
            </p>
            <p class="text-[11px] text-gray-400 mt-0.5">{{ $req->reviewed_at?->isoFormat('D MMM Y, HH:mm') }}</p>
        </div>
        @endif

        {{-- Action buttons (only for pending) --}}
        @if($req->isPending())
        <div class="space-y-2 mt-1" x-data="{ showReject: false }">

            {{-- Approve --}}
            <form action="{{ route('guru.forgot-attendance.approve', $req) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="flex gap-2">
                    <input type="text" name="teacher_note"
                        placeholder="Catatan (opsional)"
                        maxlength="255"
                        class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-green-400 focus:ring-1 focus:ring-green-400">
                    <button type="submit"
                        onclick="return confirm('Setujui pengajuan lupa absen ini?')"
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-xl transition-colors shrink-0">
                        Setujui
                    </button>
                </div>
            </form>

            {{-- Reject --}}
            <div>
                <button type="button"
                    x-on:click="showReject = !showReject"
                    class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Tolak Pengajuan
                </button>
                <div x-show="showReject" x-cloak class="mt-2">
                    <form action="{{ route('guru.forgot-attendance.reject', $req) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="flex gap-2">
                            <input type="text" name="teacher_note"
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
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm font-medium text-gray-400">Tidak ada pengajuan lupa absen</p>
        <p class="text-xs text-gray-300 mt-1">dari siswa kelas {{ $homeroomClass->name }}</p>
    </div>
    @endforelse

    {{ $requests->links() }}
    @endif

</div>
@endsection
