@extends('layouts.siswa')
@section('title', 'Ekstrakurikuler')
@section('page-title', 'Ekstrakurikuler')

@section('content')

{{-- ─── Header Banner ─────────────────────────────────────────────────── --}}
<div class="rounded-2xl p-4 mb-4 text-white shadow-md" style="background: linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%) !important; color: #ffffff !important;">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
        </div>
        <div>
            <p class="text-white font-bold text-base leading-tight">Ekstrakurikuler</p>
            <p class="text-purple-200 text-xs mt-0.5">{{ $extracurriculars->count() }} ekstra tersedia</p>
        </div>
    </div>

    @php
        $myActive  = $myMemberships->filter(fn($s) => $s === 'active')->count();
        $myPending = $myMemberships->filter(fn($s) => str_starts_with($s, 'pending'))->count();
    @endphp
    @if($myActive || $myPending)
    <div class="flex gap-2 mt-3">
        @if($myActive)
        <div class="flex-1 bg-white/15 rounded-xl py-2 text-center">
            <p class="text-green-300 font-bold text-xl leading-none">{{ $myActive }}</p>
            <p class="text-purple-100 text-[11px] mt-1">Diikuti</p>
        </div>
        @endif
        @if($myPending)
        <div class="flex-1 bg-white/15 rounded-xl py-2 text-center">
            <p class="text-yellow-300 font-bold text-xl leading-none">{{ $myPending }}</p>
            <p class="text-purple-100 text-[11px] mt-1">Menunggu</p>
        </div>
        @endif
    </div>
    @endif
</div>

{{-- ─── Tab Navigation ─────────────────────────────────────────────────── --}}
@php
    $activeTab = request('tab', 'semua'); // semua | saya
    $myExtras  = $extracurriculars->filter(fn($e) => isset($myMemberships[$e->id]));
@endphp
<div class="flex bg-gray-100 rounded-xl p-1 mb-4 gap-1">
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'semua']) }}"
       class="flex-1 text-center py-2 rounded-lg text-sm font-semibold transition-all
              {{ $activeTab === 'semua' ? 'bg-white text-violet-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
        Semua Ekstra
        <span class="ml-1 text-xs {{ $activeTab === 'semua' ? 'text-violet-400' : 'text-gray-400' }}">
            ({{ $extracurriculars->count() }})
        </span>
    </a>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'saya']) }}"
       class="flex-1 text-center py-2 rounded-lg text-sm font-semibold transition-all
              {{ $activeTab === 'saya' ? 'bg-white text-violet-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
        Yang Saya Ikuti
        <span class="ml-1 text-xs {{ $activeTab === 'saya' ? 'text-violet-400' : 'text-gray-400' }}">
            ({{ $myExtras->count() }})
        </span>
    </a>
</div>

{{-- ─── Daftar Ekstra ──────────────────────────────────────────────────── --}}
@php
    $displayList = $activeTab === 'saya' ? $myExtras : $extracurriculars;
@endphp

@if($activeTab === 'saya' && $myExtras->isEmpty())
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
    <div class="w-16 h-16 rounded-2xl bg-violet-50 flex items-center justify-center mx-auto mb-3">
        <svg class="w-8 h-8 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
    </div>
    <p class="text-gray-600 font-semibold text-sm">Belum mengikuti ekstra</p>
    <p class="text-gray-400 text-xs mt-1">Pilih dari tab "Semua Ekstra" untuk mendaftar</p>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'semua']) }}"
       class="inline-block mt-3 px-4 py-2 bg-violet-600 text-white text-xs font-semibold rounded-xl hover:bg-violet-700 transition-colors">
        Lihat Semua Ekstra
    </a>
</div>
@elseif($displayList->isEmpty())
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
    <p class="text-gray-500 font-medium text-sm">Belum ada ekstrakurikuler aktif</p>
    <p class="text-gray-400 text-xs mt-1">Hubungi admin untuk informasi lebih lanjut</p>
