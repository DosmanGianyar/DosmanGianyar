@extends('layouts.siswa')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@600;700&display=swap" rel="stylesheet">
@endpush

@section('title', 'Profil')
@section('page-title', 'Profil')

@section('content')
<div class="space-y-4">

    @if(auth()->user()->must_change_password)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
        <p class="text-sm font-semibold text-amber-800">Wajib Ganti Password</p>
        <p class="text-xs text-amber-700 mt-1">
            Demi keamanan akun, silakan ganti password default Anda sebelum melanjutkan.
            <a href="#ganti-password" class="font-semibold underline">Ganti sekarang</a>.
        </p>
    </div>
    @endif

    {{-- ─── Kartu Identitas Siswa ───────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Banner --}}
        <div class="h-20 bg-linear-to-r from-blue-600 via-blue-700 to-indigo-700 relative">
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px),
                                         radial-gradient(circle at 80% 20%, white 1px, transparent 1px);
                       background-size: 30px 30px;"></div>
        </div>

        {{-- Avatar + Nama (centered, overlaps banner) --}}
        <div class="flex flex-col items-center -mt-10 pb-4 px-4">
            <div class="relative mb-2">
                @if($siswa->photo)
                    <img src="{{ $siswa->photo_url }}"
                        class="w-20 h-20 rounded-2xl object-cover border-4 border-white shadow-lg">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-linear-to-br from-blue-500 to-indigo-600
                        border-4 border-white shadow-lg flex items-center justify-center text-white text-2xl font-bold">
                        {{ $siswa->initials }}
                    </div>
                @endif
                <label for="photo-input"
                    class="absolute -bottom-1 -right-1 w-7 h-7 bg-blue-600 rounded-full
                        flex items-center justify-center cursor-pointer hover:bg-blue-700 shadow">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    </svg>
                </label>
                <form id="photo-form" method="POST" action="{{ route('siswa.profile.photo') }}"
                    enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" id="photo-input" name="photo" accept="image/*"
                        onchange="this.form.submit()">
                </form>
            </div>
            @error('photo')
            <p class="text-xs text-red-500 text-center mt-1">{{ $message }}</p>
            @enderror
            <h2 class="text-base font-bold text-gray-800 text-center mt-1">{{ $siswa->name }}</h2>
            <p class="text-xs text-gray-500 text-center">
                {{ $siswa->schoolClass?->name ?? '—' }} @if($siswa->angkatan) · <span class="font-semibold text-amber-700">{{ $siswa->angkatan }}</span> @endif
            </p>
        </div>

        {{-- Grid Info --}}
        <div class="px-4 pb-4 grid grid-cols-3 gap-2">
            <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">NIS</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $siswa->nis ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Kelas</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $siswa->schoolClass?->name ?? '—' }}</p>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-xl px-3 py-2.5">
                <p class="text-[10px] text-amber-700 font-semibold uppercase tracking-wide">Angkatan</p>
                <p class="text-sm font-bold text-amber-900 mt-0.5">{{ $siswa->angkatan ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl px-3 py-2.5 col-span-2">
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Tanggal Lahir</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">
                    {{ $siswa->birth_date?->isoFormat('D MMM Y') ?? '—' }}
                </p>
            </div>
            <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">No. HP</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $siswa->phone ?? '—' }}</p>
            </div>
        </div>
    </div>

    @include('siswa.partials.student-card-digital')

    {{-- ─── Data Orang Tua ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Informasi Orang Tua / Wali</h3>
        <div class="grid grid-cols-1 gap-3">
            <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Nama Orang Tua</p>
                    <p class="text-sm font-medium text-gray-800">{{ $siswa->parent_name ?? '—' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 py-2">
                <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400">No. HP Orang Tua</p>
                    <p class="text-sm font-medium text-gray-800">{{ $siswa->parent_phone ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Edit Data Diri ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-800">Edit Data Diri</h3>
        </div>
        <form method="POST" action="{{ route('siswa.profile.update') }}" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">No. HP Siswa</label>
                <div class="flex items-center rounded-xl border border-gray-300 bg-gray-50/50 focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                    <input type="text" name="phone" value="{{ old('phone', $siswa->phone) }}"
                        class="w-full px-3 py-2.5 text-sm text-gray-900 bg-transparent focus:outline-none border-0"
                        placeholder="08xxxxxxxxxx">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Tempat Tinggal</label>
                <div class="flex items-center rounded-xl border border-gray-300 bg-gray-50/50 focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                    <input type="text" name="address" value="{{ old('address', $siswa->address) }}"
                        class="w-full px-3 py-2.5 text-sm text-gray-900 bg-transparent focus:outline-none border-0"
                        placeholder="Jl. Contoh No. X, Gianyar">
                </div>
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-xs active:scale-[0.99] transition-all">
                Simpan Perubahan
            </button>
        </form>
    </div>

    {{-- ─── Ganti Password ──────────────────────────────────────────── --}}
    <div id="ganti-password" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-800">Ganti Password</h3>
        </div>
        <form method="POST" action="{{ route('siswa.profile.password') }}" class="space-y-3">
            @csrf @method('PUT')
            
            {{-- Password Saat Ini --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Password Saat Ini</label>
                <div class="relative flex items-center rounded-xl border border-gray-300 bg-gray-50/50 focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                    <input type="password" id="siswa_current_password" name="current_password" required
                        class="w-full pl-3 pr-10 py-2.5 text-sm text-gray-900 bg-transparent focus:outline-none border-0"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('siswa_current_password', this)"
                        class="absolute right-0 top-0 bottom-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none bg-transparent transition-colors"
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

            {{-- Password Baru --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Password Baru</label>
                <div class="relative flex items-center rounded-xl border border-gray-300 bg-gray-50/50 focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                    <input type="password" id="siswa_new_password" name="password" required
                        class="w-full pl-3 pr-10 py-2.5 text-sm text-gray-900 bg-transparent focus:outline-none border-0"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('siswa_new_password', this)"
                        class="absolute right-0 top-0 bottom-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none bg-transparent transition-colors"
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

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Konfirmasi Password Baru</label>
                <div class="relative flex items-center rounded-xl border border-gray-300 bg-gray-50/50 focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                    <input type="password" id="siswa_confirm_password" name="password_confirmation" required
                        class="w-full pl-3 pr-10 py-2.5 text-sm text-gray-900 bg-transparent focus:outline-none border-0"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('siswa_confirm_password', this)"
                        class="absolute right-0 top-0 bottom-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none bg-transparent transition-colors"
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

            <button type="submit"
                class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl shadow-xs active:scale-[0.99] transition-all">
                Perbarui Password
            </button>
        </form>
    </div>

    <script>
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
    </script>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="w-full py-3 rounded-2xl border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 transition-colors">
            Keluar dari Akun
        </button>
    </form>

</div>
@endsection
