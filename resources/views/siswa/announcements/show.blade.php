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

    @php
        $hasHtml = $announcement->body !== strip_tags($announcement->body);
    @endphp
    <div class="announcement-content text-gray-800 leading-relaxed text-sm">
        @if($hasHtml)
            {!! $announcement->body !!}
        @else
            {!! nl2br(e($announcement->body)) !!}
        @endif
    </div>
</div>

<style>
.announcement-content p { margin-bottom: 0.75rem; }
.announcement-content h1, .announcement-content h2, .announcement-content h3 { font-weight: 800; margin-top: 1.25rem; margin-bottom: 0.5rem; color: #0f172a; }
.announcement-content ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 0.75rem; }
.announcement-content ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 0.75rem; }
.announcement-content li { margin-bottom: 0.25rem; }
.announcement-content blockquote { border-left: 4px solid #3b82f6; padding-left: 1rem; font-style: italic; color: #475569; margin: 0.75rem 0; background: #f8fafc; padding-top: 0.5rem; padding-bottom: 0.5rem; border-radius: 0 8px 8px 0; }
.announcement-content a { color: #2563eb; text-decoration: underline; font-weight: 600; }
.announcement-content table { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; border-radius: 8px; overflow: hidden; }
.announcement-content th, .announcement-content td { border: 1px solid #e2e8f0; padding: 8px 12px; font-size: 13px; }
.announcement-content th { background-color: #f1f5f9; font-weight: 700; color: #1e293b; }
</style>

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
