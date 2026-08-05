@extends('layouts.guru')
@section('title', 'Pencatatan Catatan Siswa')
@section('page-title', 'Pencatatan Catatan Siswa')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-2">
    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- Tab Bar --}}
<div class="flex bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <button onclick="switchMainTab('catat')" id="tab-catat"
        class="flex-1 py-3 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 transition-colors">
        Pencatatan Siswa
    </button>
    <button onclick="switchMainTab('riwayat')" id="tab-riwayat"
        class="flex-1 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors">
        Riwayat Catatan
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PANEL 1 — FORM PENCATATAN UNIFIED (POSITIF & NEGATIF)
══════════════════════════════════════════════════════════════ --}}
<div id="panel-catat">
    <form action="{{ route('guru.conduct.store') }}" method="POST" enctype="multipart/form-data"
          onsubmit="return composeUnifiedConduct(this)"
          id="unified-conduct-form"
          class="bg-white rounded-2xl shadow-sm border-2 border-emerald-400 p-5 space-y-5 transition-colors">
        @csrf
        <input type="hidden" name="context" id="uf-context" value="lainnya_prestasi">
        <input type="hidden" name="note" id="uf-note-hidden">

        {{-- 1. Pilih Siswa & Scan Barcode --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-gray-400 font-normal mr-1">1.</span> Pilih Siswa
                </label>
                <button type="button" onclick="openBarcodeScanner('uf')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Scan Kartu Siswa
                </button>
            </div>

            <div id="uf-scanned-card" class="hidden mb-2 p-2.5 bg-green-50 border border-green-200 rounded-xl flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-green-800 font-bold">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Terpilih via Barcode: <span id="uf-scanned-name"></span></span>
                </div>
            </div>

            <div class="flex gap-2 mb-2">
                <select id="uf-class" onchange="filterStudentSelect('uf-class','uf-student')"
                    class="flex-1 px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">— Semua Kelas —</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <select name="student_id" id="uf-student" required
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
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

        {{-- 2. Jenis Catatan (Dropdown Selector) --}}
        <div>
            <label for="uf-jenis-catatan" class="block text-sm font-medium text-gray-700 mb-1">
                <span class="text-gray-400 font-normal mr-1">2.</span> Jenis Catatan <span class="text-red-500">*</span>
            </label>
            <select id="uf-jenis-catatan" onchange="onJenisCatatanChange(this.value)"
                class="w-full px-3.5 py-3 rounded-xl border-2 font-bold text-sm focus:outline-none transition-all bg-white cursor-pointer border-emerald-400 text-emerald-700">
                <option value="lainnya_prestasi">🟢 Catatan Positif (Perilaku Baik / Apresiasi / Kebaikan)</option>
                <option value="lainnya_pelanggaran">🔴 Catatan Negatif (Pelanggaran / Ketidakdisiplinan)</option>
            </select>
        </div>

        {{-- 3. Judul / Kategori Catatan (Bebas Input) --}}
        <div>
            <label for="uf-kategori-input" class="block text-sm font-medium text-gray-700 mb-1">
                <span class="text-gray-400 font-normal mr-1">3.</span> Judul / Kategori Catatan <span class="text-gray-400 font-normal">(Bebas Input)</span>
            </label>
            <input type="text" id="uf-kategori-input"
                placeholder="Misal: Kejujuran, Membantu Guru, Keaktifan, Rambut Panjang, Keterlambatan..."
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        </div>

        {{-- 4. Detail / Deskripsi Catatan (Bebas Input) --}}
        <div>
            <label for="uf-deskripsi" class="block text-sm font-medium text-gray-700 mb-1">
                <span class="text-gray-400 font-normal mr-1">4.</span> Detail Catatan / Deskripsi <span class="text-red-500">*</span>
            </label>
            <textarea id="uf-deskripsi" rows="3" required
                placeholder="Ceritakan kejadian, detail kebaikan, atau catatan negatif siswa secara bebas..."
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
        </div>

        {{-- 5. Foto Bukti (Opsional) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Foto Bukti <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <label for="uf-photo"
                class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all">
                <div id="uf-photo-ph" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-xs text-gray-400">Upload / ambil foto</span>
                </div>
                <p id="uf-photo-name" class="hidden text-xs font-medium px-2 text-center text-emerald-600"></p>
                <input type="file" id="uf-photo" name="photo" accept="image/*" capture="environment"
                    class="sr-only" onchange="showPhotoName(this,'uf-photo-ph','uf-photo-name')">
            </label>
        </div>

        {{-- Submit Button --}}
        <div class="flex gap-3 pt-1">
            <a href="{{ route('guru.conduct.index') }}"
                class="flex-1 py-3 text-center rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit" id="uf-submit-btn"
                class="flex-1 py-3 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2 shadow-md">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <span id="uf-btn-text">Simpan Catatan Positif</span>
            </button>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PANEL 2 — RIWAYAT CATATAN
══════════════════════════════════════════════════════════════ --}}
<div id="panel-riwayat" class="hidden">
    {{-- Filter chips --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex items-center gap-2 flex-wrap mb-3">
        <button onclick="filterHistory(null,this)"
            class="hist-filter px-4 py-1.5 rounded-full text-xs font-semibold bg-blue-600 text-white transition-all">
            Semua
        </button>
        <button onclick="filterHistory('pelanggaran',this)"
            class="hist-filter px-4 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600 transition-all">
            Catatan Negatif 🔴
        </button>
        <button onclick="filterHistory('prestasi',this)"
            class="hist-filter px-4 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-green-100 hover:text-green-700 transition-all">
            Catatan Positif 🟢
        </button>
    </div>

    {{-- History list --}}
    <div id="history-list" class="space-y-2">
        @forelse($recentLogs as $log)
        @php
            $isPelanggaran = $log->isPelanggaran();
            $accentColor   = $isPelanggaran ? 'bg-red-500'   : 'bg-emerald-500';
            $badgeBg       = $isPelanggaran ? 'bg-red-50 text-red-700 border-red-200'  : 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $typeLabel     = $isPelanggaran ? 'Catatan Negatif' : 'Catatan Positif';
            $cleanTitle    = $log->parsed_title;
            $cleanDesc     = $log->parsed_description;
        @endphp
        <div class="history-card bg-white rounded-xl border border-gray-100 overflow-hidden shadow-xs"
             data-type="{{ $isPelanggaran ? 'pelanggaran' : 'prestasi' }}">
            <div class="flex">
                <div class="w-2 {{ $accentColor }} shrink-0"></div>
                <div class="flex-1 px-3.5 py-3">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <p class="text-sm font-bold text-gray-800">{{ $log->student->name }}</p>
                        <p class="text-xs text-gray-400 shrink-0">{{ $log->created_at->isoFormat('D MMM Y, HH:mm') }}</p>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">
                        {{ $log->student->nis ?? '—' }} · {{ $log->student->schoolClass?->name ?? '—' }}
                    </p>
                    <div class="flex flex-wrap items-center gap-1.5 mb-2">
                        <span class="flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $badgeBg }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isPelanggaran ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                            {{ $typeLabel }}
                        </span>
                        @if($cleanTitle && $cleanTitle !== $typeLabel)
                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                            {{ $cleanTitle }}
                        </span>
                        @endif
                    </div>
                    @if($cleanDesc)
                    <p class="text-xs text-gray-700 leading-relaxed">{{ $cleanDesc }}</p>
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
        </div>
        @endforelse
    </div>
</div>

</div>

<script>
// ── Main Tab switching ────────────────────────────────────────────────────────
function switchMainTab(name) {
    const isCatat = name === 'catat';
    document.getElementById('panel-catat').classList.toggle('hidden', !isCatat);
    document.getElementById('panel-riwayat').classList.toggle('hidden', isCatat);

    const btnCatat   = document.getElementById('tab-catat');
    const btnRiwayat = document.getElementById('tab-riwayat');

    btnCatat.className   = isCatat   ? 'flex-1 py-3 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 transition-colors' : 'flex-1 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors';
    btnRiwayat.className = !isCatat  ? 'flex-1 py-3 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 transition-colors' : 'flex-1 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors';
}

// ── Handle Dropdown Jenis Catatan Change ──────────────────────────────────────
function onJenisCatatanChange(val) {
    document.getElementById('uf-context').value = val;
    const form     = document.getElementById('unified-conduct-form');
    const select   = document.getElementById('uf-jenis-catatan');
    const btn      = document.getElementById('uf-submit-btn');
    const btnText  = document.getElementById('uf-btn-text');

    if (val === 'lainnya_pelanggaran') {
        // Red Theme (Catatan Negatif)
        form.className   = 'bg-white rounded-2xl shadow-sm border-2 border-red-400 p-5 space-y-5 transition-colors';
        select.className = 'w-full px-3.5 py-3 rounded-xl border-2 font-bold text-sm focus:outline-none transition-all bg-white cursor-pointer border-red-400 text-red-700';
        btn.className    = 'flex-1 py-3 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors flex items-center justify-center gap-2 shadow-md';
        btnText.textContent = 'Simpan Catatan Negatif';
    } else {
        // Green Theme (Catatan Positif)
        form.className   = 'bg-white rounded-2xl shadow-sm border-2 border-emerald-400 p-5 space-y-5 transition-colors';
        select.className = 'w-full px-3.5 py-3 rounded-xl border-2 font-bold text-sm focus:outline-none transition-all bg-white cursor-pointer border-emerald-400 text-emerald-700';
        btn.className    = 'flex-1 py-3 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2 shadow-md';
        btnText.textContent = 'Simpan Catatan Positif';
    }
}

// ── Compose note for unified conduct ──────────────────────────────────────────
function composeUnifiedConduct(form) {
    const kategori  = document.getElementById('uf-kategori-input').value.trim();
    const deskripsi = document.getElementById('uf-deskripsi').value.trim();
    if (!deskripsi) {
        alert('Silakan isi detail catatan / deskripsi terlebih dahulu.');
        document.getElementById('uf-deskripsi').focus();
        return false;
    }
    const prefix = kategori ? '[' + kategori + '] ' : '';
    document.getElementById('uf-note-hidden').value = prefix + deskripsi;
    return true;
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
            .replace(/bg-(blue|red|green|emerald)-\d+\s*/g, 'bg-gray-100 ')
            .replace(/text-(white|red|green|emerald)-\d*\s*/g, 'text-gray-600 ');
    });
    const activeColors = type === 'pelanggaran'
        ? ['bg-red-500',   'text-white']
        : type === 'prestasi'
            ? ['bg-emerald-600', 'text-white']
            : ['bg-blue-600',  'text-white'];
    btn.classList.remove('bg-gray-100','text-gray-600');
    btn.classList.add(...activeColors);
}
</script>

<!-- HTML5 QR Code Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<!-- Modal Scanner Barcode Kartu Siswa -->
<div id="scannerModal" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-2xl flex flex-col">
        <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h3 class="font-bold text-sm">Scan Barcode / QR Kartu Siswa</h3>
            </div>
            <button type="button" onclick="closeBarcodeScanner()" class="p-1 text-slate-400 hover:text-white rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-4 flex-1 flex flex-col items-center justify-center">
            <p class="text-xs text-gray-500 mb-3 text-center">Arahkan kamera ke Barcode / QR Code pada Kartu Siswa</p>
            <div id="reader" class="w-full rounded-xl overflow-hidden bg-slate-900 border border-slate-200" style="min-height: 250px;"></div>
            
            <div class="w-full mt-4 pt-3 border-t border-gray-100 flex gap-2">
                <input type="text" id="manualBarcodeInput" placeholder="Atau ketik/paste NISN / NIS..." class="flex-1 px-3 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" onclick="processManualCode()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition">
                    Cari
                </button>
            </div>
            <p id="scannerStatus" class="text-xs font-medium text-blue-600 mt-2 text-center hidden"></p>
        </div>
    </div>
</div>

<script>
let html5QrCode = null;
let currentTargetPrefix = 'uf';

function openBarcodeScanner(prefix) {
    currentTargetPrefix = prefix || 'uf';
    document.getElementById('scannerModal').classList.remove('hidden');
    document.getElementById('scannerStatus').textContent = 'Memulai kamera...';
    document.getElementById('scannerStatus').classList.remove('hidden');

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
    }

    const config = { fps: 10, qrbox: { width: 220, height: 220 } };

    html5QrCode.start(
        { facingMode: "environment" },
        config,
        onScanSuccess
    ).catch(err => {
        console.error("Camera access error:", err);
        document.getElementById('scannerStatus').textContent = 'Kamera tidak dapat diakses. Silakan masukkan NISN/NIS secara manual di bawah.';
        document.getElementById('scannerStatus').classList.remove('hidden');
    });
}

function closeBarcodeScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().then(() => {
            document.getElementById('scannerModal').classList.add('hidden');
        }).catch(() => {
            document.getElementById('scannerModal').classList.add('hidden');
        });
    } else {
        document.getElementById('scannerModal').classList.add('hidden');
    }
}

