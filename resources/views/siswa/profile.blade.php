@extends('layouts.siswa')

@section('title', 'Profil')
@section('page-title', 'Profil')

@section('content')
<div class="space-y-4">

    {{-- ─── Kartu Identitas Siswa ───────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-16 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
        <div class="px-4 pb-4">
            <div class="flex items-end gap-3 -mt-8 mb-3">
                <div class="relative">
                    @if($siswa->photo)
                        <img src="{{ $siswa->photo_url }}"
                            class="w-16 h-16 rounded-2xl object-cover border-4 border-white shadow-md">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-blue-600 border-4 border-white shadow-md
                            flex items-center justify-center text-white text-xl font-bold">
                            {{ $siswa->initials }}
                        </div>
                    @endif
                    <label for="photo-input"
                        class="absolute -bottom-1 -right-1 w-6 h-6 bg-blue-600 rounded-full
                            flex items-center justify-center cursor-pointer hover:bg-blue-700">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        </svg>
                    </label>
                    <form id="photo-form" method="POST" action="{{ route('siswa.profile.photo') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="photo-input" name="photo" accept="image/*"
                            onchange="document.getElementById('photo-form').submit()">
                    </form>
                </div>
                <div class="mb-0.5">
                    <h2 class="text-base font-bold text-gray-800">{{ $siswa->name }}</h2>
                    <p class="text-xs text-gray-500">{{ $siswa->schoolClass?->name ?? '—' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="bg-gray-50 rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400">NIS</p>
                    <p class="text-sm font-bold text-gray-800">{{ $siswa->nis ?? '—' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400">Kelas</p>
                    <p class="text-sm font-bold text-gray-800">{{ $siswa->schoolClass?->name ?? '—' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400">Tanggal Lahir</p>
                    <p class="text-sm font-bold text-gray-800">
                        {{ $siswa->birth_date?->isoFormat('D MMM Y') ?? '—' }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400">No. HP</p>
                    <p class="text-sm font-bold text-gray-800">{{ $siswa->phone ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── E-Kartu Pelajar ─────────────────────────────────────────── --}}
    <div class="rounded-2xl overflow-hidden shadow-lg" style="border: 1.5px solid #c7d7f5;">

        {{-- Kop Sekolah --}}
        <div class="bg-linear-to-br from-blue-800 to-indigo-900 px-4 pt-4 pb-3">
            <div class="flex items-center gap-3">
                {{-- Logo Sekolah --}}
                <div class="w-14 h-14 rounded-full shrink-0 flex items-center justify-center bg-white p-1.5 shadow-md"
                    style="border: 2.5px solid #D4AF37;">
                    <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo SMA N 1 Gianyar"
                        class="w-full h-full object-contain">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white font-extrabold text-sm tracking-wide leading-tight">SMA NEGERI 1 GIANYAR</p>
                    <p class="text-blue-200 text-[10px] leading-tight mt-0.5">Jl. Ratna No.1, Gianyar, Bali 80511</p>
                    <p class="text-blue-300 text-[10px]">Telp. (0361) 943072 · NPSN 50101048</p>
                </div>
            </div>
            {{-- Garis & Judul --}}
            <div class="mt-3 flex items-center gap-2">
                <div class="flex-1 h-px bg-white/30"></div>
                <p class="text-white text-[11px] font-bold tracking-[0.2em] px-1">KARTU PELAJAR</p>
                <div class="flex-1 h-px bg-white/30"></div>
            </div>
        </div>

        {{-- Body Kartu --}}
        <div class="bg-white px-4 py-3">
            <div class="flex gap-3">

                {{-- Foto Siswa --}}
                <div class="shrink-0 flex flex-col items-center">
                    <div class="w-20 h-25 rounded-lg overflow-hidden border-2 border-blue-300 bg-blue-50 flex items-center justify-center shadow-sm">
                        @if($siswa->photo)
                            <img src="{{ $siswa->photo_url }}"
                                class="w-full h-full object-cover object-top">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-linear-to-b from-blue-600 to-indigo-700">
                                <svg class="w-8 h-8 text-white/60 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-white font-bold text-lg leading-none">{{ $siswa->initials }}</span>
                            </div>
                        @endif
                    </div>
                    <p class="text-[9px] text-gray-400 mt-1 font-medium tracking-wide">3 × 4</p>
                </div>

                {{-- Detail Siswa --}}
                <div class="flex-1 min-w-0 space-y-2">
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide font-semibold">Nama Lengkap</p>
                        <p class="text-sm font-bold text-gray-800 leading-tight">{{ $siswa->name }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                        <div>
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide font-semibold">NIS</p>
                            <p class="text-xs font-bold text-gray-700">{{ $siswa->nis ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide font-semibold">Kelas</p>
                            <p class="text-xs font-bold text-gray-700">{{ $siswa->schoolClass?->name ?? '—' }}</p>
                        </div>
                        @if($siswa->birth_date)
                        <div class="col-span-2">
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide font-semibold">Tgl. Lahir</p>
                            <p class="text-xs font-semibold text-gray-700">{{ $siswa->birth_date->isoFormat('D MMMM Y') }}</p>
                        </div>
                        @endif
                        <div class="col-span-2">
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide font-semibold">Tahun Ajaran</p>
                            <p class="text-xs font-semibold text-gray-700">2025 / 2026</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer Kartu --}}
        <div class="bg-blue-50 border-t border-blue-100 px-4 py-2.5 flex items-center justify-between gap-3">
            {{-- QR Code --}}
            <div class="w-12 h-12 bg-white rounded-lg border border-blue-200 overflow-hidden shadow-sm shrink-0 flex items-center justify-center p-0.5">
                {!! $qrSvg !!}
            </div>
            <div class="flex-1 text-center">
                <p class="text-[10px] text-blue-800 font-bold leading-tight">SMA Negeri 1 Gianyar</p>
                <p class="text-[9px] text-blue-500">TA 2025/2026</p>
            </div>
            <span class="bg-blue-700 text-white text-[10px] font-bold px-3 py-1 rounded-full shrink-0 tracking-wide">
                SISWA
            </span>
        </div>

    </div>

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

    {{-- ─── Edit Data yang Bisa Diubah ─────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Edit Data Diri</h3>
        <form method="POST" action="{{ route('siswa.profile.update') }}" class="space-y-3">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">No. HP Siswa</label>
                <input type="text" name="phone" value="{{ old('phone', $siswa->phone) }}"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="08xxxxxxxxxx">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                <input type="text" name="address" value="{{ old('address', $siswa->address) }}"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                Simpan
            </button>
        </form>
    </div>

    {{-- ─── Ganti Password ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Ganti Password</h3>
        <form method="POST" action="{{ route('siswa.profile.password') }}" class="space-y-3">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password Saat Ini</label>
                <input type="password" name="current_password" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password Baru</label>
                <input type="password" name="password" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="w-full py-2.5 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors">
                Perbarui Password
            </button>
        </form>
    </div>

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
