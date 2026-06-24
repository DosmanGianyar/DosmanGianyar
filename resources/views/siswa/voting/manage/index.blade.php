@extends('layouts.siswa')

@section('title', 'Kelola Voting')
@section('page-title', 'Kelola Voting')

@section('content')

<div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-semibold text-gray-700">Sesi Voting Saya</h2>
    <a href="{{ route('siswa.voting.manage.create') }}"
        class="flex items-center gap-1.5 bg-blue-600 text-white text-xs font-semibold px-3 py-2 rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Buat Baru
    </a>
</div>

@if($sessions->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm font-medium">Belum ada sesi voting</p>
        <p class="text-xs mt-1">Buat sesi voting baru untuk memulai</p>
    </div>
@else
    <div class="space-y-3">
        @foreach($sessions as $session)
        @php
            $colors = ['draft' => 'bg-gray-100 text-gray-600', 'active' => 'bg-green-100 text-green-700', 'closed' => 'bg-blue-100 text-blue-700'];
            $color  = $colors[$session->status] ?? 'bg-gray-100 text-gray-600';
        @endphp
        <a href="{{ route('siswa.voting.manage.show', $session) }}"
            class="block bg-white rounded-2xl border border-gray-100 p-4 active:bg-gray-50">
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="font-semibold text-sm text-gray-800 flex-1">{{ $session->title }}</p>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $color }} shrink-0">
                    {{ $session->statusLabel() }}
                </span>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span>{{ $session->candidates_count }} kandidat</span>
                <span>{{ $session->votes_count }} suara</span>
                <span>s/d {{ $session->end_time->isoFormat('D MMM Y') }}</span>
            </div>
        </a>
        @endforeach
    </div>
@endif

@endsection
