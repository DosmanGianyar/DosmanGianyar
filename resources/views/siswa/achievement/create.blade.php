@extends('layouts.siswa')
@section('title', 'Laporkan Prestasi & Kurasi')
@section('page-title', 'Laporkan Prestasi')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    isCuration: {{ old('is_curation', 0) ? 'true' : 'false' }},
    participationType: '{{ old('participation_type', 'individu') }}',
    studentSearch: '',
    selectedMembers: [],
    toggleMember(id, name) {
        let idx = this.selectedMembers.findIndex(m => m.id === id);
        if (idx > -1) {
            this.selectedMembers.splice(idx, 1);
        } else {
            this.selectedMembers.push({ id: id, name: name });
        }
    },
    isMemberSelected(id) {
        return this.selectedMembers.some(m => m.id === id);
    }
}">

    {{-- Banner Informasi Kurasi --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="flex items-start gap-4">
            <div class="bg-white/20 p-3 rounded-2xl shrink-0">
                <svg class="w-7 h-7 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="space-y-2">
                <h3 class="font-bold text-lg leading-tight">Pencatatan Prestasi & Kurasi Kemendikdasmen</h3>
                <p class="text-xs text-blue-100 leading-relaxed">
                    Kurasi Prestasi berfungsi untuk memvalidasi keabsahan & kualitas ajang lomba yang Anda ikuti. Prestasi yang lolos kurasi resmi dapat dipakai untuk syarat beasiswa, PPDB, hingga SNPMB.
                </p>
                <div class="pt-1">
                    <a href="{{ route('siswa.achievements.download-example', 'panduan_lengkap') }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-semibold rounded-xl text-xs transition-colors shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Buku Panduan Kurasi (PDF)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('siswa.achievements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- PILIHAN MODE PENGAJUAN --}}
        <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
            <label class="block text-sm font-bold text-gray-800">
                Pilih Tipe Pengajuan Prestasi <span class="text-red-500">*</span>
            </label>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Opsi A: Prestasi Sekolah Regular --}}
                <label @click="isCuration = false"
                    :class="!isCuration ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20' : 'border-gray-200 hover:border-gray-300 bg-white'"
                    class="relative flex flex-col p-4 border rounded-2xl cursor-pointer transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">🏆</span>
                            <span class="font-bold text-gray-900 text-sm">Prestasi Internal Sekolah</span>
                        </div>
                        <input type="radio" name="is_curation" value="0" x-model="isCuration" :value="false" class="text-blue-600 focus:ring-blue-500">
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Pencatatan cepat untuk portofolio & poin prestasi di sekolah. Tanpa persyaratan berkas kurasi Kemendikdasmen.
                    </p>
                </label>

                {{-- Opsi B: Pengajuan Kurasi Kemendikdasmen --}}
                <label @click="isCuration = true"
                    :class="isCuration ? 'border-indigo-600 bg-indigo-50/50 ring-2 ring-indigo-500/20' : 'border-gray-200 hover:border-gray-300 bg-white'"
                    class="relative flex flex-col p-4 border rounded-2xl cursor-pointer transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">🎖️</span>
                            <span class="font-bold text-gray-900 text-sm">Pengajuan Kurasi Nasional</span>
                        </div>
                        <input type="radio" name="is_curation" value="1" x-model="isCuration" :value="true" class="text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Dilengkapi 5 Poin Berkas Kurasi Kemendikdasmen untuk syarat PPDB, Beasiswa, dan SNPMB.
                    </p>
                </label>
            </div>
        </div>

        {{-- BAGIAN 1: INFORMASI UTAMA PRESTASI --}}
        <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm space-y-4">
            <h4 class="font-bold text-gray-900 text-base flex items-center gap-2 border-b border-gray-100 pb-3">
                <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center text-xs font-bold">1</span>
                Informasi Utama Prestasi
            </h4>

            {{-- Judul Prestasi --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Judul Prestasi / Lomba <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
                    placeholder="Contoh: Juara 1 Porsenijar Catur Tingkat Kabupaten Gianyar 2025"
                    class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-400 @enderror">
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Penyelenggara & Nama Lomba Spesifik --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Penyelenggara Ajang</label>
                    <input type="text" name="organizer" value="{{ old('organizer') }}" maxlength="200"
                        placeholder="Contoh: Dinas Pendidikan dan Olahraga Gianyar"
                        class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Rumpun Talenta</label>
                    <select name="field_category" class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm">
                        <option value="akademik"      {{ old('field_category') == 'akademik' ? 'selected' : '' }}>Akademik</option>
                        <option value="sains_riset"   {{ old('field_category') == 'sains_riset' ? 'selected' : '' }}>Sains & Riset</option>
                        <option value="olahraga"      {{ old('field_category') == 'olahraga' ? 'selected' : '' }}>Olahraga</option>
                        <option value="seni_budaya"   {{ old('field_category') == 'seni_budaya' ? 'selected' : '' }}>Seni & Budaya</option>
                        <option value="bahasa_debat"  {{ old('field_category') == 'bahasa_debat' ? 'selected' : '' }}>Bahasa & Debat</option>
                        <option value="keagamaan"     {{ old('field_category') == 'keagamaan' ? 'selected' : '' }}>Keagamaan</option>
                        <option value="lainnya"       {{ old('field_category') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>

            {{-- Jenis Partisipasi (Individu / Beregu) --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Jenis Lomba / Partisipasi <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer transition-all"
                        :class="participationType === 'individu' ? 'border-blue-600 bg-blue-50/50 text-blue-800 font-semibold' : 'border-gray-200 text-gray-700'">
                        <input type="radio" name="participation_type" value="individu" x-model="participationType" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-xs font-bold block">Perorangan</span>
                            <span class="text-[10px] text-gray-500 font-normal block leading-tight">Lomba Individu</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer transition-all"
                        :class="participationType === 'beregu' ? 'border-purple-600 bg-purple-50/50 text-purple-800 font-semibold' : 'border-gray-200 text-gray-700'">
                        <input type="radio" name="participation_type" value="beregu" x-model="participationType" class="text-purple-600 focus:ring-purple-500">
                        <div>
                            <span class="text-xs font-bold block">Beregu / Tim</span>
                            <span class="text-[10px] text-gray-500 font-normal block leading-tight">Lomba Kelompok</span>
                        </div>
                    </label>
                </div>
                @error('participation_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Pilihan Anggota Tim (Jika Beregu) --}}
            <div x-show="participationType === 'beregu'" x-transition class="p-3.5 border border-purple-200 bg-purple-50/30 rounded-2xl space-y-3">
                <div>
                    <label class="block text-xs font-bold text-purple-900 uppercase tracking-wider mb-1">
                        👥 Pilih Anggota Tim (Siswa Lain)
                    </label>
                    <p class="text-xs text-purple-700 leading-snug">
                        Pilih teman se-tim kamu. Data prestasi akan <strong>otomatis didaftarkan</strong> ke akun seluruh anggota tim yang dipilih.
                    </p>
                </div>

                {{-- Ringkasan Anggota Terpilih --}}
                <div x-show="selectedMembers.length > 0" class="flex flex-wrap gap-1.5 pt-1">
                    <template x-for="m in selectedMembers" :key="m.id">
                        <span class="inline-flex items-center gap-1 bg-purple-600 text-white text-xs font-semibold px-2.5 py-1 rounded-lg">
                            <span x-text="m.name"></span>
                            <button type="button" @click="toggleMember(m.id, m.name)" class="hover:text-purple-200 ml-0.5">
                                &times;
                            </button>
                            <input type="hidden" name="team_member_ids[]" :value="m.id">
                        </span>
                    </template>
                </div>

                {{-- Input Pencarian Siswa --}}
                <div>
                    <input type="text" x-model="studentSearch" placeholder="Cari nama teman atau kelas..."
                        class="w-full border border-purple-300 rounded-xl px-3 py-2 text-xs bg-white focus:ring-purple-500 focus:border-purple-500">
                </div>

                {{-- Daftar Siswa --}}
                @if(!empty($students))
                <div class="max-h-48 overflow-y-auto border border-purple-200 rounded-xl bg-white divide-y divide-gray-100">
                    @foreach($students as $s)
                        <div x-show="!studentSearch || '{{ strtolower($s->name . ' ' . ($s->schoolClass?->name ?? '')) }}'.includes(studentSearch.toLowerCase())"
                            @click="toggleMember({{ $s->id }}, '{{ addslashes($s->name) }}')"
                            class="flex items-center justify-between p-2.5 cursor-pointer hover:bg-purple-50 transition-colors text-xs"
                            :class="isMemberSelected({{ $s->id }}) ? 'bg-purple-50/80 font-semibold' : ''">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" :checked="isMemberSelected({{ $s->id }})" class="rounded text-purple-600 focus:ring-purple-500">
                                <div>
                                    <p class="text-gray-800 font-medium leading-tight">{{ $s->name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $s->schoolClass?->name ?? '—' }} @if($s->nisn) · {{ $s->nisn }} @endif</p>
                                </div>
                            </div>
                            <span x-show="isMemberSelected({{ $s->id }})" class="text-[10px] text-purple-700 font-bold bg-purple-200/60 px-1.5 py-0.5 rounded">Terpilih</span>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Kategori & Tingkat --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Kategori Internal <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm">
                        <option value="">— Pilih Kategori —</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tingkat Ajang <span class="text-red-500">*</span></label>
                    <select name="level" required class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm">
                        <option value="">— Pilih Tingkat —</option>
                        <option value="sekolah"       {{ old('level') == 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                        <option value="kabupaten"     {{ old('level') == 'kabupaten' ? 'selected' : '' }}>Kabupaten/Kota</option>
                        <option value="provinsi"      {{ old('level') == 'provinsi' ? 'selected' : '' }}>Provinsi</option>
                        <option value="nasional"      {{ old('level') == 'nasional' ? 'selected' : '' }}>Nasional</option>
                        <option value="internasional" {{ old('level') == 'internasional' ? 'selected' : '' }}>Internasional</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Peringkat / Capaian</label>
                    <input type="text" name="rank" value="{{ old('rank') }}" placeholder="Juara 1, Juara 2, Finalis..."
                        class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm">
                </div>
            </div>

            {{-- Tanggal & Deskripsi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
                    <input type="date" name="achievement_date" value="{{ old('achievement_date') }}" max="{{ today()->toDateString() }}" required
                        class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Deskripsi Singkat</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Keterangan tambahan kegiatan..."
                        class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm">
                </div>
            </div>

            {{-- Upload Foto & Scan Piagam Dasar --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Foto Kegiatan / Penyerahan <span class="text-red-500">*</span></label>
                    <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-2xl p-4 cursor-pointer hover:border-blue-500 transition-colors">
                        <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span id="photo-name" class="text-xs text-gray-600 font-medium">Upload Foto (JPG/PNG, Maks 5MB)</span>
                        <input type="file" name="photo" accept="image/*" required class="hidden" onchange="document.getElementById('photo-name').textContent = this.files[0]?.name ?? 'Upload Foto'">
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Scan Piagam / Sertifikat</label>
                    <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-2xl p-4 cursor-pointer hover:border-blue-500 transition-colors">
                        <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span id="cert-name" class="text-xs text-gray-600 font-medium">Upload Piagam (PDF/JPG, Maks 10MB)</span>
                        <input type="file" name="certificate" accept=".pdf,image/*" class="hidden" onchange="document.getElementById('cert-name').textContent = this.files[0]?.name ?? 'Upload Piagam'">
                    </label>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: 5 POIN KURASI KEMENDIKDASMEN (HANYA AKTIF JIKA KURASI DILIHAT) --}}
        <div x-show="isCuration" x-collapse class="space-y-6">

            <div class="bg-indigo-900 text-white p-4 rounded-2xl flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-sm">Form Pengisian 5 Poin Kurasi Kemendikdasmen</h4>
                    <p class="text-xs text-indigo-200">Lengkapi data berikut untuk membantu verifikator menilai keabsahan ajang lomba.</p>
                </div>
                <span class="text-xs font-semibold bg-indigo-700 px-3 py-1 rounded-full border border-indigo-500">Standar Kemendikdasmen</span>
            </div>

            {{-- POIN 1 --}}
            <div class="bg-white border border-indigo-100 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-start justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center text-xs font-bold">P1</span>
                            Dokumen Standar Penyelenggaraan Cabang Ajang
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            💡 <strong>Penjelasan Siswa:</strong> Upload juknis/buku panduan resmi lomba yang memuat aturan dan kriteria penilaian.
                        </p>
                    </div>
                    <a href="{{ route('siswa.achievements.download-example', 'poin1') }}" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-semibold bg-indigo-50 px-2.5 py-1.5 rounded-lg border border-indigo-200">
                        📥 Download Contoh Juknis P1
                    </a>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Checklist Unsur yang Memuat dalam Berkas Juknis:</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="doc_standard_checklist[]" value="visi_misi" class="rounded text-indigo-600">
                            <span>Visi dan Misi</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="doc_standard_checklist[]" value="tujuan" class="rounded text-indigo-600">
                            <span>Tujuan Lomba</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="doc_standard_checklist[]" value="prosedur" class="rounded text-indigo-600">
                            <span>Prosedur Penyelenggaraan</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="doc_standard_checklist[]" value="kriteria_penilaian" class="rounded text-indigo-600">
                            <span>Kriteria Penilaian</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">File Dokumen Juknis/Pedoman (PDF/DOCX)</label>
                        <input type="file" name="doc_standard_file" accept=".pdf,.doc,.docx,image/*" class="w-full text-xs border rounded-xl p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tautan/URL Dokumen (Jika Ada Website Resmi)</label>
                        <input type="url" name="doc_standard_url" placeholder="https://..." class="w-full border rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            {{-- POIN 2 --}}
            <div class="bg-white border border-indigo-100 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-start justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center text-xs font-bold">P2</span>
                            Tingkatan Seleksi Ajang Kompetensi Talenta
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            💡 <strong>Penjelasan Siswa:</strong> Pilih berapa banyak tahapan seleksi yang Anda lalui dalam ajang ini.
                        </p>
                    </div>
                    <a href="{{ route('siswa.achievements.download-example', 'poin2') }}" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-semibold bg-indigo-50 px-2.5 py-1.5 rounded-lg border border-indigo-200">
                        📥 Download Contoh P2
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    <label class="flex items-center gap-2 border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="selection_level" value="3_tingkat" class="text-indigo-600">
                        <div>
                            <span class="font-bold block">≥3 Tingkat</span>
                            <span class="text-gray-400 text-[10px]">Kab/Kota → Prov → Nasional</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="selection_level" value="2_tingkat" class="text-indigo-600">
                        <div>
                            <span class="font-bold block">2 Tingkat</span>
                            <span class="text-gray-400 text-[10px]">Provinsi → Nasional</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="selection_level" value="1_tingkat" class="text-indigo-600">
                        <div>
                            <span class="font-bold block">1 Tingkat</span>
                            <span class="text-gray-400 text-[10px]">Langsung Final / Terbuka</span>
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Upload Berkas Bukti Seleksi (PDF/JPG)</label>
                        <input type="file" name="selection_level_file" accept=".pdf,image/*" class="w-full text-xs border rounded-xl p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tautan/URL Bukti Tahapan Seleksi</label>
                        <input type="url" name="selection_level_url" placeholder="https://..." class="w-full border rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            {{-- POIN 3 --}}
            <div class="bg-white border border-indigo-100 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-start justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center text-xs font-bold">P3</span>
                            Konsistensi Frekuensi Penyelenggaraan Lomba
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            💡 <strong>Penjelasan Siswa:</strong> Berapa kali perlombaan ini diselenggarakan secara rutin tahunan.
                        </p>
                    </div>
                    <a href="{{ route('siswa.achievements.download-example', 'poin3') }}" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-semibold bg-indigo-50 px-2.5 py-1.5 rounded-lg border border-indigo-200">
                        📥 Download Contoh Berkas P3 (DOCX)
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    <label class="flex flex-col border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <div class="flex items-center gap-1.5 mb-1">
                            <input type="radio" name="frequency_consistency" value="berturut_gt3" class="text-indigo-600">
                            <span class="font-bold">Berturut-turut >3 Kali</span>
                        </div>
                        <span class="text-gray-400 text-[10px]">Perlu juknis 4+ tahun berturut</span>
                    </label>
                    <label class="flex flex-col border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <div class="flex items-center gap-1.5 mb-1">
                            <input type="radio" name="frequency_consistency" value="berturut_3" class="text-indigo-600">
                            <span class="font-bold">Berturut 3 Kali</span>
                        </div>
                        <span class="text-gray-400 text-[10px]">Perlu juknis 3 tahun</span>
                    </label>
                    <label class="flex flex-col border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <div class="flex items-center gap-1.5 mb-1">
                            <input type="radio" name="frequency_consistency" value="berturut_2" class="text-indigo-600">
                            <span class="font-bold">Berturut 2 Kali</span>
                        </div>
                        <span class="text-gray-400 text-[10px]">Perlu juknis 2 tahun</span>
                    </label>
                    <label class="flex flex-col border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <div class="flex items-center gap-1.5 mb-1">
                            <input type="radio" name="frequency_consistency" value="tidak_berturut" class="text-indigo-600">
                            <span class="font-bold">Tidak Berturut-turut</span>
                        </div>
                        <span class="text-gray-400 text-[10px]">Ajang baru/eksperimental</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Upload Berkas Juknis Lintas Tahun (DOCX/PDF/ZIP)</label>
                        <input type="file" name="frequency_consistency_file" accept=".pdf,.doc,.docx,.zip,image/*" class="w-full text-xs border rounded-xl p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tautan Bukti Riwayat Lomba</label>
                        <input type="url" name="frequency_consistency_url" placeholder="https://..." class="w-full border rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            {{-- POIN 4 --}}
            <div class="bg-white border border-indigo-100 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-start justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center text-xs font-bold">P4</span>
                            Sarana dan Prasarana Ajang Kompetensi Talenta
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            💡 <strong>Penjelasan Siswa:</strong> Foto/dokumentasi tempat & peralatan yang digunakan saat perlombaan.
                        </p>
                    </div>
                    <a href="{{ route('siswa.achievements.download-example', 'poin4') }}" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-semibold bg-indigo-50 px-2.5 py-1.5 rounded-lg border border-indigo-200">
                        📥 Download Contoh Foto P4
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    <label class="flex items-center gap-2 border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="infrastructure_type" value="utama_pendukung" class="text-indigo-600">
                        <span>Sarana Utama & Pendukung Lengkap</span>
                    </label>
                    <label class="flex items-center gap-2 border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="infrastructure_type" value="utama" class="text-indigo-600">
                        <span>Sarana Utama Saja</span>
                    </label>
                    <label class="flex items-center gap-2 border p-3 rounded-xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="infrastructure_type" value="pendukung" class="text-indigo-600">
                        <span>Sarana Pendukung Saja</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Upload Dokumentasi Foto/Video/Pedoman Sarpras (JPG/PNG/PDF)</label>
                    <input type="file" name="infrastructure_file" accept=".pdf,image/*" class="w-full text-xs border rounded-xl p-2">
                </div>
            </div>

            {{-- POIN 5 --}}
            <div class="bg-white border border-indigo-100 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-start justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center text-xs font-bold">P5</span>
                            Penghargaan dan Apresiasi yang Disediakan
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            💡 <strong>Penjelasan Siswa:</strong> Upload bukti piagam, rekap SK pemenang, & foto penerimaan di panggung.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('siswa.achievements.download-example', 'poin5_piagam') }}" class="inline-flex items-center gap-1 text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold bg-indigo-50 px-2 py-1 rounded-lg border border-indigo-200">
                            📥 Contoh Piagam
                        </a>
                        <a href="{{ route('siswa.achievements.download-example', 'poin5_rekap') }}" class="inline-flex items-center gap-1 text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold bg-indigo-50 px-2 py-1 rounded-lg border border-indigo-200">
                            📥 Contoh Rekap
                        </a>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Checklist Bentuk Apresiasi yang Diterima:</label>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-xs">
                        <label class="flex items-center gap-1.5 border p-2 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="sertifikat" class="rounded text-indigo-600">
                            <span>Sertifikat/Trophy</span>
                        </label>
                        <label class="flex items-center gap-1.5 border p-2 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="uang_tunai" class="rounded text-indigo-600">
                            <span>Uang Tunai</span>
                        </label>
                        <label class="flex items-center gap-1.5 border p-2 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="beasiswa" class="rounded text-indigo-600">
                            <span>Beasiswa</span>
                        </label>
                        <label class="flex items-center gap-1.5 border p-2 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="pembinaan" class="rounded text-indigo-600">
                            <span>Pembinaan</span>
                        </label>
                        <label class="flex items-center gap-1.5 border p-2 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="lainnya" class="rounded text-indigo-600">
                            <span>Lainnya</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Scan Piagam Resmi (PDF/JPG)</label>
                        <input type="file" name="reward_certificate_file" accept=".pdf,image/*" class="w-full text-xs border rounded-xl p-2">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Foto Penyerahan Hadiah (JPG/PNG)</label>
                        <input type="file" name="reward_photo_file" accept="image/*" class="w-full text-xs border rounded-xl p-2">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Dokumen Rekap Pemenang SK (PDF)</label>
                        <input type="file" name="reward_recap_file" accept=".pdf,.doc,.docx,image/*" class="w-full text-xs border rounded-xl p-2">
                    </div>
                </div>
            </div>

        </div>

        {{-- TOMBOL SUBMIT --}}
        <div class="pt-2">
            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 rounded-2xl text-base shadow-lg transition-all transform active:scale-[0.99] flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="isCuration ? 'Kirim Laporan Prestasi & Kurasi Kemendikdasmen' : 'Kirim Laporan Prestasi Sekolah'">Kirim Laporan Prestasi</span>
            </button>

            <a href="{{ route('siswa.achievements.index') }}" class="block text-center text-xs text-gray-500 mt-3 py-1 hover:underline">
                Batal & Kembali ke Daftar Prestasi
            </a>
        </div>

    </form>
</div>
@endsection
