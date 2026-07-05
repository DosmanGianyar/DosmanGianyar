@extends('layouts.guru')
@section('title', 'Jurnal Bimbingan Guru Wali')
@section('page-title', 'Jurnal Bimbingan Guru Wali')

@section('content')
<div class="max-w-3xl space-y-4">

    {{-- Header Info Kelas --}}
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-4 text-white flex items-center justify-between gap-4">
        <div>
            <p class="text-xs text-indigo-200">Wali Kelas</p>
            <p class="font-bold text-base">{{ $class->name }}</p>
            <p class="text-xs text-indigo-200 mt-0.5">{{ auth()->user()->name }}</p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold">{{ $counts->sum() }}</p>
            <p class="text-xs text-indigo-200">Total Pengajuan</p>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-4 gap-2">
        @foreach([
            ['pending',   'Menunggu', 'amber'],
            ['scheduled', 'Dijadwalkan', 'blue'],
            ['completed', 'Selesai', 'green'],
            ['cancelled', 'Dibatalkan', 'gray'],
        ] as [$st, $label, $color])
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 text-center">
            <p class="text-xl font-bold text-{{ $color }}-600">{{ $counts[$st] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Export Bulanan --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">Unduh Jurnal Bulanan</p>
        <div class="flex items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Periode</label>
                <input type="month" id="export-month" value="{{ now()->format('Y-m') }}"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button onclick="doExport('pdf')"
                class="flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </button>
            <button onclick="doExport('excel')"
                class="flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Excel
            </button>
        </div>
    </div>

    {{-- Filter Status --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach(['' => 'Semua', 'pending' => 'Menunggu', 'scheduled' => 'Dijadwalkan', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $val => $label)
        <a href="{{ route('guru.homeroom-consultation.index', $val ? ['status' => $val] : []) }}"
            class="shrink-0 px-4 py-1.5 rounded-full text-xs font-semibold transition-colors
                {{ $status === $val ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-300' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Daftar Pengajuan --}}
    @forelse($consultations as $c)
    @php
        $badgeClass = [
            'pending'   => 'bg-amber-100 text-amber-700',
            'scheduled' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-gray-100 text-gray-500',
        ][$c->status];
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">

        {{-- Header card --}}
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="font-semibold text-gray-800 text-sm">{{ $c->student->name }}</p>
                <p class="text-xs text-gray-400">{{ $c->created_at->isoFormat('D MMM Y') }}</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $badgeClass }}">
                {{ $c->statusLabel() }}
            </span>
        </div>

        {{-- Topik --}}
        <div class="bg-gray-50 rounded-xl px-3 py-2">
            <p class="text-xs font-semibold text-gray-600">Topik</p>
            <p class="text-sm text-gray-800">{{ $c->topic }}</p>
            @if($c->student_note)
                <p class="text-xs text-gray-500 mt-1">{{ $c->student_note }}</p>
            @endif
        </div>

        {{-- Jadwal (jika scheduled) --}}
        @if($c->isScheduled() && $c->scheduled_date)
        <div class="bg-blue-50 rounded-xl px-3 py-2 text-xs text-blue-700">
            Jadwal: <strong>{{ $c->scheduled_date->isoFormat('D MMMM Y') }}</strong>
        </div>
        @endif

        {{-- Hasil bimbingan (jika completed) --}}
        @if($c->isCompleted())
        <div class="bg-green-50 rounded-xl px-3 py-2 space-y-1 text-xs">
            <p class="text-gray-500">Dilaksanakan: <strong class="text-gray-700">{{ $c->conducted_date?->isoFormat('D MMMM Y') }}</strong></p>
            @if($c->teacher_note)
                <p class="font-semibold text-gray-700 mt-1">Catatan Bimbingan:</p>
                <p class="text-gray-600">{{ $c->teacher_note }}</p>
            @endif
            @if($c->follow_up)
                <p class="font-semibold text-gray-700 mt-1">Tindak Lanjut:</p>
                <p class="text-gray-600">{{ $c->follow_up }}</p>
            @endif
        </div>
        @endif

        {{-- Aksi --}}
        @if($c->isPending())
        {{-- Form Jadwalkan --}}
        <form method="POST" action="{{ route('guru.homeroom-consultation.schedule', $c) }}" class="space-y-2">
            @csrf @method('PATCH')
            <div class="flex gap-2">
                <input type="date" name="scheduled_date" required
                    min="{{ now()->format('Y-m-d') }}"
                    class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Tanggal jadwal">
                <button type="submit"
                    class="bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-blue-700 transition-colors">
                    Jadwalkan
                </button>
            </div>
        </form>
        <form method="POST" action="{{ route('guru.homeroom-consultation.cancel', $c) }}"
            data-confirm="Batalkan pengajuan ini?">
            @csrf @method('PATCH')
            <button type="submit" class="text-xs text-red-500 font-medium">Tolak / Batalkan</button>
        </form>
        @endif

        @if($c->isScheduled())
        {{-- Form Isi Jurnal --}}
        <div>
            <button type="button" onclick="toggleForm('form-{{ $c->id }}')"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                + Isi Jurnal Bimbingan
            </button>
        </div>
        <div id="form-{{ $c->id }}" class="hidden space-y-3">
            <form method="POST" action="{{ route('guru.homeroom-consultation.complete', $c) }}" class="space-y-3">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
                    <input type="date" name="conducted_date" required
                        value="{{ $c->scheduled_date?->format('Y-m-d') }}"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan / Hasil Bimbingan <span class="text-red-500">*</span></label>
                    <textarea name="teacher_note" rows="4" required
                        placeholder="Uraian hasil bimbingan, kondisi siswa, permasalahan yang dibahas…"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tindak Lanjut <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <textarea name="follow_up" rows="2"
                        placeholder="Rencana tindak lanjut, rekomendasi, dll."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 text-white font-semibold py-2.5 rounded-xl text-sm hover:bg-indigo-700 transition-colors">
                    Simpan Jurnal
                </button>
            </form>
        </div>
        @endif

    </div>
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
        <p class="text-gray-400 text-sm">Belum ada pengajuan bimbingan.</p>
    </div>
    @endforelse

</div>

<script>
function toggleForm(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}

const routes = {
    pdf:   '{{ route('guru.homeroom-consultation.export-pdf') }}',
    excel: '{{ route('guru.homeroom-consultation.export-excel') }}',
};
function doExport(type) {
    const month = document.getElementById('export-month').value;
    if (!month) { swalAlert('Pilih periode terlebih dahulu.'); return; }
    window.location.href = routes[type] + '?month=' + month;
}
</script>
@endsection
