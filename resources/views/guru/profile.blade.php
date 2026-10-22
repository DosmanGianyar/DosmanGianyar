@extends('layouts.guru')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
@endpush

@section('title', 'Profil & Kartu Pendidik')
@section('page-title', 'Profil & Kartu Pendidik')

@section('content')
<div class="max-w-4xl mx-auto space-y-5 pb-10">

    {{-- Alert Messages --}}
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
            <span>⚠️ Terdapat kesalahan pengisian data:</span>
        </p>
        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-700 font-medium pl-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── KARTU PENDIDIK DIGITAL (TEACHER DIGITAL ID CARD) ─────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl border border-slate-700/60 text-white select-none transition-all duration-300"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);">
        
        {{-- Subtle background geometric pattern --}}
        <div class="absolute inset-0 pointer-events-none opacity-[0.05]"
             style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>
        
        {{-- Ambient Glow --}}
        <div class="absolute -top-16 -right-16 w-56 h-56 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-56 h-56 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Emerald-Gold Accent Strip --}}
        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-400 to-amber-400"></div>

        <div class="p-5 sm:p-7 relative z-10 flex flex-col justify-between min-h-[260px] gap-5">
            {{-- Header Kartu Guru --}}
            <div class="flex items-start justify-between gap-4 border-b border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md p-1.5 border border-white/20 shadow-inner flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="max-w-full max-h-full object-contain filter drop-shadow" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <span class="hidden text-xl">🏛️</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] tracking-[0.2em] font-extrabold uppercase text-emerald-400">KARTU PENDIDIK & TENAGA KEPENDIDIKAN</span>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[9px] font-black">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                AKTIF
                            </span>
                        </div>
                        <h2 class="text-base sm:text-lg font-black tracking-wider text-white font-['Oswald'] leading-tight">
                            SMA NEGERI 1 GIANYAR
                        </h2>
                        <p class="text-[10px] text-slate-300 font-medium tracking-wide">
                            Tenaga Pendidik Terdaftar SIMAK DOSMAN
                        </p>
                    </div>
                </div>

                {{-- Status Guru Chip --}}
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-[9px] font-mono uppercase tracking-wider text-slate-400">STATUS KEPEGAWAIAN</span>
                    <span class="text-xs font-bold text-amber-300 flex items-center gap-1">
                        <span>🎖️</span>
                        <span>{{ $guru->nip ? 'PNS / ASN' : 'Guru Tetap' }}</span>
                    </span>
                </div>
            </div>

            {{-- Body: Avatar Guru + Identitas Utama --}}
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-6 py-1">
                {{-- Foto Guru dengan Avatar Frame --}}
                <div class="relative shrink-0 group">
                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border-2 border-emerald-400/80 shadow-2xl bg-slate-900">
                        @if($guru->photo)
                            <img src="{{ $guru->photo_url }}" alt="{{ $guru->name }}"
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white text-3xl font-extrabold"
                                 style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%);">
                                {{ $guru->initials }}
                            </div>
                        @endif
                    </div>

                    {{-- Tombol Ganti Foto --}}
                    <label for="photo-input"
                           class="absolute -bottom-2 -right-2 w-8 h-8 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white
                                  flex items-center justify-center cursor-pointer shadow-lg shadow-emerald-500/40 hover:scale-110 active:scale-95 transition-all"
                           title="Ubah Foto Profil Guru">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>

                    {{-- Form Upload Foto Hidden --}}
                    <form id="photo-form" method="POST" action="{{ route('guru.profile.photo') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp,image/heic">
                    </form>
                </div>

                {{-- Detail Identitas Guru --}}
                <div class="flex-1 text-center sm:text-left space-y-2">
                    <div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-white tracking-wide font-['Plus_Jakarta_Sans'] leading-tight">
                            {{ $guru->name }}
                        </h3>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold">
                                📚 {{ $guru->subject ?? 'Guru Pengajar' }}
                            </span>
                            @if($guru->homeroomClass)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold">
                                    🏛️ Wali Kelas {{ $guru->homeroomClass->name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Grid Metadata Guru --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2.5 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-slate-300 font-bold">NIP</span>
                            <span class="text-xs font-mono font-bold text-emerald-300 mt-0.5 block">{{ $guru->nip ?? '—' }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2.5 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-slate-300 font-bold">EMAIL AKUN</span>
                            <span class="text-xs font-medium text-white truncate mt-0.5 block">{{ $guru->email }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2.5 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-slate-300 font-bold">NO. WHATSAPP</span>
                            <span class="text-xs font-mono font-bold text-amber-200 mt-0.5 block">{{ $guru->phone ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Kartu Guru --}}
            <div class="pt-3 border-t border-white/10 flex flex-wrap items-center justify-between text-[10px] text-slate-400 gap-2">
                <span class="flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
                    SIMAK DOSMAN Pendidik • SMA Negeri 1 Gianyar
                </span>
                <span class="font-mono text-[9px] text-white/40">GURU-ID: {{ str_pad($guru->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── FORMULIR EDIT DATA DIRI GURU & PENDIDIK ─────────────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
        <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-gray-800">Perbarui Data Pribadi & Kontak</h3>
                <p class="text-[11px] text-gray-400">Pastikan informasi kontak Anda mutakhir untuk keperluan administrasi sekolah</p>
            </div>
        </div>

        <form method="POST" action="{{ route('guru.profile.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" name="name" value="{{ old('name', $guru->name) }}" required
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">NIP (Nomor Induk Pegawai)</label>
                    <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-mono transition-all"
                        placeholder="19xxxxxxxxxxxxxx">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Mata Pelajaran yang Diampu</label>
                    <input type="text" name="subject" value="{{ old('subject', $guru->subject) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Matematika, Bahasa Indonesia, dll">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="phone" value="{{ old('phone', $guru->phone) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-mono transition-all"
                        placeholder="08xxxxxxxxxx">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Tempat Tinggal</label>
                    <input type="text" name="address" value="{{ old('address', $guru->address) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Alamat domisili lengkap...">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-extrabold uppercase tracking-wide rounded-xl shadow-md shadow-blue-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Perubahan Profil</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── FORMULIR GANTI PASSWORD GURU ─────────────────────────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div id="ganti-password" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
        <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-gray-800">Keamanan & Ganti Password</h3>
                <p class="text-[11px] text-gray-400">Gunakan kombinasi minimal 8 karakter tanpa spasi untuk keamanan akun pengajar</p>
            </div>
        </div>

        <form method="POST" action="{{ route('guru.profile.password') }}" class="space-y-4">
            @csrf @method('PUT')

            {{-- Password Lama --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Password Saat Ini</label>
                <div class="relative flex items-center rounded-xl border @error('current_password') border-rose-400 bg-rose-50/20 @else border-gray-300 bg-gray-50/50 @enderror focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                    <input type="password" id="guru_current_password" name="current_password" required autocomplete="current-password"
                        class="w-full pl-3.5 pr-10 py-2.5 text-xs font-semibold text-gray-900 bg-transparent focus:outline-none border-0"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('guru_current_password', this)"
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
                @error('current_password') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- Password Baru & Konfirmasi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Password Baru (Min. 8 Karakter)</label>
                    <div class="relative flex items-center rounded-xl border @error('password') border-rose-400 bg-rose-50/20 @else border-gray-300 bg-gray-50/50 @enderror focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                        <input type="password" id="guru_new_password" name="password" required autocomplete="new-password"
                            class="w-full pl-3.5 pr-10 py-2.5 text-xs font-semibold text-gray-900 bg-transparent focus:outline-none border-0"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('guru_new_password', this)"
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
                    @error('password') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <div class="relative flex items-center rounded-xl border @error('password_confirmation') border-rose-400 bg-rose-50/20 @else border-gray-300 bg-gray-50/50 @enderror focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                        <input type="password" id="guru_confirm_password" name="password_confirmation" required autocomplete="new-password"
                            class="w-full pl-3.5 pr-10 py-2.5 text-xs font-semibold text-gray-900 bg-transparent focus:outline-none border-0"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('guru_confirm_password', this)"
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

            <div class="pt-2">
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 active:scale-95 text-white text-xs font-extrabold uppercase tracking-wide rounded-xl shadow-md shadow-amber-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Perbarui Password Guru</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── MODAL CROPPER FOTO GURU ──────────────────────────────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div id="crop-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] border border-white/20 animate-in fade-in zoom-in-95 duration-200">
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
                <h3 class="text-xs font-black tracking-wider uppercase flex items-center gap-2 text-emerald-400">
                    <span>✂️ Atur & Sesuaikan Pasfoto Guru</span>
                </h3>
                <button type="button" onclick="closeCropModal()" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-gray-300 hover:text-white flex items-center justify-center text-sm font-bold cursor-pointer transition-colors">✕</button>
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
                    <button type="button" id="save-crop-btn" onclick="saveCroppedPhoto()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-500/25 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                        <span id="save-crop-spinner" class="hidden animate-spin">⏳</span>
                        <span>Simpan Foto Profil</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- ─── JAVASCRIPT GURU PROFILE ──────────────────────────────────────────── --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
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

        fetch('{{ route("guru.profile.photo") }}', {
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
