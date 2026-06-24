@extends('layouts.guru')

@section('title', 'Export Poin Perilaku')
@section('page-title', 'Export Poin Perilaku')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">

        <p class="text-sm text-gray-500">Download rekap poin perilaku siswa per bulan dalam format PDF atau Excel.</p>

        <form id="export-form" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Periode (Bulan)</label>
                <input type="month" name="month"
                    value="{{ now()->format('Y-m') }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kelas</label>
                <select name="class_id"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="doExport('pdf')"
                    class="flex-1 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </button>
                <button type="button" onclick="doExport('excel')"
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

<script>
function doExport(type) {
    const form = document.getElementById('export-form');
    const params = new URLSearchParams(new FormData(form));
    const url = type === 'pdf'
        ? '{{ route('guru.export.conduct.pdf') }}'
        : '{{ route('guru.export.conduct.excel') }}';
    window.location.href = url + '?' + params.toString();
}
</script>
@endsection
