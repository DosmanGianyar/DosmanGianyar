<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penugasan Guru Wali — SIMS | SMA Negeri 1 Gianyar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans min-h-screen p-6">

<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="/admin" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-lg font-bold text-gray-800">Penugasan Guru Wali</h1>
            <p class="text-xs text-gray-500">Tugaskan guru sebagai Guru Wali untuk siswa</p>
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

    {{-- Info --}}
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-5 flex items-start gap-3">
        <svg class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-indigo-700">
            Guru Wali adalah guru yang ditugaskan untuk membimbing beberapa siswa secara personal, <strong>berbeda dengan Wali Kelas</strong>.
            Satu siswa hanya boleh memiliki satu Guru Wali.
        </p>
    </div>

    {{-- Daftar Guru --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="font-semibold text-gray-700">Daftar Guru</p>
            <span class="text-xs text-gray-400">{{ $teachers->count() }} guru</span>
        </div>

        @if($teachers->isEmpty())
        <div class="py-16 text-center">
            <p class="text-sm text-gray-400">Belum ada data guru.</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($teachers as $teacher)
            <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                    <span class="text-indigo-700 font-bold text-sm">
                        {{ strtoupper(substr($teacher->name, 0, 1)) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $teacher->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $teacher->subject ?? $teacher->nip ?? '—' }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $teacher->student_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        {{ $teacher->student_count }} Siswa
                    </span>
                    <a href="{{ route('admin.guru-wali.show', $teacher) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors">
                        Kelola
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
</body>
</html>
