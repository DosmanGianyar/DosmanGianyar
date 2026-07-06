<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $teacher->name }} — Guru Wali</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans min-h-screen p-6">

<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.guru-wali.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-lg font-bold text-gray-800">{{ $teacher->name }}</h1>
            <p class="text-xs text-gray-500">Kelola siswa wali &mdash; {{ $assignedRecords->count() }} siswa terdaftar</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
        <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <p class="text-red-700 text-sm font-medium">{{ session('error') }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

        {{-- Tambah Siswa --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-4">Tugaskan Siswa Baru</p>
            @if($availableStudents->isEmpty())
            <p class="text-sm text-gray-400 py-4 text-center">Semua siswa sudah memiliki Guru Wali.</p>
            @else
            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Kelas</label>
                <select id="class-select" onchange="filterStudentsByClass()"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih kelas --</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <form method="POST" action="{{ route('admin.guru-wali.assign', $teacher) }}" id="assign-form">
                @csrf
                <div class="mb-3 max-h-64 overflow-y-auto border border-gray-100 rounded-xl">
                    <p id="student-checklist-empty" class="text-sm text-gray-400 py-6 text-center">
                        Pilih kelas terlebih dahulu.
                    </p>
                    <div class="divide-y divide-gray-50">
                        @foreach($availableStudents as $s)
                        <label class="student-row hidden items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer"
                            data-class-id="{{ $s->class_id }}">
                            <input type="checkbox" name="student_ids[]" value="{{ $s->id }}"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 shrink-0">
                            <span class="text-sm text-gray-700 truncate">
                                {{ $s->name }}
                                @if($s->nis) <span class="text-gray-400 text-xs">· {{ $s->nis }}</span> @endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 rounded-xl transition-colors">
                    + Tugaskan Siswa Terpilih
                </button>
            </form>
            @endif
        </div>

        {{-- Statistik Bimbingan --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <p class="font-semibold text-gray-700 mb-4">Statistik Bimbingan</p>
            <div class="grid grid-cols-2 gap-3">
                @foreach(['pending' => ['Menunggu','amber'], 'scheduled' => ['Dijadwalkan','blue'], 'completed' => ['Selesai','green'], 'cancelled' => ['Dibatalkan','gray']] as $st => [$label, $color])
                <div class="text-center p-3 rounded-xl bg-{{ $color }}-50 border border-{{ $color }}-100">
                    <p class="text-2xl font-bold text-{{ $color }}-600">{{ $counts[$st] ?? 0 }}</p>
                    <p class="text-xs text-{{ $color }}-500 mt-0.5">{{ $label }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Siswa Terdaftar --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="font-semibold text-gray-700">Siswa Terdaftar</p>
            <span class="text-xs text-gray-400">{{ $assignedRecords->count() }} siswa</span>
        </div>

        @if($assignedRecords->isEmpty())
        <div class="py-12 text-center">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-sm text-gray-400">Belum ada siswa yang ditugaskan.</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($assignedRecords as $record)
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                    <span class="text-emerald-700 font-bold text-xs">
                        {{ strtoupper(substr($record->student->name, 0, 1)) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $record->student->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $record->student->schoolClass?->name ?? '—' }}
                        @if($record->student->nis) · NIS {{ $record->student->nis }} @endif
                    </p>
                </div>
                <div class="text-xs text-gray-400 shrink-0 mr-3">
                    {{ $record->assigned_at?->format('d M Y') ?? '—' }}
                </div>
                <form method="POST" action="{{ route('admin.guru-wali.remove', [$teacher, $record->student]) }}"
                    onsubmit="return confirm('Hapus {{ $record->student->name }} dari daftar siswa wali?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Riwayat Bimbingan --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="font-semibold text-gray-700">Riwayat Bimbingan</p>
            <span class="text-xs text-gray-400">{{ $consultations->count() }} pengajuan</span>
        </div>

        @if($consultations->isEmpty())
        <div class="py-12 text-center">
            <p class="text-sm text-gray-400">Belum ada riwayat bimbingan.</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($consultations as $c)
            @php
                $statusMap = ['pending' => ['Menunggu','amber'], 'scheduled' => ['Dijadwalkan','blue'], 'completed' => ['Selesai','green'], 'cancelled' => ['Dibatalkan','gray']];
                [$statusLabel, $color] = $statusMap[$c->status] ?? ['—','gray'];
            @endphp
            <div class="flex items-start gap-4 px-5 py-3.5">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $c->student?->name ?? '—' }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $c->topic }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $c->created_at->format('d M Y') }}</p>
                </div>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-700 shrink-0 mt-0.5">
                    {{ $statusLabel }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

<script>
function filterStudentsByClass() {
    const classId  = document.getElementById('class-select').value;
    const rows     = document.querySelectorAll('.student-row');
    const emptyMsg = document.getElementById('student-checklist-empty');
    let visibleCount = 0;

    rows.forEach(function (row) {
        if (classId && row.dataset.classId === classId) {
            row.classList.remove('hidden');
            row.classList.add('flex');
            visibleCount++;
        } else {
            row.classList.add('hidden');
            row.classList.remove('flex');
            row.querySelector('input[type=checkbox]').checked = false;
        }
    });

    if (!classId) {
        emptyMsg.textContent = 'Pilih kelas terlebih dahulu.';
        emptyMsg.classList.remove('hidden');
    } else if (visibleCount === 0) {
        emptyMsg.textContent = 'Tidak ada siswa tersedia di kelas ini.';
        emptyMsg.classList.remove('hidden');
    } else {
        emptyMsg.classList.add('hidden');
    }
}

const assignForm = document.getElementById('assign-form');
if (assignForm) {
    assignForm.addEventListener('submit', function (e) {
        const checked = assignForm.querySelectorAll('input[name="student_ids[]"]:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu siswa terlebih dahulu.');
        }
    });
}
</script>
</body>
</html>
