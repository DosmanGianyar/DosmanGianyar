@extends('layouts.siswa')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
@endpush

@section('title', 'Profil & Kartu Pelajar')
@section('page-title', 'Profil & Kartu Pelajar')

@section('content')
<div class="space-y-5 max-w-4xl mx-auto pb-10">

    {{-- Alert Wajib Ganti Password --}}
    @if(auth()->user()->must_change_password)
    <div class="relative overflow-hidden bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-2xl p-4 shadow-lg shadow-amber-500/20 flex items-start gap-3.5 animate-pulse">
        <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 text-lg">
            🔐
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-black tracking-wide uppercase">Wajib Ganti Password Akun</h4>
            <p class="text-xs text-amber-100 mt-0.5 leading-relaxed">
                Demi keamanan data Anda, silakan perbarui password default akun Anda saat ini.
            </p>
            <button type="button" onclick="switchProfileTab('keamanan')" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white text-amber-900 text-xs font-bold shadow-sm hover:bg-amber-50 transition-all cursor-pointer">
                <span>Ganti Password Sekarang</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </div>
    @endif

    {{-- Feedback Messages --}}
    @if(session('success'))
    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-bold flex items-center gap-2.5 shadow-xs">
        <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs shrink-0">✓</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-900 text-xs font-bold flex items-center gap-2.5 shadow-xs">
        <span class="w-6 h-6 rounded-lg bg-rose-500 text-white flex items-center justify-center text-xs shrink-0">✕</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-900 text-xs space-y-1 shadow-xs">
        <p class="font-bold flex items-center gap-1.5 text-rose-800">
            <span>⚠️ Terdapat kendala pada data yang diisi:</span>
        </p>
        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-700 font-medium pl-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── KARTU PELAJAR DIGITAL (SMAN 1 GIANYAR DIGITAL STUDENT ID) ────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl border border-blue-900/40 text-white select-none transition-all duration-300"
         style="background: linear-gradient(135deg, #09132c 0%, #0f2452 45%, #183b7a 80%, #0c1a3b 100%);">
        
        {{-- Background decorative patterns --}}
        <div class="absolute inset-0 pointer-events-none opacity-[0.06]"
             style="background-image: radial-gradient(#ffffff 1px, transparent 1px), radial-gradient(#ffffff 1px, #09132c 1px);
                    background-size: 24px 24px; background-position: 0 0, 12px 12px;"></div>
        
        {{-- Ambient Gold & Cyan Glow --}}
        <div class="absolute -top-16 -right-16 w-56 h-56 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-56 h-56 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Gold Hairline Top Accent --}}
        <div class="h-1.5 w-full bg-gradient-to-r from-amber-400 via-amber-200 to-amber-500"></div>

        <div class="p-5 sm:p-7 relative z-10 flex flex-col justify-between min-h-[290px] gap-5">
            {{-- Header Kartu Pelajar --}}
            <div class="flex items-start justify-between gap-4 border-b border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md p-1.5 border border-white/20 shadow-inner flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="max-w-full max-h-full object-contain filter drop-shadow" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <span class="hidden text-xl">🏛️</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] tracking-[0.2em] font-extrabold uppercase text-amber-300">KARTU PELAJAR DIGITAL</span>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[9px] font-black">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                AKTIF
                            </span>
                        </div>
                        <h2 class="text-base sm:text-lg font-black tracking-wider text-white font-['Oswald'] leading-tight">
                            SMA NEGERI 1 GIANYAR
                        </h2>
                        <p class="text-[10px] text-blue-200/80 font-medium tracking-wide">
                            Jl. Ratna No. 1 Gianyar, Bali • Telp: (0361) 943034
                        </p>
                    </div>
                </div>

                {{-- Mini QR Code Button / Drawer Trigger --}}
                @if(isset($qrSvg))
                <div class="flex flex-col items-center shrink-0 bg-white p-1.5 rounded-xl shadow-lg border border-white/40">
                    <div class="w-14 h-14 [&>svg]:w-full [&>svg]:h-full flex items-center justify-center">
                        {!! $qrSvg !!}
                    </div>
                    <span class="text-[8px] font-extrabold text-gray-700 tracking-tighter uppercase mt-0.5">SCAN VERIFIKASI</span>
                </div>
                @endif
            </div>

            {{-- Body Kartu Pelajar: Avatar + Info Utama --}}
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-6 py-1">
                {{-- Foto Profil dengan Frame Estetik --}}
                <div class="relative shrink-0 group">
                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border-2 border-amber-300/80 shadow-2xl bg-slate-800">
                        @if($siswa->photo)
                            <img src="{{ $siswa->photo_url }}" alt="{{ $siswa->name }}"
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white text-3xl font-extrabold"
                                 style="background: linear-gradient(135deg, #1d4ed8 0%, #4338ca 100%);">
                                {{ $siswa->initials }}
                            </div>
                        @endif
                    </div>

                    {{-- Tombol Ganti / Crop Foto --}}
                    <label for="photo-input"
                           class="absolute -bottom-2 -right-2 w-8 h-8 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950
                                  flex items-center justify-center cursor-pointer shadow-lg shadow-amber-500/40 hover:scale-110 active:scale-95 transition-all"
                           title="Ganti Pasfoto Resmi">
                        <svg class="w-4 h-4 text-slate-950 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>

                    {{-- Form Upload Foto Hidden --}}
                    <form id="photo-form" method="POST" action="{{ route('siswa.profile.photo') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp,image/heic">
                    </form>
                </div>

                {{-- Detail Identitas Siswa --}}
                <div class="flex-1 text-center sm:text-left space-y-2">
                    <div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-white tracking-wide uppercase leading-tight font-['Plus_Jakarta_Sans']">
                            {{ $siswa->name }}
                        </h3>
                        <p class="text-xs text-blue-200 font-medium mt-0.5">
                            {{ $siswa->email ?? 'Siswa Terdaftar SMAN 1 Gianyar' }}
                        </p>
                    </div>

                    {{-- Badge Grid Data --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-blue-200/80 font-bold">NIS / NISN</span>
                            <span class="text-xs sm:text-sm font-black text-amber-300 font-mono tracking-tight">{{ $siswa->nis ?? '—' }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-blue-200/80 font-bold">KELAS</span>
                            <span class="text-xs sm:text-sm font-black text-white tracking-tight">{{ $siswa->schoolClass?->name ?? '—' }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-blue-200/80 font-bold">ANGKATAN</span>
                            <span class="text-xs sm:text-sm font-black text-amber-200 tracking-tight">{{ $siswa->angkatan ?? '—' }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-blue-200/80 font-bold">TGL LAHIR</span>
                            <span class="text-xs sm:text-sm font-bold text-white tracking-tight">
                                {{ $siswa->birth_date ? $siswa->birth_date->isoFormat('D MMM Y') : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Kartu Pelajar --}}
            <div class="pt-3 border-t border-white/10 flex flex-wrap items-center justify-between text-[10px] text-blue-200/70 gap-2">
                <span class="flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
                    Terverifikasi Sistem Informasi Manajemen Sekolah (SIMS)
                </span>
                <span class="font-mono text-[9px] text-white/50">ID: DOSMAN-{{ str_pad($siswa->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    </div>

    {{-- ─── Ketentuan Pasfoto Resmi Sekolah (Banner Informatif) ──────── --}}
    <div class="bg-gradient-to-r from-rose-50 via-red-50 to-amber-50 border border-rose-200/80 rounded-2xl p-4 shadow-xs flex items-start gap-3.5">
        <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-sm text-sm font-bold">
            📷
        </div>
        <div class="flex-1 text-xs text-rose-950 space-y-1">
            <div class="flex items-center justify-between">
                <h4 class="font-extrabold text-rose-900 tracking-wide uppercase text-[11px] flex items-center gap-1.5">
                    <span>Ketentuan Pasfoto Resmi Siswa</span>
                    <span class="text-[10px] px-2 py-0.5 bg-rose-200/70 text-rose-800 rounded-full font-bold">Wajib</span>
                </h4>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5 pt-1 text-[11px] font-semibold text-rose-900">
                <div class="flex items-center gap-1.5">
                    <span class="text-rose-600">✓</span>
                    <span>Latar Belakang (Background) <strong>Merah</strong></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-rose-600">✓</span>
                    <span>Diutamakan <strong>Batik SMAN 1 Gianyar</strong></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-rose-600">✓</span>
                    <span>Pose Tegak & Wajah Tampak Jelas</span>
                </div>
            </div>
            <p class="text-[10px] text-rose-700 font-medium pt-1">
                *Klik ikon kamera pada pasfoto di atas untuk mengunggah dan memotong posisi pasfoto secara presisi.
            </p>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── SEGMENTED NAVIGATION TABS (Mobile First, Ergonomis, Cepat) ───────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="bg-gray-100/80 p-1.5 rounded-2xl flex items-center gap-1 border border-gray-200/80 backdrop-blur-sm">
        <button type="button" onclick="switchProfileTab('ringkasan')" id="tab-btn-ringkasan"
                class="flex-1 py-2.5 px-3 rounded-xl text-xs font-extrabold transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer bg-white text-blue-900 shadow-sm">
            <span>🪪</span>
            <span class="hidden sm:inline">Ringkasan & Kontak</span>
            <span class="sm:hidden">Ringkasan</span>
        </button>

        <button type="button" onclick="switchProfileTab('biodata')" id="tab-btn-biodata"
                class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer text-gray-600 hover:text-gray-900">
            <span>📝</span>
            <span class="hidden sm:inline">Kelengkapan Biodata</span>
            <span class="sm:hidden">Biodata</span>
            @if(!$canEditProfile)
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            @endif
        </button>

        <button type="button" onclick="switchProfileTab('keamanan')" id="tab-btn-keamanan"
                class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer text-gray-600 hover:text-gray-900">
            <span>🔐</span>
            <span class="hidden sm:inline">Keamanan & Password</span>
            <span class="sm:hidden">Keamanan</span>
            @if(auth()->user()->must_change_password)
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            @endif
        </button>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── PANEL 1: RINGKASAN & KONTAK CEPAT ────────────────────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div id="panel-ringkasan" class="space-y-4 tab-panel transition-opacity duration-200">
        {{-- Data Orang Tua / Wali --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-gray-800">Informasi Orang Tua / Wali</h3>
                        <p class="text-[11px] text-gray-400">Kontak utama wali siswa yang terhubung dengan sistem</p>
                    </div>
                </div>
                <button type="button" onclick="switchProfileTab('biodata')" class="text-xs text-blue-600 font-bold hover:underline cursor-pointer flex items-center gap-1">
                    <span>Edit Data</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="bg-gray-50/80 p-3.5 rounded-xl border border-gray-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100/60 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Nama Orang Tua / Wali</p>
                        <p class="text-sm font-bold text-gray-800 truncate mt-0.5">{{ $siswa->parent_name ?? ($siswa->father_name ?? ($siswa->mother_name ?? 'Belum diisi')) }}</p>
                    </div>
                </div>

                <div class="bg-gray-50/80 p-3.5 rounded-xl border border-gray-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100/60 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">No. WhatsApp Orang Tua</p>
                        <p class="text-sm font-bold text-gray-800 truncate mt-0.5 font-mono">{{ $siswa->parent_phone ?? ($siswa->father_phone ?? ($siswa->mother_phone ?? '—')) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kontak Siswa & Domisili Cepat --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-extrabold text-gray-800 mb-3 pb-2 border-b border-gray-100 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>Kontak Pribadi & Tempat Tinggal</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                    <span class="block text-[10px] text-gray-400 font-semibold uppercase tracking-wider">No. WhatsApp Siswa</span>
                    <span class="text-xs font-bold text-gray-800 font-mono mt-0.5 block">{{ $siswa->phone ?? '—' }}</span>
                </div>
                <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                    <span class="block text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Alamat E-mail</span>
                    <span class="text-xs font-bold text-gray-800 truncate mt-0.5 block">{{ $siswa->email ?? '—' }}</span>
                </div>
                <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                    <span class="block text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Golongan Darah</span>
                    <span class="text-xs font-extrabold text-rose-600 mt-0.5 block">{{ $siswa->blood_type ? 'Tipe ' . $siswa->blood_type : 'Belum diisi' }}</span>
                </div>
                <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100 sm:col-span-3">
                    <span class="block text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Alamat Domisili</span>
                    <span class="text-xs font-medium text-gray-700 mt-0.5 block">
                        {{ $siswa->address ? $siswa->address . ($siswa->kelurahan ? ', ' . $siswa->kelurahan : '') . ($siswa->kecamatan ? ', ' . $siswa->kecamatan : '') : 'Alamat lengkap belum diisi di biodata.' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Panduan Bantuan Akun --}}
        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 text-xs text-slate-600 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xl">ℹ️</span>
                <span>Butuh perubahan pada data pokok seperti <strong>NIS, Nama Lengkap, atau Kelas</strong>?</span>
            </div>
            <span class="shrink-0 text-[11px] font-bold text-slate-700 bg-white px-3 py-1.5 rounded-xl border border-slate-200">
                Hubungi Tata Usaha / Wali Kelas
            </span>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── PANEL 2: KELENGKAPAN BIODATA SISWA ───────────────────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div id="panel-biodata" class="space-y-4 tab-panel hidden transition-opacity duration-200">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-gray-800">Formulir Kelengkapan Data Profil Siswa</h3>
                        <p class="text-[11px] text-gray-400">Pastikan data biodata dan kontak keluarga Anda terisi secara akurat</p>
                    </div>
                </div>

                @if(!$canEditProfile)
                    <span class="text-[11px] font-extrabold text-amber-800 bg-amber-50 px-3 py-1 rounded-full border border-amber-300 flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Terkunci (Mode Baca)
                    </span>
                @endif
            </div>

            @if(!$canEditProfile)
                <div class="mb-5 p-3.5 bg-amber-50/90 border border-amber-200 rounded-xl text-xs text-amber-900 font-medium flex items-center gap-2.5">
                    <span class="text-base shrink-0">🔒</span>
                    <span><strong>Pemberitahuan:</strong> Pembaruan mandiri data profil siswa sedang dinonaktifkan oleh Admin Sekolah. Jika ada perubahan data, silakan koordinasikan dengan Wali Kelas.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('siswa.profile.update') }}" class="space-y-5">
                @csrf @method('PUT')

                {{-- Bagian A: Data Diri Siswa --}}
                <div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-200/70 space-y-3.5">
                    <h4 class="text-xs font-extrabold text-indigo-900 uppercase tracking-wider flex items-center gap-2">
                        <span>👤 Data Pribadi Siswa</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Panggilan</label>
                            <input type="text" name="nickname" value="{{ old('nickname', $siswa->nickname) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Contoh: Budi">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tempat Lahir</label>
                            <input type="text" name="birth_place" value="{{ old('birth_place', $siswa->birth_place) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Gianyar">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Agama</label>
                            <input type="text" name="religion" value="{{ old('religion', $siswa->religion) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Hindu / Islam / dll">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kewarganegaraan</label>
                            <input type="text" name="citizenship" value="{{ old('citizenship', $siswa->citizenship ?? 'WNI') }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Anak Keberapa</label>
                            <input type="number" name="child_order" value="{{ old('child_order', $siswa->child_order) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="1">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jml Saudara Kandung</label>
                            <input type="number" name="siblings_count" value="{{ old('siblings_count', $siswa->siblings_count) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Anak</label>
                            <input type="text" name="orphan_status" value="{{ old('orphan_status', $siswa->orphan_status) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Lengkap / Yatim / Piatu">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Bahasa Sehari-hari</label>
                            <input type="text" name="daily_language" value="{{ old('daily_language', $siswa->daily_language) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Bahasa Indonesia / Bali">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tinggal Dengan</label>
                            <input type="text" name="living_with" value="{{ old('living_with', $siswa->living_with) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Orang Tua / Wali">
                        </div>
                    </div>
                </div>

                {{-- Bagian B: Kontak, Minat & Domisili --}}
                <div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-200/70 space-y-3.5">
                    <h4 class="text-xs font-extrabold text-blue-900 uppercase tracking-wider flex items-center gap-2">
                        <span>📱 Kontak Pribadi & Domisili</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No. WhatsApp Siswa</label>
                            <input type="text" name="phone" value="{{ old('phone', $siswa->phone) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="08xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Alamat E-mail</label>
                            <input type="email" name="email" value="{{ old('email', $siswa->email) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="siswa@sekolah.sch.id">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Hobi / Minat</label>
                            <input type="text" name="hobbies" value="{{ old('hobbies', $siswa->hobbies) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Membaca, Basket">
                        </div>
                        <div class="sm:col-span-2 md:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Cita-Cita</label>
                            <input type="text" name="aspirations" value="{{ old('aspirations', $siswa->aspirations) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Dokter / Computer Scientist">
                        </div>
                        <div class="sm:col-span-2 md:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Lengkap (Jalan / Gang)</label>
                            <input type="text" name="address" value="{{ old('address', $siswa->address) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Jl. Ratna No. 10">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">RT / RW / Banjar</label>
                            <input type="text" name="rt_rw" value="{{ old('rt_rw', $siswa->rt_rw) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="002/001">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kelurahan / Desa</label>
                            <input type="text" name="kelurahan" value="{{ old('kelurahan', $siswa->kelurahan) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kecamatan</label>
                            <input type="text" name="kecamatan" value="{{ old('kecamatan', $siswa->kecamatan) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kabupaten" value="{{ old('kabupaten', $siswa->kabupaten) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Tempat Tinggal</label>
                            <select name="residence_status" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                                <option value="">-- Pilih --</option>
                                <option value="bersama_orangtua" @selected(old('residence_status', $siswa->residence_status) === 'bersama_orangtua')>Bersama Orang Tua</option>
                                <option value="wali" @selected(old('residence_status', $siswa->residence_status) === 'wali')>Bersama Wali</option>
                                <option value="kost" @selected(old('residence_status', $siswa->residence_status) === 'kost')>Kost / Kontrak</option>
                                <option value="asrama" @selected(old('residence_status', $siswa->residence_status) === 'asrama')>Asrama</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Moda Transportasi</label>
                            <select name="transportation" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                                <option value="">-- Pilih --</option>
                                <option value="sepeda_motor" @selected(old('transportation', $siswa->transportation) === 'sepeda_motor')>Sepeda Motor</option>
                                <option value="diantar" @selected(old('transportation', $siswa->transportation) === 'diantar')>Diantar Ortu/Wali</option>
                                <option value="sepeda" @selected(old('transportation', $siswa->transportation) === 'sepeda')>Sepeda Gayung</option>
                                <option value="jalan_kaki" @selected(old('transportation', $siswa->transportation) === 'jalan_kaki')>Jalan Kaki</option>
                                <option value="umum" @selected(old('transportation', $siswa->transportation) === 'umum')>Angkutan Umum</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jarak ke Sekolah (km)</label>
                            <input type="number" step="0.1" name="distance_km" value="{{ old('distance_km', $siswa->distance_km) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="5.2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Waktu Tempuh (menit)</label>
                            <input type="number" name="travel_time_minutes" value="{{ old('travel_time_minutes', $siswa->travel_time_minutes) }}" @disabled(!$canEditProfile)
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="15">
                        </div>
                    </div>
                </div>

                {{-- Bagian C: Orang Tua & Kontak Darurat --}}
                <div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-200/70 space-y-3.5">
                    <h4 class="text-xs font-extrabold text-emerald-900 uppercase tracking-wider flex items-center gap-2">
                        <span>👨‍👩‍👧 Orang Tua & Kontak Darurat</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Ayah</label>
                            <input type="text" name="father_name" value="{{ old('father_name', $siswa->father_name) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No. WA Ayah</label>
                            <input type="text" name="father_phone" value="{{ old('father_phone', $siswa->father_phone) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pekerjaan Ayah</label>
                            <input type="text" name="father_job" value="{{ old('father_job', $siswa->father_job) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Ibu</label>
                            <input type="text" name="mother_name" value="{{ old('mother_name', $siswa->mother_name) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No. WA Ibu</label>
                            <input type="text" name="mother_phone" value="{{ old('mother_phone', $siswa->mother_phone) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pekerjaan Ibu</label>
                            <input type="text" name="mother_job" value="{{ old('mother_job', $siswa->mother_job) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kontak Darurat (Nama)</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $siswa->emergency_contact_name) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Paman / Kakek">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No. WA Darurat</label>
                            <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $siswa->emergency_contact_phone) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Hubungan Keluarga</label>
                            <input type="text" name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $siswa->emergency_contact_relation) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="Paman / Bibi">
                        </div>
                    </div>
                </div>

                {{-- Bagian D: Kesehatan & Fisik (UKS) --}}
                <div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-200/70 space-y-3.5">
                    <h4 class="text-xs font-extrabold text-rose-900 uppercase tracking-wider flex items-center gap-2">
                        <span>🩺 Kesehatan & Fisik (UKS)</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Golongan Darah</label>
                            <select name="blood_type" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                                <option value="">-- Pilih --</option>
                                <option value="A" @selected(old('blood_type', $siswa->blood_type) === 'A')>Golongan Darah A</option>
                                <option value="B" @selected(old('blood_type', $siswa->blood_type) === 'B')>Golongan Darah B</option>
                                <option value="AB" @selected(old('blood_type', $siswa->blood_type) === 'AB')>Golongan Darah AB</option>
                                <option value="O" @selected(old('blood_type', $siswa->blood_type) === 'O')>Golongan Darah O</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tinggi Badan (cm)</label>
                            <input type="number" name="height_cm" value="{{ old('height_cm', $siswa->height_cm) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="165">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Berat Badan (kg)</label>
                            <input type="number" name="weight_kg" value="{{ old('weight_kg', $siswa->weight_kg) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="55">
                        </div>
                    </div>
                </div>

                {{-- Bagian E: Pendidikan Sebelumnya & Penerimaan --}}
                <div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-200/70 space-y-3.5">
                    <h4 class="text-xs font-extrabold text-purple-900 uppercase tracking-wider flex items-center gap-2">
                        <span>🎓 Riwayat Sekolah Sebelumnya</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pendidikan Sebelumnya (SMP/MTs)</label>
                            <input type="text" name="prev_school_name" value="{{ old('prev_school_name', $siswa->prev_school_name) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="SMP N 1 Gianyar">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nomor STTB / Ijazah</label>
                            <input type="text" name="prev_sttb_no" value="{{ old('prev_sttb_no', $siswa->prev_sttb_no) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal STTB / Ijazah</label>
                            <input type="date" name="prev_sttb_date" value="{{ old('prev_sttb_date', $siswa->prev_sttb_date?->toDateString()) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Lama Belajar</label>
                            <input type="text" name="prev_study_duration" value="{{ old('prev_study_duration', $siswa->prev_study_duration) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all" placeholder="3 Tahun">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pindahan Dari Sekolah</label>
                            <input type="text" name="transfer_from_school" value="{{ old('transfer_from_school', $siswa->transfer_from_school) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Pindah</label>
                            <input type="text" name="transfer_reason" value="{{ old('transfer_reason', $siswa->transfer_reason) }}" @disabled(!$canEditProfile) class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:text-gray-500 transition-all">
                        </div>
                    </div>
                </div>

                {{-- Action Submit --}}
                @if($canEditProfile)
                    <button type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-800 text-white text-xs font-extrabold tracking-wide uppercase rounded-xl shadow-lg shadow-blue-500/25 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Perubahan Biodata Lengkap</span>
                    </button>
                @endif
            </form>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── PANEL 3: KEAMANAN, PASSWORD & PERANGKAT ──────────────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div id="panel-keamanan" class="space-y-4 tab-panel hidden transition-opacity duration-200">
        {{-- Card Ganti Password --}}
        <div id="ganti-password" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-gray-800">Perbarui Password Akun</h3>
                    <p class="text-[11px] text-gray-400">Pastikan menggunakan kombinasi password yang kuat dan aman</p>
                </div>
            </div>

            {{-- Checklist Syarat Password --}}
            <div class="mb-4 p-3.5 rounded-xl bg-amber-50/80 border border-amber-200/80 text-amber-950 text-xs flex items-start gap-3">
                <span class="text-base shrink-0 mt-0.5">💡</span>
                <div class="space-y-1">
                    <p class="font-extrabold text-amber-900 text-[11px] uppercase tracking-wide">Ketentuan Keamanan Password Baru:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-amber-850 font-medium">
                        <li>Panjang minimal <strong>8 karakter</strong> (kombinasikan huruf & angka).</li>
                        <li>Password Baru & Konfirmasi harus <strong>sama persis</strong>.</li>
                        <li>Simpan password baru Anda di tempat aman agar tidak lupa saat login berikutnya.</li>
                    </ul>
                </div>
            </div>

            <form method="POST" action="{{ route('siswa.profile.password') }}" class="space-y-3.5">
                @csrf @method('PUT')
                
                {{-- Password Saat Ini --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Password Saat Ini</label>
                    <div class="relative flex items-center rounded-xl border @error('current_password') border-rose-400 bg-rose-50/20 @else border-gray-300 bg-gray-50/50 @enderror focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                        <input type="password" id="siswa_current_password" name="current_password" required autocomplete="current-password"
                            class="w-full pl-3.5 pr-10 py-2.5 text-xs font-semibold text-gray-900 bg-transparent focus:outline-none border-0"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('siswa_current_password', this)"
                            class="absolute right-0 top-0 bottom-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none bg-transparent transition-colors cursor-pointer"
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
                    @error('current_password')
                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1 font-medium">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Grid Password Baru & Konfirmasi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    {{-- Password Baru --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Password Baru (Min. 8 Karakter)</label>
                        <div class="relative flex items-center rounded-xl border @error('password') border-rose-400 bg-rose-50/20 @else border-gray-300 bg-gray-50/50 @enderror focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                            <input type="password" id="siswa_new_password" name="password" required autocomplete="new-password"
                                class="w-full pl-3.5 pr-10 py-2.5 text-xs font-semibold text-gray-900 bg-transparent focus:outline-none border-0"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePasswordVisibility('siswa_new_password', this)"
                                class="absolute right-0 top-0 bottom-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none bg-transparent transition-colors cursor-pointer"
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
                        @error('password')
                        <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <div class="relative flex items-center rounded-xl border @error('password_confirmation') border-rose-400 bg-rose-50/20 @else border-gray-300 bg-gray-50/50 @enderror focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                            <input type="password" id="siswa_confirm_password" name="password_confirmation" required autocomplete="new-password"
                                class="w-full pl-3.5 pr-10 py-2.5 text-xs font-semibold text-gray-900 bg-transparent focus:outline-none border-0"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePasswordVisibility('siswa_confirm_password', this)"
                                class="absolute right-0 top-0 bottom-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none bg-transparent transition-colors cursor-pointer"
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

                <button type="submit"
                    class="w-full py-3 bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 hover:from-amber-600 hover:to-amber-800 text-white text-xs font-extrabold tracking-wide uppercase rounded-xl shadow-md shadow-amber-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Simpan Password Baru</span>
                </button>
            </form>
        </div>

        {{-- Log Out Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-extrabold text-gray-800">Keluar dari Sesi Ini</h4>
                <p class="text-[11px] text-gray-400">Pastikan semua data formulir Anda telah tersimpan sebelum keluar.</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Keluar dari Akun</span>
                </button>
            </form>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── MODAL CROPPER FOTO INTERAKTIF (Touch & Desktop Friendly) ─────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div id="crop-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] border border-white/20 animate-in fade-in zoom-in-95 duration-200">
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
                <h3 class="text-xs font-black tracking-wider uppercase flex items-center gap-2 text-amber-300">
                    <span>✂️ Atur & Sesuaikan Pasfoto</span>
                </h3>
                <button type="button" onclick="closeCropModal()" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-gray-300 hover:text-white flex items-center justify-center text-sm font-bold cursor-pointer transition-colors">✕</button>
            </div>

            <div class="px-4 py-2.5 bg-rose-950/90 border-b border-rose-800 text-[11px] text-rose-100 flex items-center gap-2 leading-tight">
                <span class="text-sm shrink-0">📌</span>
                <span>Pastikan posisi kepala tegak di tengah & latar belakang warna merah.</span>
            </div>

            <div class="p-4 bg-slate-950 flex-1 overflow-hidden flex items-center justify-center min-h-[290px] max-h-[420px] relative">
                <img id="crop-image" class="max-w-full max-h-full block">
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-200 flex flex-col gap-3">
                <div class="flex items-center justify-center gap-2 flex-wrap">
                    <button type="button" onclick="cropper && cropper.zoom(0.1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer shadow-xs" title="Perbesar">🔍 +</button>
                    <button type="button" onclick="cropper && cropper.zoom(-0.1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer shadow-xs" title="Perkecil">🔍 -</button>
                    <button type="button" onclick="cropper && cropper.rotate(-90)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer shadow-xs" title="Putar Kiri">↺ 90°</button>
                    <button type="button" onclick="cropper && cropper.rotate(90)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer shadow-xs" title="Putar Kanan">↻ 90°</button>
                    <button type="button" onclick="cropper && cropper.reset()" class="px-3 py-1.5 bg-white border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer shadow-xs" title="Reset">🔄 Reset</button>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-gray-200">
                    <button type="button" onclick="closeCropModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-300 cursor-pointer">Batal</button>
                    <button type="button" id="save-crop-btn" onclick="saveCroppedPhoto()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/25 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                        <span id="save-crop-spinner" class="hidden animate-spin">⏳</span>
                        <span>Simpan Foto Profil</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- ─── JAVASCRIPT LOGIC ─────────────────────────────────────────────────── --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<script>
// Switcher Tab Profil
function switchProfileTab(tabName) {
    const tabs = ['ringkasan', 'biodata', 'keamanan'];
    tabs.forEach(t => {
        const panel = document.getElementById('panel-' + t);
        const btn = document.getElementById('tab-btn-' + t);
        if (panel && btn) {
            if (t === tabName) {
                panel.classList.remove('hidden');
                btn.className = 'flex-1 py-2.5 px-3 rounded-xl text-xs font-extrabold transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer bg-white text-blue-900 shadow-sm';
            } else {
                panel.classList.add('hidden');
                btn.className = 'flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer text-gray-600 hover:text-gray-900';
            }
        }
    });

    if (history.replaceState) {
        history.replaceState(null, null, '#' + tabName);
    }
}

// Check Hash on Load
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.replace('#', '');
    if (hash === 'biodata' || hash === 'keamanan' || hash === 'ganti-password') {
        switchProfileTab(hash === 'ganti-password' ? 'keamanan' : hash);
    } else {
        @if($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation') || auth()->user()->must_change_password)
            switchProfileTab('keamanan');
        @elseif($errors->any())
            switchProfileTab('biodata');
        @endif
    }
});

// Toggle Password Visibility
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

// Cropper.js Initialization
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
        width: 500,
        height: 500,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob(function(blob) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('photo', blob, 'avatar.jpg');

        fetch('{{ route("siswa.profile.photo") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.message || 'Gagal mengunggah foto'); });
            }
            return res.json();
        })
        .then(data => {
            window.location.reload();
        })
        .catch(err => {
            alert('Gagal menyimpan foto: ' + (err.message || err));
            if (spinner) spinner.classList.add('hidden');
            if (btn) btn.disabled = false;
        });
    }, 'image/jpeg', 0.85);
}
</script>
@endsection
