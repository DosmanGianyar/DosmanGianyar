@extends('layouts.siswa')

@section('title', $session->title)
@section('page-title', 'Kelola Sesi')

@section('content')

{{-- Status Card --}}
<div class="bg-white rounded-2xl border border-gray-100 p-4 mb-4">
    <div class="flex items-start justify-between gap-2 mb-2">
        <h2 class="font-bold text-gray-800 text-sm flex-1">{{ $session->title }}</h2>
        @php
            $colors = ['draft' => 'bg-gray-100 text-gray-600', 'active' => 'bg-green-100 text-green-700', 'closed' => 'bg-blue-100 text-blue-700'];
            $color  = $colors[$session->status] ?? 'bg-gray-100 text-gray-600';
        @endphp
        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $color }} shrink-0">
            {{ $session->statusLabel() }}
        </span>
    </div>
    @if($session->description)
        <p class="text-xs text-gray-500 mb-2">{{ $session->description }}</p>
    @endif
    <div class="text-xs text-gray-500 space-y-0.5">
        <p>Mulai: {{ $session->start_time->isoFormat('D MMM Y, HH:mm') }}</p>
        <p>Selesai: {{ $session->end_time->isoFormat('D MMM Y, HH:mm') }}</p>
        <p>Total suara: <span class="font-semibold text-gray-700">{{ $totalVotes }}</span></p>
    </div>

    {{-- Actions --}}
    @if($session->isDraft())
        <div class="flex gap-2 mt-3">
            <a href="{{ route('siswa.voting.manage.edit', $session) }}"
                class="flex-1 text-center bg-gray-100 text-gray-700 text-xs font-medium py-2 rounded-lg">
                Edit
            </a>
            <form method="POST" action="{{ route('siswa.voting.manage.activate', $session) }}" class="flex-1">
                @csrf @method('PATCH')
                <button type="submit"
                    class="w-full bg-green-600 text-white text-xs font-semibold py-2 rounded-lg">
                    Aktifkan
                </button>
            </form>
        </div>
    @elseif($session->isActive())
        <form method="POST" action="{{ route('siswa.voting.manage.close', $session) }}" class="mt-3">
            @csrf @method('PATCH')
            <button type="submit"
                class="w-full bg-red-50 text-red-600 text-xs font-semibold py-2 rounded-lg border border-red-200">
                Tutup Voting Sekarang
            </button>
        </form>
    @endif
</div>

{{-- Results (if active/closed) --}}
@if(! $session->isDraft() && $totalVotes > 0)
<div class="bg-white rounded-2xl border border-gray-100 p-4 mb-4">
    <p class="text-xs font-semibold text-gray-600 mb-3">Perolehan Suara</p>
    @foreach($session->candidates->sortByDesc(fn($c) => $c->votes->count()) as $candidate)
    @php
        $count = $candidate->votes->count();
        $pct   = $totalVotes > 0 ? round($count / $totalVotes * 100) : 0;
    @endphp
    <div class="mb-2">
        <div class="flex justify-between text-xs mb-1">
            <span class="font-medium text-gray-700">{{ $candidate->name }}</span>
            <span class="text-gray-500">{{ $count }} ({{ $pct }}%)</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-1.5">
            <div class="h-1.5 rounded-full bg-blue-500" style="width: {{ $pct }}%"></div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Candidates --}}
<div class="flex items-center justify-between mb-2">
    <p class="text-xs font-semibold text-gray-600">Daftar Kandidat ({{ $session->candidates->count() }})</p>
</div>

<div class="space-y-2 mb-4">
    @forelse($session->candidates as $candidate)
    <div class="bg-white rounded-xl border border-gray-100 p-3 flex items-center gap-3">
        @if($candidate->photoUrl())
            <img src="{{ $candidate->photoUrl() }}" class="w-10 h-10 rounded-lg object-cover shrink-0">
        @else
            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        @endif
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800 truncate">{{ $candidate->name }}</p>
            @if($candidate->vision)
                <p class="text-xs text-gray-400 truncate">{{ Str::limit($candidate->vision, 60) }}</p>
            @endif
        </div>
        @if($session->isDraft())
        <form method="POST"
            action="{{ route('siswa.voting.manage.candidate.remove', [$session, $candidate]) }}"
            data-confirm="Hapus kandidat {{ addslashes($candidate->name) }}?">
            @csrf @method('DELETE')
            <button type="submit" class="text-red-400 hover:text-red-600 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </form>
        @endif
    </div>
    @empty
    <p class="text-center text-xs text-gray-400 py-4">Belum ada kandidat ditambahkan</p>
    @endforelse
</div>

{{-- Add Candidate Form (draft only) --}}
@if($session->isDraft())
<div class="bg-white rounded-2xl border border-gray-100 p-4">
    <p class="text-xs font-semibold text-gray-700 mb-3">Tambah Kandidat</p>
    <form method="POST" action="{{ route('siswa.voting.manage.candidate.store', $session) }}"
        enctype="multipart/form-data" class="space-y-3">
        @csrf

        <input type="text" name="name"
            placeholder="Nama kandidat *"
            value="{{ old('name') }}"
            required
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('name')
            <p class="text-red-500 text-xs">{{ $message }}</p>
        @enderror

        <textarea name="vision" rows="2"
            placeholder="Visi / misi (opsional)"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('vision') }}</textarea>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Foto (opsional, maks 2MB)</label>
            <input type="file" name="photo" accept="image/*"
                class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700">
        </div>

        <button type="submit"
            class="w-full bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-xl">
            Tambah Kandidat
        </button>
    </form>
</div>
@endif

@endsection
