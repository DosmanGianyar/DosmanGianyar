@extends('layouts.guru')
@section('title', 'Buat Jurnal Mengajar')
@section('page-title', 'Buat Jurnal Mengajar')

@section('content')
<div class="space-y-4 max-w-2xl">

    <form method="POST" action="{{ route('guru.journal.store') }}" id="journal-form">
        @csrf

        {{-- ─── Tanggal --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tanggal</p>
            <input type="date" name="date" required
                value="{{ old('date', date('Y-m-d')) }}"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- ─── Kelas --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Kelas *</p>
            <select name="class_id" id="class-select" required
                onchange="loadStudents(this.value)"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">— Pilih Kelas —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                    {{ $class->name }}
                </option>
                @endforeach
            </select>
            @error('class_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- ─── Mata Pelajaran --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Mata Pelajaran <span class="font-normal text-gray-400">(opsional)</span></p>
            <select name="subject_id"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">— Tidak dipilih —</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                    {{ $subject->name }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- ─── Jam Pelajaran --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Jam Ke- <span class="font-normal text-gray-400">(opsional)</span></p>
            <div class="flex flex-wrap gap-2" id="period-chips">
                @for($p = 1; $p <= 12; $p++)
                <button type="button"
                    onclick="selectPeriod({{ $p }})"
                    data-period="{{ $p }}"
                    class="w-9 h-9 rounded-xl border text-sm font-semibold transition-colors period-btn
                        {{ old('period') == $p ? 'bg-blue-600 border-blue-600 text-white' : 'bg-gray-50 border-gray-200 text-gray-700 hover:border-blue-400' }}">
                    {{ $p }}
                </button>
                @endfor
            </div>
            <input type="hidden" name="period" id="period-input" value="{{ old('period') }}">
            <input type="hidden" name="period_end" id="period-end-input" value="{{ old('period_end') }}">

            <div id="period-count-wrap" class="{{ old('period') ? '' : 'hidden' }} space-y-2">
                <p class="text-xs text-gray-500">Jumlah Jam Pelajaran:</p>
                <div class="flex gap-2" id="period-count-chips">
                    @foreach([1 => 'label', 2 => 'label', 3 => 'label'] as $cnt => $__)
                    <button type="button"
                        onclick="selectPeriodCount({{ $cnt }})"
                        data-count="{{ $cnt }}"
                        class="px-3 py-1.5 rounded-xl border text-sm font-semibold transition-colors period-count-btn
                            {{ old('period_end') && (old('period_end') - old('period') + 1) == $cnt ? 'bg-blue-600 border-blue-600 text-white' : 'bg-gray-50 border-gray-200 text-gray-700 hover:border-blue-400' }}">
                        {{ $cnt }} Jam
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ─── Tujuan Pembelajaran --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tujuan Pembelajaran <span class="font-normal text-gray-400">(opsional)</span></p>
            @if($tps->isEmpty())
            <p class="text-xs text-gray-400">Belum ada TP. <a href="{{ route('guru.tp.index') }}" class="text-blue-600 hover:underline">Tambah TP terlebih dahulu.</a></p>
            @else
            <select name="tp_id"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">— Tidak dipilih —</option>
                @php $tpGrouped = $tps->groupBy(fn($tp) => $tp->subject?->name ?? 'Umum'); @endphp
                @foreach($tpGrouped as $subjectName => $tpItems)
                <optgroup label="{{ $subjectName }}">
                    @foreach($tpItems as $tp)
                    <option value="{{ $tp->id }}" {{ old('tp_id') == $tp->id ? 'selected' : '' }}>
                        {{ $tp->code ? '['.$tp->code.'] ' : '' }}{{ Str::limit($tp->description, 60) }}
                    </option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
            @endif
        </div>

        {{-- ─── Materi & Aktivitas --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-4 mt-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Materi *</label>
                <textarea name="material" rows="3" required maxlength="1000"
                    placeholder="Topik / bab yang diajarkan..."
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('material') }}</textarea>
                @error('material')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Aktivitas Pembelajaran *</label>
                <textarea name="activity" rows="3" required maxlength="1000"
                    placeholder="Ceramah, diskusi, praktikum, presentasi..."
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('activity') }}</textarea>
                @error('activity')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Catatan Tambahan <span class="font-normal text-gray-400">(opsional)</span></label>
                <textarea name="notes" rows="2" maxlength="500"
                    placeholder="Kendala, observasi, dll..."
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- ─── Siswa Tidak Hadir --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Siswa Tidak Hadir</p>
                <span id="absent-count-badge" class="hidden px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-100 text-red-600"></span>
            </div>
            <div id="student-list-wrap">
                <p class="text-xs text-gray-400">Pilih kelas terlebih dahulu.</p>
            </div>
            <div id="absent-hidden-inputs"></div>
        </div>

        {{-- ─── Buttons --}}
        <div class="flex gap-3 mt-4 pb-8">
            <a href="{{ route('guru.journal.index') }}"
                class="flex-1 text-center px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Jurnal
            </button>
        </div>

    </form>
</div>

<script>
const AJAX_URL = '{{ route("guru.journal.api.students") }}';
let _selectedPeriod = {{ old('period') ?: 'null' }};
let _periodCount    = 1;
let _absentMap      = {};  // { studentId: 'tidak_hadir'|'izin'|'sakit' }

function loadStudents(classId) {
    const wrap = document.getElementById('student-list-wrap');
    if (!classId) {
        wrap.innerHTML = '<p class="text-xs text-gray-400">Pilih kelas terlebih dahulu.</p>';
        _absentMap = {};
        renderAbsentInputs();
        return;
    }
    wrap.innerHTML = '<p class="text-xs text-gray-400">Memuat siswa...</p>';
    _absentMap = {};
    renderAbsentInputs();

    fetch(AJAX_URL + '?class_id=' + classId)
        .then(r => r.json())
        .then(students => {
            if (!students.length) {
                wrap.innerHTML = '<p class="text-xs text-gray-400">Tidak ada siswa di kelas ini.</p>';
                return;
            }
            let html = '<div class="space-y-1.5">';
            students.forEach(s => {
                html += `
                <div class="flex items-center gap-2 py-1.5 border-b border-gray-50 last:border-0" data-student-id="${s.id}">
                    <span class="flex-1 text-sm text-gray-700 truncate">${s.name}${s.nis ? ' <span class="text-gray-400 text-xs">('+s.nis+')</span>' : ''}</span>
                    <div class="flex gap-1 shrink-0">
                        ${['tidak_hadir','izin','sakit'].map((st, i) => {
                            const labels = ['A','I','S'];
                            const colors = ['red','sky','purple'];
                            return `<button type="button" onclick="toggleAbsent(${s.id},'${st}',this)"
                                data-status="${st}"
                                class="w-7 h-7 rounded-lg border text-xs font-bold transition-colors absent-btn
                                    bg-gray-50 border-gray-200 text-gray-400 hover:border-${colors[i]}-400">
                                ${labels[i]}
                            </button>`;
                        }).join('')}
                    </div>
                </div>`;
            });
            html += '</div>';
            wrap.innerHTML = html;
        })
        .catch(() => {
            wrap.innerHTML = '<p class="text-xs text-red-500">Gagal memuat siswa.</p>';
        });
}

function toggleAbsent(studentId, status, btn) {
    const row = btn.closest('[data-student-id]');
    const btns = row.querySelectorAll('.absent-btn');
    const colors = { 'tidak_hadir': 'red', 'izin': 'sky', 'sakit': 'purple' };

    if (_absentMap[studentId] === status) {
        // deselect
        delete _absentMap[studentId];
        btns.forEach(b => {
            b.classList.remove('text-white', 'border-red-500', 'bg-red-500',
                'border-sky-500', 'bg-sky-500', 'border-purple-500', 'bg-purple-500');
            b.classList.add('bg-gray-50', 'border-gray-200', 'text-gray-400');
        });
    } else {
        _absentMap[studentId] = status;
        btns.forEach(b => {
            const s = b.dataset.status;
            const c = colors[s];
            if (s === status) {
                b.classList.remove('bg-gray-50', 'border-gray-200', 'text-gray-400');
                b.classList.add(`bg-${c}-500`, `border-${c}-500`, 'text-white');
            } else {
                b.classList.remove('text-white', `border-${colors[b.dataset.status]}-500`, `bg-${colors[b.dataset.status]}-500`);
                b.classList.add('bg-gray-50', 'border-gray-200', 'text-gray-400');
            }
        });
    }
    renderAbsentInputs();
}

function renderAbsentInputs() {
    const container = document.getElementById('absent-hidden-inputs');
    const badge     = document.getElementById('absent-count-badge');
    container.innerHTML = '';
    const entries = Object.entries(_absentMap);
    entries.forEach(([sid, status], i) => {
        container.innerHTML += `
            <input type="hidden" name="absent_students[${i}][student_id]" value="${sid}">
            <input type="hidden" name="absent_students[${i}][status]" value="${status}">`;
    });
    if (entries.length > 0) {
        badge.textContent = entries.length + ' siswa';
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function selectPeriod(p) {
    _selectedPeriod = (_selectedPeriod === p) ? null : p;
    document.getElementById('period-input').value = _selectedPeriod || '';
    document.querySelectorAll('.period-btn').forEach(b => {
        const active = parseInt(b.dataset.period) === _selectedPeriod;
        b.classList.toggle('bg-blue-600', active);
        b.classList.toggle('border-blue-600', active);
        b.classList.toggle('text-white', active);
        b.classList.toggle('bg-gray-50', !active);
        b.classList.toggle('border-gray-200', !active);
        b.classList.toggle('text-gray-700', !active);
    });
    const wrap = document.getElementById('period-count-wrap');
    if (_selectedPeriod) {
        wrap.classList.remove('hidden');
        selectPeriodCount(1);
    } else {
        wrap.classList.add('hidden');
        document.getElementById('period-end-input').value = '';
        _periodCount = 1;
    }
    updatePeriodCountLabels();
}

function selectPeriodCount(c) {
    _periodCount = c;
    const end = _selectedPeriod ? _selectedPeriod + c - 1 : '';
    document.getElementById('period-end-input').value = end > 12 ? 12 : end;
    document.querySelectorAll('.period-count-btn').forEach(b => {
        const active = parseInt(b.dataset.count) === c;
        b.classList.toggle('bg-blue-600', active);
        b.classList.toggle('border-blue-600', active);
        b.classList.toggle('text-white', active);
        b.classList.toggle('bg-gray-50', !active);
        b.classList.toggle('border-gray-200', !active);
        b.classList.toggle('text-gray-700', !active);
    });
    updatePeriodCountLabels();
}

function updatePeriodCountLabels() {
    if (!_selectedPeriod) return;
    document.querySelectorAll('.period-count-btn').forEach(b => {
        const c   = parseInt(b.dataset.count);
        const end = _selectedPeriod + c - 1;
        b.textContent = c === 1
            ? 'Jam ' + _selectedPeriod
            : 'Jam ' + _selectedPeriod + '–' + Math.min(end, 12);
    });
}

// Auto-load students if class was pre-selected (e.g. on validation error)
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('class-select');
    if (sel && sel.value) loadStudents(sel.value);
});
</script>
@endsection
