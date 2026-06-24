@extends('layouts.siswa')

@section('title', $announcement->title)
@section('page-title', 'Pengumuman')

@section('content')

<div class="bg-white rounded-2xl border border-gray-100 p-5">
    @if($announcement->is_pinned)
    <div class="flex items-center gap-1.5 text-yellow-600 text-xs font-medium mb-3">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
        </svg>
        Disematkan
    </div>
    @endif

    <h1 class="text-lg font-bold text-gray-800 mb-2">{{ $announcement->title }}</h1>

    <div class="flex items-center gap-3 text-xs text-gray-400 mb-5 pb-4 border-b border-gray-100">
        <span>{{ $announcement->author?->name }}</span>
        <span>·</span>
        <span>{{ $announcement->published_at->isoFormat('D MMMM Y, HH:mm') }}</span>
        <span>·</span>
        @php $targetLabels = ['all' => 'Semua', 'siswa' => 'Siswa', 'guru' => 'Guru']; @endphp
        <span>{{ $targetLabels[$announcement->target] ?? $announcement->target }}</span>
    </div>

    <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
        {!! nl2br(e($announcement->body)) !!}
    </div>
</div>

<div class="mt-4 text-center">
    <a href="{{ route('siswa.announcements.index') }}"
        class="inline-flex items-center gap-1.5 text-sm text-blue-600 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Daftar
    </a>
</div>

@endsection
