@extends('layouts.guru')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
@endpush

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    {{-- ─── Kartu Identitas ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-20 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
        <div class="px-6 pb-6">
            <div class="flex items-end gap-4 -mt-10 mb-4">
                {{-- Avatar + tombol ganti foto --}}
                <div class="relative">
                    @if($guru->photo)
                        <img src="{{ $guru->photo_url }}"
                            class="w-20 h-20 rounded-2xl object-cover border-4 border-white shadow-md">
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-blue-600 border-4 border-white shadow-md
                            flex items-center justify-center text-white text-2xl font-bold">
                            {{ $guru->initials }}
                        </div>
                    @endif
                    <label for="photo-input"
                        class="absolute -bottom-1 -right-1 w-7 h-7 bg-blue-600 rounded-full
                            flex items-center justify-center cursor-pointer hover:bg-blue-700 transition-colors">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                    <form id="photo-form" method="POST" action="{{ route('guru.profile.photo') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="photo-input" name="photo" accept="image/*">
                    </form>
                </div>
                @error('photo')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror

                <div class="mb-1">
                    <h2 class="text-lg font-bold text-gray-800">{{ $guru->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $guru->subject ?? 'Guru' }}
                        @if($guru->homeroomClass)
                            · Wali Kelas {{ $guru->homeroomClass->name }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-blue-50 rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-500">NIP</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $guru->nip ?? '—' }}</p>
                </div>
                <div class="bg-blue-50 rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-500">Email</p>
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $guru->email }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Form Edit Data Diri ──────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Edit Data Diri</h3>

        <form method="POST" action="{{ route('guru.profile.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $guru->name) }}" required
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}"
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Mata Pelajaran</label>
                    <input type="text" name="subject" value="{{ old('subject', $guru->subject) }}"
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Matematika, Bahasa Indonesia, ...">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">No. HP</label>
                    <input type="text" name="phone" value="{{ old('phone', $guru->phone) }}"
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="08xxxxxxxxxx">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                    <input type="text" name="address" value="{{ old('address', $guru->address) }}"
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- ─── Ganti Password ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Ganti Password</h3>

        <form method="POST" action="{{ route('guru.profile.password') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password Saat Ini</label>
                <div class="relative">
                    <input type="password" id="guru_current_password" name="current_password" required
                        class="w-full pl-3 pr-10 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" onclick="togglePasswordVisibility('guru_current_password', this)"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1"
                        title="Tampilkan/sembunyikan password">
                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.962 8.962 0 013.122-.563c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-2.483 4.13m-3.535 3.536L3 3l18 18"/>
                        </svg>
                    </button>
                </div>
                @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Password Baru</label>
                    <div class="relative">
                        <input type="password" id="guru_new_password" name="password" required
                            class="w-full pl-3 pr-10 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="togglePasswordVisibility('guru_new_password', this)"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1"
                            title="Tampilkan/sembunyikan password">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.962 8.962 0 013.122-.563c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-2.483 4.13m-3.535 3.536L3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Konfirmasi Password Baru</label>
                    <div class="relative">
                        <input type="password" id="guru_confirm_password" name="password_confirmation" required
                            class="w-full pl-3 pr-10 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="togglePasswordVisibility('guru_confirm_password', this)"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1"
                            title="Tampilkan/sembunyikan password">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.962 8.962 0 013.122-.563c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-2.483 4.13m-3.535 3.536L3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="px-5 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors">
                    Perbarui Password
                </button>
            </div>
        </form>
    </div>

    <script>
    if (typeof togglePasswordVisibility !== 'function') {
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            
            if (input.type === 'password') {
                input.type = 'text';
                if (eyeOpen) eyeOpen.classList.add('hidden');
                if (eyeClosed) eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                if (eyeOpen) eyeOpen.classList.remove('hidden');
                if (eyeClosed) eyeClosed.classList.add('hidden');
            }
        }
    }
    </script>

    {{-- ─── Modal Interactive Photo Crop ──────────────────────────── --}}
    <div id="crop-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-4 py-3 bg-gray-900 text-white flex items-center justify-between">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span>✂️ Sesuaikan & Geser Foto Profil</span>
                </h3>
                <button type="button" onclick="closeCropModal()" class="text-gray-400 hover:text-white text-lg font-bold cursor-pointer">✕</button>
            </div>

            <div class="p-4 bg-gray-900 flex-1 overflow-hidden flex items-center justify-center min-h-[280px] max-h-[420px] relative">
                <img id="crop-image" class="max-w-full max-h-full block">
            </div>

            <div class="p-3 bg-gray-50 border-t border-gray-200 flex flex-col gap-3">
                <p class="text-[11px] text-gray-500 text-center">Gunakan mouse / sentuhan untuk menggeser & menyesuaikan posisi kepala agar tidak terpotong.</p>
                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                    <button type="button" onclick="cropper && cropper.zoom(0.1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Perbesar">🔍 +</button>
                    <button type="button" onclick="cropper && cropper.zoom(-0.1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Perkecil">🔍 -</button>
                    <button type="button" onclick="cropper && cropper.rotate(-90)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Putar Kiri">↺ 90°</button>
                    <button type="button" onclick="cropper && cropper.rotate(90)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Putar Kanan">↻ 90°</button>
                    <button type="button" onclick="cropper && cropper.reset()" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Reset">🔄 Reset</button>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-200">
                    <button type="button" onclick="closeCropModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-300 cursor-pointer">Batal</button>
                    <button type="button" id="save-crop-btn" onclick="saveCroppedPhoto()" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 shadow-md active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                        <span id="save-crop-spinner" class="hidden animate-spin">⌛</span>
                        <span>Simpan Foto Profil</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let cropper = null;
    const photoInput = document.getElementById('photo-input');
    const cropModal = document.getElementById('crop-modal');
    const cropImage = document.getElementById('crop-image');

    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const reader = new FileReader();
                reader.onload = function(evt) {
                    cropImage.src = evt.target.result;
                    cropModal.classList.remove('hidden');
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(cropImage, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 0.9,
                        dragMode: 'move',
                        background: false,
                        responsive: true,
                    });
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function closeCropModal() {
        if (cropModal) cropModal.classList.add('hidden');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (photoInput) photoInput.value = '';
    }

    function saveCroppedPhoto() {
        if (!cropper) return;
        const spinner = document.getElementById('save-crop-spinner');
        const btn = document.getElementById('save-crop-btn');
        if (spinner) spinner.classList.remove('hidden');
        if (btn) btn.disabled = true;

        const canvas = cropper.getCroppedCanvas({
            width: 600,
            height: 600,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        canvas.toBlob(function(blob) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('photo', blob, 'avatar.jpg');

            fetch('{{ route("guru.profile.photo") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.redirected) {
                    window.location.href = res.redirected;
                } else {
                    window.location.reload();
                }
            })
            .catch(err => {
                alert('Gagal menyimpan foto: ' + err);
                if (spinner) spinner.classList.add('hidden');
                if (btn) btn.disabled = false;
            });
        }, 'image/jpeg', 0.9);
    }
    </script>

</div>
@endsection
