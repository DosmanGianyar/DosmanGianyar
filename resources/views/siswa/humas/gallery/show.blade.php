@extends('layouts.siswa')
@section('title', $gallery->title)
@section('page-title', 'Galeri')

@section('content')

{{-- ─── Info Album ──────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
    <h2 class="text-base font-bold text-gray-800 leading-tight">{{ $gallery->title }}</h2>
    @if($gallery->event_date)
    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        {{ $gallery->event_date->isoFormat('D MMMM Y') }}
        · {{ $photos->count() }} foto
    </p>
    @endif
    @if($gallery->description)
    <p class="text-sm text-gray-600 mt-2">{{ $gallery->description }}</p>
    @endif
</div>

{{-- ─── Grid Foto ───────────────────────────────────────────────────── --}}
@if($photos->isEmpty())
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center">
    <p class="text-sm text-gray-400">Belum ada foto dalam album ini</p>
</div>
@else
<div class="grid grid-cols-2 gap-2 mb-4">
    @foreach($photos as $i => $photo)
    <button type="button" onclick="openGalleryModal({{ $i }})"
        class="rounded-xl overflow-hidden aspect-square focus:outline-none focus:ring-2 focus:ring-orange-400">
        <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?? '' }}"
            class="w-full h-full object-cover hover:opacity-90 transition-opacity">
    </button>
    @endforeach
</div>
@endif

{{-- ─── Back ────────────────────────────────────────────────────────── --}}
<a href="{{ route('siswa.humas.gallery.index') }}"
    class="flex items-center gap-2 text-sm text-gray-500 py-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Kembali ke Galeri
</a>

{{-- ─── Lightbox Modal ──────────────────────────────────────────────── --}}
<div id="gallery-modal"
    style="display:none"
    class="fixed inset-0 z-50 items-center justify-center bg-black/90 px-4"
    onclick="closeGalleryModal()">
    <div class="relative w-full max-w-sm" onclick="event.stopPropagation()">
        {{-- Foto --}}
        <img id="gallery-modal-img" src="" alt=""
            class="w-full rounded-xl shadow-2xl object-contain max-h-[70vh]">
        {{-- Caption --}}
        <p id="gallery-modal-caption" class="text-white text-xs text-center mt-2 opacity-80 min-h-4"></p>
        {{-- Counter --}}
        <p id="gallery-modal-counter" class="text-white/50 text-xs text-center mt-1"></p>
        {{-- Nav buttons --}}
        <button onclick="prevPhoto()" id="btn-prev"
            class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 w-9 h-9 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center transition-colors">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button onclick="nextPhoto()" id="btn-next"
            class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 w-9 h-9 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center transition-colors">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        {{-- Close --}}
        <button onclick="closeGalleryModal()"
            class="absolute -top-10 right-0 w-8 h-8 bg-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/40">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

<script>
var photos = @json($photos->map(fn($p) => ['url' => $p->url(), 'caption' => $p->caption]));
var current = 0;

function openGalleryModal(index) {
    current = index;
    updateModalPhoto();
    document.getElementById('gallery-modal').style.display = 'flex';
}

function closeGalleryModal() {
    document.getElementById('gallery-modal').style.display = 'none';
}

function updateModalPhoto() {
    var p = photos[current];
    document.getElementById('gallery-modal-img').src     = p.url;
    document.getElementById('gallery-modal-caption').textContent = p.caption || '';
    document.getElementById('gallery-modal-counter').textContent = (current + 1) + ' / ' + photos.length;
    document.getElementById('btn-prev').style.display = current === 0 ? 'none' : 'flex';
    document.getElementById('btn-next').style.display = current === photos.length - 1 ? 'none' : 'flex';
}

function prevPhoto() { if (current > 0) { current--; updateModalPhoto(); } }
function nextPhoto() { if (current < photos.length - 1) { current++; updateModalPhoto(); } }

document.addEventListener('keydown', function(e) {
    if (document.getElementById('gallery-modal').style.display === 'none') return;
    if (e.key === 'ArrowLeft')  prevPhoto();
    if (e.key === 'ArrowRight') nextPhoto();
    if (e.key === 'Escape')     closeGalleryModal();
});
</script>
@endsection
