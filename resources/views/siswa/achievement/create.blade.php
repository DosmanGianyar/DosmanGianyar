@extends('layouts.siswa')
@section('title', 'Laporkan Prestasi')
@section('page-title', 'Laporkan Prestasi')

@section('content')
<div class="max-w-lg mx-auto">
    <form action="{{ route('siswa.achievements.store') }}" method="POST" enctype="multipart/form-data"
        class="space-y-4">
        @csrf

        {{-- Judul --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Prestasi <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
                placeholder="Contoh: Juara 1 Olimpiade Matematika Provinsi Bali"
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm @error('title') border-red-400 @enderror">
            @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Kategori --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
            <select name="category_id" required
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm @error('category_id') border-red-400 @enderror">
                <option value="">— Pilih Kategori —</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Tingkat & Peringkat --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tingkat <span class="text-red-500">*</span></label>
                <select name="level" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm @error('level') border-red-400 @enderror">
                    <option value="">— Pilih —</option>
                    <option value="sekolah"       {{ old('level') == 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                    <option value="kabupaten"     {{ old('level') == 'kabupaten' ? 'selected' : '' }}>Kabupaten/Kota</option>
                    <option value="provinsi"      {{ old('level') == 'provinsi' ? 'selected' : '' }}>Provinsi</option>
                    <option value="nasional"      {{ old('level') == 'nasional' ? 'selected' : '' }}>Nasional</option>
                    <option value="internasional" {{ old('level') == 'internasional' ? 'selected' : '' }}>Internasional</option>
                </select>
                @error('level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Peringkat / Penghargaan</label>
                <input type="text" name="rank" value="{{ old('rank') }}" maxlength="50"
                    placeholder="Juara 1, Medali Emas..."
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                @error('rank') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Tanggal --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Prestasi <span class="text-red-500">*</span></label>
            <input type="date" name="achievement_date" value="{{ old('achievement_date') }}"
                max="{{ today()->toDateString() }}" required
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm @error('achievement_date') border-red-400 @enderror">
            @error('achievement_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Deskripsi --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Kegiatan</label>
            <textarea name="description" rows="3" maxlength="1000"
                placeholder="Ceritakan sedikit tentang kegiatan atau perlombaan ini..."
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm resize-none">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Foto Kegiatan --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Foto Kegiatan <span class="text-red-500">*</span>
            </label>
            <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300
                rounded-xl p-4 cursor-pointer hover:border-blue-400 transition-colors @error('photo') border-red-400 @enderror"
                id="photo-label">
                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span id="photo-name" class="text-sm text-gray-500">Klik untuk pilih foto kegiatan</span>
                <span class="text-xs text-gray-400 mt-1">JPG / PNG · Maks 5MB</span>
                <input type="file" name="photo" accept="image/*" required class="hidden"
                    onchange="document.getElementById('photo-name').textContent = this.files[0]?.name ?? 'Klik untuk pilih foto'">
            </label>
            @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Scan Piagam --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Scan Piagam / Sertifikat
                <span class="text-gray-400 font-normal">(disarankan)</span>
            </label>
            <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300
                rounded-xl p-4 cursor-pointer hover:border-blue-400 transition-colors @error('certificate') border-red-400 @enderror">
                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span id="cert-name" class="text-sm text-gray-500">Klik untuk pilih foto scan piagam</span>
                <span class="text-xs text-gray-400 mt-1">JPG / PNG · Maks 5MB</span>
                <input type="file" name="certificate" accept="image/*" class="hidden"
                    onchange="document.getElementById('cert-name').textContent = this.files[0]?.name ?? 'Klik untuk pilih foto scan piagam'">
            </label>
            @error('certificate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-2xl text-base transition-colors">
            Kirim Laporan Prestasi
        </button>

        <a href="{{ route('siswa.achievements.index') }}"
            class="block text-center text-sm text-gray-500 py-2">Batal</a>

    </form>
</div>
@endsection
