@extends('layouts.siswa')
@section('title', $event->title)
@section('page-title', 'Detail Agenda')

@section('content')

{{-- ─── Cover / Date Hero ──────────────────────────────────────────── --}}
@if($event->coverPhotoUrl())
<div class="rounded-2xl overflow-hidden mb-4 h-44 relative">
    <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent"></div>
    <div class="absolute bottom-0 left-0 right-0 p-4">
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $event->typeBadgeClass() }} mb-1.5 inline-block">
            {{ $event->typeLabel() }}
        </span>
        <p class="text-white text-lg font-bold leading-tight">{{ $event->title }}</p>
    </div>
</div>
@else
<div class="bg-linear-to-br from-orange-500 to-rose-500 rounded-2xl p-4 mb-4 text-white">
    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 mb-2 inline-block">
        {{ $event->typeLabel() }}
    </span>
    <h1 class="text-xl font-bold leading-tight">{{ $event->title }}</h1>
</div>
@endif

{{-- ─── Info Baris ──────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 space-y-3">

    {{-- Tanggal --}}
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-orange-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-[11px] text-gray-400 font-medium">Tanggal</p>
            @if($event->end_date && !$event->event_date->isSameDay($event->end_date))
            <p class="text-sm font-semibold text-gray-800">
                {{ $event->event_date->isoFormat('D MMMM') }} – {{ $event->end_date->isoFormat('D MMMM Y') }}
            </p>
            @else
            <p class="text-sm font-semibold text-gray-800">{{ $event->event_date->isoFormat('dddd, D MMMM Y') }}</p>
            @endif
        </div>
        @php
            $isToday  = $event->event_date->isToday();
            $daysLeft = today()->diffInDays($event->event_date, false);
        @endphp
        @if($isToday)
        <span class="ml-auto text-xs font-bold text-orange-600 bg-orange-100 px-2 py-0.5 rounded-full animate-pulse">Hari ini</span>
        @elseif($daysLeft > 0)
        <span class="ml-auto text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">{{ $daysLeft }} hari lagi</span>
        @elseif($daysLeft < 0)
        <span class="ml-auto text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Selesai</span>
        @endif
    </div>

    @if($event->location)
    {{-- Lokasi --}}
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-[11px] text-gray-400 font-medium">Lokasi</p>
            <p class="text-sm font-semibold text-gray-800">{{ $event->location }}</p>
        </div>
    </div>
    @endif

</div>

{{-- ─── Deskripsi ───────────────────────────────────────────────────── --}}
@if($event->description)
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
    <p class="text-sm font-semibold text-gray-700 mb-2">Deskripsi</p>
    <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $event->description }}</p>
</div>
@endif

{{-- ─── Galeri Terkait ──────────────────────────────────────────────── --}}
@if($event->gallery && $event->gallery->photos->isNotEmpty())
<div class="mb-4">
    <div class="flex items-center justify-between mb-2 px-1">
        <p class="text-sm font-bold text-gray-700">Galeri: {{ $event->gallery->title }}</p>
        <a href="{{ route('siswa.humas.gallery.show', $event->gallery) }}"
            class="text-xs text-orange-500 font-semibold">Lihat semua</a>
    </div>
    <div class="grid grid-cols-3 gap-2">
        @foreach($event->gallery->photos as $photo)
        <a href="{{ route('siswa.humas.gallery.show', $event->gallery) }}"
            class="aspect-square rounded-xl overflow-hidden bg-gray-100">
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->photo) }}"
                alt="{{ $photo->caption }}" class="w-full h-full object-cover">
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- ─── Agenda Lainnya ──────────────────────────────────────────────── --}}
@if($otherEvents->isNotEmpty())
<div>
    <p class="text-sm font-bold text-gray-700 px-1 mb-2">Agenda Lainnya</p>
    <div class="space-y-2">
        @foreach($otherEvents as $other)
        <a href="{{ route('siswa.humas.event.show', $other) }}"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex items-center gap-3 block">
            <div class="w-12 h-12 rounded-xl {{ $other->event_date->isToday() ? 'bg-orange-500' : 'bg-orange-50' }} shrink-0 flex flex-col items-center justify-center">
                <p class="text-lg font-extrabold leading-none {{ $other->event_date->isToday() ? 'text-white' : 'text-orange-600' }}">
                    {{ $other->event_date->format('d') }}
                </p>
                <p class="text-[9px] uppercase font-bold {{ $other->event_date->isToday() ? 'text-orange-100' : 'text-orange-400' }}">
                    {{ $other->event_date->isoFormat('MMM') }}
                </p>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $other->title }}</p>
                @if($other->location)
                <p class="text-xs text-gray-400 truncate mt-0.5">{{ $other->location }}</p>
                @endif
            </div>
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0 {{ $other->typeBadgeClass() }}">
                {{ $other->typeLabel() }}
            </span>
        </a>
        @endforeach
    </div>
</div>
@endif

@endsection