</div>
@else
<div class="space-y-3">
    @foreach($displayList as $ekstra)
    @php
        $myStatus    = $myMemberships[$ekstra->id] ?? null;
        $isFull      = $ekstra->isFull();
        $statusColor = match($myStatus) {
            'active'        => 'green',
            'pending_join'  => 'yellow',
            'pending_leave' => 'orange',
            default         => null,
        };
        $statusLabel = match($myStatus) {
            'active'        => 'Anggota Aktif',
            'pending_join'  => 'Menunggu Persetujuan',
            'pending_leave' => 'Mengajukan Keluar',
            default         => null,
        };
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden
                {{ $myStatus === 'active' ? 'ring-1 ring-green-200' : '' }}
                {{ $myStatus === 'pending_join' ? 'ring-1 ring-yellow-200' : '' }}">
        <div class="p-4">
            <div class="flex items-start gap-3">
                {{-- Logo / Avatar --}}
                <div class="w-14 h-14 rounded-2xl shrink-0 overflow-hidden bg-gradient-to-br from-violet-100 to-purple-200 flex items-center justify-center ring-2 ring-purple-100">
                    @if($ekstra->logo)
                        <img src="{{ Storage::disk('public')->url($ekstra->logo) }}"
                             alt="{{ $ekstra->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-gray-800 text-sm leading-tight">{{ $ekstra->name }}</h3>
                        @if($myStatus)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold shrink-0
                            {{ $statusColor === 'green'  ? 'bg-green-100 text-green-700' : '' }}
                            {{ $statusColor === 'yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $statusColor === 'orange' ? 'bg-orange-100 text-orange-700' : '' }}">
                            {{ $statusLabel }}
                        </span>
                        @endif
                    </div>

                    @if($ekstra->pembina_names !== '—')
                    <p class="text-xs text-gray-500 mt-0.5">
                        <span class="text-gray-400">Pembina:</span> {{ $ekstra->pembina_names }}
                    </p>
                    @endif

                    <div class="flex items-center gap-3 mt-2">
                        <div class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-xs text-gray-600">
                                {{ $ekstra->active_members_count }}
                                @if($ekstra->max_members) / {{ $ekstra->max_members }} @endif
                                anggota
                            </span>
                        </div>
                        @if($isFull)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-100 text-red-600">
                            Penuh
                        </span>
                        @endif
                    </div>

                    @if($ekstra->description)
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed line-clamp-2">{{ $ekstra->description }}</p>
                    @endif
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="mt-3 pt-3 border-t border-gray-50">
                @if($myStatus === 'active')
                    <form method="POST" action="{{ route('siswa.extracurricular.leave', $ekstra) }}"
                          onsubmit="return confirm('Yakin ingin mengajukan keluar dari {{ addslashes($ekstra->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl border border-orange-200 text-orange-600 text-sm font-semibold hover:bg-orange-50 transition-colors">
                            Ajukan Keluar
                        </button>
                    </form>

                @elseif($myStatus === 'pending_join')
                    <form method="POST" action="{{ route('siswa.extracurricular.cancel-join', $ekstra) }}"
                          onsubmit="return confirm('Batalkan permintaan bergabung?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl border border-gray-200 text-gray-500 text-sm font-semibold hover:bg-gray-50 transition-colors">
                            Batalkan Permintaan
                        </button>
                    </form>

                @elseif($myStatus === 'pending_leave')
                    <div class="w-full py-2.5 rounded-xl bg-orange-50 text-orange-500 text-sm font-medium text-center">
                        Menunggu persetujuan keluar...
                    </div>

                @elseif($isFull)
                    <div class="w-full py-2.5 rounded-xl bg-gray-50 text-gray-400 text-sm font-medium text-center">
                        Kuota Penuh
                    </div>

                @else
                    <form method="POST" action="{{ route('siswa.extracurricular.join', $ekstra) }}">
                        @csrf
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Bergabung
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
