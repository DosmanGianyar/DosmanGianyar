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
                        onchange="this.form.submit()">
                </form>
            </div>
            @error('photo')
            <p class="text-xs text-red-500 text-center mt-1">{{ $message }}</p>
            @enderror
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

        <div style="position:relative; perspective:1400px; width:100%; aspect-ratio:85.6/54;
                    cursor:pointer; container-type:inline-size;"
             @click="flipped = !flipped">

            <div style="position:absolute; top:0; left:0; right:0; bottom:0;
                        transform-style:preserve-3d; transition:transform .7s cubic-bezier(.4,0,.2,1);"
                 :style="{ transform: flipped ? 'rotateY(180deg)' : 'rotateY(0deg)' }">

                {{-- ══════════ DEPAN ══════════ --}}
                <div style="position:absolute; top:0; left:0; right:0; bottom:0;
                            backface-visibility:hidden; -webkit-backface-visibility:hidden;
                            border-radius:14px; overflow:hidden;
                            box-shadow:0 8px 32px rgba(0,0,0,.22);
                            background:#f8f7f4; display:flex; flex-direction:column; font-family:inherit;">

                    {{-- ── Header biru ─── --}}
                    <div style="flex-shrink:0; background:linear-gradient(135deg,#0a3880 0%,#1565c0 60%,#1976d2 100%);
                                padding:2.5% 3%; display:flex; align-items:center; gap:2.5%;">
                        <div style="width:12%; aspect-ratio:1; border-radius:50%; background:white;
                                    flex-shrink:0; overflow:visible; position:relative;
                                    box-shadow:0 3px 14px rgba(0,0,0,.4);">
                            <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo"
                                 style="width:130%; height:130%; object-fit:contain;
                                        position:absolute; top:50%; left:50%;
                                        transform:translate(-50%,-50%);
                                        filter:drop-shadow(0 1px 3px rgba(0,0,0,.3));">
                        </div>
                        <div style="flex:1; min-width:0; line-height:1.25;">
                            <p style="font-size:clamp(7px,3cqw,999px); font-weight:800; color:white;
                                       letter-spacing:.05em; text-transform:uppercase;
                                       font-family:'Oswald',Arial Narrow,Arial,sans-serif; line-height:1.1;">
                                SMA Negeri 1 Gianyar
                            </p>
                            <p style="font-size:clamp(3.5px,1.35cqw,999px); color:rgba(255,255,255,.72); margin-top:1.5%;">
                                Jl. Ratna No.1, Gianyar, Bali 80511 &nbsp;·&nbsp; Telp. (0361) 943443
                            </p>
                        </div>
                        {{-- Badge KARTU PELAJAR --}}
                        <div style="flex-shrink:0; border:clamp(1px,.35cqw,999px) solid rgba(255,255,255,.4);
                                    border-radius:5px; padding:1.8% 2.5%; text-align:center;
                                    background:rgba(255,255,255,.12);">
                            <p style="font-size:clamp(4px,1.7cqw,999px); font-weight:800; color:white;
                                       letter-spacing:.12em; text-transform:uppercase; line-height:1.3;">
                                KARTU<br>PELAJAR
                            </p>
                        </div>
                    </div>

                    {{-- ── Accent strip emas ─── --}}
                    <div style="flex-shrink:0; height:.9%;
                                background:linear-gradient(90deg,#b45309 0%,#f59e0b 40%,#fbbf24 55%,#f59e0b 70%,#b45309 100%);"></div>

                    {{-- ── Body ─── --}}
                    <div style="flex:1; display:flex; min-height:0; overflow:hidden; position:relative; padding:3.5% 3% 2.5% 3%;">

                        {{-- Watermark --}}
                        <div style="position:absolute; right:2%; top:50%; transform:translateY(-50%);
                                    width:36%; aspect-ratio:1; opacity:.04; pointer-events:none;">
                            <img src="{{ asset('img/logo_sekolah.png') }}" style="width:100%; height:100%; object-fit:contain;">
                        </div>

                        {{-- Foto --}}
                        <div style="flex-shrink:0; display:flex; align-items:flex-start; margin-right:3%;">
                            <div style="width:16cqw; aspect-ratio:3/4;
                                        border:clamp(2px,.65cqw,999px) solid #1565c0;
                                        background:#dce8f8; overflow:hidden;
                                        box-shadow:0 clamp(2px,.5cqw,999px) clamp(8px,2cqw,999px) rgba(21,101,192,.25);">
                                @if($siswa->photo)
                                    <img src="{{ $siswa->photo_url }}"
                                         style="width:100%; height:100%; object-fit:cover; object-position:top;">
                                @else
                                    <div style="width:100%; height:100%; background:#dce8f8;
                                                display:flex; flex-direction:column; align-items:center; justify-content:flex-end;">
                                        <svg viewBox="0 0 24 30" fill="none" xmlns="http://www.w3.org/2000/svg"
                                             style="width:72%; color:#6fa3d8;">
                                            <ellipse cx="12" cy="9" rx="6" ry="6.5" fill="currentColor"/>
                                            <path d="M0 29c0-6.627 5.373-12 12-12s12 5.373 12 12"
                                                  stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Data --}}
                        <div style="flex:1; min-width:0; display:flex; flex-direction:column; position:relative; z-index:1;">

                            {{-- Sub-judul --}}
                            <p style="font-size:clamp(5.5px,2.2cqw,999px); font-weight:900; color:#0a3880;
                                       letter-spacing:.16em; text-align:center; text-transform:uppercase;
                                       text-decoration:underline; text-underline-offset:clamp(1px,.4cqw,999px);
                                       margin-bottom:2%;">
                                KARTU PELAJAR
                            </p>

                            {{-- Baris data dengan ikon --}}
                            @php
                            $dataRows = [
                                [
                                    'icon' => 'M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v2h20v-2c0-3.3-6.7-5-10-5z',
                                    'label' => 'Nama',
                                    'value' => $siswa->name,
                                    'bold'  => true,
                                ],
                                [
                                    'icon' => 'M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V6h16v12zM6 10h12v2H6zm0 4h8v2H6z',
                                    'label' => 'NIS / NISN',
                                    'value' => ($siswa->nis ?? '—') . ' / ' . ($siswa->nisn ?? '—'),
                                    'bold'  => false,
                                ],
                                [
                                    'icon' => 'M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z',
                                    'label' => 'Tgl. Lahir',
                                    'value' => $siswa->birth_date?->isoFormat('D MMMM Y') ?? '—',
                                    'bold'  => false,
                                ],
                                [
                                    'icon' => 'M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z',
                                    'label' => 'Kelas',
                                    'value' => $siswa->schoolClass?->name ?? '—',
                                    'bold'  => false,
                                ],
                                [
                                    'icon' => 'M11 6C9.34 6 8 7.34 8 9s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3zm0 2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm6 9.59C17 15.09 14.03 14 11 14s-6 1.09-6 2.59V18h12v-1.41zM7.2 16c.62-.56 2.04-1 3.8-1s3.18.44 3.8 1H7.2zM20 3H4v14h2V5h14v12h2V3z',
                                    'label' => 'Jenis Kelamin',
                                    'value' => match($siswa->gender ?? '') { 'L' => 'Laki-laki', 'P' => 'Perempuan', default => '—' },
                                    'bold'  => false,
                                ],
                                [
                                    'icon' => 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
                                    'label' => 'Alamat',
                                    'value' => $siswa->address ?? '—',
                                    'bold'  => false,
                                ],
                            ];
                            @endphp

                            @foreach($dataRows as $row)
                            <div style="display:flex; align-items:flex-start; gap:1.5%; margin-bottom:1.4%; line-height:1.25;">
                                <svg viewBox="0 0 24 24" fill="#1565c0"
                                     style="width:clamp(6px,1.9cqw,999px); height:clamp(6px,1.9cqw,999px);
                                            flex-shrink:0; margin-top:.15em; opacity:.7;">
                                    <path d="{{ $row['icon'] }}"/>
                                </svg>
                                <span style="font-size:clamp(4px,1.5cqw,999px); color:#6b7280;
                                              flex-shrink:0; width:26%;">{{ $row['label'] }}</span>
                                <span style="font-size:clamp(4px,1.5cqw,999px); color:#9ca3af; flex-shrink:0;">:</span>
                                <span style="font-size:clamp(4px,{{ $row['bold'] ? '1.8' : '1.5' }}cqw,999px);
                                              font-weight:{{ $row['bold'] ? 700 : 600 }};
                                              color:{{ $row['bold'] ? '#111827' : '#374151' }};
                                              overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0;">
                                    {{ $row['value'] }}
                                </span>
                            </div>
                            @endforeach

                            {{-- Footer: berlaku + tanda tangan --}}
                            <div style="margin-top:auto; display:flex; justify-content:space-between; align-items:flex-end;">
                                <p style="font-size:clamp(3.5px,1.25cqw,999px); color:#9ca3af; font-style:italic;
                                           max-width:42%; line-height:1.5;">
                                    Berlaku selama menjadi<br>siswa SMAN 1 Gianyar
                                </p>
                                <div style="text-align:center; flex-shrink:0;">
                                    <p style="font-size:clamp(3.5px,1.35cqw,999px); color:#4b5563; margin-bottom:.8%;">
                                        Gianyar, {{ now()->isoFormat('D MMMM Y') }}
                                    </p>
                                    <p style="font-size:clamp(3.5px,1.35cqw,999px); color:#4b5563; margin-bottom:3.5%;">
                                        Kepala Sekolah,
                                    </p>
                                    <div style="height:3.2cqw; position:relative;">
                                        {{-- Lingkaran stempel dekoratif --}}
                                        <div style="position:absolute; left:50%; top:50%; transform:translate(-55%,-50%);
                                                    width:7cqw; aspect-ratio:1; border-radius:50%;
                                                    border:clamp(1px,.35cqw,999px) solid rgba(13,71,161,.2);
                                                    pointer-events:none;"></div>
                                    </div>
                                    <div style="display:inline-block; min-width:15cqw;
                                                border-top:clamp(.5px,.18cqw,999px) solid #374151; padding-top:.5%;">
                                        <p style="font-size:clamp(3.5px,1.35cqw,999px); color:#c0c0c0; letter-spacing:.04em;">
                                            ..............................
                                        </p>
                                        <p style="font-size:clamp(3px,1.15cqw,999px); color:#9ca3af;">
                                            NIP. ———————————
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Strip bawah biru ─── --}}
                    <div style="flex-shrink:0; height:3.8%;
                                background:linear-gradient(90deg,#0a3880 0%,#1565c0 50%,#1976d2 100%);"></div>
                </div>

                {{-- ══════════ BELAKANG ══════════ --}}
                <div style="position:absolute; top:0; left:0; right:0; bottom:0;
                            backface-visibility:hidden; -webkit-backface-visibility:hidden;
                            transform:rotateY(180deg);
                            border-radius:14px; overflow:hidden;
                            box-shadow:0 8px 32px rgba(0,0,0,.22);
                            background:white; display:flex; flex-direction:column; font-family:inherit;">

                    {{-- ── Strip atas biru ─── --}}
                    <div style="flex-shrink:0; height:5%;
                                background:linear-gradient(90deg,#0a3880 0%,#1565c0 50%,#1976d2 100%);
                                display:flex; align-items:center; padding:0 3%; gap:2%;">
                        <div style="width:3.5%; aspect-ratio:1; border-radius:50%; background:white; flex-shrink:0; overflow:hidden;">
                            <img src="{{ asset('img/logo_sekolah.png') }}" style="width:100%; height:100%; object-fit:contain;">
                        </div>
                        <p style="font-size:clamp(5px,1.8cqw,999px); font-weight:700; color:white; letter-spacing:.06em; text-transform:uppercase;">
                            SMA NEGERI 1 GIANYAR
                        </p>
                        <p style="font-size:clamp(3.5px,1.2cqw,999px); color:rgba(255,255,255,.65); margin-left:auto;">
                            NPSN 50102079
                        </p>
                    </div>

                    {{-- ── Body tengah ─── --}}
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center;
                                justify-content:center; padding:2% 5%; gap:0; overflow:hidden;">

                        {{-- QR code --}}
                        <div style="width:34%; aspect-ratio:1;
                                    border:clamp(1.5px,.5cqw,999px) solid #e5e7eb;
                                    border-radius:clamp(5px,1.5cqw,999px); padding:clamp(3px,1cqw,999px);
                                    box-shadow:0 2px 12px rgba(0,0,0,.1);">
                            <img src="{{ $qrSvg }}" alt="QR Code" style="width:100%; height:100%; display:block;">
                        </div>

                        <p style="font-size:clamp(4.5px,1.6cqw,999px); color:#9ca3af; margin-top:2%;
                                   letter-spacing:.03em; text-align:center;">
                            Scan untuk verifikasi identitas siswa
                        </p>

                        {{-- Divider --}}
                        <div style="width:55%; height:1px;
                                    background:linear-gradient(90deg,transparent,#e5e7eb,transparent);
                                    margin:2% 0;"></div>

                        {{-- Nama + NIS --}}
                        <p style="font-size:clamp(6.5px,2.6cqw,999px); font-weight:700; color:#111827;
                                   text-align:center; max-width:90%; overflow:hidden;
                                   text-overflow:ellipsis; white-space:nowrap;">
                            {{ $siswa->name }}
                        </p>
                        <p style="font-size:clamp(5px,1.7cqw,999px); color:#6b7280; text-align:center; margin-top:.5%;">
                            NIS: {{ $siswa->nis ?? '—' }}
                            @if($siswa->nisn)&nbsp;·&nbsp; NISN: {{ $siswa->nisn }}@endif
                        </p>
                        <p style="font-size:clamp(4px,1.4cqw,999px); color:#9ca3af; text-align:center; margin-top:.3%;">
                            {{ $siswa->schoolClass?->name ?? '' }}
                            @if($siswa->schoolClass?->name && ($siswa->gender ?? '')) &nbsp;·&nbsp; @endif
                            {{ match($siswa->gender ?? '') { 'L' => 'Laki-laki', 'P' => 'Perempuan', default => '' } }}
                        </p>
                    </div>

                    {{-- ── Strip bawah emas ─── --}}
                    <div style="flex-shrink:0; height:3.8%;
                                background:linear-gradient(90deg,#b45309 0%,#f59e0b 40%,#fbbf24 55%,#f59e0b 70%,#b45309 100%);
                                display:flex; align-items:center; justify-content:center;">
                        <p style="font-size:clamp(4px,1.5cqw,999px); font-weight:700; color:white;
                                   letter-spacing:.25em; text-transform:uppercase; opacity:.9;">SISWA</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- Hint teks --}}
        <p style="text-align:center; font-size:11px; color:#9ca3af; margin-top:8px;">
            <span x-show="!flipped">Ketuk kartu untuk melihat QR Code &rarr;</span>
            <span x-show="flipped">&larr; Ketuk kartu untuk kembali ke depan</span>
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
