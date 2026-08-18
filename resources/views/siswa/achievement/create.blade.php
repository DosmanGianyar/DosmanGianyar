@extends('layouts.siswa')
@section('title', 'Laporkan Prestasi & Kurasi')
@section('page-title', 'Laporkan Prestasi & Kurasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    isCuration: {{ old('is_curation', 0) == 1 ? 1 : 0 }},
    participationType: '{{ old('participation_type', 'individu') }}',
    studentSearch: '',
    selectedMembers: {{ json_encode(old('team_member_ids', [])) }},
    allStudents: {{ json_encode($students->map(fn($s) => [
        'id' => $s->id,
        'name' => $s->name,
        'nisn' => $s->nisn ?: '—',
        'class' => $s->schoolClass?->name ?? '—'
    ])->values()) }},
    toggleMember(student) {
        let idx = this.selectedMembers.findIndex(id => id == student.id);
        if (idx > -1) {
            this.selectedMembers.splice(idx, 1);
        } else {
            this.selectedMembers.push(student.id);
        }
    },
    isMemberSelected(id) {
        return this.selectedMembers.some(mId => mId == id);
    },
    getSelectedStudentNames() {
        return this.allStudents.filter(s => this.selectedMembers.includes(s.id));
    }
}">

    {{-- Banner Informasi Kurasi Kemendikdasmen --}}
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 relative z-10">
            <div class="flex items-start gap-4">
                <div class="bg-white/20 p-3.5 rounded-2xl shrink-0 backdrop-blur-md border border-white/20">
                    <svg class="w-8 h-8 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <span class="bg-yellow-400 text-gray-900 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                        SISTEM KURASI TALENTA SISWA
                    </span>
                    <h3 class="font-black text-xl leading-tight">Pencatatan & Kurasi Prestasi Puspresnas</h3>
                    <p class="text-xs text-blue-100 leading-relaxed max-w-2xl">
                        Pilih apakah Anda hanya ingin melaporkan prestasi untuk poin internal sekolah, atau sekaligus melengkapi <strong>5 Berkas Persyaratan Kurasi Resmi Kemendikdasmen</strong> untuk beasiswa, PPDB, & SNPMB.
                    </p>
                </div>
            </div>
            <a href="{{ route('siswa.achievements.download-example', 'panduan_lengkap') }}" target="_blank"
                class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-bold rounded-2xl text-xs transition shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Buku Panduan Kurasi (PDF)
            </a>
        </div>
    </div>

    <form action="{{ route('siswa.achievements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- ─── PILIHAN UTAMA: MODE PENGAJUAN (HANYA LAPOR VS APPORVE KURASI) ──────── --}}
        <div class="bg-white border-2 border-indigo-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h4 class="font-extrabold text-gray-900 text-base">Pilih Tujuan Pengajuan Prestasi</h4>
                    <p class="text-xs text-gray-500">Tentukan apakah ingin mengkurasi prestasi ke tingkat nasional atau pencatatan sekolah biasa.</p>
                </div>
                <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full">Langkah 1 dari 2</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Opsi A: Hanya Laporkan Prestasi Internal Sekolah --}}
                <div @click="isCuration = 0"
                    :class="isCuration === 0 ? 'border-blue-600 bg-blue-50/60 ring-2 ring-blue-500/30' : 'border-gray-200 hover:border-gray-300 bg-white'"
                    class="relative flex flex-col p-5 border-2 rounded-2xl cursor-pointer transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-xl">🏆</div>
                            <div>
                                <h5 class="font-extrabold text-gray-900 text-sm">1. Hanya Laporkan Prestasi</h5>
                                <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">Portofolio Internal</span>
                            </div>
                        </div>
                        <input type="radio" name="is_curation" value="0" x-model.number="isCuration" :checked="isCuration === 0" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Pencatatan prestasi cepat untuk pengumpulan poin nilai kesiswaan & portofolio sekolah. <strong>Tanpa syarat pengisian 5 berkas kurasi.</strong>
                    </p>
                </div>

                {{-- Opsi B: Melaporkan + Pengajuan Kurasi Kemendikdasmen --}}
                <div @click="isCuration = 1"
                    :class="isCuration === 1 ? 'border-purple-600 bg-purple-50/60 ring-2 ring-purple-500/30' : 'border-gray-200 hover:border-gray-300 bg-white'"
                    class="relative flex flex-col p-5 border-2 rounded-2xl cursor-pointer transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-xl">🎖️</div>
                            <div>
                                <h5 class="font-extrabold text-gray-900 text-sm">2. Laporkan & Ajukan Kurasi</h5>
                                <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">Kemendikdasmen / BTIKP</span>
                            </div>
                        </div>
                        <input type="radio" name="is_curation" value="1" x-model.number="isCuration" :checked="isCuration === 1" class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Mengunggah <strong>5 Poin Berkas Persyaratan Kurasi Resmi</strong> untuk memvalidasi keabsahan ajang lomba bagi syarat PPDB, Beasiswa, & SNPMB.
                    </p>
                </div>
            </div>
        </div>

        {{-- ─── BAGIAN 1: INFORMASI UTAMA PRESTASI ────────────────────────────── --}}
        <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow-sm space-y-4">
            <h4 class="font-extrabold text-gray-900 text-base flex items-center gap-2 border-b border-gray-100 pb-3">
                <span class="w-7 h-7 bg-blue-600 text-white rounded-xl flex items-center justify-center text-xs font-bold">1</span>
                Informasi Utama Prestasi & Perlombaan
            </h4>

            {{-- Judul Prestasi --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Judul Capaian / Nama Lomba <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
                    placeholder="Contoh: Juara 1 Porsenijar Catur Tingkat Kabupaten Gianyar 2026"
                    class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-400 @enderror">
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Penyelenggara & Rumpun Talenta --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Penyelenggara Lomba / Ajang</label>
                    <input type="text" name="organizer" value="{{ old('organizer') }}" maxlength="200"
                        placeholder="Contoh: Disdikpora Gianyar / Kemendikbudristek / KONI"
                        class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Rumpun Talenta <span class="text-red-500">*</span></label>
                    <select name="field_category" required class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs font-bold">
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

            {{-- Jenis Partisipasi (Individu vs Beregu) --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Jenis Lomba / Partisipasi <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-3.5 border-2 rounded-2xl cursor-pointer transition-all"
                        :class="participationType === 'individu' ? 'border-blue-600 bg-blue-50/70 text-blue-900 font-bold' : 'border-gray-200 text-gray-700'">
                        <input type="radio" name="participation_type" value="individu" x-model="participationType" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-xs font-extrabold block">Perorangan (Individu)</span>
                            <span class="text-[10px] text-gray-500 font-normal">Diikuti secara mandiri</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3.5 border-2 rounded-2xl cursor-pointer transition-all"
                        :class="participationType === 'beregu' ? 'border-purple-600 bg-purple-50/70 text-purple-900 font-bold' : 'border-gray-200 text-gray-700'">
                        <input type="radio" name="participation_type" value="beregu" x-model="participationType" class="text-purple-600 focus:ring-purple-500">
                        <div>
                            <span class="text-xs font-extrabold block">Beregu / Tim (Kelompok)</span>
                            <span class="text-[10px] text-gray-500 font-normal">Diikuti bersama tim</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 👥 PILIHAN ANGGOTA TIM (JIKA BEREGU) ─── --}}
            <div x-show="participationType === 'beregu'" x-transition class="p-4 border-2 border-purple-200 bg-purple-50/40 rounded-2xl space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-extrabold text-purple-900 uppercase tracking-wider">
                            👥 Pilih Anggota Tim (Siswa Lain SMAN 1 Gianyar)
                        </label>
                        <p class="text-[11px] text-purple-700 mt-0.5">
                            Cari dan pilih teman se-tim Anda. Prestasi ini akan <strong>otomatis tercatat</strong> pada akun seluruh anggota tim yang dipilih.
                        </p>
                    </div>
                    <span class="text-xs font-bold bg-purple-200 text-purple-900 px-2.5 py-1 rounded-full" x-text="selectedMembers.length + ' Anggota Terpilih'"></span>
                </div>

                {{-- Badges Terpilih --}}
                <div class="flex flex-wrap gap-1.5 pt-1" x-show="selectedMembers.length > 0">
                    <template x-for="st in getSelectedStudentNames()" :key="st.id">
                        <span class="inline-flex items-center gap-1.5 bg-purple-700 text-white text-xs font-bold px-3 py-1 rounded-xl shadow-sm">
                            <span x-text="st.name + ' (' + st.class + ')'"></span>
                            <button type="button" @click="toggleMember(st)" class="hover:text-purple-200 ml-1 text-sm">&times;</button>
                            <input type="hidden" name="team_member_ids[]" :value="st.id">
                        </span>
                    </template>
                </div>

                {{-- Search Filter Input --}}
                <div class="relative">
                    <input type="text" x-model="studentSearch" placeholder="Ketik nama teman atau kelas (contoh: XI MIPA 1)..."
                        class="w-full border border-purple-300 rounded-xl px-3.5 py-2 text-xs bg-white focus:ring-2 focus:ring-purple-500">
                </div>

                {{-- List Siswa Scrollable --}}
                <div class="max-h-52 overflow-y-auto border border-purple-200 rounded-xl bg-white divide-y divide-gray-100 shadow-inner">
                    <template x-for="s in allStudents.filter(st => !studentSearch || (st.name + ' ' + st.class + ' ' + st.nisn).toLowerCase().includes(studentSearch.toLowerCase()))" :key="s.id">
                        <div @click="toggleMember(s)"
                            class="flex items-center justify-between p-2.5 cursor-pointer hover:bg-purple-50 transition text-xs"
                            :class="isMemberSelected(s.id) ? 'bg-purple-100/70 font-bold text-purple-900' : 'text-gray-700'">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" :checked="isMemberSelected(s.id)" class="rounded text-purple-600 focus:ring-purple-500">
                                <div>
                                    <p class="font-bold text-xs" x-text="s.name"></p>
                                    <p class="text-[10px] text-gray-500" x-text="s.class + ' · NISN: ' + s.nisn"></p>
                                </div>
                            </div>
                            <span x-show="isMemberSelected(s.id)" class="text-[10px] font-extrabold text-purple-700 bg-purple-200 px-2 py-0.5 rounded-md">Terpilih</span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Kategori & Tingkat --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Kategori Internal <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs font-bold">
                        <option value="">— Pilih Kategori —</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tingkat Ajang <span class="text-red-500">*</span></label>
                    <select name="level" required class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs font-bold">
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
                        class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs font-medium">
                </div>
            </div>

            {{-- Tanggal & Deskripsi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
                    <input type="date" name="achievement_date" value="{{ old('achievement_date') }}" max="{{ today()->toDateString() }}" required
                        class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Deskripsi Singkat</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Keterangan tambahan..."
                        class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs font-medium">
                </div>
            </div>

            {{-- Upload Foto Kegiatan Wajib & Certificate --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Foto Kegiatan / Penyerahan Piala <span class="text-red-500">*</span></label>
                    <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-2xl p-4 cursor-pointer hover:border-blue-500 transition-colors bg-gray-50/50">
                        <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span id="photo-name" class="text-xs text-gray-600 font-bold">Upload Foto Fisik Lomba (Wajib, Maks 5MB)</span>
                        <input type="file" name="photo" accept="image/*" required class="hidden" onchange="document.getElementById('photo-name').textContent = this.files[0]?.name ?? 'Upload Foto'">
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Scan Piagam / Sertifikat (Opsional)</label>
                    <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-2xl p-4 cursor-pointer hover:border-blue-500 transition-colors bg-gray-50/50">
                        <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span id="cert-name" class="text-xs text-gray-600 font-bold">Upload Scan Piagam (PDF/JPG, Maks 10MB)</span>
                        <input type="file" name="certificate" accept=".pdf,image/*" class="hidden" onchange="document.getElementById('cert-name').textContent = this.files[0]?.name ?? 'Upload Piagam'">
                    </label>
                </div>
            </div>
        </div>

        {{-- ─── BAGIAN 2: 5 POIN PERSYARATAN KURASI KEMENDIKDASMEN ───────────── --}}
        <div x-show="isCuration === 1" x-collapse class="space-y-6">

            <div class="bg-gradient-to-r from-indigo-900 to-purple-900 text-white p-5 rounded-3xl shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <div>
                    <h4 class="font-black text-base">5 Poin Persyaratan Kurasi Kemendikdasmen / Puspresnas</h4>
                    <p class="text-xs text-indigo-200">Lengkapi 5 kelompok dokumen di bawah sesuai panduan resmi agar ajang lomba lolos kurasi.</p>
                </div>
                <span class="text-xs font-extrabold bg-yellow-400 text-gray-900 px-3 py-1 rounded-full shrink-0">Standar Resmi Kurasi</span>
            </div>

            {{-- POIN 1 --}}
            <div class="bg-white border border-indigo-200 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-3 gap-2">
                    <div>
                        <h4 class="font-extrabold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center text-xs font-bold">P1</span>
                            Dokumen Standar Penyelenggaraan Cabang Ajang
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            💡 <strong>Pedoman:</strong> Juknis/Buku Panduan resmi lomba yang memuat aturan dan kriteria penilaian.
                        </p>
                    </div>
                    <a href="{{ route('siswa.achievements.download-example', 'poin1') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-indigo-700 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-200">
                        📥 Download Contoh Juknis P1
                    </a>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Checklist Unsur yang Memuat dalam Berkas Juknis:</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="doc_standard_checklist[]" value="visi_misi" class="rounded text-indigo-600">
                            <span class="font-medium">Visi dan Misi</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="doc_standard_checklist[]" value="tujuan" class="rounded text-indigo-600">
                            <span class="font-medium">Tujuan Lomba</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="doc_standard_checklist[]" value="prosedur" class="rounded text-indigo-600">
                            <span class="font-medium">Prosedur Penyelenggaraan</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="doc_standard_checklist[]" value="kriteria_penilaian" class="rounded text-indigo-600">
                            <span class="font-medium">Kriteria Penilaian</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">File Dokumen Juknis/Pedoman (PDF/DOCX)</label>
                        <input type="file" name="doc_standard_file" accept=".pdf,.doc,.docx,image/*" class="w-full text-xs border rounded-xl p-2 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tautan/URL Dokumen (Jika Ada Website Resmi)</label>
                        <input type="url" name="doc_standard_url" placeholder="https://..." class="w-full border rounded-xl px-3.5 py-2 text-xs font-medium">
                    </div>
                </div>
            </div>

            {{-- POIN 2 --}}
            <div class="bg-white border border-indigo-200 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-3 gap-2">
                    <div>
                        <h4 class="font-extrabold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center text-xs font-bold">P2</span>
                            Tingkatan Seleksi Ajang Kompetensi Talenta
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            💡 <strong>Pedoman:</strong> Pilih jumlah tahapan seleksi yang Anda lalui dalam perlombaan ini.
                        </p>
                    </div>
                    <a href="{{ route('siswa.achievements.download-example', 'poin2') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-indigo-700 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-200">
                        📥 Download Contoh Bukti P2
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    <label class="flex items-start gap-2.5 border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="selection_level" value="3_tingkat" class="text-indigo-600 mt-0.5">
                        <div>
                            <span class="font-extrabold block text-gray-900">≥3 Tingkat Seleksi</span>
                            <span class="text-gray-500 text-[11px]">Kab/Kota → Provinsi → Nasional</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-2.5 border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="selection_level" value="2_tingkat" class="text-indigo-600 mt-0.5">
                        <div>
                            <span class="font-extrabold block text-gray-900">2 Tingkat Seleksi</span>
                            <span class="text-gray-500 text-[11px]">Provinsi → Nasional</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-2.5 border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="selection_level" value="1_tingkat" class="text-indigo-600 mt-0.5">
                        <div>
                            <span class="font-extrabold block text-gray-900">1 Tingkat Seleksi</span>
                            <span class="text-gray-500 text-[11px]">Langsung Final / Terbuka</span>
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Upload Berkas Bukti Tahapan Seleksi (PDF/JPG)</label>
                        <input type="file" name="selection_level_file" accept=".pdf,image/*" class="w-full text-xs border rounded-xl p-2 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tautan/URL Bukti Tahapan Seleksi</label>
                        <input type="url" name="selection_level_url" placeholder="https://..." class="w-full border rounded-xl px-3.5 py-2 text-xs font-medium">
                    </div>
                </div>
            </div>

            {{-- POIN 3 --}}
            <div class="bg-white border border-indigo-200 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-3 gap-2">
                    <div>
                        <h4 class="font-extrabold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center text-xs font-bold">P3</span>
                            Konsistensi Frekuensi Penyelenggaraan Lomba
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            💡 <strong>Pedoman:</strong> Rutinitas penyelenggaraan ajang lomba lintas tahun.
                        </p>
                    </div>
                    <a href="{{ route('siswa.achievements.download-example', 'poin3') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-indigo-700 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-200">
                        📥 Download Contoh Berkas P3 (DOCX)
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    <label class="flex flex-col border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="frequency_consistency" value="berturut_gt3" class="text-indigo-600">
                            <span class="font-extrabold text-gray-900">Berturut >3 Kali</span>
                        </div>
                        <span class="text-gray-500 text-[10px]">Ada juknis 4+ tahun berturut</span>
                    </label>
                    <label class="flex flex-col border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="frequency_consistency" value="berturut_3" class="text-indigo-600">
                            <span class="font-extrabold text-gray-900">Berturut 3 Kali</span>
                        </div>
                        <span class="text-gray-500 text-[10px]">Ada juknis 3 tahun</span>
                    </label>
                    <label class="flex flex-col border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="frequency_consistency" value="berturut_2" class="text-indigo-600">
                            <span class="font-extrabold text-gray-900">Berturut 2 Kali</span>
                        </div>
                        <span class="text-gray-500 text-[10px]">Ada juknis 2 tahun</span>
                    </label>
                    <label class="flex flex-col border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="frequency_consistency" value="tidak_berturut" class="text-indigo-600">
                            <span class="font-extrabold text-gray-900">Tidak Berturut</span>
                        </div>
                        <span class="text-gray-500 text-[10px]">Ajang baru/eksperimental</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Upload Berkas Juknis Lintas Tahun (DOCX/PDF/ZIP)</label>
                        <input type="file" name="frequency_consistency_file" accept=".pdf,.doc,.docx,.zip,image/*" class="w-full text-xs border rounded-xl p-2 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tautan Bukti Riwayat Lomba</label>
                        <input type="url" name="frequency_consistency_url" placeholder="https://..." class="w-full border rounded-xl px-3.5 py-2 text-xs font-medium">
                    </div>
                </div>
            </div>

            {{-- POIN 4 --}}
            <div class="bg-white border border-indigo-200 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-3 gap-2">
                    <div>
                        <h4 class="font-extrabold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center text-xs font-bold">P4</span>
                            Sarana dan Prasarana Ajang Kompetensi Talenta
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            💡 <strong>Pedoman:</strong> Foto/dokumentasi fisik tempat & peralatan saat ajang berlangsung.
                        </p>
                    </div>
                    <a href="{{ route('siswa.achievements.download-example', 'poin4') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-indigo-700 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-200">
                        📥 Download Contoh Foto P4
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    <label class="flex items-center gap-2 border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="infrastructure_type" value="utama_pendukung" class="text-indigo-600">
                        <span class="font-extrabold text-gray-900">Utama & Pendukung Lengkap</span>
                    </label>
                    <label class="flex items-center gap-2 border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="infrastructure_type" value="utama" class="text-indigo-600">
                        <span class="font-extrabold text-gray-900">Sarana Utama Saja</span>
                    </label>
                    <label class="flex items-center gap-2 border-2 p-3 rounded-2xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="infrastructure_type" value="pendukung" class="text-indigo-600">
                        <span class="font-extrabold text-gray-900">Sarana Pendukung Saja</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Upload Foto/Dokumentasi Sarpras (JPG/PNG/PDF)</label>
                    <input type="file" name="infrastructure_file" accept=".pdf,image/*" class="w-full text-xs border rounded-xl p-2 bg-gray-50">
                </div>
            </div>

            {{-- POIN 5 --}}
            <div class="bg-white border border-indigo-200 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-3 gap-2">
                    <div>
                        <h4 class="font-extrabold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center text-xs font-bold">P5</span>
                            Penghargaan dan Apresiasi yang Disediakan
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            💡 <strong>Pedoman:</strong> Bukti piagam, SK rekap pemenang, dan foto penerimaan di panggung.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('siswa.achievements.download-example', 'poin5_piagam') }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-700 font-bold bg-indigo-50 px-2.5 py-1.5 rounded-xl border border-indigo-200">
                            📥 Contoh Piagam
                        </a>
                        <a href="{{ route('siswa.achievements.download-example', 'poin5_rekap') }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-700 font-bold bg-indigo-50 px-2.5 py-1.5 rounded-xl border border-indigo-200">
                            📥 Contoh SK Rekap
                        </a>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Checklist Bentuk Apresiasi yang Diterima:</label>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-xs">
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="sertifikat" class="rounded text-indigo-600">
                            <span class="font-medium">Sertifikat/Trophy</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="uang_tunai" class="rounded text-indigo-600">
                            <span class="font-medium">Uang Tunai</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="beasiswa" class="rounded text-indigo-600">
                            <span class="font-medium">Beasiswa</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="pembinaan" class="rounded text-indigo-600">
                            <span class="font-medium">Pembinaan</span>
                        </label>
                        <label class="flex items-center gap-2 border p-2.5 rounded-xl cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="reward_types[]" value="lainnya" class="rounded text-indigo-600">
                            <span class="font-medium">Lainnya</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Scan Piagam Resmi (PDF/JPG)</label>
                        <input type="file" name="reward_certificate_file" accept=".pdf,image/*" class="w-full text-xs border rounded-xl p-2 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Foto Panggung Juara (JPG/PNG)</label>
                        <input type="file" name="reward_photo_file" accept="image/*" class="w-full text-xs border rounded-xl p-2 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Dokumen Rekap SK Pemenang (PDF)</label>
                        <input type="file" name="reward_recap_file" accept=".pdf,.doc,.docx,image/*" class="w-full text-xs border rounded-xl p-2 bg-gray-50">
                    </div>
                </div>
            </div>

        </div>

        {{-- ─── TOMBOL SUBMIT PENGAJUAN ────────────────────────────────────────── --}}
        <div class="pt-4">
            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 hover:from-blue-800 hover:to-purple-900 text-white font-extrabold py-4 rounded-2xl text-base shadow-xl transition transform active:scale-[0.99] flex items-center justify-center gap-2">
                <svg class="w-6 h-6 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="isCuration === 1 ? 'Kirim Laporkan & Pengajuan Kurasi Kemendikdasmen' : 'Kirim Laporkan Prestasi Internal Sekolah'"></span>
            </button>
        </div>
    </form>
</div>
@endsection
