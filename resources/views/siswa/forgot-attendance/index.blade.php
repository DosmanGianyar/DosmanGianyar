@extends('layouts.siswa')
@section('title', 'Lupa Absen')
@section('page-title', 'Lupa Absen')

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-2xl p-3 mb-4 flex items-center gap-2">
    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- Header info --}}
<div class="bg-linear-to-br from-amber-500 to-orange-600 rounded-2xl p-4 mb-4 text-white">
    <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="font-bold text-base">Lupa Absen</p>
            <p class="text-orange-100 text-xs">Ajukan koreksi presensi ke wali kelas</p>
        </div>
    </div>
    <p class="text-xs text-orange-100 leading-relaxed">
        Jika kamu lupa absen atau presensimu tidak terekam, ajukan permohonan di sini.
        Wali kelas akan meninjau dan menyetujui pengajuanmu.
    </p>
</div>

{{-- Tombol Buat Pengajuan --}}
<a href="{{ route('siswa.forgot-attendance.create') }}"
    class="flex items-center justify-center gap-2 w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-2xl mb-4 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Buat Pengajuan Baru
</a>

{{-- Riwayat Pengajuan --}}
<p class="text-sm font-semibold text-gray-700 px-1 mb-2">Riwayat Pengajuan</p>

<div class="space-y-2">
@forelse($requests as $req)
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-start justify-between gap-2 mb-2">
        <div>
            <p class="text-sm font-semibold text-gray-800">
                {{ $req->date->isoFormat('dddd, D MMMM Y') }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5 leading-snug">{{ $req->reason }}</p>
        </div>
        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $req->statusBadgeClass() }}">
            {{ $req->statusLabel() }}
        </span>
    </div>

    @if($req->teacher_note)
    <div class="bg-gray-50 rounded-xl px-3 py-2 mt-2">
        <p class="text-xs text-gray-500">
            <span class="font-semibold">Catatan wali kelas:</span> {{ $req->teacher_note }}
        </p>
    </div>
    @endif

    @if($req->reviewed_at)
    <p class="text-[11px] text-gray-400 mt-2">
        Ditinjau {{ $req->reviewed_at->isoFormat('D MMM Y, HH:mm') }}
    </p>
    @else
    <p class="text-[11px] text-gray-400 mt-2">
        Diajukan {{ $req->created_at->isoFormat('D MMM Y, HH:mm') }}
    </p>
    @endif

    {{-- Tombol batal (hanya untuk pending) --}}
    @if($req->isPending())
    <form action="{{ route('siswa.forgot-attendance.destroy', $req) }}" method="POST" class="mt-3"
        onsubmit="return confirm('Batalkan pengajuan ini?')">
        @csrf
        @method('DELETE')
        <button type="submit"
            class="text-xs font-semibold text-red-500 hover:text-red-700 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Batalkan Pengajuan
        </button>
    </form>
    @endif
</div>
@empty
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center">
    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="text-sm text-gray-400">Belum ada pengajuan lupa absen</p>
</div>
@endforelse
</div>

{{ $requests->links() }}

@endsection
