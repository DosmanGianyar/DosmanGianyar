@extends('layouts.guru')
@section('title', 'Absensi Harian')
@section('page-title', 'Absensi Harian')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <form method="GET" action="{{ route('guru.attendance.index') }}"
        class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:items-end">
        <div class="flex gap-3 flex-1">
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
                <select name="class_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        @php $canEdit = auth()->user()->homeroomClass || auth()->user()->isBk() || auth()->user()->role === 'admin'; @endphp
        @if($canEdit)
        <div class="flex gap-2 sm:w-auto">
            <button type="button" onclick="openManualModal(null, '{{ $date }}')"
                class="flex items-center justify-center gap-1.5 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Input Manual
            </button>
            <a href="{{ route('guru.attendance.dispensation.create') }}"
                class="flex items-center justify-center gap-1.5 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Dispensasi
            </a>
        </div>
        @endif
    </form>

    {{-- Summary chips --}}
    @php
        $chips = [
            'hadir'      => ['label' => 'Hadir',      'bg' => 'bg-green-100',  'text' => 'text-green-700'],
            'terlambat'  => ['label' => 'Terlambat',  'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
            'izin'       => ['label' => 'Izin',       'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
            'sakit'      => ['label' => 'Sakit',      'bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
            'alpa'       => ['label' => 'Alpa',       'bg' => 'bg-red-100',    'text' => 'text-red-700'],
            'dispensasi' => ['label' => 'Dispensasi', 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-700'],
        ];
    @endphp
    <div class="flex flex-wrap gap-2">
        @foreach($chips as $key => $chip)
        <div class="flex items-center gap-1.5 {{ $chip['bg'] }} {{ $chip['text'] }} px-3 py-1.5 rounded-full text-xs font-semibold">
            {{ $chip['label'] }}<span class="font-bold">{{ $summary[$key] }}</span>
        </div>
        @endforeach
    </div>

    {{-- ── Mobile: card list (tersembunyi di md ke atas) ── --}}
    <div class="md:hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden divide-y divide-gray-50">
        @forelse($students as $student)
        @php
            $att         = $student->attendances->first();
            $status      = $effectiveStatuses[$student->id] ?? 'alpa';
            $statusChip  = $chips[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => $status];
            $checkInTime = $att?->check_in_time ? \Carbon\Carbon::parse($att->check_in_time)->format('H:i') : null;
        @endphp
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-blue-600">{{ $student->initials }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-sm text-gray-800 truncate">{{ $student->name }}</p>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusChip['bg'] }} {{ $statusChip['text'] }}">
                        {{ ucfirst($statusChip['label']) }}
                    </span>
                    @if($checkInTime)
                        <span class="text-xs text-gray-400">{{ $checkInTime }}</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                @if($att?->photo)
                <a href="{{ Storage::url($att->photo) }}" target="_blank"
                    class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </a>
                @endif
                @if($canEdit)
                <button type="button"
                    onclick="openManualModal({{ $student->id }}, '{{ $date }}')"
                    class="w-9 h-9 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>
                @endif
                {{-- TESTING ONLY — hapus tombol ini setelah tahap uji coba selesai --}}
                @if(auth()->user()->role === 'admin' && $att)
                <form method="POST" action="{{ route('guru.attendance.destroy', $att->id) }}"
                    onsubmit="return confirm('Hapus data absensi {{ $student->name }} tanggal {{ $date }}? (mode testing)');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="px-4 py-10 text-center text-gray-400 text-sm">
            Tidak ada data siswa di kelas ini.
        </div>
        @endforelse
    </div>

    {{-- ── Desktop: tabel (tersembunyi di bawah md) ── --}}
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Siswa</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jam Masuk</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($students as $student)
                @php
                    $att        = $student->attendances->first();
                    $status     = $effectiveStatuses[$student->id] ?? 'alpa';
                    $statusChip = $chips[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => $status];
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-blue-600">{{ $student->initials }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $student->name }}</p>
                                <p class="text-xs text-gray-400">{{ $student->nis }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $att?->check_in_time ? \Carbon\Carbon::parse($att->check_in_time)->format('H:i') : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusChip['bg'] }} {{ $statusChip['text'] }}">
                            {{ ucfirst($statusChip['label']) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($att?->photo)
                            <a href="{{ Storage::url($att->photo) }}" target="_blank"
                                class="text-blue-500 hover:text-blue-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </a>
                            @endif
                            @if($canEdit)
                            <button type="button"
                                onclick="openManualModal({{ $student->id }}, '{{ $date }}')"
                                class="flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg hover:bg-emerald-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Ubah
                            </button>
                            @endif
                            {{-- TESTING ONLY — hapus tombol ini setelah tahap uji coba selesai --}}
                            @if(auth()->user()->role === 'admin' && $att)
                            <form method="POST" action="{{ route('guru.attendance.destroy', $att->id) }}"
                                onsubmit="return confirm('Hapus data absensi {{ $student->name }} tanggal {{ $date }}? (mode testing)');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">
                        Tidak ada data siswa di kelas ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- Flash toast --}}
@if(session('success'))
<div id="toast-success"
    class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 bg-emerald-600 text-white text-sm font-medium px-5 py-3 rounded-2xl shadow-lg">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
<script>setTimeout(()=>{ const t=document.getElementById('toast-success'); if(t) t.remove(); }, 3500);</script>
@endif

{{-- Manual Attendance Modal --}}
<div id="modal-manual" class="hidden fixed inset-0 z-40 items-end sm:items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeManualModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800">Input Absensi Manual</h3>
            <button type="button" onclick="closeManualModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('guru.attendance.manual') }}" class="space-y-3">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Siswa</label>
                <select id="modal-student" name="student_id" required
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                    <option value="">— Pilih Siswa —</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
                <input id="modal-date" type="date" name="date" required
                    max="{{ today()->toDateString() }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" required
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                    <option value="hadir">Hadir</option>
                    <option value="terlambat">Terlambat</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alpa">Alpa</option>
                    <option value="dispensasi">Dispensasi</option>
                </select>
            </div>

            <div class="pt-1 flex gap-3">
                <button type="button" onclick="closeManualModal()"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openManualModal(studentId, date) {
    const modal = document.getElementById('modal-manual');
    const sel = document.getElementById('modal-student');
    const dateInput = document.getElementById('modal-date');
    if (studentId) sel.value = studentId;
    if (date) dateInput.value = date;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeManualModal() {
    const modal = document.getElementById('modal-manual');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}
</script>
@endsection
