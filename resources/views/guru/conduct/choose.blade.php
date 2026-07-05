@extends('layouts.guru')
@section('title', 'Catat Perilaku Siswa')
@section('page-title', 'Catat Perilaku Siswa')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-2">
    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <p class="text-green-700 text-sm">{{ session('success') }}</p>
</div>
@endif

{{-- Tab Bar --}}
<div class="flex bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <button onclick="switchTab('negatif')" id="tab-negatif"
        class="flex-1 py-3 text-sm font-semibold border-b-2 border-red-500 text-red-600 transition-colors">
        Catatan Negatif
    </button>
    <button onclick="switchTab('positif')" id="tab-positif"
        class="flex-1 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors">
        Catatan Positif
    </button>
    <button onclick="switchTab('riwayat')" id="tab-riwayat"
        class="flex-1 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors">
        Riwayat
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════
     TAB 1 — CATATAN NEGATIF
══════════════════════════════════════════════════════════════ --}}
<div id="panel-negatif">
    <form action="{{ route('guru.conduct.store') }}" method="POST" enctype="multipart/form-data"
          onsubmit="return composeNoteNegatif(this)"
          class="bg-white rounded-2xl shadow-sm border-2 border-red-300 p-5 space-y-5">
        @csrf
        <input type="hidden" name="context" value="lainnya_pelanggaran">
        <input type="hidden" name="note" id="cn-note-hidden">

        {{-- Header badge --}}
        <div class="flex items-center gap-2">
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-700">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Catatan Negatif
            </span>
            <span class="text-xs text-gray-400">Catat catatan negatif siswa</span>
        </div>

        {{-- 1. Pilih Siswa --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span class="text-gray-400 font-normal mr-1">1.</span> Pilih Siswa
            </label>
            <div class="flex gap-2 mb-2">
                <select id="cn-class" onchange="filterStudentSelect('cn-class','cn-student')"
                    class="flex-1 px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 bg-white">
                    <option value="">— Semua Kelas —</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <select name="student_id" id="cn-student" required
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 bg-white">
                <option value="">— Pilih Siswa —</option>
                @foreach($classes as $class)
                    @foreach($class->students as $student)
                    <option value="{{ $student->id }}" data-class="{{ $class->id }}">
                        {{ $student->name }} ({{ $class->name }})
                    </option>
                    @endforeach
                @endforeach
            </select>
            @error('student_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- 2. Tingkat --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span class="text-gray-400 font-normal mr-1">2.</span> Tingkat
            </label>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['ringan' => ['Ringan','amber'], 'sedang' => ['Sedang','orange'], 'berat' => ['Berat','red']] as $val => [$label, $color])
                <button type="button" onclick="selectTingkat('{{ $val }}')"
                    id="tingkat-{{ $val }}"
                    class="tingkat-chip py-2.5 rounded-xl text-sm font-bold border-2 transition-all
                        {{ $color === 'amber'  ? 'border-amber-200  text-amber-600  hover:border-amber-400'  : '' }}
                        {{ $color === 'orange' ? 'border-orange-200 text-orange-600 hover:border-orange-400' : '' }}
                        {{ $color === 'red'    ? 'border-red-200    text-red-600    hover:border-red-400'    : '' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <input type="hidden" id="cn-tingkat" value="">
        </div>

        {{-- 3. Deskripsi --}}
        <div>
            <label for="cn-deskripsi" class="block text-sm font-medium text-gray-700 mb-1">
                <span class="text-gray-400 font-normal mr-1">3.</span> Deskripsi <span class="text-red-500">*</span>
            </label>
            <textarea id="cn-deskripsi" rows="3" required
                placeholder="Ceritakan catatan negatif yang dilakukan siswa..."
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
        </div>


        {{-- Foto --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Foto Bukti <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <label for="cn-photo"
                class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-red-300 hover:bg-red-50 transition-all">
                <div id="cn-photo-ph" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-xs text-gray-400">Upload / ambil foto</span>
                </div>
                <p id="cn-photo-name" class="hidden text-xs text-red-600 font-medium px-2 text-center"></p>
                <input type="file" id="cn-photo" name="photo" accept="image/*" capture="environment"
                    class="sr-only" onchange="showPhotoName(this,'cn-photo-ph','cn-photo-name')">
            </label>
        </div>

        {{-- Submit --}}
        <div class="flex gap-3 pt-1">
            <a href="{{ route('guru.conduct.index') }}"
                class="flex-1 py-3 text-center rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit"
                class="flex-1 py-3 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Simpan Catatan Negatif
            </button>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════════
     TAB 2 — CATATAN POSITIF
══════════════════════════════════════════════════════════════ --}}
<div id="panel-positif" class="hidden">
    <form action="{{ route('guru.conduct.store') }}" method="POST" enctype="multipart/form-data"
          onsubmit="return validatePositif()"
          class="bg-white rounded-2xl shadow-sm border-2 border-green-300 p-5 space-y-5">
        @csrf
        <input type="hidden" name="context" id="cp-context" value="">
        <input type="hidden" name="category_id" id="cp-category-id" value="">

        {{-- Header badge --}}
        <div class="flex items-center gap-2">
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-50 text-green-700">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                Catatan Positif
            </span>
            <span class="text-xs text-gray-400">Catat prestasi atau perilaku positif siswa</span>
        </div>

        {{-- 1. Pilih Siswa --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span class="text-gray-400 font-normal mr-1">1.</span> Pilih Siswa
            </label>
            <div class="flex gap-2 mb-2">
                <select id="cp-class" onchange="filterStudentSelect('cp-class','cp-student')"
                    class="flex-1 px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                    <option value="">— Semua Kelas —</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <select name="student_id" id="cp-student" required
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                <option value="">— Pilih Siswa —</option>
                @foreach($classes as $class)
                    @foreach($class->students as $student)
                    <option value="{{ $student->id }}" data-class="{{ $class->id }}">
                        {{ $student->name }} ({{ $class->name }})
                    </option>
                    @endforeach
                @endforeach
            </select>
        </div>

        {{-- 2. Pilih Kategori --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span class="text-gray-400 font-normal mr-1">2.</span> Pilih Kategori Catatan Positif
            </label>
            @if($prestasiCategories->isEmpty())
            <div class="py-4 text-center text-sm text-gray-400">
                Belum ada kategori. Tambahkan melalui panel admin.
            </div>
            @else
            {{-- Group by context --}}
            @php
                $grouped = $prestasiCategories->groupBy('context');
                $contextLabels = ['akademik' => 'Prestasi Akademik', 'lomba' => 'Prestasi Lomba'];
            @endphp
            @foreach($grouped as $ctx => $cats)
            <div class="mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">
                    {{ $contextLabels[$ctx] ?? ucfirst($ctx) }}
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach($cats as $cat)
                    <button type="button"
                        onclick="selectCategory(this, '{{ $ctx }}', '{{ $cat->id }}')"
                        class="category-chip px-3 py-2 rounded-lg text-sm font-medium border-2 border-gray-200 text-gray-600
                               hover:border-green-400 hover:text-green-700 transition-all"
                        data-context="{{ $ctx }}" data-id="{{ $cat->id }}">
                        {{ $cat->name }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endforeach
            @endif
            <div id="cp-category-error" class="hidden mt-1 text-xs text-red-600">Pilih kategori terlebih dahulu.</div>
        </div>

        {{-- 3. Catatan (opsional) --}}
        <div>
            <label for="cp-note" class="block text-sm font-medium text-gray-700 mb-1">
                <span class="text-gray-400 font-normal mr-1">3.</span> Catatan
                <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea id="cp-note" name="note" rows="3"
                placeholder="Deskripsi tambahan..."
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"></textarea>
        </div>

        {{-- Foto --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Foto Bukti <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <label for="cp-photo"
                class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all">
                <div id="cp-photo-ph" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-xs text-gray-400">Upload / ambil foto</span>
                </div>
                <p id="cp-photo-name" class="hidden text-xs text-green-600 font-medium px-2 text-center"></p>
                <input type="file" id="cp-photo" name="photo" accept="image/*" capture="environment"
                    class="sr-only" onchange="showPhotoName(this,'cp-photo-ph','cp-photo-name')">
            </label>
        </div>

        {{-- Submit --}}
        <div class="flex gap-3 pt-1">
            <a href="{{ route('guru.conduct.index') }}"
                class="flex-1 py-3 text-center rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit"
                class="flex-1 py-3 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                Simpan Catatan Positif
            </button>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════════
     TAB 3 — RIWAYAT
══════════════════════════════════════════════════════════════ --}}
<div id="panel-riwayat" class="hidden">

    {{-- Filter chips --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex items-center gap-2 flex-wrap">
        <button onclick="filterHistory(null,this)"
            class="hist-filter px-4 py-1.5 rounded-full text-xs font-semibold bg-blue-600 text-white transition-all">
            Semua
        </button>
        <button onclick="filterHistory('pelanggaran',this)"
            class="hist-filter px-4 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600 transition-all">
            Catatan Negatif
        </button>
        <button onclick="filterHistory('prestasi',this)"
            class="hist-filter px-4 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-green-100 hover:text-green-700 transition-all">
            Catatan Positif
        </button>
    </div>

    {{-- History list --}}
    <div id="history-list" class="space-y-2">
        @forelse($recentLogs as $log)
        @php
            $isPelanggaran = $log->category?->type === 'pelanggaran';
            $accentColor   = $isPelanggaran ? 'bg-red-500'   : 'bg-green-500';
            $badgeBg       = $isPelanggaran ? 'bg-red-50 text-red-700 border-red-200'  : 'bg-green-50 text-green-700 border-green-200';
            $typeLabel     = $isPelanggaran ? 'Catatan Negatif' : 'Catatan Positif';
        @endphp
        <div class="history-card bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm"
             data-type="{{ $log->category?->type ?? 'unknown' }}">
            <div class="flex">
                {{-- Left accent bar --}}
                <div class="w-1.5 {{ $accentColor }} shrink-0"></div>
                <div class="flex-1 px-3 py-3">
                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <p class="text-sm font-bold text-gray-800">{{ $log->student->name }}</p>
                        <p class="text-xs text-gray-400 shrink-0">{{ $log->created_at->isoFormat('D MMM Y') }}</p>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">
                        {{ $log->student->nis ?? '—' }} · {{ $log->student->schoolClass?->name ?? '—' }}
                    </p>
                    {{-- Badges --}}
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        <span class="flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold border {{ $badgeBg }}">
                            @if($isPelanggaran)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            @else
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            @endif
                            {{ $typeLabel }}
                        </span>
                        @if($log->category && !str_starts_with($log->category->name, '__sistem__'))
                        <span class="px-2 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">
                            {{ $log->category->name }}
                        </span>
                        @endif
                    </div>
                    {{-- Note --}}
                    @if($log->note)
                    <p class="text-xs text-gray-600 line-clamp-2">{{ $log->note }}</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm font-semibold text-gray-400">Belum ada catatan</p>
            <p class="text-xs text-gray-300 mt-1">Catatan yang Anda buat akan muncul di sini</p>
        </div>
        @endforelse
    </div>

    @if($recentLogs->count() >= 20)
    <p class="text-center">
        <a href="{{ route('guru.conduct.index') }}" class="text-xs text-gray-400 hover:text-blue-600">
            Lihat semua rekap →
        </a>
    </p>
    @endif
</div>

</div>

<script>
// ── Tab switching ──────────────────────────────────────────────────────────────
const TABS = {
    negatif: { border: 'border-red-500',   text: 'text-red-600'   },
    positif: { border: 'border-green-500', text: 'text-green-600' },
    riwayat: { border: 'border-blue-500',  text: 'text-blue-600'  },
};

function switchTab(name) {
    ['negatif','positif','riwayat'].forEach(t => {
        const btn   = document.getElementById('tab-' + t);
        const panel = document.getElementById('panel-' + t);
        const active = t === name;
        panel.classList.toggle('hidden', !active);
        btn.className = btn.className
            .replace(/border-(red|green|blue|transparent)-\d+/g, 'border-transparent')
            .replace(/text-(red|green|blue)-\d+/g, 'text-gray-400');
        if (active) {
            btn.classList.remove('border-transparent', 'text-gray-400', 'hover:text-gray-600');
            btn.classList.add(TABS[t].border, TABS[t].text);
        }
    });
}

// ── Siswa filter ───────────────────────────────────────────────────────────────
function filterStudentSelect(classSelectId, studentSelectId) {
    const classId = document.getElementById(classSelectId).value;
    const select  = document.getElementById(studentSelectId);
    [...select.options].forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!classId || opt.dataset.class === classId) ? '' : 'none';
    });
    if (select.selectedOptions[0]?.style.display === 'none') select.value = '';
}

// ── Tingkat chips ──────────────────────────────────────────────────────────────
const TINGKAT_COLORS = {
    ringan: ['border-amber-400',  'bg-amber-50',  'text-amber-700'],
    sedang: ['border-orange-400', 'bg-orange-50', 'text-orange-700'],
    berat:  ['border-red-500',    'bg-red-50',    'text-red-700'],
};

function selectTingkat(val) {
    document.getElementById('cn-tingkat').value = val;
    ['ringan','sedang','berat'].forEach(t => {
        const btn = document.getElementById('tingkat-' + t);
        if (!btn) return;
        btn.className = btn.className
            .replace(/border-(amber|orange|red)-\d+\s*/g, '')
            .replace(/bg-(amber|orange|red)-\d+\s*/g, '')
            .replace(/text-(amber|orange|red)-\d+\s*/g, '');
        if (t === val) {
            btn.classList.add(...TINGKAT_COLORS[t]);
        } else {
            const defaultColors = {
                ringan: ['border-amber-200',  'text-amber-600'],
                sedang: ['border-orange-200', 'text-orange-600'],
                berat:  ['border-red-200',    'text-red-600'],
            };
            btn.classList.add(...defaultColors[t]);
        }
    });
}

// ── Category chips ─────────────────────────────────────────────────────────────
function selectCategory(el, ctx, id) {
    document.querySelectorAll('.category-chip').forEach(c => {
        c.classList.remove('border-green-500','bg-green-50','text-green-700','font-semibold');
        c.classList.add('border-gray-200','text-gray-600');
    });
    el.classList.remove('border-gray-200','text-gray-600');
    el.classList.add('border-green-500','bg-green-50','text-green-700','font-semibold');
    document.getElementById('cp-context').value     = ctx;
    document.getElementById('cp-category-id').value = id;
    document.getElementById('cp-category-error').classList.add('hidden');
}

function validatePositif() {
    if (!document.getElementById('cp-category-id').value) {
        document.getElementById('cp-category-error').classList.remove('hidden');
        return false;
    }
    return true;
}

// ── Compose note for catatan negatif ──────────────────────────────────────────
function composeNoteNegatif(form) {
    const tingkat   = document.getElementById('cn-tingkat').value;
    const deskripsi = document.getElementById('cn-deskripsi').value.trim();
    if (!deskripsi) {
        document.getElementById('cn-deskripsi').focus();
        return false;
    }
    const prefix = tingkat ? '[' + tingkat.charAt(0).toUpperCase() + tingkat.slice(1) + '] ' : '';
    document.getElementById('cn-note-hidden').value = prefix + deskripsi;
    return true;
}

// ── Photo preview ──────────────────────────────────────────────────────────────
function showPhotoName(input, phId, nameId) {
    if (input.files?.[0]) {
        document.getElementById(phId).classList.add('hidden');
        const n = document.getElementById(nameId);
        n.classList.remove('hidden');
        n.textContent = input.files[0].name;
    }
}

// ── History filter ─────────────────────────────────────────────────────────────
function filterHistory(type, btn) {
    document.querySelectorAll('.history-card').forEach(card => {
        const match = !type || card.dataset.type === type;
        card.style.display = match ? '' : 'none';
    });
    document.querySelectorAll('.hist-filter').forEach(b => {
        b.className = b.className
            .replace(/bg-(blue|red|green)-\d+\s*/g, 'bg-gray-100 ')
            .replace(/text-(white|red|green)-\d*\s*/g, 'text-gray-600 ');
    });
    const activeColors = type === 'pelanggaran'
        ? ['bg-red-500',   'text-white']
        : type === 'prestasi'
            ? ['bg-green-600', 'text-white']
            : ['bg-blue-600',  'text-white'];
    btn.classList.remove('bg-gray-100','text-gray-600');
    btn.classList.add(...activeColors);
}
</script>
@endsection
