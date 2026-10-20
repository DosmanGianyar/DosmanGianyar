@extends('layouts.guru')
@section('title', 'Input Nilai')
@section('page-title', 'Input Nilai Siswa')

@section('content')
<div class="space-y-4">

{{-- ─── Header Info & Filter Bar ──────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4">
    <div class="flex items-center justify-between mb-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-800 leading-tight">Pengelolaan Nilai Akademik Siswa</h1>
                <p class="text-[11px] text-slate-400">Pilih kelas, semester, dan tahun ajaran target</p>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('guru.grades.index') }}"
        class="flex flex-wrap gap-2.5 items-end pt-2 border-t border-slate-100">
        <div class="flex-1 min-w-[130px]">
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Pilih Kelas</label>
            <select name="class_id"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-700">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Semester</label>
            <select name="semester"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-700">
                <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
            </select>
        </div>
        <div class="w-36">
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tahun Ajaran</label>
            <select name="academic_year"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-700">
                @foreach($academicYears as $ay)
                    <option value="{{ $ay }}" {{ $academicYear === $ay ? 'selected' : '' }}>{{ $ay }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl shadow-xs transition-all shrink-0">
            Tampilkan Data
        </button>
    </form>
</div>

{{-- ─── Form Tambah / Perbarui Nilai ──────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4">
    <div class="flex items-center justify-between mb-3.5">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            Tambah / Perbarui Nilai Siswa
        </h2>
        <span class="text-[10px] text-slate-400 font-medium">Auto-update jika nilai sudah ada</span>
    </div>

    <form method="POST" action="{{ route('guru.grades.store') }}" class="space-y-3">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="academic_year" value="{{ $academicYear }}">

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Siswa <span class="text-rose-500">*</span></label>
                <select name="student_id" required
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-700">
                    <option value="">— Pilih Siswa —</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->nis }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                <select name="subject_id" required
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-700">
                    <option value="">— Pilih Mapel —</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Tipe Evaluasi <span class="text-rose-500">*</span></label>
                <select name="type" required
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-700">
                    <option value="UH">UH (Ulangan Harian)</option>
                    <option value="UTS">UTS (Tengah Semester)</option>
                    <option value="UAS">UAS (Akhir Semester)</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Skor Nilai <span class="text-rose-500">*</span></label>
                <input type="number" name="score" required min="0" max="100" step="0.5"
                    placeholder="0–100"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Catatan Keterangan (opsional)</label>
            <input type="text" name="notes" maxlength="200"
                placeholder="Contoh: Remedial UH bab trigonometri..."
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
            <p class="text-[11px] text-slate-400">Data nilai langsung tersinkronisasi ke Rapor Siswa & Orang Tua.</p>
            <button type="submit"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl shadow-xs transition-all shrink-0">
                Simpan & Rekam Nilai
            </button>
        </div>
    </form>
</div>

{{-- ─── Rekap Nilai yang Sudah Diinput ─────────────────────────────── --}}
@if($grades->isEmpty())
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 py-12 text-center">
    <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
    </div>
    <p class="text-xs font-bold text-slate-600">Belum Ada Rekap Nilai</p>
    <p class="text-[11px] text-slate-400 mt-0.5">Silakan pilih kelas dan input nilai siswa melalui form di atas</p>
</div>
@else
<div class="space-y-3">
    <div class="px-1 flex items-center justify-between">
        <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Rekapitulasi Nilai Siswa</p>
        <span class="text-[11px] text-slate-400">{{ $students->count() }} siswa di kelas ini</span>
    </div>

    @foreach($students as $student)
    @php $studentGrades = $grades->get($student->id); @endphp
    @if($studentGrades && $studentGrades->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center">
                    {{ substr($student->name, 0, 1) }}
                </div>
                <p class="text-xs font-bold text-slate-800">{{ $student->name }}</p>
                <span class="text-[10px] font-semibold text-slate-400 bg-white border border-slate-200 px-1.5 py-0.2 rounded-md">NIS: {{ $student->nis }}</span>
            </div>
            <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">
                {{ $studentGrades->count() }} Nilai
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($studentGrades->groupBy('subject_id') as $subjectId => $sg)
            <div class="px-4 py-2.5 flex items-center gap-3 hover:bg-slate-50/40 transition-colors">
                <p class="text-xs font-bold text-slate-700 w-36 shrink-0 truncate">
                    {{ $sg->first()->subject->name }}
                </p>
                <div class="flex-1 flex flex-wrap gap-1.5">
                    @foreach($sg as $g)
                    <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 rounded-xl px-2.5 py-1">
                        <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $g->type }}</span>
                        <span class="text-xs font-black {{ $g->scoreColor() }}">{{ number_format($g->score, 0) }}</span>
                        <form method="POST" action="{{ route('guru.grades.destroy', $g) }}" class="inline"
                            data-confirm="Hapus nilai ini?">
                            @csrf @method('DELETE')
                            <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                            <input type="hidden" name="semester" value="{{ $semester }}">
                            <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                            <button type="submit" class="text-slate-300 hover:text-rose-600 transition-colors ml-0.5" title="Hapus Nilai">
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
    class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-slate-900/90 backdrop-blur-md text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg z-50 transition-opacity border border-white/10 flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <span>{{ session('success') }}</span>
</div>
<script>setTimeout(function(){ document.getElementById('toast-ok').style.opacity='0'; }, 2500);</script>
@endif

@endsection
