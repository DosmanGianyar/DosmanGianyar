@extends('layouts.siswa')

@section('title', $session ? 'Edit Sesi Voting' : 'Buat Sesi Voting')
@section('page-title', $session ? 'Edit Sesi' : 'Buat Sesi Voting')

@section('content')

<form method="POST"
    action="{{ $session ? route('siswa.voting.manage.update', $session) : route('siswa.voting.manage.store') }}"
    class="space-y-4">
    @csrf
    @if($session) @method('PUT') @endif

    {{-- Title --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Judul Voting <span class="text-red-500">*</span></label>
        <input type="text" name="title"
            value="{{ old('title', $session?->title) }}"
            placeholder="Misal: Pemilihan Ketua OSIS 2025"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-300 @enderror">
        @error('title')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Deskripsi</label>
        <textarea name="description" rows="3"
            placeholder="Deskripsi singkat tentang voting ini..."
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('description') border-red-300 @enderror">{{ old('description', $session?->description) }}</textarea>
        @error('description')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Start Time --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Waktu Mulai <span class="text-red-500">*</span></label>
        <input type="datetime-local" name="start_time"
            value="{{ old('start_time', $session?->start_time?->format('Y-m-d\TH:i')) }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_time') border-red-300 @enderror">
        @error('start_time')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- End Time --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Waktu Selesai <span class="text-red-500">*</span></label>
        <input type="datetime-local" name="end_time"
            value="{{ old('end_time', $session?->end_time?->format('Y-m-d\TH:i')) }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_time') border-red-300 @enderror">
        @error('end_time')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit"
        class="w-full bg-blue-600 text-white font-semibold py-3.5 rounded-xl text-sm mt-2">
        {{ $session ? 'Simpan Perubahan' : 'Buat Sesi Voting' }}
    </button>
</form>

@endsection
