@extends('layouts.siswa')

@section('title', 'Profil')
@section('page-title', 'Profil')

@section('content')
<div class="space-y-4">

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
                        onchange="document.getElementById('photo-form').submit()">
                </form>
            </div>
            <h2 class="text-base font-bold text-gray-800 text-center mt-1">{{ $siswa->name }}</h2>
            <p class="text-xs text-gray-500 text-center">{{ $siswa->schoolClass?->name ?? '—' }}</p>
        </div>

        {{-- Grid Info --}}
        <div class="px-4 pb-4 grid grid-cols-2 gap-2">
            <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">NIS</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $siswa->nis ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Kelas</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $siswa->schoolClass?->name ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl px-3 py-2.5">
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

    {{-- ─── E-Kartu Pelajar (Flip: Depan & Belakang) ─────────────── --}}
    <div x-data="{ flipped: false }" class="select-none">

        {{-- container-type:inline-size → agar cqw merujuk lebar kartu ini --}}
        <div style="position:relative; perspective:1200px; width:100%; aspect-ratio:85.6/54;
                    cursor:pointer; container-type:inline-size;"
             @click="flipped = !flipped">

            <div style="position:absolute; top:0; left:0; right:0; bottom:0;
                        transform-style:preserve-3d; transition:transform .65s cubic-bezier(.4,0,.2,1);"
                 :style="{ transform: flipped ? 'rotateY(180deg)' : 'rotateY(0deg)' }">

                {{-- ══════════ DEPAN ══════════ --}}
                <div style="position:absolute; top:0; left:0; right:0; bottom:0;
                            backface-visibility:hidden; -webkit-backface-visibility:hidden;
                            border-radius:16px; overflow:hidden;
                            box-shadow:0 16px 48px rgba(29,78,216,.4),0 4px 16px rgba(0,0,0,.2);
                            background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 40%,#3b82f6 75%,#4f46e5 100%);
                            display:flex; flex-direction:column; font-family:inherit;">

                    {{-- Dekorasi --}}
                    <div style="position:absolute;top:-35%;right:-8%;width:75%;height:170%;
                                background:rgba(255,255,255,.06);border-radius:50%;pointer-events:none;"></div>
                    <div style="position:absolute;bottom:-40%;left:15%;width:60%;height:140%;
                                background:rgba(99,102,241,.1);border-radius:50%;pointer-events:none;"></div>

                    {{-- Header --}}
                    <div style="flex-shrink:0; padding:4% 4% 2.5%;
                                display:flex; align-items:center; justify-content:space-between;
                                border-bottom:1px solid rgba(255,255,255,.16); background:rgba(0,0,0,.14);">
                        <div style="display:flex;align-items:center;gap:2.5%;">
                            {{-- logo: % width sudah responsif terhadap lebar kartu --}}
                            <div style="width:9%;aspect-ratio:1;border-radius:50%;
                                        background:white;padding:2px;
                                        box-shadow:0 0 0 2px rgba(212,175,55,.75);flex-shrink:0;">
                                <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo"
                                     style="width:100%;height:100%;object-fit:contain;">
                            </div>
                            <div style="line-height:1.2;">
                                {{-- cqw = % lebar kontainer kartu ini → selalu proporsional --}}
                                <p style="font-size:clamp(8px,2.8cqw,999px);font-weight:800;color:white;letter-spacing:.02em;">SMA NEGERI 1 GIANYAR</p>
                                <p style="font-size:clamp(6px,1.9cqw,999px);color:rgba(186,230,253,.85);">Jl. Ratna No.1 · NPSN 50102079</p>
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <p style="font-size:clamp(6px,2.1cqw,999px);font-weight:700;color:rgba(253,224,71,1);letter-spacing:.1em;text-transform:uppercase;">KARTU PELAJAR</p>
                            <p style="font-size:clamp(5px,1.7cqw,999px);color:rgba(255,255,255,.55);">TA 2025/2026</p>
                        </div>
                    </div>

                    {{-- Body: background putih abu ──────────────────── --}}
                    <div style="flex:1; display:flex; align-items:center; padding:3% 4%; min-height:0;
                                background:rgba(243,244,246,.95);">

                        {{-- Foto --}}
                        <div style="flex-shrink:0; margin-right:4%;">
                            {{-- border proporsional 3cqw agar selalu pas dgn ukuran kartu --}}
                            <div style="width:18%; aspect-ratio:3/4;
                                        border-radius:clamp(4px,1.5cqw,999px); overflow:hidden;
                                        border:clamp(2px,0.6cqw,999px) solid #1d4ed8;
                                        box-shadow:0 clamp(2px,0.8cqw,999px) clamp(8px,3cqw,999px) rgba(29,78,216,.35);
                                        background:#dbeafe;">
                                @if($siswa->photo)
                                    <img src="{{ $siswa->photo_url }}"
                                         style="width:100%;height:100%;object-fit:cover;object-position:top;">
                                @else
                                    <div style="width:100%;height:100%;display:flex;flex-direction:column;
                                                align-items:center;justify-content:center;
                                                background:linear-gradient(160deg,#1d4ed8,#4f46e5);">
                                        <svg style="width:35%;height:35%;color:rgba(255,255,255,.55);"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span style="font-size:clamp(10px,4.5cqw,999px);font-weight:700;color:white;margin-top:4%;">{{ $siswa->initials }}</span>
                                    </div>
                                @endif
                            </div>
                            <p style="font-size:clamp(5px,1.5cqw,999px);color:#9ca3af;text-align:center;margin-top:3px;">3 × 4</p>
                        </div>

                        {{-- Info: teks gelap di atas background putih abu --}}
                        <div style="flex:1;min-width:0;display:flex;flex-direction:column;justify-content:center;
                                    border-left:clamp(1px,0.3cqw,999px) solid #d1d5db;padding-left:4%;">
                            <div style="margin-bottom:5%;">
                                <p style="font-size:clamp(6px,1.8cqw,999px);color:#6b7280;
                                           letter-spacing:.1em;text-transform:uppercase;margin-bottom:2px;">Nama Lengkap</p>
                                <p style="font-size:clamp(11px,4.2cqw,999px);font-weight:800;color:#111827;line-height:1.15;
                                           white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $siswa->name }}</p>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:4% 3%;">
                                <div>
                                    <p style="font-size:clamp(6px,1.8cqw,999px);color:#9ca3af;letter-spacing:.08em;text-transform:uppercase;">NIS</p>
                                    <p style="font-size:clamp(9px,3cqw,999px);font-weight:700;color:#1d4ed8;">{{ $siswa->nis ?? '—' }}</p>
                                </div>
                                <div>
                                    <p style="font-size:clamp(6px,1.8cqw,999px);color:#9ca3af;letter-spacing:.08em;text-transform:uppercase;">NISN</p>
                                    <p style="font-size:clamp(9px,3cqw,999px);font-weight:700;color:#1d4ed8;">{{ $siswa->nisn ?? '—' }}</p>
                                </div>
                                <div>
                                    <p style="font-size:clamp(6px,1.8cqw,999px);color:#9ca3af;letter-spacing:.08em;text-transform:uppercase;">Kelas</p>
                                    <p style="font-size:clamp(9px,3cqw,999px);font-weight:700;color:#1d4ed8;">{{ $siswa->schoolClass?->name ?? '—' }}</p>
                                </div>
                                @if($siswa->birth_date)
                                <div>
                                    <p style="font-size:clamp(6px,1.8cqw,999px);color:#9ca3af;letter-spacing:.08em;text-transform:uppercase;">Tgl. Lahir</p>
                                    <p style="font-size:clamp(8px,2.7cqw,999px);font-weight:600;color:#374151;">{{ $siswa->birth_date->isoFormat('D MMM Y') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div style="flex-shrink:0; padding:2% 4%;
                                display:flex; align-items:center; justify-content:space-between;
                                border-top:1px solid rgba(255,255,255,.13); background:rgba(0,0,0,.2);">
                        <p style="font-size:clamp(6px,1.9cqw,999px);color:rgba(186,230,253,.55);">SMA Negeri 1 Gianyar · Bali</p>
                        <div style="display:flex;align-items:center;gap:4%;">
                            <span style="font-size:clamp(5px,1.6cqw,999px);color:rgba(255,255,255,.4);">Klik untuk QR →</span>
                            <div style="background:linear-gradient(135deg,#d97706,#fbbf24);color:white;
                                        font-size:clamp(6px,1.9cqw,999px);font-weight:700;
                                        padding:2% 5%;border-radius:20px;letter-spacing:.1em;
                                        box-shadow:0 2px 8px rgba(217,119,6,.5);">SISWA</div>
                        </div>
                    </div>
                </div>

                {{-- ══════════ BELAKANG ══════════ --}}
                <div style="position:absolute; top:0; left:0; right:0; bottom:0;
                            backface-visibility:hidden; -webkit-backface-visibility:hidden;
                            transform:rotateY(180deg);
                            border-radius:16px; overflow:hidden;
                            box-shadow:0 16px 48px rgba(29,78,216,.4),0 4px 16px rgba(0,0,0,.2);
                            background:linear-gradient(150deg,#0f172a 0%,#1e293b 55%,#1e3a8a 100%);
                            display:flex; flex-direction:column; font-family:inherit;">

                    {{-- Dekorasi --}}
                    <div style="position:absolute;top:-20%;left:-10%;width:55%;height:75%;
                                background:rgba(59,130,246,.07);border-radius:50%;pointer-events:none;"></div>
                    <div style="position:absolute;bottom:-20%;right:-10%;width:50%;height:70%;
                                background:rgba(99,102,241,.09);border-radius:50%;pointer-events:none;"></div>

                    {{-- Header belakang --}}
                    <div style="flex-shrink:0;padding:3.5% 4%;
                                display:flex;align-items:center;gap:3%;
                                border-bottom:1px solid rgba(255,255,255,.1); background:rgba(0,0,0,.2);">
                        <div style="width:7%;aspect-ratio:1;border-radius:50%;
                                    background:white;padding:2px;flex-shrink:0;">
                            <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo"
                                 style="width:100%;height:100%;object-fit:contain;">
                        </div>
                        <div>
                            <p style="font-size:clamp(8px,2.5cqw,999px);font-weight:700;color:white;">SMA NEGERI 1 GIANYAR</p>
                            <p style="font-size:clamp(6px,1.7cqw,999px);color:rgba(148,163,184,.7);">NPSN 50102079 · Gianyar, Bali</p>
                        </div>
                    </div>

                    {{-- QR di tengah — 32% agar tidak melebihi tinggi body --}}
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1% 6%;min-height:0;overflow:hidden;">
                        <div style="width:32%; aspect-ratio:1;
                                    background:white; border-radius:clamp(5px,1.5cqw,999px); padding:clamp(3px,1cqw,999px);
                                    box-shadow:0 4px 20px rgba(0,0,0,.5),0 0 0 2px rgba(253,224,71,.3);">
                            <img src="{{ $qrSvg }}" alt="QR Code" style="width:100%;height:100%;display:block;">
                        </div>
                        <p style="font-size:clamp(6px,1.9cqw,999px);color:rgba(148,163,184,.7);margin-top:3%;
                                   letter-spacing:.04em;text-align:center;">
                            Scan untuk melihat biodata siswa
                        </p>
                    </div>

                    {{-- Footer belakang --}}
                    <div style="flex-shrink:0;padding:2.5% 4%;
                                border-top:1px solid rgba(255,255,255,.1); background:rgba(0,0,0,.25);
                                display:flex;align-items:center;justify-content:space-between;">
                        <div style="min-width:0;">
                            <p style="font-size:clamp(8px,3cqw,999px);font-weight:700;color:white;
                                       white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $siswa->name }}</p>
                            <p style="font-size:clamp(6px,1.9cqw,999px);color:rgba(148,163,184,.65);">
                                NIS: {{ $siswa->nis ?? '—' }}{{ $siswa->nisn ? ' · NISN: '.$siswa->nisn : '' }}
                            </p>
                        </div>
                        <div style="background:linear-gradient(135deg,#d97706,#fbbf24);color:white;
                                    font-size:clamp(6px,1.9cqw,999px);font-weight:700;
                                    padding:2.5% 6%;border-radius:20px;letter-spacing:.1em;flex-shrink:0;
                                    box-shadow:0 2px 8px rgba(217,119,6,.5);">SISWA</div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Hint teks --}}
        <p style="text-align:center;font-size:11px;color:#9ca3af;margin-top:8px;">
            <span x-show="!flipped">Klik kartu untuk melihat QR Code &rarr;</span>
            <span x-show="flipped">&larr; Klik kartu untuk kembali ke depan</span>
        </p>
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

    {{-- ─── Edit Data ───────────────────────────────────────────────── --}}
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

    {{-- ─── Ganti Password ──────────────────────────────────────────── --}}
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
