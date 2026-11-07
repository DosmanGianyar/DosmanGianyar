@extends('layouts.guru')

@php
    $isPegawai = $guru->role === 'pegawai';
    $roleTitle = $isPegawai ? 'Tenaga Kependidikan (Pegawai)' : 'Tenaga Pendidik (Guru)';
    $selectedSubjectIds = old('subject_ids', $guru->subjects?->pluck('id')->all() ?? []);
    if (!is_array($selectedSubjectIds)) {
        $selectedSubjectIds = [];
    }
    $selectedSubjectIds = array_map('intval', $selectedSubjectIds);
    $subjectDisplay = $guru->subjects && $guru->subjects->isNotEmpty()
        ? $guru->subjects->pluck('name')->join(', ')
        : ($guru->subject ?: ($isPegawai ? ($guru->position ?: 'Pegawai Sekolah') : 'Guru Pengajar'));
@endphp

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
@endpush

@section('title', $isPegawai ? 'Profil & Kartu Pegawai' : 'Profil & Kartu Pendidik')
@section('page-title', $isPegawai ? 'Profil & Kartu Pegawai' : 'Profil & Kartu Pendidik')

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
    {{-- ─── KARTU PENDIDIK / PEGAWAI DIGITAL (DIGITAL ID CARD) ───────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl border border-slate-700/60 text-white select-none transition-all duration-300"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);">

        <div class="absolute inset-0 pointer-events-none opacity-[0.05]"
             style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>

        <div class="absolute -top-16 -right-16 w-56 h-56 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-56 h-56 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-400 to-amber-400"></div>

        <div class="p-5 sm:p-7 relative z-10 flex flex-col justify-between min-h-[260px] gap-5">
            {{-- Header Kartu --}}
            <div class="flex items-start justify-between gap-4 border-b border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md p-1.5 border border-white/20 shadow-inner flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="max-w-full max-h-full object-contain filter drop-shadow" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <span class="hidden text-xl">🏛️</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] tracking-[0.2em] font-extrabold uppercase text-emerald-400">
                                {{ $isPegawai ? 'KARTU TENAGA KEPENDIDIKAN (PEGAWAI)' : 'KARTU PENDIDIK & TENAGA KEPENDIDIKAN' }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[9px] font-black">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                AKTIF
                            </span>
                        </div>
                        <h2 class="text-base sm:text-lg font-black tracking-wider text-white font-['Oswald'] leading-tight">
                            SMA NEGERI 1 GIANYAR
                        </h2>
                        <p class="text-[10px] text-slate-300 font-medium tracking-wide">
                            {{ $roleTitle }} Terdaftar SIMAK DOSMAN
                        </p>
                    </div>
                </div>

                {{-- Status Kepegawaian Chip --}}
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-[9px] font-mono uppercase tracking-wider text-slate-400">STATUS KEPEGAWAIAN</span>
                    <span class="text-xs font-bold text-amber-300 flex items-center gap-1">
                        <span>🎖️</span>
                        <span>{{ $guru->employment_status ?: ($guru->nip ? 'PNS / ASN' : ($isPegawai ? 'Pegawai Sekolah' : 'Guru Pengajar')) }}</span>
                    </span>
                    @if($guru->rank_grade)
                        <span class="text-[10px] text-slate-300 font-mono mt-0.5">{{ $guru->rank_grade }}</span>
                    @endif
                </div>
            </div>

            {{-- Body: Avatar + Identitas Utama --}}
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-6 py-1">
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

                    <label for="photo-input"
                           class="absolute -bottom-2 -right-2 w-8 h-8 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white
                                  flex items-center justify-center cursor-pointer shadow-lg shadow-emerald-500/40 hover:scale-110 active:scale-95 transition-all"
                           title="Ubah Foto Profil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>

                    <form id="photo-form" method="POST" action="{{ route('guru.profile.photo') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp,image/heic">
                    </form>
                </div>

                {{-- Detail Identitas --}}
                <div class="flex-1 text-center sm:text-left space-y-2 min-w-0">
                    <div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-white tracking-wide font-['Plus_Jakarta_Sans'] leading-tight">
                            {{ $guru->name }}
                        </h3>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-1.5">
                            @if(!$isPegawai && $guru->teacher_code)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-sky-500/20 text-sky-300 border border-sky-400/30 text-xs font-mono font-bold">
                                    🏷️ Kode: {{ $guru->teacher_code }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold">
                                {{ $isPegawai ? '💼 ' . ($guru->position ?: 'Pegawai / Staf') : '📚 ' . $subjectDisplay }}
                            </span>
                            @if($isPegawai && $guru->work_unit)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 text-xs font-bold">
                                    🏢 {{ $guru->work_unit }}
                                </span>
                            @endif
                            @if(!$isPegawai && $guru->position)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-purple-500/20 text-purple-300 border border-purple-400/30 text-xs font-bold">
                                    ⭐ {{ $guru->position }}
                                </span>
                            @endif
                            @if($guru->homeroomClass)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold">
                                    🏛️ Wali Kelas {{ $guru->homeroomClass->name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Grid Metadata --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2.5 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-slate-300 font-bold">NIP / NI PPPK</span>
                            <span class="text-xs font-mono font-bold text-emerald-300 mt-0.5 block">{{ $guru->nip ?? '—' }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2.5 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-slate-300 font-bold">EMAIL AKUN</span>
                            <span class="text-xs font-medium text-white truncate mt-0.5 block">{{ $guru->email ?? '—' }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-2.5 border border-white/10">
                            <span class="block text-[9px] uppercase tracking-wider text-slate-300 font-bold">NO. WHATSAPP / HP</span>
                            <span class="text-xs font-mono font-bold text-amber-200 mt-0.5 block">{{ $guru->phone ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Kartu --}}
            <div class="pt-3 border-t border-white/10 flex flex-wrap items-center justify-between text-[10px] text-slate-400 gap-2">
                <span class="flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
                    SIMAK DOSMAN {{ $isPegawai ? 'Tenaga Kependidikan' : 'Pendidik' }} • SMA Negeri 1 Gianyar
                </span>
                <span class="font-mono text-[9px] text-white/40">{{ $isPegawai ? 'PEG' : 'GURU' }}-ID: {{ str_pad($guru->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── FORMULIR LENGKAP EDIT DATA DIRI GURU & PEGAWAI ───────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <form method="POST" action="{{ route('guru.profile.update') }}" class="space-y-5">
        @csrf @method('PUT')

        {{-- 1. IDENTITAS PRIBADI --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-gray-800">1. Identitas Pribadi {{ $isPegawai ? 'Pegawai' : 'Guru' }}</h3>
                    <p class="text-[11px] text-gray-400">Lengkapi biodata diri sesuai dokumen kependudukan resmi</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $guru->name) }}" required
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Contoh: I Nyoman Sudarma, S.Pd., M.Pd.">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Panggilan</label>
                    <input type="text" name="nickname" value="{{ old('nickname', $guru->nickname) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Nama sapaan / panggilan">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" name="nik" value="{{ old('nik', $guru->nik) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold font-mono rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="16 digit NIK KTP">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                    <select name="gender"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="">— Pilih Jenis Kelamin —</option>
                        <option value="L" @selected(old('gender', $guru->gender) === 'L')>Laki-laki (L)</option>
                        <option value="P" @selected(old('gender', $guru->gender) === 'P')>Perempuan (P)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Agama</label>
                    @php $religions = ['Hindu', 'Islam', 'Kristen', 'Katolik', 'Buddha', 'Konghucu']; @endphp
                    <select name="religion"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="">— Pilih Agama —</option>
                        @foreach($religions as $rel)
                            <option value="{{ $rel }}" @selected(old('religion', $guru->religion) === $rel)>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $guru->birth_place) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Contoh: Gianyar">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $guru->birth_date?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Golongan Darah</label>
                    @php $bloodTypes = ['A', 'B', 'AB', 'O', '-']; @endphp
                    <select name="blood_type"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="">— Pilih Golongan Darah —</option>
                        @foreach($bloodTypes as $bt)
                            <option value="{{ $bt }}" @selected(old('blood_type', $guru->blood_type) === $bt)>{{ $bt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status Pernikahan</label>
                    @php $maritalStatuses = ['Belum Menikah', 'Menikah', 'Cerai Hidup', 'Cerai Mati']; @endphp
                    <select name="marital_status"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="">— Pilih Status Pernikahan —</option>
                        @foreach($maritalStatuses as $ms)
                            <option value="{{ $ms }}" @selected(old('marital_status', $guru->marital_status) === $ms)>{{ $ms }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kewarganegaraan</label>
                    <input type="text" name="citizenship" value="{{ old('citizenship', $guru->citizenship ?? 'WNI') }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="WNI">
                </div>
            </div>
        </div>

        {{-- 2. DATA KEPEGAWAIAN, JABATAN & MATA PELAJARAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-gray-800">
                        2. Data Kepegawaian {{ $isPegawai ? '& Jabatan Tenaga Kependidikan' : '& Mata Pelajaran Guru' }}
                    </h3>
                    <p class="text-[11px] text-gray-400">
                        {{ $isPegawai ? 'Detail NIP, status kepegawaian, unit kerja, jabatan, dan riwayat pendidikan' : 'Detail NIP, NUPTK, Kode Guru, mata pelajaran yang diampu, dan pangkat/golongan' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">NIP / NI PPPK / Nomor Pegawai</label>
                    <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold font-mono rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="19xxxxxxxxxxxxxxxx">
                    @error('nip') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if(!$isPegawai)
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">NUPTK</label>
                    <input type="text" name="nuptk" value="{{ old('nuptk', $guru->nuptk) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold font-mono rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="16 digit NUPTK">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kode Guru (Jadwal Pelajaran)</label>
                    <input type="text" name="teacher_code" value="{{ old('teacher_code', $guru->teacher_code) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold font-mono rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Contoh: P. SUDARMA / B. Puspita">
                    @error('teacher_code') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status Kepegawaian</label>
                    @php
                        $empStatuses = $isPegawai
                            ? ['PNS', 'PPPK', 'Pegawai Tetap', 'Pegawai Kontrak / Honorer', 'Tenaga Outsourcing']
                            : ['PNS', 'PPPK', 'Guru Tetap', 'Guru Kontrak / Honorer', 'Guru Tamu'];
                    @endphp
                    <select name="employment_status"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="">— Pilih Status Kepegawaian —</option>
                        @foreach($empStatuses as $es)
                            <option value="{{ $es }}" @selected(old('employment_status', $guru->employment_status) === $es)>{{ $es }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Pangkat / Golongan Ruang</label>
                    <input type="text" name="rank_grade" value="{{ old('rank_grade', $guru->rank_grade) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Contoh: Pembina (IV/a) / Penata Muda (III/a) / Gol. IX">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        {{ $isPegawai ? 'Jabatan Pegawai' : 'Jabatan / Tugas Tambahan' }}
                    </label>
                    <input type="text" name="position" value="{{ old('position', $guru->position) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="{{ $isPegawai ? 'Contoh: Staf Administrasi, Pustakawan, Laboran, Bendahara' : 'Contoh: Guru Mata Pelajaran, Guru BK, Wakasek Kurikulum' }}">
                </div>

                @if($isPegawai)
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Unit Kerja / Bagian</label>
                    @php
                        $workUnits = [
                            'Tata Usaha & Administrasi',
                            'Perpustakaan',
                            'Laboratorium IPA / Komputer',
                            'Sarana & Prasarana',
                            'Keuangan / Bendahara',
                            'Layanan Kesiswaan & BK',
                            'Kurikulum & Akademik',
                            'Humas & Pelayanan Terpadu',
                            'Keamanan & Ketertiban',
                            'Kebersihan & Taman',
                            'Lainnya',
                        ];
                    @endphp
                    <select name="work_unit"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="">— Pilih Unit Kerja / Bagian —</option>
                        @foreach($workUnits as $wu)
                            <option value="{{ $wu }}" @selected(old('work_unit', $guru->work_unit) === $wu)>{{ $wu }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">TMT (Tanggal Mulai Bertugas)</label>
                    <input type="date" name="tmt_date" value="{{ old('tmt_date', $guru->tmt_date?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Pendidikan Terakhir</label>
                    @php $educations = ['SMA / SMK / Sederajat', 'D1 / D2 / D3', 'S1 / D4', 'S2 (Magister)', 'S3 (Doktor)']; @endphp
                    <select name="last_education"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="">— Pilih Pendidikan Terakhir —</option>
                        @foreach($educations as $edu)
                            <option value="{{ $edu }}" @selected(old('last_education', $guru->last_education) === $edu)>{{ $edu }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="{{ $isPegawai ? 'sm:col-span-2' : '' }}">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Perguruan Tinggi & Program Studi / Jurusan</label>
                    <input type="text" name="alma_mater" value="{{ old('alma_mater', $guru->alma_mater) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Contoh: Universitas Pendidikan Ganesha - Pendidikan Matematika">
                </div>

                @if(!$isPegawai)
                {{-- Pilihan Mata Pelajaran (Multi-Select dari tabel subjects + sinkronisasi teks) --}}
                <div class="sm:col-span-2 pt-2 border-t border-gray-100">
                    <input type="hidden" name="subjects_section_present" value="1">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                        <div>
                            <label class="block text-xs font-extrabold text-gray-800">Daftar Mata Pelajaran yang Diampu (Bisa Pilih Lebih dari Satu)</label>
                            <p class="text-[11px] text-gray-400">Centang mata pelajaran yang Anda ajarkan agar terhubung otomatis dengan Jadwal & Jurnal Mengajar</p>
                        </div>
                        <input type="text" id="subject-search" placeholder="Cari mata pelajaran..."
                            oninput="filterSubjectCheckboxes(this.value)"
                            class="px-3 py-1.5 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:border-blue-500 w-full sm:w-56">
                    </div>

                    @if(isset($allSubjects) && $allSubjects->isNotEmpty())
                    <div id="subject-checkbox-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-56 overflow-y-auto p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
                        @foreach($allSubjects as $subj)
                            @php $isChecked = in_array((int) $subj->id, $selectedSubjectIds, true); @endphp
                            <label data-subject-name="{{ strtolower($subj->name) }}"
                                   class="subject-item flex items-center gap-2.5 p-2 rounded-xl border cursor-pointer transition-all select-none {{ $isChecked ? 'bg-blue-50 border-blue-300 text-blue-900' : 'bg-white border-gray-200/80 text-gray-700 hover:border-blue-200' }}">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subj->id }}"
                                       data-label="{{ $subj->name }}"
                                       onchange="onSubjectCheckboxChange(this)"
                                       @checked($isChecked)
                                       class="w-4 h-4 rounded text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="text-xs font-semibold leading-tight">{{ $subj->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @endif

                    <div class="mt-3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Label Ringkas Mata Pelajaran / Bidang Studi (Ditampilkan di Kartu Profil)</label>
                        <input type="text" id="subject-text-input" name="subject" value="{{ old('subject', $guru->subject ?: $subjectDisplay) }}"
                            class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                            placeholder="Contoh: Matematika, Fisika">
                        <p class="text-[10.5px] text-gray-400 mt-1">Otomatis terisi sesuai mata pelajaran yang dicentang di atas, atau dapat Anda sesuaikan sendiri.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- 3. KONTAK, ALAMAT DOMISILI & KONTAK DARURAT --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-gray-800">3. Kontak, Alamat Domisili & Kontak Darurat</h3>
                    <p class="text-[11px] text-gray-400">Nomor HP/WhatsApp, email aktif, dan alamat tempat tinggal saat ini</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">No. WhatsApp / HP Aktif</label>
                    <input type="text" name="phone" value="{{ old('phone', $guru->phone) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold font-mono rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="08xxxxxxxxxx">
                    @error('phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Email Akun <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $guru->email) }}" required
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="nama@email.com">
                    @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Lengkap Tempat Tinggal</label>
                    <input type="text" name="address" value="{{ old('address', $guru->address) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Jalan, Banjar/Lingkungan, Nomor Rumah...">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">RT / RW (Opsional)</label>
                    <input type="text" name="rt_rw" value="{{ old('rt_rw', $guru->rt_rw) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Contoh: 001/002">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Desa / Kelurahan</label>
                    <input type="text" name="kelurahan" value="{{ old('kelurahan', $guru->kelurahan) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Contoh: Gianyar">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kecamatan</label>
                    <input type="text" name="kecamatan" value="{{ old('kecamatan', $guru->kecamatan) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Contoh: Gianyar">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kabupaten / Kota</label>
                    <input type="text" name="kabupaten" value="{{ old('kabupaten', $guru->kabupaten) }}"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                        placeholder="Contoh: Gianyar">
                </div>

                <div class="sm:col-span-2 pt-3 border-t border-gray-100">
                    <h4 class="text-xs font-extrabold text-gray-700 mb-3">Kontak Keluarga / Darurat yang Dapat Dihubungi</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama Kontak Darurat</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $guru->emergency_contact_name) }}"
                                class="w-full px-3.5 py-2 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500"
                                placeholder="Nama suami/istri/keluarga">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">No. HP Kontak Darurat</label>
                            <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $guru->emergency_contact_phone) }}"
                                class="w-full px-3.5 py-2 text-xs font-semibold font-mono rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500"
                                placeholder="08xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Hubungan Keluarga</label>
                            <input type="text" name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $guru->emergency_contact_relation) }}"
                                class="w-full px-3.5 py-2 text-xs font-semibold rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-blue-500"
                                placeholder="Suami / Istri / Orang Tua / Saudara">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-5 mt-4 border-t border-gray-100 flex justify-end">
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-extrabold uppercase tracking-wide rounded-xl shadow-md shadow-blue-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Seluruh Perubahan Profil</span>
                </button>
            </div>
        </div>
    </form>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── FORMULIR GANTI PASSWORD GURU / PEGAWAI ───────────────────────────── --}}
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
                <p class="text-[11px] text-gray-400">Gunakan kombinasi minimal 8 karakter tanpa spasi untuk keamanan akun Anda</p>
            </div>
        </div>

        <form method="POST" action="{{ route('guru.profile.password') }}" class="space-y-4">
            @csrf @method('PUT')

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
                    <span>Perbarui Password</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ─── MODAL CROPPER FOTO ───────────────────────────────────────────────── --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div id="crop-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] border border-white/20 animate-in fade-in zoom-in-95 duration-200">
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
                <h3 class="text-xs font-black tracking-wider uppercase flex items-center gap-2 text-emerald-400">
                    <span>✂️ Atur & Sesuaikan Pasfoto Profil</span>
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

<script>
function filterSubjectCheckboxes(query) {
    const q = (query || '').trim().toLowerCase();
    document.querySelectorAll('#subject-checkbox-grid .subject-item').forEach(function(item) {
        const name = item.getAttribute('data-subject-name') || '';
        item.style.display = (!q || name.includes(q)) ? '' : 'none';
    });
}

function onSubjectCheckboxChange(checkbox) {
    const label = checkbox.closest('.subject-item');
    if (label) {
        if (checkbox.checked) {
            label.classList.add('bg-blue-50', 'border-blue-300', 'text-blue-900');
            label.classList.remove('bg-white', 'border-gray-200/80', 'text-gray-700');
        } else {
            label.classList.remove('bg-blue-50', 'border-blue-300', 'text-blue-900');
            label.classList.add('bg-white', 'border-gray-200/80', 'text-gray-700');
        }
    }

    const selected = [];
    document.querySelectorAll('#subject-checkbox-grid input[name="subject_ids[]"]:checked').forEach(function(cb) {
        const lbl = cb.getAttribute('data-label');
        if (lbl) selected.push(lbl);
    });
    const textInput = document.getElementById('subject-text-input');
    if (textInput && selected.length > 0) {
        textInput.value = selected.join(', ');
    }
}

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
