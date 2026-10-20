@extends('layouts.siswa')
@section('title', $announcement->title)
@section('page-title', 'Detail Pengumuman')

@section('content')
<div class="space-y-4">

    {{-- Navigasi Kembali --}}
    <div class="flex items-center gap-2">
        <a href="{{ route('siswa.humas.announcements.index') }}" class="px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-2xs">
            ← Kembali ke Pengumuman
        </a>
    </div>

    {{-- Detail Pengumuman Card --}}
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-4 space-y-4">
        @if($announcement->is_pinned)
            <span class="inline-block bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-1 rounded-lg">📌 Pengumuman Pilihan (Pinned)</span>
        @endif

        <h1 class="text-lg font-bold text-gray-900 leading-snug">{{ $announcement->title }}</h1>

        <div class="flex items-center gap-2 text-xs text-gray-400 border-b border-gray-100 pb-3">
            <span>📅 {{ $announcement->published_at?->isoFormat('D MMMM Y, HH:mm') }} WITA</span>
            @if($announcement->author)
                <span>• Oleh: {{ $announcement->author->name }}</span>
            @endif
        </div>

        @if($announcement->image)
            <div class="rounded-xl overflow-hidden shadow-sm border border-gray-200 bg-gray-900 flex justify-center">
                <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}" class="max-h-96 w-auto object-contain">
            </div>
        @endif

        @php
            $hasHtml = $announcement->body !== strip_tags($announcement->body);
        @endphp
        <div class="announcement-content text-sm text-gray-800 leading-relaxed pt-2">
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
    .announcement-content blockquote { border-left: 4px solid #ea580c; padding-left: 1rem; font-style: italic; color: #475569; margin: 0.75rem 0; background: #fff7ed; padding-top: 0.5rem; padding-bottom: 0.5rem; border-radius: 0 8px 8px 0; }
    .announcement-content a { color: #ea580c; text-decoration: underline; font-weight: 600; }
    .announcement-content table { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; border-radius: 8px; overflow: hidden; }
    .announcement-content th, .announcement-content td { border: 1px solid #e2e8f0; padding: 8px 12px; font-size: 13px; }
    .announcement-content th { background-color: #f1f5f9; font-weight: 700; color: #1e293b; }
    </style>

    {{-- Pengumuman Lainnya --}}
    @if($otherAnnouncements->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-4">
        <h3 class="text-sm font-bold text-gray-800 mb-3">Pengumuman Lainnya</h3>
        <div class="divide-y divide-gray-50">
            @foreach($otherAnnouncements as $other)
            <a href="{{ route('siswa.humas.announcements.show', $other) }}" class="py-2.5 flex items-center justify-between block hover:text-orange-600">
                <div class="min-w-0 pr-2">
                    <p class="text-xs font-bold text-gray-800 truncate">{{ $other->title }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $other->published_at?->isoFormat('D MMM Y') }}</p>
                </div>
                <span class="text-xs text-orange-600 font-bold">→</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
