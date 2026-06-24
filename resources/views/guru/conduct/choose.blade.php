@extends('layouts.guru')
@section('title', 'Catat Prestasi / Pelanggaran')
@section('page-title', 'Catat Prestasi / Pelanggaran')

@section('content')
<div class="max-w-lg mx-auto space-y-6">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-2">
        <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <p class="text-green-700 text-sm">{{ session('success') }}</p>
    </div>
    @endif

    {{-- ── Tombol Dropdown ────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row gap-3">

        {{-- Dropdown: Prestasi --}}
        <div class="relative flex-1" id="dd-prestasi">
            <button type="button" onclick="toggleDropdown('prestasi')"
                class="w-full flex items-center justify-between gap-3 px-4 py-3.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Prestasi
                </div>
                <svg id="chevron-prestasi" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="menu-prestasi"
                class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-20">
                <a href="{{ route('guru.conduct.create', ['context' => 'akademik']) }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-green-50 transition-colors">
                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <div>
                        <p class="font-medium">Prestasi Akademik</p>
                        <p class="text-xs text-gray-400">Pencapaian selama kegiatan belajar mengajar</p>
                    </div>
                </a>
                <div class="h-px bg-gray-50 mx-3"></div>
                <a href="{{ route('guru.conduct.create', ['context' => 'lomba']) }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-green-50 transition-colors">
                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    <div>
                        <p class="font-medium">Prestasi Lomba</p>
                        <p class="text-xs text-gray-400">Prestasi dalam perlombaan atau kejuaraan</p>
                    </div>
                </a>
                <div class="h-px bg-gray-100 mx-3"></div>
                <a href="{{ route('guru.conduct.create', ['context' => 'lainnya_prestasi']) }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <div>
                        <p class="font-medium text-gray-600">Lainnya...</p>
                        <p class="text-xs text-gray-400">Catat prestasi dengan deskripsi bebas</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Dropdown: Pelanggaran --}}
        <div class="relative flex-1" id="dd-pelanggaran">
            <button type="button" onclick="toggleDropdown('pelanggaran')"
                class="w-full flex items-center justify-between gap-3 px-4 py-3.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Pelanggaran
                </div>
                <svg id="chevron-pelanggaran" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="menu-pelanggaran"
                class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-20">
                <a href="{{ route('guru.conduct.create', ['context' => 'kelas']) }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4 text-yellow-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <div>
                        <p class="font-medium">Pelanggaran Kelas</p>
                        <p class="text-xs text-gray-400">Pelanggaran saat kegiatan belajar mengajar</p>
                    </div>
                </a>
                <div class="h-px bg-gray-50 mx-3"></div>
                <a href="{{ route('guru.conduct.create', ['context' => 'sidak']) }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <div>
                        <p class="font-medium">Pelanggaran Sidak</p>
                        <p class="text-xs text-gray-400">Pelanggaran yang ditemukan saat inspeksi mendadak</p>
                    </div>
                </a>
                <div class="h-px bg-gray-100 mx-3"></div>
                <a href="{{ route('guru.conduct.create', ['context' => 'lainnya_pelanggaran']) }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <div>
                        <p class="font-medium text-gray-600">Lainnya...</p>
                        <p class="text-xs text-gray-400">Catat pelanggaran dengan deskripsi bebas</p>
                    </div>
                </a>
            </div>
        </div>

    </div>

    {{-- Link rekap --}}
    <p class="text-center">
        <a href="{{ route('guru.conduct.index') }}" class="text-xs text-gray-400 hover:text-blue-600 transition-colors">
            ← Lihat Rekap Siswa
        </a>
    </p>

</div>

<script>
function toggleDropdown(name) {
    const menu    = document.getElementById('menu-' + name);
    const chevron = document.getElementById('chevron-' + name);
    const isOpen  = !menu.classList.contains('hidden');

    // close all first
    ['prestasi', 'pelanggaran'].forEach(n => {
        document.getElementById('menu-' + n).classList.add('hidden');
        document.getElementById('chevron-' + n).classList.remove('rotate-180');
    });

    if (!isOpen) {
        menu.classList.remove('hidden');
        chevron.classList.add('rotate-180');
    }
}

// close on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('#dd-prestasi') && !e.target.closest('#dd-pelanggaran')) {
        ['prestasi', 'pelanggaran'].forEach(n => {
            document.getElementById('menu-' + n).classList.add('hidden');
            document.getElementById('chevron-' + n).classList.remove('rotate-180');
        });
    }
});
</script>
@endsection
