@extends('layouts.siswa')
@section('title', 'Katalog Aset')
@section('page-title', 'Katalog Aset')

@section('content')

{{-- ─── Filter ──────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 mb-4">
    <form method="GET" class="space-y-2">
        <div class="flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Cari nama aset..."
                class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-300">
            <button type="submit"
                class="px-4 py-2 bg-violet-600 text-white text-sm font-semibold rounded-xl hover:bg-violet-700 transition-colors">
                Cari
            </button>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <select name="category" class="border border-gray-200 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-violet-300">
                <option value="">Semua Kategori</option>
                @foreach(['furniture'=>'Furnitur','elektronik'=>'Elektronik','olahraga'=>'Olahraga','lab'=>'Lab','perpustakaan'=>'Perpustakaan','lain'=>'Lain-lain'] as $val=>$label)
                <option value="{{ $val }}" {{ request('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="condition" class="border border-gray-200 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-violet-300">
                <option value="">Semua Kondisi</option>
                <option value="baik"         {{ request('condition') === 'baik'         ? 'selected' : '' }}>Baik</option>
                <option value="rusak_ringan"  {{ request('condition') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                <option value="rusak_berat"   {{ request('condition') === 'rusak_berat'  ? 'selected' : '' }}>Rusak Berat</option>
            </select>
            <select name="room_id" class="border border-gray-200 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-violet-300">
                <option value="">Semua Ruangan</option>
                @foreach($rooms as $room)
                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                    {{ $room->name }}
                </option>
                @endforeach
            </select>
        </div>
        @if(request()->hasAny(['q','category','condition','room_id']))
        <a href="{{ route('siswa.sarpras.catalog') }}"
            class="block text-center text-xs text-gray-500 hover:text-gray-700">
            Reset filter
        </a>
        @endif
    </form>
</div>

{{-- ─── Hasil ───────────────────────────────────────────────────────── --}}
<div class="mb-2 px-1 flex items-center justify-between">
    <p class="text-xs text-gray-400">{{ $assets->total() }} aset ditemukan</p>
</div>

<div class="grid grid-cols-2 gap-3">
    @forelse($assets as $asset)
    @php
        $condColor = match($asset->condition) {
            'baik'        => 'bg-green-100 text-green-700',
            'rusak_ringan'=> 'bg-yellow-100 text-yellow-700',
            'rusak_berat' => 'bg-red-100 text-red-700',
            default       => 'bg-gray-100 text-gray-600',
        };
    @endphp
    <a href="{{ route('siswa.sarpras.asset.show', $asset->qr_code) }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        {{-- Foto --}}
        <div class="h-24 bg-gray-50 relative">
            @if($asset->photo)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($asset->photo) }}"
                alt="{{ $asset->name }}" class="w-full h-full object-cover">
            @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            @endif
            <span class="absolute top-1.5 right-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded-full {{ $condColor }}">
                {{ $asset->conditionLabel() }}
            </span>
        </div>
        {{-- Info --}}
        <div class="p-2.5 flex-1 flex flex-col gap-0.5">
            <p class="text-xs font-semibold text-gray-800 line-clamp-2 leading-tight">{{ $asset->name }}</p>
            <p class="text-[10px] text-gray-400 mt-auto">{{ $asset->categoryLabel() }}</p>
            @if($asset->room)
            <p class="text-[10px] text-gray-400 truncate">{{ $asset->room->name }}</p>
            @endif
        </div>
    </a>
    @empty
    <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center">
        <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <p class="text-sm text-gray-400">Tidak ada aset ditemukan</p>
    </div>
    @endforelse
</div>

@if($assets->hasPages())
<div class="mt-3 bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3">
    {{ $assets->links() }}
</div>
@endif

@endsection
