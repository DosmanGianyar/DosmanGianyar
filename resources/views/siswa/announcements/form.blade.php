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
        <label class="block text-xs font-semibold text-gray-600 mb-1">Judul Pengumuman <span class="text-red-500">*</span></label>
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
        <label class="block text-xs font-semibold text-gray-600 mb-1">Ditujukan Untuk (Target Peran)</label>
        <select name="target" id="target_role"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="all"      {{ old('target', $announcement?->target) === 'all'      ? 'selected' : '' }}>Semua User (Siswa, Guru, Orang Tua)</option>
            <option value="guru"     {{ old('target', $announcement?->target) === 'guru'     ? 'selected' : '' }}>Hanya Guru</option>
            <option value="siswa"    {{ old('target', $announcement?->target) === 'siswa'    ? 'selected' : '' }}>Hanya Siswa</option>
            <option value="orangtua" {{ old('target', $announcement?->target) === 'orangtua' ? 'selected' : '' }}>Hanya Orang Tua</option>
        </select>
    </div>

    {{-- Filter Kelas Opsional --}}
    <div id="class_filter_wrapper" class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 space-y-2">
        <div class="flex items-center justify-between">
            <label class="block text-xs font-semibold text-blue-900">Filter Kelas Spesifik (Opsional)</label>
            <span class="text-[11px] text-blue-600">Kosongkan jika untuk Semua Kelas</span>
        </div>
        @php
            $selectedClasses = old('target_class_ids', $announcement?->target_class_ids ?? []);
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1">
            @foreach($classes as $cls)
                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg p-2 text-xs cursor-pointer hover:bg-blue-50">
                    <input type="checkbox" name="target_class_ids[]" value="{{ $cls->id }}"
                        {{ in_array($cls->id, $selectedClasses) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 rounded">
                    <span class="font-medium text-gray-700">{{ $cls->name }}</span>
                </label>
            @endforeach
        </div>
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
