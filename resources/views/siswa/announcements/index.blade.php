@extends('layouts.siswa')

@section('title', 'Pengumuman')
@section('page-title', 'Pengumuman')

@section('content')

@if(auth()->user()->role === 'siswa_pengelola')
<div class="flex justify-end mb-3 gap-2">
    <a href="{{ route('siswa.announcements.manage') }}"
        class="flex items-center gap-1.5 bg-gray-100 text-gray-700 text-xs font-medium px-3 py-2 rounded-lg">
        Kelola
    </a>
    <a href="{{ route('siswa.announcements.create') }}"
        class="flex items-center gap-1.5 bg-blue-600 text-white text-xs font-semibold px-3 py-2 rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Buat
    </a>
</div>
@endif

@if($announcements->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
        </svg>
        <p class="text-sm font-medium">Belum ada pengumuman</p>
    </div>
@else
    <div class="space-y-3">
        @foreach($announcements as $item)
        <a href="{{ route('siswa.announcements.show', $item) }}"
            class="block bg-white rounded-2xl border border-gray-100 p-4 active:bg-gray-50">
            <div class="flex items-start gap-2 mb-1">
                @if($item->is_pinned)
                <span class="shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
                    </svg>
                </span>
                @endif
                <h3 class="font-semibold text-sm text-gray-800 flex-1 leading-snug">{{ $item->title }}</h3>
                @php
                    $targetLabels = ['all' => 'Semua', 'siswa' => 'Siswa', 'guru' => 'Guru'];
                @endphp
                <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full shrink-0">
                    {{ $targetLabels[$item->target] ?? $item->target }}
                </span>
            </div>
            <p class="text-xs text-gray-500 line-clamp-2 mb-2">{{ Str::limit(strip_tags($item->body), 120) }}</p>
            <div class="flex items-center gap-3 text-xs text-gray-400">
                <span>{{ $item->author?->name }}</span>
                <span>·</span>
                <span>{{ $item->published_at->diffForHumans() }}</span>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $announcements->links() }}
    </div>
@endif

@endsection
