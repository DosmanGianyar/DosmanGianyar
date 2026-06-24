@extends('layouts.guru')
@section('title', 'Rekap Prestasi & Pelanggaran')
@section('page-title', 'Rekap Prestasi & Pelanggaran')

@section('content')
<div class="space-y-4">

    {{-- Filter kelas + tombol input --}}
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
        <a href="{{ route('guru.conduct.create') }}"
            class="flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Catat Baru
        </a>
    </div>

    {{-- Daftar Siswa --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @forelse($students as $student)
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-blue-600">{{ $student->initials }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-sm text-gray-800 truncate">{{ $student->name }}</p>
                <p class="text-xs text-gray-400">{{ $student->nis }}</p>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                @if($student->prestasi_count > 0)
                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-green-50 text-green-700">
                    ★ {{ $student->prestasi_count }}
                </span>
                @endif
                @if($student->pelanggaran_count > 0)
                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-50 text-red-700">
                    ⚠ {{ $student->pelanggaran_count }}
                </span>
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
    </div>

</div>
@endsection
