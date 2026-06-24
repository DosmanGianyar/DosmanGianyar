@extends('layouts.guru')
@section('title', 'Sarpras')
@section('page-title', 'Dashboard Sarpras')

@section('content')
<div class="space-y-6">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ $stats['total_assets'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Aset</p>
        </div>
        <div class="bg-green-50 rounded-2xl p-4 shadow-sm border border-green-100 text-center">
            <p class="text-2xl font-bold text-green-700">{{ $stats['baik'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Kondisi Baik</p>
        </div>
        <div class="bg-yellow-50 rounded-2xl p-4 shadow-sm border border-yellow-100 text-center">
            <p class="text-2xl font-bold text-yellow-700">{{ $stats['rusak_ringan'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Rusak Ringan</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-4 shadow-sm border border-red-100 text-center">
            <p class="text-2xl font-bold text-red-700">{{ $stats['rusak_berat'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Rusak Berat</p>
        </div>
        <div class="bg-orange-50 rounded-2xl p-4 shadow-sm border border-orange-100 text-center">
            <p class="text-2xl font-bold text-orange-700">{{ $stats['pending_damage'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Laporan Baru</p>
            @if($stats['overdue_damage'] > 0)
            <p class="text-[10px] font-bold text-red-600 mt-1 bg-red-100 rounded-full px-2 py-0.5 inline-block">
                {{ $stats['overdue_damage'] }} terlambat
            </p>
            @endif
        </div>
        <div class="bg-purple-50 rounded-2xl p-4 shadow-sm border border-purple-100 text-center">
            <p class="text-2xl font-bold text-purple-700">{{ $stats['pending_loans'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Pinjaman Baru</p>
        </div>
    </div>

    {{-- Export Laporan --}}
    <div class="flex justify-end">
        <a href="{{ route('guru.export.sarpras.form') }}"
            class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl shadow-sm hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export PDF
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Laporan Kerusakan Terbaru --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-800">Kerusakan Belum Ditangani</h2>
                <a href="{{ route('guru.sarpras.damage') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentDamage as $report)
                <div class="px-5 py-3 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $report->asset->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $report->description }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Oleh {{ $report->reporter->name }} · {{ $report->created_at->diffForHumans() }}</p>
                    </div>
                    <form method="POST" action="{{ route('guru.sarpras.damage.progress', $report) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors shrink-0">
                            Tangani
                        </button>
                    </form>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Tidak ada laporan kerusakan baru</div>
                @endforelse
            </div>
        </div>

        {{-- Peminjaman Menunggu Approval --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-800">Peminjaman Menunggu Persetujuan</h2>
                <a href="{{ route('guru.sarpras.loans') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentLoans as $loan)
                <div class="px-5 py-3 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $loan->asset->name }}</p>
                        <p class="text-xs text-gray-500">{{ $loan->user->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $loan->start_date->format('d M') }} – {{ $loan->end_date->format('d M Y') }}
                        </p>
                    </div>
                    <div class="flex gap-1.5 shrink-0">
                        <form method="POST" action="{{ route('guru.sarpras.loans.approve', $loan) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs px-2 py-1 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                                Setuju
                            </button>
                        </form>
                        <button onclick="document.getElementById('reject-{{ $loan->id }}').classList.toggle('hidden')"
                            class="text-xs px-2 py-1 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors">
                            Tolak
                        </button>
                    </div>
                </div>
                {{-- Reject form (hidden) --}}
                <div id="reject-{{ $loan->id }}" class="hidden px-5 pb-3">
                    <form method="POST" action="{{ route('guru.sarpras.loans.reject', $loan) }}" class="flex gap-2">
                        @csrf @method('PATCH')
                        <input type="text" name="rejection_note" placeholder="Alasan penolakan..."
                            class="flex-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-300">
                        <button type="submit" class="text-xs px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Kirim</button>
                    </form>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Tidak ada peminjaman menunggu persetujuan</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
