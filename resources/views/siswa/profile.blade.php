@extends('layouts.siswa')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
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
        <div class="h-20 relative overflow-hidden" style="background: linear-gradient(135deg, #0f2460 0%, #1a3a8f 50%, #1e3fad 100%) !important;">
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
                    <div class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg flex items-center justify-center text-white text-2xl font-bold"
                         style="background: linear-gradient(135deg, #1d4ed8 0%, #4338ca 100%) !important;">
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
                    <input type="file" id="photo-input" name="photo" accept="image/*">
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

        {{-- Ketentuan Foto Profil Resmi Siswa --}}
        <div class="mx-4 mb-4 bg-red-50/90 border border-red-200/90 rounded-2xl p-3.5 flex items-start gap-3 shadow-xs">
            <div class="w-8 h-8 bg-red-600 text-white rounded-xl flex items-center justify-center text-sm font-bold shrink-0 shadow-xs">📌</div>
            <div class="text-xs text-red-950 space-y-1">
                <p class="font-extrabold text-red-900 flex items-center gap-1">
                    Ketentuan Foto Profil Resmi Siswa:
                </p>
                <ul class="list-disc list-inside space-y-0.5 text-red-800 font-medium leading-relaxed">
                    <li>Wajib foto resmi berpakaian sekolah rapi.</li>
                    <li><strong>Latar belakang (background) warna merah</strong>.</li>
                    <li><strong>Diutamakan berpakaian Batik SMAN 1 Gianyar</strong>.</li>
                </ul>
                <p class="text-[11px] text-red-700 font-semibold pt-0.5">⚠️ Foto yang tidak sesuai aturan sekolah dapat dihapus oleh Admin.</p>
            </div>
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

    {{-- ─── Edit Data Diri Siswa ────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-800">Kelengkapan Data Profil Siswa</h3>
            </div>
            @if(!$canEditProfile)
                <span class="text-[11px] font-bold text-amber-800 bg-amber-100 px-2.5 py-1 rounded-full border border-amber-300 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Read-Only (Dikunci Admin)
                </span>
            @endif
        </div>

        @if(!$canEditProfile)
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-medium">
                🔒 <strong>Pemberitahuan:</strong> Pengisian & pembaruan data profil siswa sedang dikunci oleh pihak sekolah. Hubungi Wali Kelas jika ada data yang memerlukan perubahan.
            </div>
        @endif

        <form method="POST" action="{{ route('siswa.profile.update') }}" class="space-y-4">
            @csrf @method('PUT')

            {{-- Section A: Data Diri Siswa --}}
            <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/70 space-y-3">
                <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider flex items-center gap-1.5">
                    <span>👤 Data Diri Siswa</span>
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Panggilan</label>
                        <input type="text" name="nickname" value="{{ old('nickname', $siswa->nickname) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Contoh: Budi">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tempat Lahir</label>
                        <input type="text" name="birth_place" value="{{ old('birth_place', $siswa->birth_place) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Gianyar">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Agama</label>
                        <input type="text" name="religion" value="{{ old('religion', $siswa->religion) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Hindu / Islam / dll">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kewarganegaraan</label>
                        <input type="text" name="citizenship" value="{{ old('citizenship', $siswa->citizenship ?? 'WNI') }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Anak Keberapa</label>
                        <input type="number" name="child_order" value="{{ old('child_order', $siswa->child_order) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="1">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Jml Saudara Kandung</label>
                        <input type="number" name="siblings_count" value="{{ old('siblings_count', $siswa->siblings_count) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Anak (Yatim/Piatu)</label>
                        <input type="text" name="orphan_status" value="{{ old('orphan_status', $siswa->orphan_status) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Lengkap / Yatim / Piatu">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Bahasa Sehari-hari</label>
                        <input type="text" name="daily_language" value="{{ old('daily_language', $siswa->daily_language) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Bahasa Indonesia / Bali">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tinggal Dengan</label>
                        <input type="text" name="living_with" value="{{ old('living_with', $siswa->living_with) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Orang Tua / Wali">
                    </div>
                </div>
            </div>

            {{-- Section 1: Kontak & Domisili --}}
            <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/70 space-y-3">
                <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                    <span>📱 Kontak & Domisili</span>
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">No. WhatsApp Siswa</label>
                        <input type="text" name="phone" value="{{ old('phone', $siswa->phone) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="08xxxxxxxxxx">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $siswa->email) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="siswa@sekolah.sch.id">
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Hobi / Minat</label>
                        <input type="text" name="hobbies" value="{{ old('hobbies', $siswa->hobbies) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Membaca, Badminton">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Cita-Cita</label>
                        <input type="text" name="aspirations" value="{{ old('aspirations', $siswa->aspirations) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Dokter / Computer Scientist">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Jalan</label>
                        <input type="text" name="address" value="{{ old('address', $siswa->address) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Jl. Ratna No. 10">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">RT / RW</label>
                        <input type="text" name="rt_rw" value="{{ old('rt_rw', $siswa->rt_rw) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="002/001">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kelurahan / Desa</label>
                        <input type="text" name="kelurahan" value="{{ old('kelurahan', $siswa->kelurahan) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kecamatan</label>
                        <input type="text" name="kecamatan" value="{{ old('kecamatan', $siswa->kecamatan) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kabupaten / Kota</label>
                        <input type="text" name="kabupaten" value="{{ old('kabupaten', $siswa->kabupaten) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Tempat Tinggal</label>
                        <select name="residence_status" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                            <option value="">-- Pilih --</option>
                            <option value="bersama_orangtua" @selected(old('residence_status', $siswa->residence_status) === 'bersama_orangtua')>Tinggal Bersama Orang Tua</option>
                            <option value="wali" @selected(old('residence_status', $siswa->residence_status) === 'wali')>Tinggal Bersama Wali</option>
                            <option value="kost" @selected(old('residence_status', $siswa->residence_status) === 'kost')>Kost / Kontrak</option>
                            <option value="asrama" @selected(old('residence_status', $siswa->residence_status) === 'asrama')>Asrama</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Moda Transportasi</label>
                        <select name="transportation" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                            <option value="">-- Pilih --</option>
                            <option value="sepeda_motor" @selected(old('transportation', $siswa->transportation) === 'sepeda_motor')>Sepeda Motor</option>
                            <option value="diantar" @selected(old('transportation', $siswa->transportation) === 'diantar')>Diantar Ortu/Wali</option>
                            <option value="sepeda" @selected(old('transportation', $siswa->transportation) === 'sepeda')>Sepeda</option>
                            <option value="jalan_kaki" @selected(old('transportation', $siswa->transportation) === 'jalan_kaki')>Jalan Kaki</option>
                            <option value="umum" @selected(old('transportation', $siswa->transportation) === 'umum')>Angkutan Umum</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Jarak ke Sekolah (km)</label>
                        <input type="number" step="0.1" name="distance_km" value="{{ old('distance_km', $siswa->distance_km) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="5.2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Waktu Tempuh (menit)</label>
                        <input type="number" name="travel_time_minutes" value="{{ old('travel_time_minutes', $siswa->travel_time_minutes) }}" @disabled(!$canEditProfile)
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="15">
                    </div>
                </div>
            </div>

            {{-- Section 2: Orang Tua & Darurat --}}
            <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/70 space-y-3">
                <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider flex items-center gap-1.5">
                    <span>👨‍👩‍👧 Orang Tua & Kontak Darurat</span>
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Ayah</label>
                        <input type="text" name="father_name" value="{{ old('father_name', $siswa->father_name) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">No. WA Ayah</label>
                        <input type="text" name="father_phone" value="{{ old('father_phone', $siswa->father_phone) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Pekerjaan Ayah</label>
                        <input type="text" name="father_job" value="{{ old('father_job', $siswa->father_job) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Ibu</label>
                        <input type="text" name="mother_name" value="{{ old('mother_name', $siswa->mother_name) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">No. WA Ibu</label>
                        <input type="text" name="mother_phone" value="{{ old('mother_phone', $siswa->mother_phone) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Pekerjaan Ibu</label>
                        <input type="text" name="mother_job" value="{{ old('mother_job', $siswa->mother_job) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kontak Darurat (Nama)</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $siswa->emergency_contact_name) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Kakek / Paman">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">No. WA Darurat</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $siswa->emergency_contact_phone) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Hubungan Kontak Darurat</label>
                        <input type="text" name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $siswa->emergency_contact_relation) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="Paman">
                    </div>
                </div>
            </div>

            {{-- Section 3: Kesehatan & Fisik (UKS) --}}
            <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/70 space-y-3">
                <h4 class="text-xs font-bold text-red-900 uppercase tracking-wider flex items-center gap-1.5">
                    <span>🩺 Kesehatan & Fisik (UKS)</span>
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Golongan Darah</label>
                        <select name="blood_type" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                            <option value="">-- Pilih --</option>
                            <option value="A" @selected(old('blood_type', $siswa->blood_type) === 'A')>Golongan Darah A</option>
                            <option value="B" @selected(old('blood_type', $siswa->blood_type) === 'B')>Golongan Darah B</option>
                            <option value="AB" @selected(old('blood_type', $siswa->blood_type) === 'AB')>Golongan Darah AB</option>
                            <option value="O" @selected(old('blood_type', $siswa->blood_type) === 'O')>Golongan Darah O</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tinggi Badan (cm)</label>
                        <input type="number" name="height_cm" value="{{ old('height_cm', $siswa->height_cm) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="165">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Berat Badan (kg)</label>
                        <input type="number" name="weight_kg" value="{{ old('weight_kg', $siswa->weight_kg) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="55">
                    </div>
            {{-- Section D: Pendidikan & Penerimaan --}}
            <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/70 space-y-3">
                <h4 class="text-xs font-bold text-purple-900 uppercase tracking-wider flex items-center gap-1.5">
                    <span>🎓 Pendidikan Sebelumnya & Penerimaan</span>
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Pendidikan Sebelumnya (Lulusan Dari)</label>
                        <input type="text" name="prev_school_name" value="{{ old('prev_school_name', $siswa->prev_school_name) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="SMP N 1 Gianyar">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nomor STTB / Ijazah</label>
                        <input type="text" name="prev_sttb_no" value="{{ old('prev_sttb_no', $siswa->prev_sttb_no) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal STTB / Ijazah</label>
                        <input type="date" name="prev_sttb_date" value="{{ old('prev_sttb_date', $siswa->prev_sttb_date?->toDateString()) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Lama Belajar</label>
                        <input type="text" name="prev_study_duration" value="{{ old('prev_study_duration', $siswa->prev_study_duration) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100" placeholder="3 Tahun">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Pindahan Dari Sekolah</label>
                        <input type="text" name="transfer_from_school" value="{{ old('transfer_from_school', $siswa->transfer_from_school) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Alasan Pindah</label>
                        <input type="text" name="transfer_reason" value="{{ old('transfer_reason', $siswa->transfer_reason) }}" @disabled(!$canEditProfile) class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
                    </div>
                </div>
            </div>
            </div>

            @if($canEditProfile)
                <button type="submit"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm active:scale-[0.99] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Kelengkapan Data Profil</span>
                </button>
            @endif
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

        {{-- Catatan Syarat Password --}}
        <div class="mb-3 p-2.5 rounded-xl bg-amber-50/90 border border-amber-200/90 text-amber-900 text-xs">
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="leading-relaxed">
                    <p class="font-bold text-amber-950 mb-0.5">Syarat Ketentuan Password Baru:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-amber-850 font-medium">
                        <li>Panjang password <strong>minimal 8 karakter</strong>.</li>
                        <li>Password Baru & Konfirmasi harus <strong>sama persis</strong>.</li>
                        <li>Gunakan kombinasi angka atau huruf yang mudah Anda ingat.</li>
                    </ul>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('siswa.profile.password') }}" class="space-y-3">
            @csrf @method('PUT')
            
            {{-- Password Saat Ini --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Password Saat Ini</label>
                <div class="relative flex items-center rounded-xl border @error('current_password') border-red-400 bg-red-50/20 @else border-gray-300 bg-gray-50/50 @enderror focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                    <input type="password" id="siswa_current_password" name="current_password" required autocomplete="current-password"
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
                @error('current_password')
                <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Password Baru (Min. 8 Karakter)</label>
                <div class="relative flex items-center rounded-xl border @error('password') border-red-400 bg-red-50/20 @else border-gray-300 bg-gray-50/50 @enderror focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                    <input type="password" id="siswa_new_password" name="password" required autocomplete="new-password"
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
                @error('password')
                <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Konfirmasi Password Baru</label>
                <div class="relative flex items-center rounded-xl border @error('password_confirmation') border-red-400 bg-red-50/20 @else border-gray-300 bg-gray-50/50 @enderror focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                    <input type="password" id="siswa_confirm_password" name="password_confirmation" required autocomplete="new-password"
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
                @error('password_confirmation')
                <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            <button type="submit"
                class="w-full py-3 bg-linear-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-800 text-white text-sm font-extrabold rounded-xl shadow-md shadow-blue-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Simpan Password Baru</span>
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

    {{-- ─── Modal Interactive Photo Crop ──────────────────────────── --}}
    <div id="crop-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-4 py-3 bg-gray-900 text-white flex items-center justify-between">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <span>✂️ Sesuaikan & Geser Foto Profil</span>
                </h3>
                <button type="button" onclick="closeCropModal()" class="text-gray-400 hover:text-white text-lg font-bold cursor-pointer">✕</button>
            </div>

            <div class="px-3.5 py-2.5 bg-red-950/80 border-b border-red-800 text-xs text-red-100 flex items-center gap-2">
                <span class="text-base shrink-0">📌</span>
                <span><strong>Ingat:</strong> Gunakan <strong>foto resmi berpakaian sekolah (diutamakan Batik SMAN 1 Gianyar)</strong> dengan <strong>latar belakang merah</strong>.</span>
            </div>

            <div class="p-4 bg-gray-900 flex-1 overflow-hidden flex items-center justify-center min-h-[280px] max-h-[420px] relative">
                <img id="crop-image" class="max-w-full max-h-full block">
            </div>

            <div class="p-3 bg-gray-50 border-t border-gray-200 flex flex-col gap-3">
                <p class="text-[11px] text-gray-500 text-center">Gunakan mouse / sentuhan untuk menggeser & menyesuaikan posisi kepala agar tidak terpotong.</p>
                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                    <button type="button" onclick="cropper && cropper.zoom(0.1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Perbesar">🔍 +</button>
                    <button type="button" onclick="cropper && cropper.zoom(-0.1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Perkecil">🔍 -</button>
                    <button type="button" onclick="cropper && cropper.rotate(-90)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Putar Kiri">↺ 90°</button>
                    <button type="button" onclick="cropper && cropper.rotate(90)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Putar Kanan">↻ 90°</button>
                    <button type="button" onclick="cropper && cropper.reset()" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer" title="Reset">🔄 Reset</button>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-200">
                    <button type="button" onclick="closeCropModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-300 cursor-pointer">Batal</button>
                    <button type="button" id="save-crop-btn" onclick="saveCroppedPhoto()" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 shadow-md active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                        <span id="save-crop-spinner" class="hidden animate-spin">⌛</span>
                        <span>Simpan Foto Profil</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
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
            width: 600,
            height: 600,
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
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.redirected) {
                    window.location.href = res.redirected;
                } else {
                    window.location.reload();
                }
            })
            .catch(err => {
                alert('Gagal menyimpan foto: ' + err);
                if (spinner) spinner.classList.add('hidden');
                if (btn) btn.disabled = false;
            });
        }, 'image/jpeg', 0.9);
    }
    </script>

</div>
@endsection
