@extends('layouts.siswa')

@section('title', $session->title . ' — Hasil')
@section('page-title', 'Hasil Voting')

@section('content')

{{-- Header --}}
<div class="bg-gradient-to-br {{ $session->isActive() ? 'from-blue-600 to-indigo-700' : 'from-gray-600 to-gray-700' }} rounded-2xl p-4 mb-4 text-white">
    <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full font-medium">
        {{ $session->statusLabel() }}
    </span>
    <h2 class="text-base font-bold mt-2">{{ $session->title }}</h2>
    <p class="text-white/70 text-xs mt-1">{{ $totalVotes }} total suara</p>
</div>

@if($hasVoted && $userVote)
<div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-4 flex items-center gap-2">
    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <p class="text-green-700 text-xs">
        Kamu telah memilih <span class="font-semibold">{{ $userVote->candidate->name }}</span>
    </p>
</div>
@endif

{{-- Results --}}
<div class="space-y-3">
    @php
        $sortedCandidates = $session->candidates->sortByDesc(fn($c) => $c->votes->count());
        $rank = 0;
    @endphp
    @foreach($sortedCandidates as $candidate)
    @php
        $count   = $candidate->votes->count();
        $pct     = $totalVotes > 0 ? round($count / $totalVotes * 100) : 0;
        $rank++;
        $isMyVote = $userVote?->candidate_id === $candidate->id;
        $isWinner = $rank === 1 && $totalVotes > 0;
    @endphp
    <div class="bg-white rounded-2xl border {{ $isMyVote ? 'border-blue-200 bg-blue-50' : 'border-gray-100' }} p-4">
        <div class="flex items-center gap-3 mb-3">
            @if($candidate->photoUrl())
                <div class="relative shrink-0">
                    <img src="{{ $candidate->photoUrl() }}" class="w-12 h-12 rounded-xl object-cover">
                    @if($isWinner)
                        <span class="absolute -top-1 -right-1 text-xs">🏆</span>
                    @endif
                </div>
            @else
                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center shrink-0 relative">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    @if($isWinner)
                        <span class="absolute -top-1 -right-1 text-xs">🏆</span>
                    @endif
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="font-semibold text-sm text-gray-800 truncate">{{ $candidate->name }}</p>
                    @if($isMyVote)
                        <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full font-medium shrink-0">Pilihanmu</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500">{{ $count }} suara ({{ $pct }}%)</p>
            </div>
            <span class="text-lg font-bold {{ $isWinner ? 'text-yellow-500' : 'text-gray-400' }} shrink-0">
                {{ $pct }}%
            </span>
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full transition-all duration-700
                {{ $isWinner ? 'bg-yellow-400' : ($isMyVote ? 'bg-blue-500' : 'bg-gray-300') }}"
                style="width: {{ $pct }}%">
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-6 text-center">
    <a href="{{ route('siswa.voting.index') }}"
        class="inline-flex items-center gap-1.5 text-sm text-blue-600 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Daftar Voting
    </a>
</div>

@endsection
