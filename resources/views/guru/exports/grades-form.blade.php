@extends('layouts.guru')
@section('title', 'Export Nilai')
@section('page-title', 'Export Rekap Nilai')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <p class="text-sm font-bold text-gray-700 mb-4">Filter Export Nilai</p>

    <form method="GET" action="{{ route('guru.export.grades.pdf') }}" id="form-grades" class="space-y-3">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas <span class="text-red-500">*</span></label>
            <select name="class_id" required
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                <option value="">— Pilih Kelas —</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Semester <span class="text-red-500">*</span></label>
            <select name="semester" required
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                <option value="1" {{ $currentSem == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                <option value="2" {{ $currentSem == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
            <input type="text" name="academic_year" value="{{ $currentYear }}" required
                placeholder="2025/2026"
                pattern="\d{4}/\d{4}"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                class="flex-1 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition-colors flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Download PDF
            </button>
            <button type="button" onclick="submitExcel()"
                class="flex-1 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition-colors flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Excel
            </button>
        </div>
    </form>
</div>

<div class="bg-blue-50 border border-blue-100 rounded-2xl px-4 py-3">
    <p class="text-xs text-blue-700 font-semibold mb-1">Informasi</p>
    <ul class="text-xs text-blue-600 space-y-0.5 list-disc list-inside">
        <li>PDF menampilkan semua siswa dan nilai per mata pelajaran</li>
        <li>Excel berisi baris per siswa per mapel dengan rata-rata UH, UTS, dan UAS</li>
        <li>Hanya nilai yang sudah diinput yang akan tampil</li>
    </ul>
</div>

</div>
<script>
function submitExcel() {
    var form = document.getElementById('form-grades');
    var orig = form.action;
    form.action = '{{ route('guru.export.grades.excel') }}';
    form.submit();
    form.action = orig;
}
</script>
@endsection
