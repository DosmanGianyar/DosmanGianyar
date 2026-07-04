<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — SIMS | SMA Negeri 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">

{{-- ─── Mobile Top Bar ──────────────────────────────────────────────── --}}
<div class="lg:hidden fixed top-0 left-0 right-0 z-30 bg-white border-b border-gray-200 h-14 flex items-center px-4 justify-between gap-3">
    <button onclick="toggleSidebar()"
        class="w-10 h-10 flex items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 active:bg-gray-200 transition-colors shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    <span class="text-sm font-semibold text-gray-800 truncate flex-1 text-center">
        @yield('page-title', 'Dashboard')
    </span>
    @if(auth()->user()->photo)
        <img src="{{ auth()->user()->photo_url }}"
            class="w-8 h-8 rounded-full object-cover border-2 border-blue-100 shrink-0">
    @else
        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
            {{ auth()->user()->initials }}
        </div>
    @endif
</div>

{{-- ─── Sidebar Overlay (Mobile) ───────────────────────────────────── --}}
<div id="overlay" onclick="toggleSidebar()"
    class="hidden fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

{{-- ─── Sidebar ─────────────────────────────────────────────────────── --}}
<aside id="sidebar"
    class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 flex flex-col overflow-hidden">

    {{-- Logo --}}
    <div class="shrink-0 h-16 flex items-center gap-3 px-4 border-b border-gray-100">
        <img src="/img/logo_sekolah.png" alt="Logo"
            class="w-8 h-8 rounded-lg object-contain shrink-0">
        <div class="overflow-hidden">
            <p class="text-gray-900 font-bold text-sm leading-tight truncate">SMA Negeri 1 Gianyar</p>
            <p class="text-gray-400 text-[11px] mt-0.5">Portal Guru</p>
        </div>
    </div>

    {{-- User Card --}}
    <div class="shrink-0 px-3 py-3 border-b border-gray-100">
        <div class="flex items-center gap-2.5 bg-gray-50 rounded-xl px-3 py-2.5">
            @if(auth()->user()->photo)
                <img src="{{ auth()->user()->photo_url }}" class="w-8 h-8 rounded-full object-cover shrink-0">
            @else
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                    {{ auth()->user()->initials }}
                </div>
            @endif
            <div class="overflow-hidden flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-gray-400 truncate">{{ auth()->user()->subject ?? 'Guru' }}</p>
            </div>
        </div>
    </div>

    {{-- Nav Items --}}
    <nav class="px-2 py-3"
        style="flex:1 1 0%;min-height:0;overflow-y:scroll;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;">
        @php
            $navGroups = [
                [
                    'label' => null,
                    'items' => [
                        ['route' => 'guru.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ],
                ],
                [
                    'label' => 'Kesiswaan',
                    'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'items' => [
                        ['route' => 'guru.conduct.index',           'label' => 'Rekap Siswa',          'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['route' => 'guru.conduct.choose',          'label' => 'Catat Prestasi/Peln.', 'icon' => 'M12 4v16m8-8H4'],
                        ['route' => 'guru.attendance.permits',      'label' => 'Approval Izin/Sakit',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['route' => 'guru.forgot-attendance.index', 'label' => 'Lupa Absen Siswa',     'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['route' => 'guru.early-checkout.index',    'label' => 'Izin Pulang Awal',     'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'],
                    ],
                ],
                [
                    'label' => 'Kurikulum',
                    'icon'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    'items' => [
                        ['route' => 'guru.grades.index',                'label' => 'Input Nilai',      'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                        ['route' => 'guru.teacher-attendance.index',    'label' => 'Absensi Mengajar', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        ['route' => 'guru.homeroom-consultation.index', 'label' => 'Jurnal Bimbingan', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                    ],
                ],
                [
                    'label' => 'Laporan',
                    'icon'  => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'items' => [
                        ['route' => 'guru.export.attendance.form', 'label' => 'Export Absensi', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ],
                ],
                [
                    'label' => 'Akun',
                    'icon'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                    'items' => [
                        ['route' => 'guru.profile', 'label' => 'Profil Saya', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ],
                ],
            ];
        @endphp

        {{-- Flat items (no group label) --}}
        @foreach($navGroups as $group)
            @if(!$group['label'])
                @foreach($group['items'] as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                        class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150 mb-1
                            {{ $active ? 'bg-blue-600 text-white shadow-sm shadow-blue-200' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800' }}">
                        <svg class="w-4.5 h-4.5 shrink-0 {{ $active ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            @endif
        @endforeach

        {{-- Accordion groups --}}
        @foreach($navGroups as $group)
            @if($group['label'])
                @php
                    $groupActive = collect($group['items'])->contains(fn($item) =>
                        request()->routeIs($item['route']) ||
                        ($item['route'] === 'guru.conduct.choose' && request()->routeIs('guru.conduct.create'))
                    );
                @endphp
                <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }" class="mb-0.5">
                    {{-- Parent toggle button --}}
                    <button @click="open = !open"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150
                            {{ $groupActive ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-800' }}">
                        <svg class="w-4.5 h-4.5 shrink-0 {{ $groupActive ? 'text-blue-500' : 'text-gray-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $group['icon'] }}"/>
                        </svg>
                        <span class="flex-1 text-left truncate">{{ $group['label'] }}</span>
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 {{ $groupActive ? 'text-blue-400' : 'text-gray-400' }}"
                            :class="open ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Children --}}
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="mt-0.5 ml-3 pl-3 border-l-2 border-gray-100 space-y-0.5 pb-1">
                        @foreach($group['items'] as $item)
                            @php
                                $active = request()->routeIs($item['route'])
                                    || ($item['route'] === 'guru.conduct.choose' && request()->routeIs('guru.conduct.create'));
                            @endphp
                            <a href="{{ route($item['route']) }}"
                                class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-sm font-medium transition-all duration-150
                                    {{ $active ? 'bg-blue-600 text-white shadow-sm shadow-blue-200' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800' }}">
                                <svg class="w-4 h-4 shrink-0 {{ $active ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Logout --}}
        <div class="pt-3 mt-2 border-t border-gray-100 pb-6">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-500 hover:bg-red-50 hover:text-red-600 transition-all duration-150">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </nav>
</aside>

{{-- ─── Main Content ────────────────────────────────────────────────── --}}
<main class="lg:ml-64 min-h-screen pt-14 lg:pt-0">
    {{-- Desktop Header --}}
    <div class="hidden lg:flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
            <p class="text-xs text-gray-500">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
        <div class="flex items-center gap-4">
            {{-- Notifikasi --}}
            <button class="relative text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
        </div>
    </div>

    {{-- Flash Messages via SweetAlert2 --}}
    @if(session('success'))
    <script>document.addEventListener('DOMContentLoaded',()=>swalToast('success',@json(session('success'))));</script>
    @endif
    @if(session('error'))
    <script>document.addEventListener('DOMContentLoaded',()=>swalToast('error',@json(session('error'))));</script>
    @endif
    @if(session('warning'))
    <script>document.addEventListener('DOMContentLoaded',()=>swalToast('warning',@json(session('warning'))));</script>
    @endif

    {{-- Page Content --}}
    <div class="p-4 lg:p-6">
        @yield('content')
    </div>
</main>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const isOpen  = !sidebar.classList.contains('-translate-x-full');

        sidebar.classList.toggle('-translate-x-full', isOpen);
        overlay.classList.toggle('hidden', isOpen);
    }
</script>
</body>
</html>
