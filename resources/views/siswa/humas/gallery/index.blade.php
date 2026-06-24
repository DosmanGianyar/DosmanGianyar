@extends('layouts.siswa')
@section('title', 'Galeri Sekolah')
@section('page-title', 'Galeri')

@section('content')

<div class="mb-4">
    <p class="text-xs text-gray-400">{{ $galleries->total() }} album tersedia</p>
</div>

@if($galleries->isEmpty())
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-16 text-center">
    <div class="w-14 h-14 bg-pink-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-7 h-7 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </div>
    <p class="text-sm font-semibold text-gray-600">Belum ada galeri</p>
    <p class="text-xs text-gray-400 mt-1">Album foto kegiatan sekolah akan muncul di sini</p>
</div>
@else
<div class="grid grid-cols-2 gap-3 mb-4">
    @foreach($galleries as $gallery)
    <a href="{{ route('siswa.humas.gallery.show', $gallery) }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-28 bg-gray-100 relative">
            @if($gallery->coverPhotoUrl())
            <img src="{{ $gallery->coverPhotoUrl() }}" alt="{{ $gallery->title }}"
                class="w-full h-full object-cover">
            @else
            <div class="w-full h-full flex items-center justify-center bg-orange-50">
                <svg class="w-10 h-10 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            @endif
            @if($gallery->photos_count > 0)
            <span class="absolute bottom-2 right-2 bg-black/60 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full flex items-center gap-1">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $gallery->photos_count }}
            </span>
            @endif
        </div>
        <div class="p-3">
            <p class="text-xs font-bold text-gray-800 line-clamp-2 leading-tight">{{ $gallery->title }}</p>
            @if($gallery->event_date)
            <p class="text-[10px] text-gray-400 mt-1">{{ $gallery->event_date->isoFormat('D MMM Y') }}</p>
            @endif
        </div>
    </a>
    @endforeach
</div>

{{ $galleries->links() }}
@endif

@endsection
