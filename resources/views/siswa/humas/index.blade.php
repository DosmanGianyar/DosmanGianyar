@extends('layouts.siswa')
@section('title', 'Humas')
@section('page-title', 'Humas')

@section('content')

{{-- ─── Header ───────────────────────────────────────────────────────── --}}
<div class="bg-linear-to-br from-orange-500 to-rose-500 rounded-2xl p-4 mb-4 text-white">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
        </div>
        <div>
            <p class="text-orange-100 text-xs">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h2 class="text-lg font-bold leading-tight">Humas</h2>
            <p class="text-orange-100 text-xs mt-0.5">Hubungan Masyarakat & Informasi Sekolah</p>
        </div>
    </div>
</div>

{{-- ─── Aksi Cepat ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-3 mb-4">
    <a href="{{ route('siswa.announcements.index') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 flex flex-col items-center gap-1.5">
        <div class="w-9 h-9 bg-orange-100 rounded-xl flex items-center justify-center">
            <svg class="w-4.5 h-4.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
        </div>
        <span class="text-[11px] font-semibold text-gray-700 text-center">Pengumuman</span>
    </a>

    <a href="#agenda"
        onclick="document.getElementById('section-agenda').scrollIntoView({behavior:'smooth'}); return false;"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 flex flex-col items-center gap-1.5">
        <div class="w-9 h-9 bg-rose-100 rounded-xl flex items-center justify-center">
            <svg class="w-4.5 h-4.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <span class="text-[11px] font-semibold text-gray-700 text-center">Agenda</span>
    </a>

    <a href="{{ route('siswa.humas.gallery.index') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 flex flex-col items-center gap-1.5">
        <div class="w-9 h-9 bg-pink-100 rounded-xl flex items-center justify-center">
            <svg class="w-4.5 h-4.5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <span class="text-[11px] font-semibold text-gray-700 text-center">Galeri</span>
    </a>
</div>

{{-- ─── Agenda Sekolah ──────────────────────────────────────────────── --}}
<div id="section-agenda" class="mb-4">
    <div class="flex items-center justify-between mb-2 px-1">
        <p class="text-sm font-bold text-gray-700">Agenda Sekolah</p>
        <span class="text-xs text-gray-400">Mendatang</span>
    </div>

    @if($upcomingEvents->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-10 text-center">
        <p class="text-sm text-gray-400">Belum ada agenda sekolah</p>
    </div>
    @else
    <div class="space-y-2">
        @foreach($upcomingEvents as $event)
        @php
            $isToday   = $event->event_date->isToday();
            $isPast    = $event->event_date->isPast() && !$isToday;
            $daysLeft  = today()->diffInDays($event->event_date, false);
        @endphp
        <a href="{{ route('siswa.humas.event.show', $event) }}"
            class="bg-white rounded-2xl shadow-sm border {{ $isToday ? 'border-orange-200' : 'border-gray-100' }} overflow-hidden block">
            <div class="flex gap-3 p-3">
                {{-- Cover photo atau tanggal box --}}
                @if($event->coverPhotoUrl())
                <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0">
                    <img src="{{ $event->coverPhotoUrl() }}" alt="" class="w-full h-full object-cover">
                </div>
                @else
                <div class="w-16 h-16 rounded-xl {{ $isToday ? 'bg-orange-500' : 'bg-orange-50' }} shrink-0 flex flex-col items-center justify-center">
                    <p class="text-2xl font-extrabold leading-none {{ $isToday ? 'text-white' : 'text-orange-600' }}">
                        {{ $event->event_date->format('d') }}
                    </p>
                    <p class="text-[10px] uppercase font-bold {{ $isToday ? 'text-orange-100' : 'text-orange-400' }}">
                        {{ $event->event_date->isoFormat('MMM') }}
                    </p>
                </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-bold text-gray-800 leading-tight">{{ $event->title }}</p>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0 {{ $event->typeBadgeClass() }}">
                            {{ $event->typeLabel() }}
                        </span>
                    </div>
                    @if($event->location)
                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $event->location }}
                    </p>
                    @endif
                    <div class="flex items-center gap-2 mt-1">
                        @if($event->end_date && !$event->event_date->isSameDay($event->end_date))
                        <p class="text-[11px] text-gray-400">
                            {{ $event->event_date->isoFormat('D MMM') }} – {{ $event->end_date->isoFormat('D MMM Y') }}
                        </p>
                        @else
                        <p class="text-[11px] text-gray-400">{{ $event->event_date->isoFormat('D MMMM Y') }}</p>
                        @endif
                        @if($isToday)
                        <span class="text-[10px] font-bold text-orange-600 bg-orange-100 px-1.5 py-0.5 rounded-full animate-pulse">Hari ini</span>
                        @elseif($daysLeft > 0 && $daysLeft <= 7)
                        <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded-full">{{ $daysLeft }} hari lagi</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Galeri terkait (jika ada) --}}
            @if($event->gallery && $event->gallery->photos->isNotEmpty())
            <div class="border-t border-gray-100 px-3 py-2">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-semibold text-gray-500 flex items-center gap-1">
                        <svg class="w-3 h-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Galeri: {{ $event->gallery->title }}
                    </span>
                    <a href="{{ route('siswa.humas.gallery.show', $event->gallery) }}"
                        class="text-[10px] text-orange-500 font-semibold hover:underline">Lihat semua</a>
                </div>
                <div class="flex gap-1.5 overflow-x-auto">
                    @foreach($event->gallery->photos->take(4) as $photo)
                    <a href="{{ route('siswa.humas.gallery.show', $event->gallery) }}"
                        class="shrink-0 w-14 h-14 rounded-lg overflow-hidden bg-gray-100">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->photo) }}"
                            alt="{{ $photo->caption }}" class="w-full h-full object-cover">
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </a>
        @endforeach
    </div>
    @endif
</div>

{{-- ─── Galeri Terbaru ──────────────────────────────────────────────── --}}
@if($latestGalleries->isNotEmpty())
<div class="mb-4">
    <div class="flex items-center justify-between mb-2 px-1">
        <p class="text-sm font-bold text-gray-700">Galeri Terbaru</p>
        <a href="{{ route('siswa.humas.gallery.index') }}" class="text-xs text-orange-500 font-semibold">Lihat Semua</a>
    </div>
    <div class="grid grid-cols-2 gap-3">
        @foreach($latestGalleries as $gallery)
        <a href="{{ route('siswa.humas.gallery.show', $gallery) }}"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="h-24 bg-gray-100 relative">
                @if($gallery->coverPhotoUrl())
                <img src="{{ $gallery->coverPhotoUrl() }}" alt="{{ $gallery->title }}"
                    class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center bg-orange-50">
                    <svg class="w-8 h-8 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif
                @if($gallery->photos_count > 0)
                <span class="absolute bottom-1.5 right-1.5 bg-black/50 text-white text-[10px] font-semibold px-1.5 py-0.5 rounded-full">
                    {{ $gallery->photos_count }} foto
                </span>
                @endif
            </div>
            <div class="p-2.5">
                <p class="text-xs font-semibold text-gray-700 line-clamp-2 leading-tight">{{ $gallery->title }}</p>
                @if($gallery->event_date)
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $gallery->event_date->isoFormat('D MMM Y') }}</p>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

@endsection
