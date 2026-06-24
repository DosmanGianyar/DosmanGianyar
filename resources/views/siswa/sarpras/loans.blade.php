@extends('layouts.siswa')
@section('title', 'Peminjaman Saya')
@section('page-title', 'Peminjaman Aset Saya')

@section('content')
<div class="space-y-4">

    <div class="space-y-3">
        @forelse($loans as $loan)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="text-sm font-semibold text-gray-800">{{ $loan->asset->name }}</p>
                @php
                    $colors = ['pending'=>'bg-yellow-100 text-yellow-700','approved'=>'bg-blue-100 text-blue-700','active'=>'bg-green-100 text-green-700','returned'=>'bg-gray-100 text-gray-600','rejected'=>'bg-red-100 text-red-700'];
                @endphp
                <span class="text-xs px-2 py-0.5 rounded-full font-medium shrink-0 {{ $colors[$loan->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $loan->statusLabel() }}
                </span>
            </div>
            <p class="text-xs text-gray-500">{{ $loan->purpose }}</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ $loan->start_date->format('d M Y') }} – {{ $loan->end_date->format('d M Y') }}
            </p>
            @if($loan->status === 'rejected' && $loan->rejection_note)
            <div class="mt-2 bg-red-50 rounded-xl px-3 py-2">
                <p class="text-xs text-red-600"><span class="font-medium">Alasan:</span> {{ $loan->rejection_note }}</p>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <p class="text-gray-400 text-sm">Belum ada riwayat peminjaman</p>
        </div>
        @endforelse
    </div>

    @if($loans->hasPages())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3">{{ $loans->links() }}</div>
    @endif

    <a href="{{ route('siswa.sarpras.scan') }}"
        class="block w-full py-3 text-center text-sm font-semibold text-white bg-blue-600 rounded-2xl hover:bg-blue-700 transition-colors">
        Scan QR untuk Pinjam Aset
    </a>

</div>
@endsection
