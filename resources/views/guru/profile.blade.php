@extends('layouts.guru')

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
                        <input type="file" id="photo-input" name="photo" accept="image/*"
                            onchange="document.getElementById('photo-form').submit()">
                    </form>
                </div>

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
                <input type="password" name="current_password" required
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Password Baru</label>
                    <input type="password" name="password" required
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
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

</div>
@endsection
