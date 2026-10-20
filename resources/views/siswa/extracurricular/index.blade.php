@extends('layouts.siswa')
@section('title', 'Ekstrakurikuler')
@section('page-title', 'Ekstrakurikuler')

@section('content')

{{-- ─── Header Banner Ekstrakurikuler ─────────────────────────────────── --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-purple-950 p-4 text-white shadow-sm mb-4">
    <div class="relative z-10">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">Pengembangan Bakat & Minat</h1>
                    <p class="text-xs text-purple-200 mt-0.5">{{ $extracurriculars->count() }} unit ekstrakurikuler terdaftar</p>
                </div>
            </div>

            <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/10 text-purple-200 border border-white/10">
                SMAN 1 Gianyar
            </span>
        </div>

        @php
            $myActive  = $myMemberships->filter(fn($s) => $s === 'active')->count();
            $myPending = $myMemberships->filter(fn($s) => str_starts_with($s, 'pending'))->count();
        @endphp
        @if($myActive || $myPending)
        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-white/10">
            @if($myActive)
            <div class="bg-white/10 backdrop-blur-xs rounded-xl p-2.5 flex items-center justify-between border border-white/10">
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-purple-200 font-bold">Ekstra Aktif</p>
                    <p class="text-lg font-black text-emerald-300 leading-none mt-0.5">{{ $myActive }}</p>
                </div>
                <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            @endif

            @if($myPending)
            <div class="bg-white/10 backdrop-blur-xs rounded-xl p-2.5 flex items-center justify-between border border-white/10">
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-purple-200 font-bold">Menunggu Respon</p>
                    <p class="text-lg font-black text-amber-300 leading-none mt-0.5">{{ $myPending }}</p>
                </div>
                <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- ─── Tab Navigation (Semua vs Saya) ─────────────────────────────────── --}}
@php
    $activeTab = request('tab', 'semua'); // semua | saya
    $myExtras  = $extracurriculars->filter(fn($e) => isset($myMemberships[$e->id]));
@endphp
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-1 mb-4">
    <div class="flex gap-1 bg-slate-100/70 p-1 rounded-xl">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'semua']) }}"
           class="flex-1 text-center py-2 px-3 rounded-lg text-xs font-bold transition-all
                  {{ $activeTab === 'semua' ? 'bg-white text-purple-700 shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
            <span>Semua Ekstrakurikuler</span>
            <span class="ml-1 text-[11px] {{ $activeTab === 'semua' ? 'text-purple-500 font-semibold' : 'text-slate-400' }}">
                ({{ $extracurriculars->count() }})
            </span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'saya']) }}"
           class="flex-1 text-center py-2 px-3 rounded-lg text-xs font-bold transition-all
                  {{ $activeTab === 'saya' ? 'bg-white text-purple-700 shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
            <span>Yang Saya Ikuti</span>
            <span class="ml-1 text-[11px] {{ $activeTab === 'saya' ? 'text-purple-500 font-semibold' : 'text-slate-400' }}">
                ({{ $myExtras->count() }})
            </span>
        </a>
    </div>
</div>

{{-- ─── Daftar Ekstrakurikuler ─────────────────────────────────────────── --}}
@php
    $displayList = $activeTab === 'saya' ? $myExtras : $extracurriculars;
@endphp

@if($activeTab === 'saya' && $myExtras->isEmpty())
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-8 text-center">
    <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-400 flex items-center justify-center mx-auto mb-3">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
    </div>
    <p class="text-slate-800 font-bold text-sm">Belum Mengikuti Ekstrakurikuler</p>
    <p class="text-slate-400 text-xs mt-1 max-w-xs mx-auto">Silakan jelajahi tab "Semua Ekstrakurikuler" untuk mendaftar kegiatan yang kamu minati</p>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'semua']) }}"
       class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-purple-600 text-white text-xs font-bold rounded-xl shadow-xs hover:bg-purple-700 transition-colors">
        <span>Lihat Semua Ekstrakurikuler</span>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
