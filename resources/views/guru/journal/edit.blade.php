@extends('layouts.guru')
@section('title', 'Edit Jurnal Mengajar')
@section('page-title', 'Edit Jurnal Mengajar')

@section('content')
<div class="space-y-4 max-w-2xl">

    <form method="POST" action="{{ route('guru.journal.update', $journal) }}" id="journal-form">
        @csrf
        @method('PUT')

        {{-- ─── Tanggal --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tanggal *</p>
            <input type="date" name="date" required
                value="{{ old('date', $journal->date?->format('Y-m-d')) }}"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- ─── Kelas --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Kelas *</p>
            <select name="class_id" id="class-select" required
                onchange="onClassChange(this.value)"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">— Pilih Kelas —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" data-grade="{{ $class->grade }}" {{ old('class_id', $journal->class_id) == $class->id ? 'selected' : '' }}>
                    {{ $class->name }}
                </option>
                @endforeach
            </select>
            @error('class_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- ─── Mata Pelajaran --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Mata Pelajaran <span class="font-normal text-gray-400">(opsional)</span></p>
            <select name="subject_id" id="subject-select"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">— Tidak dipilih —</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" {{ old('subject_id', $journal->subject_id) == $subject->id ? 'selected' : '' }}>
                    {{ $subject->name }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- ─── Jam Pelajaran --}}
        @php
            $initialPeriod = old('period', $journal->period);
            $initialPeriodEnd = old('period_end', $journal->period_end);
            $initialCount = ($initialPeriodEnd && $initialPeriodEnd >= $initialPeriod) ? ($initialPeriodEnd - $initialPeriod + 1) : 1;
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Jam Ke- <span class="font-normal text-gray-400">(opsional)</span></p>
            <div class="flex flex-wrap gap-2" id="period-chips">
                @for($p = 1; $p <= 12; $p++)
                <button type="button"
                    onclick="selectPeriod({{ $p }})"
                    data-period="{{ $p }}"
                    class="w-9 h-9 rounded-xl border text-sm font-semibold transition-colors period-btn
                        {{ $initialPeriod == $p ? 'bg-blue-600 border-blue-600 text-white' : 'bg-gray-50 border-gray-200 text-gray-700 hover:border-blue-400' }}">
                    {{ $p }}
                </button>
                @endfor
            </div>
            <input type="hidden" name="period" id="period-input" value="{{ $initialPeriod }}">
            <input type="hidden" name="period_end" id="period-end-input" value="{{ $initialPeriodEnd }}">

            <div id="period-count-wrap" class="{{ $initialPeriod ? '' : 'hidden' }} space-y-2">
                <p class="text-xs text-gray-500">Jumlah Jam Pelajaran:</p>
                <div class="flex gap-2" id="period-count-chips">
                    @foreach([1 => '1 Jam', 2 => '2 Jam', 3 => '3 Jam'] as $cnt => $lbl)
                    <button type="button"
                        onclick="selectPeriodCount({{ $cnt }})"
                        data-count="{{ $cnt }}"
                        class="px-3 py-1.5 rounded-xl border text-sm font-semibold transition-colors period-count-btn
                            {{ $initialCount == $cnt ? 'bg-blue-600 border-blue-600 text-white' : 'bg-gray-50 border-gray-200 text-gray-700 hover:border-blue-400' }}">
                        {{ $lbl }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ─── Tujuan Pembelajaran --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tujuan Pembelajaran (TP) <span class="font-normal text-gray-400">(opsional)</span></p>
            @if($tps->isEmpty())
            <p class="text-xs text-gray-400">Belum ada TP. <a href="{{ route('guru.tp.index') }}" class="text-blue-600 hover:underline">Tambah TP terlebih dahulu.</a></p>
            @else
            <select name="tp_id" id="tp-select"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">— Tidak dipilih —</option>
                @foreach($tps as $tp)
                <option value="{{ $tp->id }}"
                    data-subject-id="{{ $tp->subject_id ?? '' }}"
                    data-grade-level="{{ $tp->grade_level ?? '' }}"
                    {{ old('tp_id', $journal->tp_id) == $tp->id ? 'selected' : '' }}>
                    {{ $tp->subject ? '[' . $tp->subject->name . '] ' : '' }}{{ $tp->grade_level ? '[Kls '.$tp->grade_level.'] ' : '' }}{{ $tp->code ? '('.$tp->code.') ' : '' }}{{ Str::limit($tp->description, 60) }}
                </option>
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
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('material', $journal->material) }}</textarea>
                @error('material')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Aktivitas Pembelajaran *</label>
                <textarea name="activity" rows="3" required maxlength="1000"
                    placeholder="Ceramah, diskusi, praktikum, presentasi..."
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('activity', $journal->activity) }}</textarea>
                @error('activity')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Catatan Tambahan <span class="font-normal text-gray-400">(opsional)</span></label>
                <textarea name="notes" rows="2" maxlength="500"
                    placeholder="Kendala, observasi, dll..."
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('notes', $journal->notes) }}</textarea>
            </div>
        </div>

        {{-- ─── Siswa Tidak Hadir --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 mt-3">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Siswa Tidak Hadir</p>
                <span id="absent-count-badge" class="hidden px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-100 text-red-600"></span>
            </div>
            <div id="student-list-wrap">
                <p class="text-xs text-gray-400">Memuat siswa...</p>
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
                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Perbarui Jurnal
            </button>
        </div>

    </form>
</div>

<script>
const AJAX_URL = '{{ route("guru.journal.api.students") }}';
let _selectedPeriod = {{ $initialPeriod ?: 'null' }};
let _periodCount    = {{ $initialCount ?: 1 }};
let _absentMap      = {
    @foreach($existingAbsences as $sId => $absRecord)
        {{ $sId }}: '{{ $absRecord->status }}',
    @endforeach
};

function loadStudents(classId) {
    const wrap = document.getElementById('student-list-wrap');
    const dateInput = document.querySelector('input[name="date"]');
    const dateVal = dateInput ? dateInput.value : '';

    if (!classId) {
        wrap.innerHTML = '<p class="text-xs text-gray-400">Pilih kelas terlebih dahulu.</p>';
        renderAbsentInputs();
        return;
    }
    wrap.innerHTML = '<p class="text-xs text-gray-400">Memuat siswa...</p>';

    fetch(AJAX_URL + '?class_id=' + classId + '&date=' + dateVal)
        .then(r => r.json())
        .then(students => {
            if (!students.length) {
                wrap.innerHTML = '<p class="text-xs text-gray-400">Tidak ada siswa di kelas ini.</p>';
                return;
            }
            let html = '<div class="space-y-1.5">';
            students.forEach(s => {
                const curSt = _absentMap[s.id];
                const morningBadgeBg = (s.morning_status && s.morning_status !== 'hadir') 
                    ? 'bg-amber-50 text-amber-700 border-amber-200 font-bold' 
                    : 'bg-gray-50 text-gray-400 border-gray-100';

                html += `
                <div class="flex items-center gap-2 py-1.5 border-b border-gray-50 last:border-0" data-student-id="${s.id}">
                    <div class="flex-1 min-w-0">
                        <span class="text-sm font-medium text-gray-700 truncate">${s.name}${s.nis ? ' <span class="text-gray-400 text-xs">('+s.nis+')</span>' : ''}</span>
                        <span class="ml-1.5 text-[10px] px-1.5 py-0.5 rounded border ${morningBadgeBg}">
                            Pagi: ${s.morning_status_label}
                        </span>
                    </div>
                    <div class="flex gap-1 shrink-0">
                        ${[
                            {st: 'alpa',       label: 'A', color: 'red'},
                            {st: 'izin',        label: 'I', color: 'sky'},
                            {st: 'sakit',       label: 'S', color: 'purple'},
                            {st: 'dispensasi',  label: 'D', color: 'teal'}
                        ].map(item => {
                            const isSelected = _absentMap[s.id] === item.st || (_absentMap[s.id] === 'tidak_hadir' && item.st === 'alpa');
                            return `<button type="button" onclick="setAbsentStatus(${s.id}, '${item.st}', this)"
                                data-status="${item.st}"
                                class="w-7 h-7 rounded-lg text-xs font-bold border transition-colors absent-btn
                                    ${isSelected ? `bg-${item.color}-600 border-${item.color}-600 text-white` : `bg-gray-50 border-gray-200 text-gray-500 hover:border-${item.color}-400`}">
                                ${item.label}
                            </button>`;
                        }).join('')}
                    </div>
                </div>`;
            });
            html += '</div>';
            wrap.innerHTML = html;
            renderAbsentInputs();
        });
}

function setAbsentStatus(studentId, status, btn) {
    const row = btn.closest('[data-student-id]');
    const btns = row.querySelectorAll('.absent-btn');
    const colors = { 'alpa': 'red', 'tidak_hadir': 'red', 'izin': 'sky', 'sakit': 'purple', 'dispensasi': 'teal' };

    const currentSavedStatus = _absentMap[studentId];
    const isAlreadySelected = (currentSavedStatus === status || (status === 'alpa' && currentSavedStatus === 'tidak_hadir') || (status === 'tidak_hadir' && currentSavedStatus === 'alpa'));

    if (isAlreadySelected) {
        delete _absentMap[studentId];
        btns.forEach(b => {
            b.className = 'w-7 h-7 rounded-lg text-xs font-bold border transition-colors absent-btn bg-gray-50 border-gray-200 text-gray-500';
        });
    } else {
        _absentMap[studentId] = status;
        btns.forEach(b => {
            const s = b.dataset.status;
            const c = colors[s] || 'red';
            if (s === status) {
                b.className = `w-7 h-7 rounded-lg text-xs font-bold border transition-colors absent-btn bg-${c}-600 border-${c}-600 text-white`;
            } else {
                b.className = 'w-7 h-7 rounded-lg text-xs font-bold border transition-colors absent-btn bg-gray-50 border-gray-200 text-gray-500';
            }
        });
    }
    renderAbsentInputs();
}

function renderAbsentInputs() {
    const container = document.getElementById('absent-hidden-inputs');
    const badge = document.getElementById('absent-count-badge');
    container.innerHTML = '';
    const keys = Object.keys(_absentMap);
    if (keys.length) {
        badge.classList.remove('hidden');
        badge.textContent = keys.length + ' tidak hadir';
    } else {
        badge.classList.add('hidden');
    }
    keys.forEach((studentId, i) => {
        container.innerHTML += `
            <input type="hidden" name="absent_students[${i}][student_id]" value="${studentId}">
            <input type="hidden" name="absent_students[${i}][status]" value="${_absentMap[studentId]}">
        `;
    });
}

function selectPeriod(p) {
    if (_selectedPeriod === p) {
        _selectedPeriod = null;
    } else {
        _selectedPeriod = p;
    }
    document.getElementById('period-input').value = _selectedPeriod || '';
    updatePeriodUI();
}

function selectPeriodCount(cnt) {
    _periodCount = cnt;
    updatePeriodUI();
}

function updatePeriodUI() {
    const wrap = document.getElementById('period-count-wrap');
    if (!_selectedPeriod) {
        wrap.classList.add('hidden');
        document.getElementById('period-end-input').value = '';
    } else {
        wrap.classList.remove('hidden');
        const endP = _selectedPeriod + _periodCount - 1;
        document.getElementById('period-end-input').value = endP;
    }

    document.querySelectorAll('.period-btn').forEach(btn => {
        const p = parseInt(btn.dataset.period);
        btn.className = (p === _selectedPeriod)
            ? 'w-9 h-9 rounded-xl border text-sm font-semibold transition-colors period-btn bg-blue-600 border-blue-600 text-white'
            : 'w-9 h-9 rounded-xl border text-sm font-semibold transition-colors period-btn bg-gray-50 border-gray-200 text-gray-700 hover:border-blue-400';
    });

    document.querySelectorAll('.period-count-btn').forEach(btn => {
        const cnt = parseInt(btn.dataset.count);
        btn.className = (cnt === _periodCount)
            ? 'px-3 py-1.5 rounded-xl border text-sm font-semibold transition-colors period-count-btn bg-blue-600 border-blue-600 text-white'
            : 'px-3 py-1.5 rounded-xl border text-sm font-semibold transition-colors period-count-btn bg-gray-50 border-gray-200 text-gray-700 hover:border-blue-400';
    });
}

function onClassChange(classId) {
    loadStudents(classId);
}

document.addEventListener('DOMContentLoaded', () => {
    const classSelect = document.getElementById('class-select');
    if (classSelect && classSelect.value) {
        loadStudents(classSelect.value);
    }
});
</script>
@endsection
