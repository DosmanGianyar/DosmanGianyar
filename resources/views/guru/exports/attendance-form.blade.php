@extends('layouts.guru')

@section('title', 'Export Absensi')
@section('page-title', 'Export Absensi')

@section('content')
<div class="max-w-lg space-y-4">

    {{-- Tab Header --}}
    <div class="flex rounded-xl overflow-hidden border border-gray-200 bg-white">
        <button id="tab-siswa" onclick="switchTab('siswa')"
            class="flex-1 py-3 text-sm font-semibold transition-colors tab-btn tab-active">
            Absensi Siswa
        </button>
        <button id="tab-guru" onclick="switchTab('guru')"
            class="flex-1 py-3 text-sm font-semibold transition-colors tab-btn">
            Absensi Guru
        </button>
    </div>

    {{-- Tab: Absensi Siswa --}}
    <div id="panel-siswa" class="space-y-4">

        {{-- Format: Daftar (list) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wide text-gray-500">Format Daftar</span>
                <span class="text-xs text-gray-400">— semua kelas, filter per status</span>
            </div>
            <form id="form-siswa" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Periode (Bulan)</label>
                    <input type="month" name="month" value="{{ now()->format('Y-m') }}"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kelas</label>
                    <select name="class_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Filter Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="hadir">Hadir</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="alpa">Alpa</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="doExport('siswa','pdf')"
                        class="flex-1 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </button>
                    <button type="button" onclick="doExport('siswa','excel')"
                        class="flex-1 flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </button>
                </div>
            </form>
        </div>

        {{-- Format: Grid / Rekap Bulanan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 p-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wide text-indigo-600">Format Rekap Bulanan</span>
                <span class="text-xs text-gray-400">— grid warna per tanggal</span>
            </div>

            {{-- Legend preview --}}
            <div class="flex flex-wrap gap-2">
                @foreach([
                    ['Hadir',      '#86efac'],
                    ['Terlambat',  '#fcd34d'],
                    ['Alpa',       '#fca5a5'],
                    ['Izin',       '#93c5fd'],
                    ['Sakit',      '#c4b5fd'],
                    ['Libur',      '#e5e7eb'],
                ] as [$label, $color])
                <div class="flex items-center gap-1">
                    <span class="inline-block w-4 h-4 rounded border border-gray-200" style="background:{{ $color }}"></span>
                    <span class="text-xs text-gray-600">{{ $label }}</span>
                </div>
                @endforeach
            </div>

            <form id="form-grid" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Periode (Bulan)</label>
                    <input type="month" name="month" value="{{ now()->format('Y-m') }}"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Kelas <span class="text-red-500">*</span>
                    </label>
                    @if($classes->isEmpty())
                        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-xs text-amber-700">
                            Anda belum terdaftar sebagai wali kelas. Hubungi admin untuk mengatur wali kelas.
                        </div>
                    @else
                        <select name="class_id" id="grid-class" required
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pilih kelas…</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Format grid menampilkan 1 kelas per halaman.</p>
                    @endif
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="doGridExport('pdf')"
                        class="flex-1 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF Grid
                    </button>
                    <button type="button" onclick="doGridExport('excel')"
                        class="flex-1 flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel Grid
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tab: Absensi Guru --}}
    <div id="panel-guru" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5 hidden">
        <p class="text-xs text-gray-500">Rekap kehadiran mengajar guru berdasarkan jam mengajar di kelas (diisi guru secara manual).</p>

        <form id="form-guru" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Periode (Bulan)</label>
                <input type="month" name="month"
                    value="{{ now()->format('Y-m') }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Guru</label>
                <select name="teacher_id"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ $teacher->id == auth()->id() ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="doExport('guru','pdf')"
                    class="flex-1 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </button>
                <button type="button" onclick="doExport('guru','excel')"
                    class="flex-1 flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .tab-active { background: #2563eb; color: white; }
    .tab-btn:not(.tab-active) { color: #6b7280; }
    .tab-btn:not(.tab-active):hover { background: #f3f4f6; }
</style>

<script>
const routes = {
    siswa: { pdf: '{{ route('guru.export.attendance.pdf') }}', excel: '{{ route('guru.export.attendance.excel') }}' },
    guru:  { pdf: '{{ route('guru.export.teacher-attendance.pdf') }}', excel: '{{ route('guru.export.teacher-attendance.excel') }}' },
    grid:  { pdf: '{{ route('guru.export.attendance.grid-pdf') }}', excel: '{{ route('guru.export.attendance.grid-excel') }}' },
};

function switchTab(tab) {
    ['siswa','guru'].forEach(t => {
        document.getElementById('panel-' + t).classList.toggle('hidden', t !== tab);
        document.getElementById('tab-' + t).classList.toggle('tab-active', t === tab);
    });
}

function doExport(tab, type) {
    const form   = document.getElementById('form-' + tab);
    const params = new URLSearchParams(new FormData(form));
    window.location.href = routes[tab][type] + '?' + params.toString();
}

function doGridExport(type) {
    const form    = document.getElementById('form-grid');
    const classEl = document.getElementById('grid-class');
    if (!classEl.value) { classEl.focus(); swalAlert('Pilih kelas terlebih dahulu.'); return; }
    const params = new URLSearchParams(new FormData(form));
    window.location.href = routes.grid[type] + '?' + params.toString();
}
</script>
@endsection
