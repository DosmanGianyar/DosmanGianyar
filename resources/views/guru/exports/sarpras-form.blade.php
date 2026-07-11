@extends('layouts.guru')
@section('title', 'Export Laporan Sarpras')

@section('content')
<div class="max-w-xl mx-auto">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-800">Export Laporan Sarpras</h1>
        <p class="text-sm text-gray-500 mt-1">Unduh laporan aset dan kerusakan dalam format PDF</p>
    </div>

    {{-- Laporan Aset --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
        <h2 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
            <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            Rekap Aset
        </h2>
        <form method="GET" action="{{ route('guru.export.sarpras.assets.pdf') }}" target="_blank" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ruangan</label>
                    <select name="room_id" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Ruangan</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kondisi</label>
                    <select name="condition" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Kondisi</option>
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                <select name="category" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Kategori</option>
                    <option value="perpus">Perpustakaan</option>
                    <option value="sarana">Sarana</option>
                    <option value="prasarana">Prasarana</option>
                </select>
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unduh PDF Rekap Aset
            </button>
        </form>
    </div>

    {{-- Laporan Kerusakan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
            <div class="w-6 h-6 bg-red-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            Laporan Kerusakan
        </h2>
        <form method="GET" action="{{ route('guru.export.sarpras.damage.pdf') }}" target="_blank" class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="in_progress">Ditangani</option>
                    <option value="resolved">Selesai</option>
                </select>
            </div>
            <button type="submit"
                class="w-full bg-red-600 text-white text-sm font-semibold py-2.5 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unduh PDF Laporan Kerusakan
            </button>
        </form>
    </div>

</div>
@endsection
