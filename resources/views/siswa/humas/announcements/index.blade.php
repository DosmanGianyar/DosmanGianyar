@extends('layouts.siswa')
@section('title', 'Pengumuman Sekolah')
@section('page-title', 'Pengumuman Sekolah')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white p-3.5 rounded-2xl shadow-xs border border-gray-100">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('siswa.humas') }}" class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200">
                ←
            </a>
            <div>
                <h2 class="text-base font-bold text-gray-800 leading-tight">Pengumuman Sekolah</h2>
                <p class="text-xs text-gray-500">Informasi & Pengumuman Resmi Humas</p>
            </div>
        </div>
    </div>

    {{-- Daftar Pengumuman --}}
    <div class="space-y-3">
        @forelse($announcements as $ann)
        <a href="{{ route('siswa.humas.announcements.show', $ann) }}"
            class="bg-white rounded-2xl shadow-xs border border-gray-100 p-4 block hover:border-orange-200 transition-all">
            @if($ann->image)
                <div class="w-full h-44 rounded-xl overflow-hidden bg-gray-900 mb-3 border border-gray-100 flex items-center justify-center">
                    <img src="{{ $ann->image_url }}" alt="{{ $ann->title }}" class="w-full h-full object-cover">
                </div>
            @endif

            <div class="flex items-center gap-2 mb-1">
                @if($ann->is_pinned)
                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-md">📌 Pinned</span>
                @endif
                <span class="text-xs text-gray-400 font-medium">{{ $ann->published_at?->isoFormat('D MMMM Y, HH:mm') }} WITA</span>
            </div>

            <h3 class="text-base font-bold text-gray-900 leading-snug mb-1.5">{{ $ann->title }}</h3>
            <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed">{{ $ann->body }}</p>

            <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-end">
                <span class="text-xs font-bold text-orange-600 hover:underline">Baca Selengkapnya →</span>
            </div>
        </a>
        @empty
        <div class="bg-white rounded-2xl p-8 text-center border border-gray-100">
            <span class="text-3xl">📢</span>
            <p class="text-sm font-semibold text-gray-600 mt-2">Belum ada pengumuman saat ini.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $announcements->links() }}
    </div>

</div>
@endsection
