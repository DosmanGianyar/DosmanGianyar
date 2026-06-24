@extends('layouts.siswa')
@section('title', $asset->name)
@section('page-title', 'Detail Aset')

@section('content')
<div class="space-y-4">

    {{-- Asset Header Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($asset->photo)
        <img src="{{ Storage::url($asset->photo) }}" class="w-full h-48 object-cover">
        @else
        <div class="w-full h-36 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
            <svg class="w-16 h-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
        </div>
        @endif

        <div class="p-4">
            <h1 class="text-lg font-bold text-gray-900">{{ $asset->name }}</h1>
            <div class="flex flex-wrap gap-2 mt-2">
                <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700 font-medium">
                    {{ $asset->categoryLabel() }}
                </span>
                <span class="text-xs px-2 py-1 rounded-full font-medium
                    {{ $asset->condition === 'baik' ? 'bg-green-50 text-green-700' : ($asset->condition === 'rusak_ringan' ? 'bg-yellow-50 text-yellow-700' : 'bg-red-50 text-red-700') }}">
                    {{ $asset->conditionLabel() }}
                </span>
                @if($pendingDamage > 0)
                <span class="text-xs px-2 py-1 rounded-full bg-orange-50 text-orange-700 font-medium">
                    {{ $pendingDamage }} lap. kerusakan aktif
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Detail Grid --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-50">
        @if($asset->room)
        <div class="flex items-center justify-between px-4 py-3">
            <span class="text-sm text-gray-500">Ruangan</span>
            <span class="text-sm font-medium text-gray-800">{{ $asset->room->name }}</span>
        </div>
        @endif
        <div class="flex items-center justify-between px-4 py-3">
            <span class="text-sm text-gray-500">Jumlah</span>
            <span class="text-sm font-medium text-gray-800">{{ $asset->quantity }} unit</span>
        </div>
        @if($asset->purchase_year)
        <div class="flex items-center justify-between px-4 py-3">
            <span class="text-sm text-gray-500">Tahun Pengadaan</span>
            <span class="text-sm font-medium text-gray-800">{{ $asset->purchase_year }}</span>
        </div>
        @endif
        @if($asset->description)
        <div class="px-4 py-3">
            <p class="text-sm text-gray-500 mb-1">Keterangan</p>
            <p class="text-sm text-gray-800">{{ $asset->description }}</p>
        </div>
        @endif
    </div>

    {{-- Active Loan Warning --}}
    @if($myActiveLoan)
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <p class="text-sm font-semibold text-blue-800">Kamu memiliki pinjaman aktif</p>
        <p class="text-xs text-blue-600 mt-1">
            Status: <strong>{{ $myActiveLoan->statusLabel() }}</strong> ·
            {{ $myActiveLoan->start_date->format('d M') }} – {{ $myActiveLoan->end_date->format('d M Y') }}
        </p>
    </div>
    @endif

    {{-- Action Buttons --}}
    <div class="grid grid-cols-2 gap-3">
        {{-- Laporkan Kerusakan --}}
        <a href="{{ route('siswa.sarpras.damage.create', $asset) }}"
            class="flex flex-col items-center gap-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:bg-orange-50 hover:border-orange-200 transition-colors">
            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-gray-700 text-center">Laporkan Kerusakan</p>
        </a>

        {{-- Pinjam Aset --}}
        @if(!$myActiveLoan)
        <a href="{{ route('siswa.sarpras.loan.create', $asset) }}"
            class="flex flex-col items-center gap-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:bg-purple-50 hover:border-purple-200 transition-colors">
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-gray-700 text-center">Pinjam Aset</p>
        </a>
        @else
        <div class="flex flex-col items-center gap-2 bg-gray-50 rounded-2xl border border-gray-100 p-4 opacity-50">
            <div class="w-10 h-10 bg-gray-200 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-gray-500 text-center">Sudah Dipinjam</p>
        </div>
        @endif
    </div>

    {{-- Riwayat Laporan Kerusakan --}}
    @if($asset->damageReports->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Riwayat Laporan Kerusakan</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($asset->damageReports->take(3) as $report)
            <div class="px-4 py-3 flex items-start gap-3">
                <span class="text-xs px-2 py-0.5 rounded-full shrink-0 mt-0.5 font-medium
                    {{ $report->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($report->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                    {{ $report->statusLabel() }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-700 truncate">{{ $report->description }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $report->created_at->isoFormat('D MMM Y') }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
