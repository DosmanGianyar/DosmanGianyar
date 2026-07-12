@extends('layouts.orangtua')
@section('title', 'Beranda')
@section('page-title', 'Beranda')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    <div class="bg-linear-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 text-white">
        <p class="text-sm font-semibold">Selamat datang, {{ auth()->user()->name }}</p>
        <p class="text-blue-200 text-xs mt-0.5">Pantau data putra/putri Anda di sini</p>
    </div>

    @forelse($children as $child)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-3 mb-3">
            @if($child->photo)
                <img src="{{ $child->photo_url }}" class="w-12 h-12 rounded-full object-cover ring-2 ring-blue-100">
            @else
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-sm font-bold">
                    {{ $child->initials }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm truncate">{{ $child->name }}</p>
                <p class="text-xs text-gray-400">{{ $child->schoolClass?->name ?? '—' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <a href="{{ route('orangtua.attendance.history', $child->id) }}"
                class="bg-blue-50 rounded-xl py-2.5 text-center hover:bg-blue-100 transition-colors">
                <p class="text-xs font-semibold text-blue-700">Absensi</p>
            </a>
            <a href="{{ route('orangtua.conduct.index', $child->id) }}"
                class="bg-amber-50 rounded-xl py-2.5 text-center hover:bg-amber-100 transition-colors">
                <p class="text-xs font-semibold text-amber-700">Catatan</p>
            </a>
            <a href="{{ route('orangtua.achievements.index', $child->id) }}"
                class="bg-green-50 rounded-xl py-2.5 text-center hover:bg-green-100 transition-colors">
                <p class="text-xs font-semibold text-green-700">Prestasi</p>
            </a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
        <p class="text-gray-700 font-semibold">Belum ada data anak yang terhubung</p>
        <p class="text-gray-400 text-xs mt-1">Hubungi admin sekolah jika ini tidak sesuai.</p>
    </div>
    @endforelse

</div>
@endsection