</div>
@elseif($displayList->isEmpty())
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-8 text-center">
    <p class="text-slate-700 font-bold text-sm">Belum ada ekstrakurikuler aktif</p>
    <p class="text-slate-400 text-xs mt-1">Silakan hubungi staf kesiswaan atau pembina untuk info pembukaan ekstra</p>
</div>
@else
<div class="space-y-3">
    @foreach($displayList as $ekstra)
    @php
        $myStatus    = $myMemberships[$ekstra->id] ?? null;
        $isFull      = $ekstra->isFull();
        $statusBadge = match($myStatus) {
            'active'        => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80', 'label' => 'Anggota Aktif'],
            'pending_join'  => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200/80',   'label' => 'Menunggu Persetujuan'],
            'pending_leave' => ['bg' => 'bg-orange-50 text-orange-700 border-orange-200/80', 'label' => 'Mengajukan Keluar'],
            default         => null,
        };
    @endphp
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden
                {{ $myStatus === 'active' ? 'ring-1 ring-emerald-300/80' : '' }}
                {{ $myStatus === 'pending_join' ? 'ring-1 ring-amber-300/80' : '' }}">
        <div class="p-4">
            <div class="flex items-start gap-3">
                {{-- Logo / Avatar --}}
                <div class="w-13 h-13 rounded-2xl shrink-0 overflow-hidden bg-slate-100 flex items-center justify-center border border-slate-200/80">
                    @if($ekstra->logo)
                        <img src="{{ Storage::disk('public')->url($ekstra->logo) }}"
                             alt="{{ $ekstra->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center text-purple-600">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="font-bold text-slate-800 text-sm leading-snug">{{ $ekstra->name }}</h2>
                        @if($statusBadge)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border shrink-0 {{ $statusBadge['bg'] }}">
                            {{ $statusBadge['label'] }}
                        </span>
                        @endif
                    </div>

                    @if($ekstra->pembina_names !== '—')
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        <span class="text-slate-400">Pembina:</span> {{ $ekstra->pembina_names }}
                    </p>
                    @endif

                    <div class="flex items-center gap-2 mt-2">
                        <div class="flex items-center gap-1 text-[11px] text-slate-600 bg-slate-50 border border-slate-100 px-2 py-0.5 rounded-md">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="font-medium">
                                {{ $ekstra->active_members_count }}
                                @if($ekstra->max_members) / {{ $ekstra->max_members }} @endif
                                Anggota
                            </span>
                        </div>

                        @if($isFull)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                            Kuota Penuh
                        </span>
                        @endif
                    </div>

                    @if($ekstra->description)
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed line-clamp-2">{{ $ekstra->description }}</p>
                    @endif
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="mt-3 pt-3 border-t border-slate-100">
                @if($myStatus === 'active')
                    <form method="POST" action="{{ route('siswa.extracurricular.leave', $ekstra) }}"
                          onsubmit="return confirm('Yakin ingin mengajukan keluar dari {{ addslashes($ekstra->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-100/60 text-rose-700 text-xs font-bold transition-all active:scale-[0.98]">
                            Ajukan Keluar dari Ekstrakurikuler
                        </button>
                    </form>

                @elseif($myStatus === 'pending_join')
                    <form method="POST" action="{{ route('siswa.extracurricular.cancel-join', $ekstra) }}"
                          onsubmit="return confirm('Batalkan permintaan bergabung?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-bold transition-all active:scale-[0.98]">
                            Batalkan Permintaan Bergabung
                        </button>
                    </form>

                @elseif($myStatus === 'pending_leave')
                    <div class="w-full py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold text-center">
                        Permintaan keluar sedang menunggu persetujuan pembina...
                    </div>

                @elseif($isFull)
                    <div class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold text-center">
                        Pendaftaran Ditutup (Kuota Penuh)
                    </div>

                @else
                    <form method="POST" action="{{ route('siswa.extracurricular.join', $ekstra) }}">
                        @csrf
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-xs transition-all active:scale-[0.98] flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Daftar Ekstrakurikuler</span>
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
