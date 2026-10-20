@extends('layouts.siswa')
@section('title', 'Humas')
@section('page-title', 'Humas')

@section('content')

{{-- ─── Header Hubungan Masyarakat ───────────────────────────────────── --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950 p-4 text-white shadow-sm mb-4">
    <div class="relative z-10 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/15 text-white backdrop-blur-xs">
                        Humas & Publikasi
                    </span>
                    <span class="text-[11px] text-white/90">{{ now()->isoFormat('D MMMM Y') }}</span>
                </div>
                <h1 class="text-base font-bold text-white mt-0.5">Informasi, Agenda & Galeri</h1>
            </div>
        </div>

        <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/10 text-amber-200 border border-white/10">
            SMAN 1 Gianyar
        </span>
    </div>
</div>

{{-- ─── Aksi Cepat Humas (3 Tombol Navigasi Cepat) ────────────────────── --}}
<div class="grid grid-cols-3 gap-2.5 mb-4">
    <a href="{{ route('siswa.announcements.index') }}"
        class="group bg-white hover:bg-slate-50 active:scale-[0.98] rounded-2xl shadow-xs border border-slate-200/80 p-3 flex flex-col items-center gap-2 transition-all">
        <div class="w-10 h-10 bg-amber-50 group-hover:scale-105 border border-amber-100 rounded-xl flex items-center justify-center text-amber-600 transition-transform">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
        </div>
        <span class="text-xs font-bold text-slate-800 text-center">Pengumuman</span>
    </a>

    <a href="#agenda"
        onclick="document.getElementById('section-agenda').scrollIntoView({behavior:'smooth'}); return false;"
        class="group bg-white hover:bg-slate-50 active:scale-[0.98] rounded-2xl shadow-xs border border-slate-200/80 p-3 flex flex-col items-center gap-2 transition-all">
        <div class="w-10 h-10 bg-indigo-50 group-hover:scale-105 border border-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 transition-transform">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <span class="text-xs font-bold text-slate-800 text-center">Agenda Kegiatan</span>
    </a>

    <a href="{{ route('siswa.humas.gallery.index') }}"
        class="group bg-white hover:bg-slate-50 active:scale-[0.98] rounded-2xl shadow-xs border border-slate-200/80 p-3 flex flex-col items-center gap-2 transition-all">
        <div class="w-10 h-10 bg-rose-50 group-hover:scale-105 border border-rose-100 rounded-xl flex items-center justify-center text-rose-600 transition-transform">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <span class="text-xs font-bold text-slate-800 text-center">Galeri Foto</span>
    </a>
</div>

{{-- ─── Pengumuman Terbaru ───────────────────────────────────────────── --}}
@if(isset($latestAnnouncements) && $latestAnnouncements->isNotEmpty())
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div>
            <h2 class="text-xs font-bold text-slate-800">Pengumuman Sekolah Terbaru</h2>
            <p class="text-[11px] text-slate-400">Informasi dan edaran resmi civitas akademika</p>
        </div>
        <a href="{{ route('siswa.humas.announcements.index') }}" class="text-[11px] text-amber-600 hover:text-amber-700 font-semibold inline-flex items-center gap-0.5">
            <span>Lihat Semua</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <div class="divide-y divide-slate-100">
        @foreach($latestAnnouncements as $ann)
        <a href="{{ route('siswa.humas.announcements.show', $ann) }}"
            class="p-4 flex items-start gap-3 hover:bg-slate-50/60 transition-colors block">
            @if($ann->image)
                <div class="w-15 h-15 rounded-xl overflow-hidden bg-slate-900 shrink-0 border border-slate-200/80">
                    <img src="{{ $ann->image_url }}" alt="{{ $ann->title }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 mb-1">
                    @if($ann->is_pinned)
                        <span class="bg-amber-100/80 text-amber-800 text-[10px] font-bold px-1.5 py-0.2 rounded-md">📌 Sematan</span>
                    @endif
                    <span class="text-[10px] text-slate-400 font-medium">{{ $ann->published_at?->isoFormat('D MMMM Y') }}</span>
                </div>
                <h3 class="text-xs font-bold text-slate-800 line-clamp-1 leading-snug">{{ $ann->title }}</h3>
                <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5 leading-relaxed">{{ Str::limit(strip_tags($ann->body), 120) }}</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- ─── Agenda Sekolah ──────────────────────────────────────────────── --}}
<div id="section-agenda" class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div>
            <h2 class="text-xs font-bold text-slate-800">Agenda & Kegiatan Sekolah</h2>
            <p class="text-[11px] text-slate-400">Jadwal event, upacara & agenda mendatang</p>
        </div>
        <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-full">
            Mendatang
        </span>
    </div>

    @if($upcomingEvents->isEmpty())
    <div class="py-10 text-center">
        <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-xs text-slate-400 font-medium">Belum ada agenda kegiatan yang terdaftar</p>
    </div>
    @else
    <div class="divide-y divide-slate-100">
        @foreach($upcomingEvents as $event)
        @php
            $isToday   = $event->event_date->isToday();
            $isPast    = $event->event_date->isPast() && !$isToday;
            $daysLeft  = today()->diffInDays($event->event_date, false);
        @endphp
        <a href="{{ route('siswa.humas.event.show', $event) }}"
            class="p-4 hover:bg-slate-50/60 transition-colors block {{ $isToday ? 'bg-amber-50/40' : '' }}">
            <div class="flex gap-3">
                {{-- Cover photo atau tanggal box --}}
                @if($event->coverPhotoUrl())
                <div class="w-15 h-15 rounded-xl overflow-hidden shrink-0 border border-slate-200/80">
                    <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                </div>
                @else
                <div class="w-13 h-13 rounded-xl {{ $isToday ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-50 border border-slate-100 text-slate-800' }} shrink-0 flex flex-col items-center justify-center">
                    <p class="text-lg font-black leading-none">
                        {{ $event->event_date->format('d') }}
                    </p>
                    <p class="text-[9px] uppercase font-bold {{ $isToday ? 'text-amber-100' : 'text-slate-400' }} mt-0.5">
                        {{ $event->event_date->isoFormat('MMM') }}
                    </p>
                </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-xs font-bold text-slate-800 leading-snug">{{ $event->title }}</h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 {{ $event->typeBadgeClass() }}">
                            {{ $event->typeLabel() }}
                        </span>
                    </div>

                    @if($event->location)
                    <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $event->location }}</span>
                    </p>
                    @endif

                    <div class="flex items-center gap-2 mt-1">
                        @if($event->end_date && !$event->event_date->isSameDay($event->end_date))
                        <p class="text-[10px] text-slate-400">
                            {{ $event->event_date->isoFormat('D MMM') }} – {{ $event->end_date->isoFormat('D MMM Y') }}
                        </p>
                        @else
                        <p class="text-[10px] text-slate-400">{{ $event->event_date->isoFormat('D MMMM Y') }}</p>
                        @endif

                        @if($isToday)
                        <span class="text-[9px] font-bold text-amber-700 bg-amber-100 border border-amber-200 px-1.5 py-0.2 rounded-md animate-pulse">Hari ini</span>
                        @elseif($daysLeft > 0 && $daysLeft <= 7)
                        <span class="text-[9px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.2 rounded-md">{{ $daysLeft }} hari lagi</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Galeri terkait (jika ada) --}}
            @if($event->gallery && $event->gallery->photos->isNotEmpty())
            <div class="mt-3 pt-2.5 border-t border-slate-100">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold text-slate-500 flex items-center gap-1">
                        <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Galeri: {{ $event->gallery->title }}
                    </span>
                    <span class="text-[10px] text-amber-600 font-semibold">Lihat foto →</span>
                </div>
                <div class="flex gap-1.5 overflow-x-auto scrollbar-none py-0.5">
                    @foreach($event->gallery->photos->take(4) as $photo)
                    <div class="shrink-0 w-13 h-13 rounded-lg overflow-hidden bg-slate-100 border border-slate-200/80">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->photo) }}"
                            alt="{{ $photo->caption }}" class="w-full h-full object-cover">
                    </div>
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
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 mb-6">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="text-xs font-bold text-slate-800">Dokumentasi & Galeri</h2>
            <p class="text-[11px] text-slate-400">Arsip foto kegiatan SMAN 1 Gianyar</p>
        </div>
        <a href="{{ route('siswa.humas.gallery.index') }}" class="text-[11px] text-amber-600 hover:text-amber-700 font-semibold inline-flex items-center gap-0.5">
            <span>Lihat Semua</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <div class="grid grid-cols-2 gap-2.5">
        @foreach($latestGalleries as $gallery)
        <a href="{{ route('siswa.humas.gallery.show', $gallery) }}"
            class="group bg-slate-50 hover:bg-slate-100/70 rounded-xl border border-slate-200/80 overflow-hidden transition-all">
            <div class="h-24 bg-slate-200 relative overflow-hidden">
                @if($gallery->coverPhotoUrl())
                <img src="{{ $gallery->coverPhotoUrl() }}" alt="{{ $gallery->title }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                <div class="w-full h-full flex items-center justify-center bg-amber-50 text-amber-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif
                @if($gallery->photos_count > 0)
                <span class="absolute bottom-1.5 right-1.5 bg-black/60 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full backdrop-blur-xs">
                    {{ $gallery->photos_count }} foto
                </span>
                @endif
            </div>
            <div class="p-2.5">
                <p class="text-xs font-bold text-slate-800 line-clamp-1 leading-snug group-hover:text-amber-700 transition-colors">{{ $gallery->title }}</p>
                @if($gallery->event_date)
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $gallery->event_date->isoFormat('D MMM Y') }}</p>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

@endsection
