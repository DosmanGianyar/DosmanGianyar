@extends('layouts.guru')
@section('title', 'Input Dispensasi')
@section('page-title', 'Input Dispensasi Kolektif')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-3">
        <ul class="text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('guru.attendance.dispensation.store') }}" method="POST"
        enctype="multipart/form-data"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-5">
        @csrf

        <div>
            <label for="activity_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan</label>
            <input type="text" id="activity_name" name="activity_name"
                value="{{ old('activity_name') }}"
                placeholder="Contoh: Lomba OSN Kabupaten"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kegiatan</label>
            <input type="date" id="date" name="date"
                value="{{ old('date', date('Y-m-d')) }}"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Pilih Siswa --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700">Pilih Siswa</label>
                <button type="button" onclick="toggleAll()"
                    class="text-xs text-blue-600 hover:underline" id="toggle-all-btn">
                    Pilih Semua
                </button>
            </div>

            {{-- Filter kelas --}}
            <select id="class-filter" onchange="filterStudents()"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3 bg-white">
                <option value="">— Semua Kelas —</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>

            <div class="border border-gray-200 rounded-xl max-h-64 overflow-y-auto divide-y divide-gray-50" id="student-list">
                @foreach($classes as $class)
                    @foreach($class->students->sortBy('name') as $student)
                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer student-row"
                        data-class="{{ $class->id }}">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                            class="w-4 h-4 rounded text-blue-600 student-check"
                            {{ in_array($student->id, old('student_ids', [])) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-medium text-gray-700">{{ $student->name }}</span>
                            <span class="text-xs text-gray-400 ml-1">· {{ $class->name }}</span>
                        </div>
                    </label>
                    @endforeach
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-1" id="selected-count">0 siswa dipilih</p>
            @error('student_ids')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lampiran --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Surat/Lampiran <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png"
                class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 file:font-medium hover:file:bg-blue-100">
        </div>

        <div class="flex gap-3 pt-1">
            <a href="{{ route('guru.attendance.index') }}"
                class="flex-1 py-3 text-center rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit"
                class="flex-1 py-3 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                Simpan Dispensasi
            </button>
        </div>
    </form>
</div>

<script>
let allSelected = false;

function filterStudents() {
    const classId = document.getElementById('class-filter').value;
    document.querySelectorAll('.student-row').forEach(row => {
        row.style.display = (!classId || row.dataset.class === classId) ? '' : 'none';
    });
    updateCount();
}

function toggleAll() {
    const visibleChecks = [...document.querySelectorAll('.student-row')]
        .filter(r => r.style.display !== 'none')
        .map(r => r.querySelector('.student-check'));

    const anyUnchecked = visibleChecks.some(c => !c.checked);
    visibleChecks.forEach(c => c.checked = anyUnchecked);
    document.getElementById('toggle-all-btn').textContent = anyUnchecked ? 'Batalkan Semua' : 'Pilih Semua';
    updateCount();
}

function updateCount() {
    const count = document.querySelectorAll('.student-check:checked').length;
    document.getElementById('selected-count').textContent = count + ' siswa dipilih';
}

document.querySelectorAll('.student-check').forEach(c => c.addEventListener('change', updateCount));
updateCount();
</script>
@endsection
