@extends('layouts.siswa')
@section('title', 'Pinjam Aset')
@section('page-title', 'Pinjam Aset')

@section('content')
<div class="space-y-4">

    {{-- Asset Info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-800">{{ $asset->name }}</p>
            <p class="text-xs text-gray-500">{{ $asset->categoryLabel() }} · {{ $asset->conditionLabel() }}</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
        @foreach($errors->all() as $error)
        <p class="text-sm text-red-700">{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('siswa.sarpras.loan.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="asset_id" value="{{ $asset->id }}">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                        min="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 @error('start_date') border-red-400 @enderror">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}"
                        min="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 @error('end_date') border-red-400 @enderror">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keperluan <span class="text-red-500">*</span></label>
                <input type="text" name="purpose" value="{{ old('purpose') }}"
                    placeholder="Untuk keperluan apa aset ini dipinjam..." required maxlength="255"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 @error('purpose') border-red-400 @enderror">
                @error('purpose')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-3">
            <p class="text-xs text-yellow-700">Permintaan peminjaman akan dikirim ke guru untuk disetujui. Pastikan tanggal dan keperluan sudah benar.</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('siswa.sarpras.asset.show', $asset->qr_code) }}"
                class="flex-1 py-3 text-sm font-semibold text-gray-600 bg-gray-100 rounded-2xl text-center hover:bg-gray-200 transition-colors">
                Batal
            </a>
            <button type="submit"
                class="flex-1 py-3 text-sm font-semibold text-white bg-purple-600 rounded-2xl hover:bg-purple-700 transition-colors">
                Kirim Permintaan
            </button>
        </div>
    </form>
</div>
@endsection
