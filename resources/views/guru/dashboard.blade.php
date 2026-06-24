@extends('layouts.guru')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Sapaan --}}
<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-800">
        Selamat {{ now()->hour < 11 ? 'Pagi' : (now()->hour < 15 ? 'Siang' : 'Sore') }},
        {{ explode(' ', $guru->name)[0] }} 👋
    </h2>
    <p class="text-sm text-gray-500 mt-0.5">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
</div>

{{-- ─── Stat Cards ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-3 mb-5">

    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-500">Total Siswa</span>
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-800">{{ $stats['total_students'] }}</p>
        <p class="text-xs text-blue-600 mt-0.5">siswa di kelas wali</p>
    </div>

    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 {{ $stats['alert_kritis'] > 0 ? 'border-orange-300 bg-orange-50' : '' }}">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-500">Alert Poin Kritis</span>
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-800">{{ $stats['alert_kritis'] }}</p>
        <p class="text-xs text-orange-600 mt-0.5">perlu perhatian BK</p>
    </div>
</div>

{{-- ─── Quick Action Bar ─────────────────────────────────────────── --}}
<div class="flex flex-wrap gap-2 mb-4">
    <a href="{{ route('guru.grades.index') }}"
        class="flex items-center gap-1.5 px-3 py-2 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-xl hover:bg-emerald-100 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
        Input Nilai
    </a>
    <a href="{{ route('guru.export.grades.form') }}"
        class="flex items-center gap-1.5 px-3 py-2 bg-teal-50 text-teal-700 text-xs font-semibold rounded-xl hover:bg-teal-100 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Nilai
    </a>
</div>

<div class="pb-2">

    {{-- ─── Alert Poin Kritis ───────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-orange-50">
            <h3 class="text-sm font-semibold text-orange-800 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Alert Poin Kritis
            </h3>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentAlerts as $alert)
            <div class="px-4 py-3">
                <p class="text-sm font-medium text-gray-800">{{ $alert['name'] }}</p>
                <p class="text-xs text-gray-500">{{ $alert['class'] }}</p>
                <div class="mt-1 flex items-center gap-1">
                    <span class="text-sm font-bold text-red-600">{{ $alert['point'] }}</span>
                    <span class="text-xs text-gray-400">poin</span>
                </div>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-sm text-gray-400">
                Tidak ada alert poin kritis
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
