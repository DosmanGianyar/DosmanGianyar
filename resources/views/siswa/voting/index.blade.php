@extends('layouts.siswa')

@section('title', 'E-Voting')
@section('page-title', 'E-Voting')

@section('content')

{{-- Pengelola shortcut --}}
@if(auth()->user()->role === 'siswa_pengelola')
<div class="mb-4">
    <a href="{{ route('siswa.voting.manage.index') }}"
        class="flex items-center gap-2 bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-3 text-indigo-700">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold">Kelola Voting</p>
            <p class="text-xs text-indigo-500">Buat & kelola sesi voting</p>
        </div>
        <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
</div>
@endif

@if($sessions->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        <p class="text-sm font-medium">Belum ada sesi voting</p>
        <p class="text-xs mt-1">Sesi voting akan muncul di sini saat aktif</p>
    </div>
@else
    <div class="space-y-3">
        @foreach($sessions as $session)
        @php
            $totalVotes = $session->votes->count();
        @endphp
        <a href="{{ route('siswa.voting.show', $session) }}"
            class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 active:bg-gray-50">

            <div class="flex items-start justify-between gap-2 mb-2">
                <h3 class="font-semibold text-gray-800 text-sm leading-snug flex-1">{{ $session->title }}</h3>
                @php
                    $colors = ['active' => 'bg-green-100 text-green-700', 'closed' => 'bg-blue-100 text-blue-700'];
                    $color  = $colors[$session->status] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded-full {{ $color }}">
                    {{ $session->statusLabel() }}
                </span>
            </div>

            @if($session->description)
                <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $session->description }}</p>
            @endif

            <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $session->candidates->count() }} kandidat
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $totalVotes }} suara
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    s/d {{ $session->end_time->isoFormat('D MMM Y, HH:mm') }}
                </span>
            </div>

            @if($session->user_has_voted)
                <div class="flex items-center gap-1.5 text-xs text-green-600 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Sudah memilih · {{ $session->user_vote?->candidate?->name }}
                </div>
            @elseif($session->isActive())
                <div class="flex items-center gap-1.5 text-xs text-blue-600 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                    </svg>
                    Belum memilih — Tap untuk voting
                </div>
            @else
                <div class="flex items-center gap-1.5 text-xs text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Lihat hasil
                </div>
            @endif
        </a>
        @endforeach
    </div>
@endif

@endsection
