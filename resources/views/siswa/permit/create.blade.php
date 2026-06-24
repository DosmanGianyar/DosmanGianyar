@extends('layouts.siswa')
@section('title', 'Ajukan Izin / Sakit / Dispensasi')
@section('page-title', 'Ajukan Pengajuan')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-3">
        <ul class="text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="permit-form" action="{{ route('siswa.permit.store') }}" method="POST"
        enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-5"
        onsubmit="showConfirm(event)">
        @csrf

        {{-- Tipe --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pengajuan</label>
            <div class="grid grid-cols-3 gap-2">

                <label id="lbl-izin"
                    class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ old('type') === 'izin' ? 'border-sky-500 bg-sky-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="type" value="izin" class="sr-only"
                        {{ old('type') === 'izin' ? 'checked' : '' }} onchange="onTypeChange(this)">
                    <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-700">Izin</span>
                </label>

                <label id="lbl-sakit"
                    class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ old('type') === 'sakit' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="type" value="sakit" class="sr-only"
                        {{ old('type') === 'sakit' ? 'checked' : '' }} onchange="onTypeChange(this)">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-700">Sakit</span>
                </label>

                <label id="lbl-dispensasi"
                    class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ old('type') === 'dispensasi' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="type" value="dispensasi" class="sr-only"
                        {{ old('type') === 'dispensasi' ? 'checked' : '' }} onchange="onTypeChange(this)">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-700">Dispensasi</span>
                </label>

            </div>
            @error('type')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tanggal --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date"
                    value="{{ old('start_date', date('Y-m-d')) }}"
                    min="{{ date('Y-m-d') }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                        @error('start_date') border-red-400 @enderror"
                    onchange="updateMinEndDate(this.value)">
                @error('start_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date"
                    value="{{ old('end_date', date('Y-m-d')) }}"
                    min="{{ date('Y-m-d') }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                        @error('end_date') border-red-400 @enderror">
                @error('end_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Alasan / Nama Kegiatan --}}
        <div>
            <label for="reason" id="reason-label" class="block text-sm font-medium text-gray-700 mb-1">
                Keterangan / Alasan
            </label>
            <textarea id="reason" name="reason" rows="3"
                placeholder="Jelaskan alasan pengajuan kamu..."
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none
                    @error('reason') border-red-400 @enderror">{{ old('reason') }}</textarea>
            @error('reason')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Upload Lampiran --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" id="file-label">
                Lampiran
                <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <label for="file"
                class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all">
                <div id="upload-placeholder" class="flex flex-col items-center gap-1">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="text-xs text-gray-500">Klik untuk upload</span>
                    <span class="text-xs text-gray-400">PDF, JPG, PNG — maks 2MB</span>
                </div>
                <p id="file-name" class="hidden text-sm text-blue-600 font-medium"></p>
                <input type="file" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png"
                    class="sr-only" onchange="showFileName(this)">
            </label>
            @error('file')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3 pt-1">
            <a href="{{ route('siswa.permit.index') }}"
                class="flex-1 py-3 text-center rounded-xl border border-gray-200 text-sm font-medium text-gray-600">
                Batal
            </a>
            <button type="submit"
                class="flex-1 py-3 bg-blue-600 text-white rounded-xl text-sm font-semibold">
                Kirim Pengajuan
            </button>
        </div>
    </form>
</div>

{{-- ─── Confirmation Modal ──────────────────────────────────────────────── --}}
<div id="confirm-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm px-4"
    onclick="cancelConfirm()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-5 space-y-4"
        onclick="event.stopPropagation()">

        <div class="flex items-center gap-3">
            <div id="confirm-icon" class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 bg-blue-100">
                <svg id="confirm-svg" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">Konfirmasi Pengajuan</p>
                <p id="confirm-type-label" class="text-xs text-gray-500"></p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Jenis</span>
                <span id="sum-type" class="font-semibold text-gray-800"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Tanggal</span>
                <span id="sum-date" class="font-semibold text-gray-800"></span>
            </div>
            <div class="flex justify-between items-start gap-4">
                <span class="text-gray-500 shrink-0">Keterangan</span>
                <span id="sum-reason" class="font-semibold text-gray-800 text-right leading-snug"></span>
            </div>
            <div id="sum-file-row" class="flex justify-between hidden">
                <span class="text-gray-500">Lampiran</span>
                <span id="sum-file" class="font-semibold text-gray-800"></span>
            </div>
        </div>

        <p class="text-xs text-gray-400 text-center">
            Pengajuan akan dikirim ke guru untuk diverifikasi. Pastikan data sudah benar.
        </p>

        <div class="flex gap-3">
            <button onclick="cancelConfirm()"
                class="flex-1 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-600">
                Kembali
            </button>
            <button id="confirm-submit-btn" onclick="submitForm()"
                class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-blue-600">
                Ya, Kirim
            </button>
        </div>
    </div>
</div>

<script>
const typeConfig = {
    izin:       { label: 'Izin',       icon: 'bg-sky-100',    btn: 'bg-sky-600',    border: 'border-sky-500 bg-sky-50',       reasonLabel: 'Keterangan / Alasan',     fileLabel: 'Lampiran (opsional)' },
    sakit:      { label: 'Sakit',      icon: 'bg-purple-100', btn: 'bg-purple-600', border: 'border-purple-500 bg-purple-50', reasonLabel: 'Keterangan / Catatan',     fileLabel: 'Surat Dokter (opsional)' },
    dispensasi: { label: 'Dispensasi', icon: 'bg-orange-100', btn: 'bg-orange-600', border: 'border-orange-500 bg-orange-50', reasonLabel: 'Nama Kegiatan / Keterangan', fileLabel: 'SK Kegiatan (opsional)' },
};
const borderDefault = 'border-gray-200 hover:border-gray-300';

function onTypeChange(radio) {
    document.querySelectorAll('input[name="type"]').forEach(r => {
        const lbl = document.getElementById('lbl-' + r.value);
        lbl.classList.remove(...lbl.className.split(' ').filter(c => c.includes('border-') || c.includes('bg-')));
        lbl.classList.add(...(r === radio ? typeConfig[r.value].border : borderDefault).split(' '));
    });
    const cfg = typeConfig[radio.value];
    document.getElementById('reason-label').firstChild.textContent = cfg.reasonLabel + '\n';
    document.getElementById('reason-label').childNodes[0].textContent = cfg.reasonLabel;
    document.getElementById('file-label').childNodes[0].textContent = '';
    // update file label inner span
    document.getElementById('file-label').innerHTML =
        cfg.fileLabel.replace('(opsional)', '<span class="text-gray-400 font-normal">(opsional)</span>')
                     .replace(' (wajib)', ' <span class="text-red-400 font-normal">(wajib)</span>');
    document.getElementById('reason').placeholder = cfg.reasonLabel + '...';
}

function updateMinEndDate(v) {
    const el = document.getElementById('end_date');
    el.min = v;
    if (el.value < v) el.value = v;
}

// Pre-select type from query string (e.g. ?type=dispensasi)
(function() {
    const urlType = new URLSearchParams(window.location.search).get('type');
    if (urlType) {
        const radio = document.querySelector('input[name="type"][value="' + urlType + '"]');
        if (radio) { radio.checked = true; onTypeChange(radio); }
    }
})();

function showFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('upload-placeholder').classList.add('hidden');
        const fn = document.getElementById('file-name');
        fn.classList.remove('hidden');
        fn.textContent = input.files[0].name;
    }
}

function formatDate(d) {
    if (!d) return '-';
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const dt = new Date(d + 'T00:00:00');
    return dt.getDate() + ' ' + months[dt.getMonth()] + ' ' + dt.getFullYear();
}

function showConfirm(e) {
    e.preventDefault();

    const typeEl = document.querySelector('input[name="type"]:checked');
    const start  = document.getElementById('start_date').value;
    const end    = document.getElementById('end_date').value;
    const reason = document.getElementById('reason').value.trim();
    const fileEl = document.getElementById('file');

    if (!typeEl) { swalAlert('Pilih jenis pengajuan.'); return; }
    if (!reason) { swalAlert('Keterangan / alasan wajib diisi.'); return; }

    const cfg = typeConfig[typeEl.value];
    document.getElementById('confirm-type-label').textContent = 'Pengajuan ' + cfg.label;
    document.getElementById('sum-type').textContent   = cfg.label;
    document.getElementById('sum-date').textContent   = start === end
        ? formatDate(start)
        : formatDate(start) + ' – ' + formatDate(end);
    document.getElementById('sum-reason').textContent = reason.length > 60 ? reason.slice(0, 60) + '…' : reason;

    if (fileEl.files && fileEl.files[0]) {
        document.getElementById('sum-file').textContent = fileEl.files[0].name;
        document.getElementById('sum-file-row').classList.remove('hidden');
    } else {
        document.getElementById('sum-file-row').classList.add('hidden');
    }

    const modal = document.getElementById('confirm-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cancelConfirm() {
    const modal = document.getElementById('confirm-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function submitForm() {
    document.getElementById('confirm-submit-btn').textContent = 'Mengirim...';
    document.getElementById('confirm-submit-btn').disabled = true;
    document.getElementById('permit-form').submit();
}
</script>
@endsection
