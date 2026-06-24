@extends('layouts.guru')
@section('title', 'Input Nilai')
@section('page-title', 'Input Nilai Siswa')

@section('content')
<div class="space-y-4">

{{-- ─── Filter ──────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <form method="GET" action="{{ route('guru.grades.index') }}"
        class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Kelas</label>
            <select name="class_id"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-28">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Semester</label>
            <select name="semester"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Semester 1</option>
                <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Semester 2</option>
            </select>
        </div>
        <div class="w-36">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun Ajaran</label>
            <select name="academic_year"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                @foreach($academicYears as $ay)
                    <option value="{{ $ay }}" {{ $academicYear === $ay ? 'selected' : '' }}>{{ $ay }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
            class="px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors shrink-0">
            Tampilkan
        </button>
    </form>
</div>

{{-- ─── Form Tambah Nilai ───────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <p class="text-sm font-bold text-gray-700 mb-3">Tambah / Perbarui Nilai</p>
    <form method="POST" action="{{ route('guru.grades.store') }}" class="space-y-3">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="academic_year" value="{{ $academicYear }}">

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Siswa <span class="text-red-500">*</span></label>
                <select name="student_id" required
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                    <option value="">— Pilih —</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Mapel <span class="text-red-500">*</span></label>
                <select name="subject_id" required
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                    <option value="">— Pilih —</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe <span class="text-red-500">*</span></label>
                <select name="type" required
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                    <option value="UH">UH (Ulangan Harian)</option>
                    <option value="UTS">UTS</option>
                    <option value="UAS">UAS</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nilai <span class="text-red-500">*</span></label>
                <input type="number" name="score" required min="0" max="100" step="0.5"
                    placeholder="0–100"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan (opsional)</label>
            <input type="text" name="notes" maxlength="200"
                placeholder="Keterangan tambahan..."
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
        <div class="flex items-center justify-between pt-1">
            <p class="text-xs text-gray-400">Jika nilai sudah ada (siswa+mapel+tipe+sem+TA sama), nilai akan diperbarui.</p>
            <button type="submit"
                class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors shrink-0">
                Simpan Nilai
            </button>
        </div>
    </form>
</div>

{{-- ─── Rekap Nilai yang Sudah Diinput ─────────────────────────────── --}}
@if($grades->isEmpty())
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-10 text-center">
    <p class="text-sm text-gray-400">Belum ada nilai untuk filter ini</p>
</div>
@else
<div class="space-y-3">
    @foreach($students as $student)
    @php $studentGrades = $grades->get($student->id); @endphp
    @if($studentGrades && $studentGrades->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-2.5 border-b border-gray-50 bg-gray-50/60">
            <p class="text-sm font-bold text-gray-700">{{ $student->name }}
                <span class="text-xs font-normal text-gray-400 ml-1">{{ $student->nis }}</span>
            </p>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($studentGrades->groupBy('subject_id') as $subjectId => $sg)
            <div class="px-4 py-2.5 flex items-center gap-3">
                <p class="text-sm font-medium text-gray-700 w-36 shrink-0 truncate">
                    {{ $sg->first()->subject->name }}
                </p>
                <div class="flex-1 flex flex-wrap gap-2">
                    @foreach($sg as $g)
                    <div class="flex items-center gap-1.5 bg-gray-50 rounded-xl px-2.5 py-1">
                        <span class="text-[11px] font-semibold text-gray-500">{{ $g->type }}</span>
                        <span class="text-sm font-bold {{ $g->scoreColor() }}">{{ number_format($g->score, 0) }}</span>
                        <form method="POST" action="{{ route('guru.grades.destroy', $g) }}" class="inline"
                            data-confirm="Hapus nilai ini?">
                            @csrf @method('DELETE')
                            <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                            <input type="hidden" name="semester" value="{{ $semester }}">
                            <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                            <button type="submit" class="text-red-400 hover:text-red-600 ml-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach
</div>
@endif

</div>

@if(session('success'))
<div id="toast-ok"
    class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-emerald-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-lg z-50 transition-opacity">
    {{ session('success') }}
</div>
<script>setTimeout(function(){ document.getElementById('toast-ok').style.opacity='0'; }, 2500);</script>
@endif

@endsection
