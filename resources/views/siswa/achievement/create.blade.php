@extends('layouts.siswa')
@section('title', 'Laporkan Prestasi')
@section('page-title', 'Laporkan Prestasi')

@section('content')
<div class="max-w-lg mx-auto" x-data="{
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
    <form action="{{ route('siswa.achievements.store') }}" method="POST" enctype="multipart/form-data"
        class="space-y-4">
        @csrf

        {{-- Judul --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Prestasi / Kejuaraan <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
                placeholder="Contoh: Juara 1 Lomba Debat Bahasa Inggris"
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm @error('title') border-red-400 @enderror">
            @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Kategori --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
            <select name="category_id" required
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm @error('category_id') border-red-400 @enderror">
                <option value="">— Pilih Kategori —</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Jenis Partisipasi --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jenis Lomba / Partisipasi <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer transition-all"
                    :class="participationType === 'individu' ? 'border-blue-600 bg-blue-50/50 text-blue-800 font-semibold' : 'border-gray-200 text-gray-700'">
                    <input type="radio" name="participation_type" value="individu" x-model="participationType" class="text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="text-sm block">Perorangan</span>
                        <span class="text-[11px] text-gray-500 font-normal block leading-tight">Lomba Individu</span>
                    </div>
                </label>

                <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer transition-all"
                    :class="participationType === 'beregu' ? 'border-purple-600 bg-purple-50/50 text-purple-800 font-semibold' : 'border-gray-200 text-gray-700'">
                    <input type="radio" name="participation_type" value="beregu" x-model="participationType" class="text-purple-600 focus:ring-purple-500">
                    <div>
                        <span class="text-sm block">Beregu / Tim</span>
                        <span class="text-[11px] text-gray-500 font-normal block leading-tight">Lomba Kelompok</span>
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
        </div>

        {{-- Tingkat & Peringkat --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tingkat <span class="text-red-500">*</span></label>
                <select name="level" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm @error('level') border-red-400 @enderror">
                    <option value="">— Pilih —</option>
                    <option value="sekolah"       {{ old('level') == 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                    <option value="kabupaten"     {{ old('level') == 'kabupaten' ? 'selected' : '' }}>Kabupaten/Kota</option>
                    <option value="provinsi"      {{ old('level') == 'provinsi' ? 'selected' : '' }}>Provinsi</option>
                    <option value="nasional"      {{ old('level') == 'nasional' ? 'selected' : '' }}>Nasional</option>
                    <option value="internasional" {{ old('level') == 'internasional' ? 'selected' : '' }}>Internasional</option>
                </select>
                @error('level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Peringkat / Penghargaan</label>
                <input type="text" name="rank" value="{{ old('rank') }}" maxlength="50"
                    placeholder="Juara 1, Medali Emas..."
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">
                @error('rank') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Tanggal --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Prestasi <span class="text-red-500">*</span></label>
            <input type="date" name="achievement_date" value="{{ old('achievement_date') }}"
                max="{{ today()->toDateString() }}" required
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm @error('achievement_date') border-red-400 @enderror">
            @error('achievement_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Deskripsi --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Kegiatan</label>
            <textarea name="description" rows="3" maxlength="1000"
                placeholder="Ceritakan sedikit tentang kegiatan atau perlombaan ini..."
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm resize-none">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Foto Kegiatan --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Foto Kegiatan <span class="text-red-500">*</span>
            </label>
            <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300
                rounded-xl p-4 cursor-pointer hover:border-blue-400 transition-colors @error('photo') border-red-400 @enderror"
                id="photo-label">
                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span id="photo-name" class="text-sm text-gray-500">Klik untuk pilih foto kegiatan</span>
                <span class="text-xs text-gray-400 mt-1">JPG / PNG · Maks 5MB</span>
                <input type="file" name="photo" accept="image/*" required class="hidden"
                    onchange="document.getElementById('photo-name').textContent = this.files[0]?.name ?? 'Klik untuk pilih foto'">
            </label>
            @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Scan Piagam --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Scan Piagam / Sertifikat
                <span class="text-gray-400 font-normal">(disarankan)</span>
            </label>
            <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300
                rounded-xl p-4 cursor-pointer hover:border-blue-400 transition-colors @error('certificate') border-red-400 @enderror">
                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span id="cert-name" class="text-sm text-gray-500">Klik untuk pilih foto scan piagam</span>
                <span class="text-xs text-gray-400 mt-1">JPG / PNG · Maks 5MB</span>
                <input type="file" name="certificate" accept="image/*" class="hidden"
                    onchange="document.getElementById('cert-name').textContent = this.files[0]?.name ?? 'Klik untuk pilih foto scan piagam'">
            </label>
            @error('certificate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-2xl text-base transition-colors shadow-sm">
            Kirim Laporan Prestasi
        </button>

        <a href="{{ route('siswa.achievements.index') }}"
            class="block text-center text-sm text-gray-500 py-2">Batal</a>

    </form>
</div>
@endsection
