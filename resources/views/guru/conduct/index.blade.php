@extends('layouts.guru')
@section('title', 'Rekap Catatan Perilaku')
@section('page-title', 'Rekap Catatan Perilaku')

@section('content')
<div class="space-y-4">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">Catatan Negatif</p>
            <p class="text-2xl font-extrabold text-red-500">{{ $totalPelanggaran }}</p>
            <p class="text-xs text-gray-400">total catatan</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">Catatan Positif</p>
            <p class="text-2xl font-extrabold text-green-600">{{ $totalPrestasi }}</p>
            <p class="text-xs text-gray-400">total catatan</p>
        </div>
    </div>

    {{-- Filter kelas + tombol catat --}}
    <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
        <form method="GET" action="{{ route('guru.conduct.index') }}" class="flex-1">
            <label class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
            <select name="class_id" onchange="this.form.submit()"
                class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('guru.conduct.choose') }}"
            class="flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Catat Baru
        </a>
    </div>

    {{-- Search --}}
    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
        </svg>
        <input type="text" id="search-input" placeholder="Cari nama siswa..."
            oninput="filterStudents(this.value)"
            class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    {{-- Daftar Siswa --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" id="student-list">
        @forelse($students as $student)
        <div class="student-row flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors"
            data-name="{{ strtolower($student->name) }}">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-blue-600">{{ $student->initials }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-sm text-gray-800 truncate">{{ $student->name }}</p>
                <p class="text-xs text-gray-400">{{ $student->nis }}</p>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                @if($student->pelanggaran_count > 0)
                <span class="flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    {{ $student->pelanggaran_count }}
                </span>
                @endif
                @if($student->prestasi_count > 0)
                <span class="flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold bg-green-50 text-green-700">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    {{ $student->prestasi_count }}
                </span>
                @endif
                @if($student->pelanggaran_count == 0 && $student->prestasi_count == 0)
                <span class="text-sm text-gray-300">—</span>
                @endif
                <a href="{{ route('guru.conduct.student', $student) }}"
                    class="w-8 h-8 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-blue-100 text-gray-500 hover:text-blue-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="px-4 py-10 text-center text-gray-400 text-sm">
            Tidak ada siswa di kelas ini.
        </div>
        @endforelse
        <div id="no-result" class="hidden px-4 py-10 text-center text-gray-400 text-sm">
            Tidak ada siswa yang cocok.
        </div>
    </div>

</div>

<script>
function filterStudents(q) {
    const rows = document.querySelectorAll('.student-row');
    const lower = q.toLowerCase().trim();
    let visible = 0;
    rows.forEach(row => {
        const match = !lower || row.dataset.name.includes(lower);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('no-result').classList.toggle('hidden', visible > 0 || !lower);
}
</script>
@endsection
