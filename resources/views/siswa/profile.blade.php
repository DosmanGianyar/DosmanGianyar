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
                @php
                    $principalName = '';
                    $principalNip  = '';
                @endphp
                <div style="position:absolute; top:0; left:0; right:0; bottom:0;
                            backface-visibility:hidden; -webkit-backface-visibility:hidden;
                            border-radius:16px; overflow:hidden;
                            box-shadow:0 8px 32px rgba(0,0,0,.22);
                            background:white; display:flex; flex-direction:column; font-family:inherit;">

                    {{-- Header biru ─────────────────────────────────────────── --}}
                    <div style="flex-shrink:0;background:linear-gradient(180deg,#0d47a1 0%,#1565c0 100%);
                                padding:2.5% 3%;display:flex;align-items:center;gap:3%;">
                        {{-- Logo "keluar" dari lingkaran: overflow visible + scale --}}
                        <div style="width:14%;aspect-ratio:1;border-radius:50%;background:white;
                                    flex-shrink:0;overflow:visible;position:relative;
                                    box-shadow:0 4px 18px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.3);">
                            <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo"
                                 style="width:130%;height:130%;object-fit:contain;
                                        position:absolute;top:50%;left:50%;
                                        transform:translate(-50%,-50%);
                                        filter:drop-shadow(0 2px 4px rgba(0,0,0,.25));">
                        </div>
                        <div style="line-height:1.2;min-width:0;">
                            <p style="font-size:clamp(9px,3.6cqw,999px);font-weight:700;color:white;
                                       letter-spacing:.06em;line-height:1.1;text-transform:uppercase;
                                       font-family:'Oswald',Arial Narrow,Arial,sans-serif;">
                                SMA Negeri 1 Gianyar
                            </p>
                            <p style="font-size:clamp(4px,1.4cqw,999px);color:rgba(255,255,255,.75);margin-top:2px;">
                                Jl. Ratna No.1, Gianyar, Bali · Telp. (0361) 943443
                            </p>
                        </div>
                    </div>

                    {{-- Body putih ──────────────────────────────────────────── --}}
                    <div style="flex:1;background:#f9f8f5;display:flex;min-height:0;overflow:hidden;position:relative;">

                        {{-- Watermark logo sekolah --}}
                        <div style="position:absolute;right:3%;top:50%;transform:translateY(-50%);
                                    width:40%;aspect-ratio:1;opacity:.05;pointer-events:none;">
                            <img src="{{ asset('img/logo_sekolah.png') }}"
                                 style="width:100%;height:100%;object-fit:contain;">
                        </div>

                        {{-- Foto ───────────────────────────────────────────── --}}
                        <div style="flex-shrink:0;padding:7% 0 2% 3%;display:flex;align-items:flex-start;">
                            <div style="width:17cqw;aspect-ratio:3/4;
                                        border:clamp(2px,0.65cqw,999px) solid #dc2626;
                                        background:#fee2e2;overflow:hidden;
                                        box-shadow:0 clamp(1px,0.4cqw,999px) clamp(4px,1.5cqw,999px) rgba(220,38,38,.2);">
                                @if($siswa->photo)
                                    <img src="{{ $siswa->photo_url }}"
                                         style="width:100%;height:100%;object-fit:cover;object-position:top;">
                                @else
                                    <div style="width:100%;height:100%;background:#e9eaec;
                                                display:flex;align-items:center;justify-content:center;">
                                        <svg viewBox="0 0 24 30" fill="none" xmlns="http://www.w3.org/2000/svg"
                                             style="width:60%;height:60%;color:#b0b5bc;">
                                            <ellipse cx="12" cy="8.5" rx="5.5" ry="6" fill="currentColor"/>
                                            <path d="M1 28c0-6.075 4.925-11 11-11s11 4.925 11 11"
                                                  stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Info ───────────────────────────────────────────── --}}
                        <div style="flex:1;padding:2% 3% 2% 2%;display:flex;flex-direction:column;
                                    min-width:0;position:relative;z-index:1;">

                            {{-- Judul --}}
                            <p style="font-size:clamp(7px,2.7cqw,999px);font-weight:900;text-align:center;
                                       text-decoration:underline;letter-spacing:.12em;color:#0d47a1;
                                       margin-bottom:1.8%;text-transform:uppercase;
                                       text-underline-offset:clamp(1px,0.4cqw,999px);">
                                KARTU PELAJAR
                            </p>

                            {{-- Baris data --}}
                            @php
                            $infoRows = [
                                ['NISN',          $siswa->nisn ?? '—'],
                                ['Nama',          $siswa->name],
                                ['NIS',           $siswa->nis  ?? '—'],
                                ['Kelas',         $siswa->schoolClass?->name ?? '—'],
                                ['Tgl. Lahir',    $siswa->birth_date?->isoFormat('D MMMM Y') ?? '—'],
                                ['Jenis Kelamin', match($siswa->gender ?? '') {
                                    'L' => 'Laki-laki', 'P' => 'Perempuan', default => '—'
                                }],
                            ];
                            @endphp
                            @foreach($infoRows as $ri => [$lbl, $val])
                            <div style="display:flex;align-items:baseline;gap:1%;
                                        margin-bottom:{{ $ri === 1 ? '1.8' : '1.4' }}%;line-height:1.2;">
                                <span style="font-size:clamp(5px,1.85cqw,999px);color:#4b5563;
                                              flex-shrink:0;width:28%;">{{ $lbl }}</span>
                                <span style="font-size:clamp(5px,1.85cqw,999px);color:#4b5563;flex-shrink:0;">:</span>
                                <span style="font-size:clamp(5px,{{ $ri === 1 ? '2.1' : '1.85' }}cqw,999px);
                                              font-weight:{{ $ri === 1 ? 700 : 600 }};
                                              color:{{ $ri === 1 ? '#111827' : '#1f2937' }};
                                              overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;">
                                    {{ $val }}
                                </span>
                            </div>
                            @endforeach

                            {{-- Tanda tangan --}}
                            <div style="margin-top:auto;display:flex;justify-content:space-between;align-items:flex-end;">
                                <p style="font-size:clamp(4px,1.3cqw,999px);color:#9ca3af;font-style:italic;
                                           max-width:48%;line-height:1.4;">
                                    Kartu ini berlaku selama menjadi siswa SMA Negeri 1 Gianyar
                                </p>
                                <div style="text-align:center;flex-shrink:0;margin-right:8%;">
                                    <p style="font-size:clamp(7px,2.4cqw,999px);color:#374151;margin-bottom:0.3%;">
                                        Kepala Sekolah,
                                    </p>
                                    <div style="height:4cqw;"></div>
                                    <div style="display:inline-block;min-width:16cqw;
                                                border-top:clamp(0.5px,0.2cqw,999px) solid #374151;padding-top:0.5%;">
                                        @if($principalName)
                                        <p style="font-size:clamp(7px,2.4cqw,999px);font-weight:700;color:#111827;">
                                            {{ $principalName }}
                                        </p>
                                        @else
                                        <p style="font-size:clamp(7px,2.4cqw,999px);color:#c0c0c0;letter-spacing:.08em;">
                                            .......................
                                        </p>
                                        @endif
                                        @if($principalNip)
                                        <p style="font-size:clamp(6px,2cqw,999px);color:#6b7280;">
                                            NIP. {{ $principalNip }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Strip bawah biru --}}
                    <div style="flex-shrink:0;height:3.5%;background:#1565c0;"></div>
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
