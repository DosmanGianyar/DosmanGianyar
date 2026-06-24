@extends('layouts.guru')

@section('title', 'Absensi Mengajar')
@section('page-title', 'Absensi Mengajar')

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- Date Picker --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <form method="GET" action="{{ route('guru.teacher-attendance.index') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Tanggal</label>
                <input type="date" name="date"
                    value="{{ $date->toDateString() }}"
                    max="{{ now()->toDateString() }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                Tampilkan
            </button>
        </form>

        @if(!$scheduleDay)
        <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
            Tanggal yang dipilih adalah hari libur (Sabtu/Minggu). Pilih hari Senin–Jumat.
        </div>
        @endif
    </div>

    @if($scheduleDay)
    {{-- Jadwal Otomatis --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-2 h-5 rounded-full bg-blue-600"></div>
                <h2 class="font-semibold text-gray-800">
                    Jadwal Mengajar — {{ $date->isoFormat('dddd, D MMMM Y') }}
                </h2>
            </div>
            <button onclick="toggleManual()"
                class="flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 border border-blue-200 hover:border-blue-400 px-3 py-1.5 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Sesi Manual
            </button>
        </div>

        {{-- Manual Add Form --}}
        <div id="manual-form" class="hidden mb-5 border border-dashed border-blue-300 rounded-xl p-4 bg-blue-50">
            <p class="text-xs font-semibold text-blue-700 mb-3">Sesi Manual (tukar jam / kelas pengganti)</p>
            <form method="POST" action="{{ route('guru.teacher-attendance.manual') }}" id="form-manual">
                @csrf
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                <input type="hidden" name="status" value="hadir">

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jam ke-</label>
                        <input type="number" name="period" id="manual-period" min="1" max="12" placeholder="1–12"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
                        <select name="class_id" id="manual-class" required
                            onchange="loadStudents('manual', this.value)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                            <option value="">Pilih kelas…</option>
                            @foreach(\App\Models\SchoolClass::orderBy('name')->get() as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Mata Pelajaran</label>
                        <select name="subject_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                            <option value="">Pilih mapel…</option>
                            @foreach(\App\Models\Subject::orderBy('name')->get() as $subj)
                                <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jam Mulai</label>
                        <input type="time" name="start_time"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jam Selesai</label>
                        <input type="time" name="end_time"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    </div>
                </div>

                <div class="mt-3">
                    <input type="text" name="note" placeholder="Catatan sesi (opsional)…"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                </div>

                {{-- Student List (loaded via AJAX) --}}
                <div id="manual-students-wrap" class="hidden mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-gray-700">Daftar Hadir Siswa</p>
                        <p class="text-xs text-gray-400">Tandai yang tidak hadir / izin / sakit</p>
                    </div>
                    <div id="manual-students-list" class="space-y-1.5 max-h-64 overflow-y-auto pr-1"></div>
                </div>

                <div class="mt-4 flex gap-2">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-xs transition-colors">
                        Simpan Sesi
                    </button>
                    <button type="button" onclick="toggleManual()"
                        class="text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>

        {{-- Scheduled Sessions --}}
        @if($schedules->isEmpty())
        <div class="text-center py-8 text-gray-400 text-sm">
            Tidak ada jadwal terdaftar untuk hari ini.<br>
            <span class="text-xs">Gunakan "Tambah Sesi Manual" di atas untuk mencatat kehadiran.</span>
        </div>
        @else
        <form method="POST" action="{{ route('guru.teacher-attendance.store') }}" id="form-scheduled">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <div class="space-y-4">
                @foreach($schedules as $idx => $schedule)
                @php
                    $saved  = $existing->get($schedule->id);
                    $status = $saved?->status ?? 'hadir';
                @endphp
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <input type="hidden" name="attendances[{{ $idx }}][schedule_id]" value="{{ $schedule->id }}">
                    <input type="hidden" name="attendances[{{ $idx }}][class_id]"    value="{{ $schedule->class_id }}">
                    <input type="hidden" name="attendances[{{ $idx }}][subject_id]"  value="{{ $schedule->subject_id }}">
                    <input type="hidden" name="attendances[{{ $idx }}][period]"      value="{{ $schedule->period }}">
                    <input type="hidden" name="attendances[{{ $idx }}][start_time]"  value="{{ $schedule->start_time }}">
                    <input type="hidden" name="attendances[{{ $idx }}][end_time]"    value="{{ $schedule->end_time }}">

                    {{-- Session Header --}}
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">
                                Jam ke-{{ $schedule->period }} &nbsp;·&nbsp; {{ $schedule->schoolClass?->name }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $schedule->subject?->name }}
                                @if($schedule->start_time)
                                    &nbsp;·&nbsp; {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}
                                @endif
                                @if($schedule->room) &nbsp;·&nbsp; {{ $schedule->room }} @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach(['hadir' => ['Hadir','bg-green-100 text-green-700 border-green-300'], 'tidak_hadir' => ['Tidak Hadir','bg-red-100 text-red-700 border-red-300'], 'izin' => ['Izin','bg-blue-100 text-blue-700 border-blue-300'], 'sakit' => ['Sakit','bg-purple-100 text-purple-700 border-purple-300']] as $val => [$label, $cls])
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" name="attendances[{{ $idx }}][status]" value="{{ $val }}"
                                    {{ $status === $val ? 'checked' : '' }} class="sr-only peer">
                                <span class="px-3 py-1 rounded-lg border text-xs font-medium transition-all
                                    peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-current {{ $cls }}">
                                    {{ $label }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="attendances[{{ $idx }}][note]"
                            value="{{ $saved?->note }}" placeholder="Catatan sesi (opsional)…"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    </div>

                    {{-- Student Attendance for this session --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-semibold text-gray-600">Daftar Hadir Siswa</p>
                            <button type="button"
                                onclick="loadStudentsScheduled({{ $idx }}, {{ $schedule->class_id }})"
                                id="btn-load-{{ $idx }}"
                                class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                                Muat daftar siswa ↓
                            </button>
                        </div>
                        <div id="students-{{ $idx }}" class="space-y-1.5 hidden max-h-64 overflow-y-auto pr-1">
                            {{-- filled via JS --}}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-5 flex justify-end">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                    Simpan Absensi
                </button>
            </div>
        </form>
        @endif
    </div>
    @endif

    {{-- Recent History --}}
    @if($history->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-2 h-5 rounded-full bg-gray-400"></div>
            <h2 class="font-semibold text-gray-800">Riwayat 30 Hari Terakhir</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 px-3 text-xs text-gray-500 font-semibold">Tanggal</th>
                        <th class="text-left py-2 px-3 text-xs text-gray-500 font-semibold">Jam</th>
                        <th class="text-left py-2 px-3 text-xs text-gray-500 font-semibold">Kelas</th>
                        <th class="text-left py-2 px-3 text-xs text-gray-500 font-semibold">Mata Pelajaran</th>
                        <th class="text-left py-2 px-3 text-xs text-gray-500 font-semibold">Status</th>
                        <th class="py-2 px-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $r)
                    @php
                        $statusColor = match($r->status) {
                            'hadir'       => 'text-green-600',
                            'tidak_hadir' => 'text-red-600',
                            'izin'        => 'text-blue-600',
                            'sakit'       => 'text-purple-600',
                            default       => 'text-gray-600',
                        };
                    @endphp
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-2 px-3 text-xs text-gray-700">{{ $r->date->isoFormat('ddd, D MMM Y') }}</td>
                        <td class="py-2 px-3 text-xs text-gray-700">ke-{{ $r->period }}</td>
                        <td class="py-2 px-3 text-xs text-gray-700">{{ $r->schoolClass?->name ?? '—' }}</td>
                        <td class="py-2 px-3 text-xs text-gray-700">{{ $r->subject?->name ?? '—' }}</td>
                        <td class="py-2 px-3 text-xs font-semibold {{ $statusColor }}">{{ $r->statusLabel() }}</td>
                        <td class="py-2 px-3 text-right">
                            <form method="POST"
                                action="{{ route('guru.teacher-attendance.destroy', $r) }}"
                                data-confirm="Hapus catatan absensi ini?"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition-colors">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<script>
const API_URL = '{{ route('guru.teacher-attendance.api.students') }}';
const STATUS_OPTIONS = [
    { value: 'hadir',       label: 'Hadir',       cls: 'bg-green-100 text-green-700 border-green-300' },
    { value: 'tidak_hadir', label: 'Tidak Hadir',  cls: 'bg-red-100 text-red-700 border-red-300' },
    { value: 'izin',        label: 'Izin',         cls: 'bg-blue-100 text-blue-700 border-blue-300' },
    { value: 'sakit',       label: 'Sakit',        cls: 'bg-purple-100 text-purple-700 border-purple-300' },
];

function toggleManual() {
    const el = document.getElementById('manual-form');
    el.classList.toggle('hidden');
    if (!el.classList.contains('hidden')) document.getElementById('manual-period').focus();
}

function buildStudentRow(student, namePrefix, defaultStatus = 'hadir') {
    const row = document.createElement('div');
    row.className = 'flex items-center justify-between bg-white rounded-lg border border-gray-100 px-3 py-2';

    const info = document.createElement('div');
    info.innerHTML = `<p class="text-xs font-medium text-gray-800">${student.name}</p>
                      <p class="text-[10px] text-gray-400">${student.nis ?? ''}</p>`;
    info.className = 'flex-1 min-w-0 mr-3';

    const controls = document.createElement('div');
    controls.className = 'flex items-center gap-1.5 flex-shrink-0';

    STATUS_OPTIONS.forEach(opt => {
        const label = document.createElement('label');
        label.className = 'cursor-pointer';

        const radio = document.createElement('input');
        radio.type  = 'radio';
        radio.name  = `${namePrefix}[${student.id}][status]`;
        radio.value = opt.value;
        radio.checked = opt.value === defaultStatus;
        radio.className = 'sr-only peer';

        // Hidden fields for student id
        const hiddenId = document.createElement('input');
        hiddenId.type  = 'hidden';
        hiddenId.name  = `${namePrefix}[${student.id}][id]`;
        hiddenId.value = student.id;

        const span = document.createElement('span');
        span.textContent = opt.label;
        span.className = `px-2 py-0.5 rounded-md border text-[10px] font-medium transition-all ${opt.cls} peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-current`;

        // Update span style on peer change via JS since sr-only peer can't work with separate elements
        radio.addEventListener('change', () => {
            row.querySelectorAll('span.status-opt').forEach(s => s.classList.remove('ring-2','ring-offset-1','ring-current'));
            if (radio.checked) span.classList.add('ring-2','ring-offset-1','ring-current');
        });
        span.classList.add('status-opt');
        if (opt.value === defaultStatus) span.classList.add('ring-2','ring-offset-1','ring-current');

        span.addEventListener('click', () => radio.click());

        label.appendChild(hiddenId);
        label.appendChild(radio);
        label.appendChild(span);
        controls.appendChild(label);
    });

    row.appendChild(info);
    row.appendChild(controls);
    return row;
}

// Load students for MANUAL form
async function loadStudents(formType, classId) {
    if (!classId) return;

    const wrap = document.getElementById('manual-students-wrap');
    const list = document.getElementById('manual-students-list');
    list.innerHTML = '<p class="text-xs text-gray-400 py-2 text-center">Memuat…</p>';
    wrap.classList.remove('hidden');

    const res = await fetch(`${API_URL}?class_id=${classId}`);
    const students = await res.json();

    list.innerHTML = '';
    students.forEach(s => {
        list.appendChild(buildStudentRow(s, 'students', 'hadir'));
    });
}

// Load students for SCHEDULED session (lazy, button-triggered)
async function loadStudentsScheduled(idx, classId) {
    const container = document.getElementById(`students-${idx}`);
    const btn = document.getElementById(`btn-load-${idx}`);

    if (!container.classList.contains('hidden')) {
        container.classList.add('hidden');
        btn.textContent = 'Muat daftar siswa ↓';
        return;
    }

    btn.textContent = 'Memuat…';
    const res = await fetch(`${API_URL}?class_id=${classId}`);
    const students = await res.json();

    container.innerHTML = '';
    students.forEach(s => {
        container.appendChild(buildStudentRow(s, `attendances[${idx}][students]`, 'hadir'));
    });

    container.classList.remove('hidden');
    btn.textContent = 'Sembunyikan ↑';
}
</script>
@endsection
