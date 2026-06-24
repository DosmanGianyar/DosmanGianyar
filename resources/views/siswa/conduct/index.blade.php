@extends('layouts.siswa')
@section('title', 'Poin Saya')
@section('page-title', 'Poin & Perilaku')

@section('content')
<div class="space-y-4">

    {{-- Total Poin Card --}}
    @php
        $barPct   = max(0, min(100, (($totalPoint + 100) / 200) * 100));
        $barColor = $totalPoint >= 0 ? 'bg-green-500' : ($totalPoint >= -50 ? 'bg-yellow-500' : 'bg-red-500');
        $bgGrad   = $totalPoint >= 0 ? 'from-green-600 to-emerald-700' : ($totalPoint >= -50 ? 'from-yellow-500 to-orange-600' : 'from-red-600 to-rose-700');
        $ptLabel  = $totalPoint >= 0 ? 'Baik' : ($totalPoint >= -50 ? 'Perlu Perhatian' : 'Kritis');
    @endphp
    <div class="bg-linear-to-br {{ $bgGrad }} rounded-2xl p-4 text-white">
        <p class="text-xs font-medium opacity-80 mb-1">Total Poin Aktif</p>
        <div class="flex items-end justify-between mb-3">
            <p class="text-4xl font-black">{{ $totalPoint >= 0 ? '+' : '' }}{{ $totalPoint }}</p>
            <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full">
                {{ $ptLabel }}
            </span>
        </div>
        <div class="h-2 bg-white/20 rounded-full overflow-hidden">
            <div class="bg-white/80 h-full rounded-full transition-all" style="width: {{ $barPct }}%"></div>
        </div>
        <div class="flex justify-between mt-1">
            <span class="text-xs opacity-60">-100</span>
            <span class="text-xs opacity-60">0</span>
            <span class="text-xs opacity-60">+100</span>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-green-50 rounded-2xl p-4 text-center">
            <p class="text-2xl font-bold text-green-700">+{{ $prestasi }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Prestasi</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-4 text-center">
            <p class="text-2xl font-bold text-red-700">{{ $pelanggaran }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Pelanggaran</p>
        </div>
    </div>

    {{-- Tren Poin Bulanan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <p class="text-sm font-semibold text-gray-700 mb-3">Tren Poin (6 Bulan Terakhir)</p>
        @php
            $maxAbs = max($trend->map(fn($t) => abs($t['net']))->max(), 1);
        @endphp
        <div class="flex items-end gap-2" style="height: 72px;">
            @foreach($trend as $t)
            @php
                $h    = max(4, round((abs($t['net']) / $maxAbs) * 52));
                $pos  = $t['net'] >= 0;
                $bar  = $pos ? 'bg-green-500' : 'bg-red-400';
            @endphp
            <div class="flex-1 flex flex-col items-center gap-0.5">
                <span class="text-[9px] font-bold {{ $pos ? 'text-green-600' : 'text-red-500' }}">
                    {{ $t['net'] >= 0 ? '+' : '' }}{{ $t['net'] }}
                </span>
                <div class="w-full {{ $bar }} rounded-t-md" style="height: {{ $h }}px"></div>
                <span class="text-[9px] text-gray-400 font-medium">{{ $t['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    @if($totalPoint <= -75)
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-red-800">Poin Kritis</p>
            <p class="text-xs text-red-600 mt-0.5">Total poin kamu sudah melewati batas -75. Segera temui BK untuk pembinaan.</p>
        </div>
    </div>
    @endif

    {{-- Riwayat --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Riwayat Poin</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($logs as $log)
            <div class="flex items-start gap-3 px-4 py-3">
                <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center
                    {{ $log->point > 0 ? 'bg-green-100' : 'bg-red-100' }}">
                    <svg class="w-4 h-4 {{ $log->point > 0 ? 'text-green-600' : 'text-red-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($log->point > 0)
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
                    </p>
                </div>
                <span class="text-sm font-bold shrink-0 {{ $log->point > 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $log->point > 0 ? '+' : '' }}{{ $log->point }}
                </span>
            </div>
            @empty
            <div class="px-4 py-8 text-center">
                <p class="text-gray-400 text-sm">Belum ada riwayat poin</p>
            </div>
            @endforelse
        </div>
        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </div>

</div>
@endsection
