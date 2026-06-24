@extends('layouts.siswa')
@section('title', 'Edit Pengajuan')
@section('page-title', 'Edit Pengajuan')

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

    <form id="permit-form" action="{{ route('siswa.permit.update', $permit) }}" method="POST"
        enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-5"
        onsubmit="showConfirm(event)">
        @csrf
        @method('PUT')

        {{-- Tipe --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pengajuan</label>
            <div class="grid grid-cols-3 gap-2">

                @foreach(['izin' => ['sky','Izin'], 'sakit' => ['purple','Sakit'], 'dispensasi' => ['orange','Dispensasi']] as $val => [$color, $labelText])
                @php $checked = old('type', $permit->type) === $val; @endphp
                <label id="lbl-{{ $val }}"
                    class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ $checked ? "border-{$color}-500 bg-{$color}-50" : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="type" value="{{ $val }}" class="sr-only"
                        {{ $checked ? 'checked' : '' }} onchange="onTypeChange(this)">
                    <div class="w-8 h-8 bg-{{ $color }}-100 rounded-full flex items-center justify-center">
                        @if($val === 'izin')
                        <svg class="w-4 h-4 text-{{ $color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        @elseif($val === 'sakit')
                        <svg class="w-4 h-4 text-{{ $color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        @else
                        <svg class="w-4 h-4 text-{{ $color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        @endif
                    </div>
                    <span class="text-xs font-semibold text-gray-700">{{ $labelText }}</span>
                </label>
                @endforeach

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
                    value="{{ old('start_date', $permit->start_date->format('Y-m-d')) }}"
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
                    value="{{ old('end_date', $permit->end_date->format('Y-m-d')) }}"
                    min="{{ $permit->start_date->format('Y-m-d') }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                        @error('end_date') border-red-400 @enderror">
                @error('end_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Alasan / Nama Kegiatan --}}
        <div>
            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                {{ old('type', $permit->type) === 'dispensasi' ? 'Nama Kegiatan / Keterangan' : 'Keterangan / Alasan' }}
            </label>
            <textarea id="reason" name="reason" rows="3"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none
                    @error('reason') border-red-400 @enderror">{{ old('reason', $permit->reason) }}</textarea>
            @error('reason')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lampiran --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ old('type', $permit->type) === 'dispensasi' ? 'SK Kegiatan' : 'Lampiran' }}
                <span class="text-gray-400 font-normal">(opsional)</span>
            </label>

            @if($permit->file)
            <div class="flex items-center justify-between bg-blue-50 rounded-xl px-3 py-2 mb-2 text-xs">
                <a href="{{ Storage::url($permit->file) }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    Lampiran saat ini
                </a>
                <span class="text-gray-400">Unggah baru untuk mengganti</span>
            </div>
            @endif

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
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

{{-- ─── Confirmation Modal ──────────────────────────────────────────────── --}}
<div id="confirm-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm px-4"
    onclick="cancelConfirm()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-5 space-y-4" onclick="event.stopPropagation()">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">Konfirmasi Perubahan</p>
                <p class="text-xs text-gray-500">Pastikan data sudah benar sebelum menyimpan</p>
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
        </div>

        <div class="flex gap-3">
            <button onclick="cancelConfirm()"
                class="flex-1 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-600">
                Kembali
            </button>
            <button id="confirm-submit-btn" onclick="submitForm()"
                class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-blue-600">
                Ya, Simpan
            </button>
        </div>
    </div>
</div>

<script>
const typeLabels = { izin: 'Izin', sakit: 'Sakit', dispensasi: 'Dispensasi' };
const typeColors  = {
    izin:       { border: 'border-sky-500 bg-sky-50' },
    sakit:      { border: 'border-purple-500 bg-purple-50' },
    dispensasi: { border: 'border-orange-500 bg-orange-50' },
};

function onTypeChange(radio) {
    document.querySelectorAll('input[name="type"]').forEach(r => {
        const lbl = document.getElementById('lbl-' + r.value);
        lbl.className = lbl.className.replace(/border-\S+/g, '').replace(/bg-\S+50/g, '').trim();
        const cls = r === radio ? typeColors[r.value].border : 'border-gray-200 hover:border-gray-300';
        lbl.classList.add(...cls.split(' '));
    });
}

function updateMinEndDate(v) {
    const el = document.getElementById('end_date');
    el.min = v;
    if (el.value < v) el.value = v;
}

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
    if (!reason) { swalAlert('Keterangan / alasan wajib diisi.'); return; }

    document.getElementById('sum-type').textContent   = typeLabels[typeEl?.value] ?? '-';
    document.getElementById('sum-date').textContent   = start === end ? formatDate(start) : formatDate(start) + ' – ' + formatDate(end);
    document.getElementById('sum-reason').textContent = reason.length > 60 ? reason.slice(0, 60) + '…' : reason;

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
    document.getElementById('confirm-submit-btn').textContent = 'Menyimpan...';
    document.getElementById('confirm-submit-btn').disabled = true;
    document.getElementById('permit-form').submit();
}
</script>
@endsection
