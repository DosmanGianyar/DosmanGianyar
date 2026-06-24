@extends('layouts.siswa')

@section('title', $announcement ? 'Edit Pengumuman' : 'Buat Pengumuman')
@section('page-title', $announcement ? 'Edit Pengumuman' : 'Buat Pengumuman')

@section('content')

<form method="POST"
    action="{{ $announcement ? route('siswa.announcements.update', $announcement) : route('siswa.announcements.store') }}"
    class="space-y-4">
    @csrf
    @if($announcement) @method('PUT') @endif

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Judul <span class="text-red-500">*</span></label>
        <input type="text" name="title"
            value="{{ old('title', $announcement?->title) }}"
            placeholder="Judul pengumuman"
            required
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-300 @enderror">
        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Isi Pengumuman <span class="text-red-500">*</span></label>
        <textarea name="body" rows="8"
            placeholder="Tulis isi pengumuman di sini..."
            required
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('body') border-red-300 @enderror">{{ old('body', $announcement?->body) }}</textarea>
        @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Ditujukan Untuk</label>
        <select name="target"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="all"   {{ old('target', $announcement?->target) === 'all'   ? 'selected' : '' }}>Semua (Siswa & Guru)</option>
            <option value="siswa" {{ old('target', $announcement?->target) === 'siswa' ? 'selected' : '' }}>Siswa Saja</option>
            <option value="guru"  {{ old('target', $announcement?->target) === 'guru'  ? 'selected' : '' }}>Guru Saja</option>
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Jadwal Terbit (kosongkan = terbit sekarang)</label>
        <input type="datetime-local" name="published_at"
            value="{{ old('published_at', $announcement?->published_at?->format('Y-m-d\TH:i')) }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <label class="flex items-center gap-3 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 cursor-pointer">
        <input type="checkbox" name="is_pinned" value="1"
            {{ old('is_pinned', $announcement?->is_pinned) ? 'checked' : '' }}
            class="w-4 h-4 text-yellow-500 rounded">
        <div>
            <p class="text-sm font-semibold text-yellow-800">Sematkan di atas</p>
            <p class="text-xs text-yellow-600">Pengumuman ini akan selalu muncul di posisi teratas</p>
        </div>
    </label>

    <button type="submit"
        class="w-full bg-blue-600 text-white font-semibold py-3.5 rounded-xl text-sm">
        {{ $announcement ? 'Simpan Perubahan' : 'Terbitkan Pengumuman' }}
    </button>
</form>

@endsection
