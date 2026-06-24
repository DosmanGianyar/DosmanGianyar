@extends('layouts.siswa')
@section('title', 'Laporkan Kerusakan')
@section('page-title', 'Laporkan Kerusakan')

@section('content')
<div class="space-y-4">

    {{-- Asset Info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-800">{{ $asset->name }}</p>
            <p class="text-xs text-gray-500">{{ $asset->categoryLabel() }} · Kondisi: {{ $asset->conditionLabel() }}</p>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('siswa.sarpras.damage.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="asset_id" value="{{ $asset->id }}">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

            {{-- Deskripsi --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Kerusakan <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4" required maxlength="500"
                    placeholder="Jelaskan kerusakan yang kamu temukan..."
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-orange-300 @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Foto --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Kerusakan <span class="text-red-500">*</span></label>
                <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-orange-400 transition-colors">
                    <input type="file" name="photo" id="photo-input" accept="image/*" capture="environment" required
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        onchange="showPreview(this)">
                    <div id="photo-placeholder">
                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm text-gray-500">Ketuk untuk foto kerusakan</p>
                        <p class="text-xs text-gray-400 mt-0.5">Maks. 5MB</p>
                    </div>
                    <img id="photo-preview" src="#" class="hidden w-full max-h-48 object-contain rounded-lg mx-auto">
                </div>
                @error('photo')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('siswa.sarpras.asset.show', $asset->qr_code) }}"
                class="flex-1 py-3 text-sm font-semibold text-gray-600 bg-gray-100 rounded-2xl text-center hover:bg-gray-200 transition-colors">
                Batal
            </a>
            <button type="submit"
                class="flex-1 py-3 text-sm font-semibold text-white bg-orange-600 rounded-2xl hover:bg-orange-700 transition-colors">
                Kirim Laporan
            </button>
        </div>
    </form>
</div>

<script>
    function showPreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('photo-placeholder').classList.add('hidden');
                const preview = document.getElementById('photo-preview');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
