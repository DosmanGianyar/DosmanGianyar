@extends('layouts.orangtua')
@section('title', 'Profil Orang Tua')
@section('page-title', 'Profil Saya')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
@endpush

@section('content')
<div class="max-w-3xl mx-auto space-y-5 pb-10">

    @if(session('success'))
    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-bold flex items-center gap-2.5 shadow-xs">
        <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs shrink-0">✓</span>
        <span>{{ session('success') }}</span>
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
    {{-- ─── 1. KARTU IDENTITAS ORANG TUA (DEEP NAVY GLASSMORPHISM) ────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="relative rounded-3xl p-5 sm:p-6 text-white shadow-xl shadow-blue-950/20 overflow-hidden"
         style="background: linear-gradient(135deg, #0b1e4f 0%, #123075 50%, #19439e 100%); border: 1px solid rgba(255,255,255,0.15);">

        <div class="absolute -right-10 -bottom-10 w-44 h-44 rounded-full bg-blue-400/10 blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-36 h-36 rounded-full bg-indigo-400/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-white/90 border border-white/20 shadow-2xs">
                    <span>👨‍👩‍👧‍👦</span>
                    <span>{{ $orangtua->parent_relation ?: 'Orang Tua / Wali Siswa' }}</span>
                </span>
                <span class="text-[10px] font-extrabold tracking-wider uppercase px-2.5 py-1 rounded-full bg-amber-400/20 text-amber-300 border border-amber-300/30">
                    Akun Terverifikasi
                </span>
            </div>

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-5 py-1">
                <div class="relative shrink-0 group">
                    <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden border-2 border-white/30 shadow-xl bg-slate-900/40">
                        @if($orangtua->photo)
                            <img src="{{ $orangtua->photo_url }}" alt="{{ $orangtua->name }}"
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white text-2xl font-black bg-gradient-to-br from-blue-600 to-indigo-700">
                                {{ $orangtua->initials }}
                            </div>
                        @endif
                    </div>

                    <label for="photo-input"
                           class="absolute -bottom-2 -right-2 w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white
                                  flex items-center justify-center cursor-pointer shadow-lg shadow-blue-500/40 hover:scale-110 active:scale-95 transition-all"
                           title="Ubah Foto Profil">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>

                    <form id="photo-form" method="POST" action="{{ route('orangtua.profile.photo') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp,image/heic">
                    </form>
                </div>

                <div class="flex-1 text-center sm:text-left space-y-1.5 min-w-0">
                    <h1 class="text-lg sm:text-xl font-black text-white tracking-tight leading-tight truncate">
                        {{ $orangtua->name }}
                    </h1>
                    <p class="text-xs text-blue-200/90 font-medium">
                        {{ $orangtua->parent_relation ?: 'Orang Tua / Wali' }} dari {{ $children->count() }} siswa SMAN 1 Gianyar
                    </p>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white/10 text-white border border-white/15 text-xs font-mono font-bold">
                            <span>📱</span>
                            <span>{{ $orangtua->phone ?? '—' }}</span>
                        </span>
                        @if($orangtua->occupation)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-500/20 text-sky-200 border border-sky-400/30 text-xs font-semibold">
                            <span>💼</span>
                            <span>{{ $orangtua->occupation }}</span>
                        </span>
                        @endif
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold">
                            <span>✅</span>
                            <span>Login Aktif</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── 2. DAFTAR PUTRA / PUTRI TERHUBUNG ────────────────────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 p-5 sm:p-6 space-y-3.5">
        <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="text-base">🎓</span>
                <h2 class="text-sm font-extrabold text-slate-800">Putra / Putri yang Terhubung</h2>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-100">
                {{ $children->count() }} Siswa
            </span>
        </div>

        <div class="space-y-2.5">
            @forelse($children as $child)
                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50/80 border border-slate-200/70 gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($child->photo)
                            <img src="{{ $child->photo_url }}" alt="{{ $child->name }}" class="w-10 h-10 rounded-xl object-cover ring-2 ring-white shadow-2xs shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-700 flex items-center justify-center font-black text-sm shrink-0 border border-blue-200/60 shadow-2xs">
                                {{ $child->initials }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h3 class="text-xs font-bold text-slate-800 truncate">{{ $child->name }}</h3>
                            <p class="text-[11px] text-slate-500 font-medium">Kelas {{ $child->schoolClass?->name ?? '—' }} · NIS {{ $child->nis ?? '—' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('orangtua.attendance.history', $child->id) }}"
                       class="shrink-0 px-2.5 py-1.5 rounded-xl bg-white hover:bg-blue-50 text-blue-600 border border-slate-200 text-xs font-bold shadow-2xs transition-colors flex items-center gap-1">
                        <span>Pantau</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @empty
                <div class="text-center py-6 text-slate-400 text-xs">
                    Belum ada siswa yang terhubung dengan nomor HP akun ini.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── 3. FORMULIR LENGKAP PERBARUI DATA PROFIL ORANG TUA ───────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <form method="POST" action="{{ route('orangtua.profile.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- A. IDENTITAS DIRI ORANG TUA / WALI --}}
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 p-5 sm:p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800">1. Identitas Diri Orang Tua / Wali</h2>
                    <p class="text-[11px] text-slate-400">Data diri resmi orang tua atau wali siswa</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Lengkap Orang Tua / Wali <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $orangtua->name) }}" required
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Masukkan nama lengkap Anda">
                    @error('name') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Panggilan</label>
                    <input type="text" name="nickname" value="{{ old('nickname', $orangtua->nickname) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Nama sapaan">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Hubungan dengan Siswa</label>
                    @php $relations = ['Ayah Kandung', 'Ibu Kandung', 'Wali']; @endphp
                    <select name="parent_relation"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800">
                        <option value="">— Pilih Hubungan —</option>
                        @foreach($relations as $rel)
                            <option value="{{ $rel }}" @selected(old('parent_relation', $orangtua->parent_relation) === $rel)>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" name="nik" value="{{ old('nik', $orangtua->nik) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold font-mono rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="16 digit NIK KTP">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                    <select name="gender"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800">
                        <option value="">— Pilih Jenis Kelamin —</option>
                        <option value="L" @selected(old('gender', $orangtua->gender) === 'L')>Laki-laki (L)</option>
                        <option value="P" @selected(old('gender', $orangtua->gender) === 'P')>Perempuan (P)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $orangtua->birth_place) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Contoh: Gianyar">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $orangtua->birth_date?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Agama</label>
                    @php $religions = ['Hindu', 'Islam', 'Kristen', 'Katolik', 'Buddha', 'Konghucu']; @endphp
                    <select name="religion"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800">
                        <option value="">— Pilih Agama —</option>
                        @foreach($religions as $r)
                            <option value="{{ $r }}" @selected(old('religion', $orangtua->religion) === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kewarganegaraan</label>
                    <input type="text" name="citizenship" value="{{ old('citizenship', $orangtua->citizenship ?? 'WNI') }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="WNI">
                </div>
            </div>
        </div>

        {{-- B. PENDIDIKAN & PEKERJAAN ORANG TUA --}}
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 p-5 sm:p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800">2. Pendidikan & Pekerjaan Orang Tua / Wali</h2>
                    <p class="text-[11px] text-slate-400">Informasi pendidikan terakhir, pekerjaan, dan penghasilan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pendidikan Terakhir</label>
                    @php $educations = ['SD / Sederajat', 'SMP / Sederajat', 'SMA / SMK / Sederajat', 'D1 / D2 / D3', 'S1 / D4', 'S2 (Magister)', 'S3 (Doktor)', 'Tidak Sekolah']; @endphp
                    <select name="last_education"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800">
                        <option value="">— Pilih Pendidikan Terakhir —</option>
                        @foreach($educations as $edu)
                            <option value="{{ $edu }}" @selected(old('last_education', $orangtua->last_education) === $edu)>{{ $edu }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pekerjaan Utama</label>
                    <input type="text" name="occupation" value="{{ old('occupation', $orangtua->occupation) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Contoh: Wiraswasta, PNS, Karyawan Swasta, Petani">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Instansi / Tempat Bekerja (Opsional)</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $orangtua->company_name) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Nama perusahaan / usaha / instansi">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Penghasilan Rata-rata per Bulan</label>
                    @php
                        $incomes = [
                            'Kurang dari Rp 1.000.000',
                            'Rp 1.000.000 - Rp 2.500.000',
                            'Rp 2.500.000 - Rp 5.000.000',
                            'Rp 5.000.000 - Rp 10.000.000',
                            'Lebih dari Rp 10.000.000',
                            'Tidak Berpenghasilan',
                        ];
                    @endphp
                    <select name="income_range"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800">
                        <option value="">— Pilih Rentang Penghasilan —</option>
                        @foreach($incomes as $inc)
                            <option value="{{ $inc }}" @selected(old('income_range', $orangtua->income_range) === $inc)>{{ $inc }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- C. KONTAK & ALAMAT DOMISILI ORANG TUA --}}
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 p-5 sm:p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800">3. Kontak & Alamat Domisili</h2>
                    <p class="text-[11px] text-slate-400">Nomor HP/WhatsApp (sekaligus ID login), email, alamat tempat tinggal, dan kontak darurat</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nomor HP / WhatsApp (ID Login Akun) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $orangtua->phone) }}" required
                        class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="08xxxxxxxxxx">
                    @error('phone') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    <p class="text-[10.5px] text-slate-400 mt-1">Jika diubah, nomor login Anda dan nomor HP orang tua pada data anak otomatis diperbarui.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email (Opsional)</label>
                    <input type="email" name="email" value="{{ old('email', $orangtua->email) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="email@contoh.com">
                    @error('email') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="block text-xs font-bold text-slate-700 mb-1">
                        Alamat Lengkap Domisili / Tempat Tinggal
                    </label>
                    <input type="text" id="address" name="address" value="{{ old('address', $orangtua->address) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Contoh: Jl. Ngurah Rai No. 10, Banjar Sengguan, Gianyar">
                    @error('address') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">RT / RW (Opsional)</label>
                    <input type="text" name="rt_rw" value="{{ old('rt_rw', $orangtua->rt_rw) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Contoh: 001/002">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Desa / Kelurahan</label>
                    <input type="text" name="kelurahan" value="{{ old('kelurahan', $orangtua->kelurahan) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Contoh: Gianyar">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan</label>
                    <input type="text" name="kecamatan" value="{{ old('kecamatan', $orangtua->kecamatan) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Contoh: Gianyar">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kabupaten / Kota</label>
                    <input type="text" name="kabupaten" value="{{ old('kabupaten', $orangtua->kabupaten) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Contoh: Gianyar">
                </div>

                <div class="sm:col-span-2 pt-3 border-t border-slate-100">
                    <h3 class="text-xs font-extrabold text-slate-700 mb-3">Kontak Keluarga / Cadangan Darurat</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Kontak Cadangan</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $orangtua->emergency_contact_name) }}"
                                class="w-full px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 text-slate-800"
                                placeholder="Nama kerabat / pasangan">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">No. HP Cadangan</label>
                            <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $orangtua->emergency_contact_phone) }}"
                                class="w-full px-3.5 py-2 text-xs font-semibold font-mono rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 text-slate-800"
                                placeholder="08xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Hubungan</label>
                            <input type="text" name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $orangtua->emergency_contact_relation) }}"
                                class="w-full px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 text-slate-800"
                                placeholder="Ibu / Ayah / Paman / Bibi">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit"
                    class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 active:scale-[0.98] text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Seluruh Perubahan Profil Orang Tua</span>
                </button>
            </div>
        </div>
    </form>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── 4. GANTI PASSWORD AKUN ───────────────────────────────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-extrabold text-slate-800">Ganti Password Akun</h2>
                <p class="text-[11px] text-slate-400">Jaga keamanan akun orang tua Anda dengan password yang aman</p>
            </div>
        </div>

        <div class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200/80 space-y-1.5">
            <div class="flex items-center gap-1.5 text-xs font-bold text-amber-900">
                <span>🛡️</span>
                <span>Ketentuan Pembuatan Password Baru:</span>
            </div>
            <ul class="text-[11px] text-amber-800 space-y-1 pl-5 list-disc font-medium">
                <li>Panjang password minimal 8 karakter.</li>
                <li>Tidak boleh mengandung spasi.</li>
                <li>Password Baru dan Konfirmasi harus sama persis.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('orangtua.profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1">
                    Password Saat Ini <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="current_password" name="current_password" required
                        class="w-full pl-3.5 pr-10 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Masukkan password yang saat ini digunakan">
                    <button type="button" onclick="togglePasswordVisibility('current_password', this)"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
                @error('current_password')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1">
                    Password Baru (Min. 8 Karakter) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                        class="w-full pl-3.5 pr-10 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Masukkan password baru">
                    <button type="button" onclick="togglePasswordVisibility('password', this)"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">
                    Ulangi Password Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full pl-3.5 pr-10 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-800"
                        placeholder="Ketik ulang password baru">
                    <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full py-3 bg-slate-900 hover:bg-slate-800 active:scale-[0.98] text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Perbarui Password</span>
                </button>
            </div>
        </form>
    </div>

</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- ─── MODAL CROPPER FOTO PROFIL ────────────────────────────────────────── --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div id="crop-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl animate-scale-up">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Sesuaikan Foto Profil</h3>
            <button onclick="closeCropModal()" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                ✕
            </button>
        </div>
        <div class="p-4 bg-slate-950 flex items-center justify-center max-h-[350px] overflow-hidden">
            <img id="crop-image" src="" alt="Crop" class="max-w-full block">
        </div>
        <div class="p-4 bg-slate-50 flex items-center justify-end gap-2">
            <button type="button" onclick="closeCropModal()"
                class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200/70 transition-colors">
                Batal
            </button>
            <button type="button" id="save-crop-btn" onclick="saveCroppedPhoto()"
                class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex items-center gap-1.5 transition-all">
                <svg id="save-crop-spinner" class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Terapkan Foto</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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

let cropper = null;
const photoInput = document.getElementById('photo-input');
const cropModal  = document.getElementById('crop-modal');
const cropImage  = document.getElementById('crop-image');

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

        fetch('{{ route("orangtua.profile.photo") }}', {
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
@endpush