function processManualCode() {
    const val = document.getElementById('manualBarcodeInput').value.trim();
    if (val) {
        onScanSuccess(val);
    }
}

function onScanSuccess(decodedText) {
    document.getElementById('scannerStatus').textContent = 'Memproses identitas siswa...';
    document.getElementById('scannerStatus').classList.remove('hidden');

    fetch("{{ route('guru.conduct.scan-lookup') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ code: decodedText })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.student) {
            const student = data.student;
            const prefix  = currentTargetPrefix;
            
            const classSelect   = document.getElementById(prefix + '-class');
            const studentSelect = document.getElementById(prefix + '-student');

            if (classSelect) {
                classSelect.value = student.class_id || '';
                filterStudentSelect(prefix + '-class', prefix + '-student');
            }

            if (studentSelect) {
                studentSelect.value = student.id;
            }

            closeBarcodeScanner();

            const alertCard = document.getElementById(prefix + '-scanned-card');
            if (alertCard) {
                alertCard.classList.remove('hidden');
                document.getElementById(prefix + '-scanned-name').textContent = student.name + ' (' + student.class_name + ')';
            }

            if (typeof triggerSwalToast === 'function') {
                triggerSwalToast('success', 'Siswa Ditemukan: ' + student.name + ' (' + student.class_name + ')');
            } else {
                alert('Siswa Ditemukan: ' + student.name + ' (' + student.class_name + ')');
            }
        } else {
            document.getElementById('scannerStatus').textContent = data.message || 'Siswa tidak ditemukan.';
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('scannerStatus').textContent = 'Gagal terhubung ke server.';
    });
}
</script>
@endsection
