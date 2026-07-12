<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — SIMS | SMA Negeri 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    <meta name="theme-color" content="#1e3a8a">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-gray-50 font-sans pb-20">

{{-- ─── Top Header ──────────────────────────────────────────────────── --}}
<header class="fixed top-0 left-0 right-0 z-20 h-14 flex items-center justify-between px-4"
    style="background: linear-gradient(135deg, #0f2460 0%, #1a3a8f 50%, #1e3fad 100%);
           box-shadow: 0 2px 16px rgba(15,36,96,0.45);">
    @php
        $isRootPage = request()->routeIs('orangtua.dashboard');
    @endphp

    @if($isRootPage)
        <a href="{{ route('orangtua.dashboard') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg overflow-hidden bg-white/15 flex items-center justify-center p-1 ring-1 ring-white/25">
                <img src="/img/logo_sekolah.png" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div class="leading-tight">
                <p class="font-bold text-white text-xs leading-none tracking-wide">SMA N 1 Gianyar</p>
                <p class="text-blue-200 text-[10px] tracking-widest uppercase">SIMS</p>
            </div>
        </a>
    @else
        <button onclick="history.back()"
            class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 transition-colors -ml-1">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    @endif

    <h2 class="text-sm font-semibold text-white tracking-wide">@yield('page-title', 'Beranda')</h2>

    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-white/20 ring-2 ring-white/40 flex items-center justify-center text-white text-xs font-bold">
            {{ auth()->user()->initials }}
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Keluar"
                class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 transition-colors">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
</header>

{{-- ─── Flash Messages via SweetAlert2 ─────────────────────────────── --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded',()=>swalToast('success',@json(session('success'))));</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded',()=>swalToast('error',@json(session('error'))));</script>
@endif

{{-- ─── Main Content ────────────────────────────────────────────────── --}}
<main class="pt-16 px-4 pb-24" style="padding-bottom: max(6rem, calc(4rem + env(safe-area-inset-bottom) + 1rem))">
    @yield('content')
</main>

{{-- ─── Bottom Navigation ───────────────────────────────────────────── --}}
@php
    /** @var \App\Models\User $navStudent */
    $navStudent = $student ?? (auth()->user()->children->count() === 1 ? auth()->user()->children->first() : null);
    $isDashboard   = request()->routeIs('orangtua.dashboard');
    $isAttendance  = request()->routeIs('orangtua.attendance.*');
    $isConduct     = request()->routeIs('orangtua.conduct.*');
    $isAchievement = request()->routeIs('orangtua.achievements.*');
@endphp
<nav class="fixed bottom-0 left-0 right-0 z-20 bg-white border-t border-gray-200 safe-area-bottom">
    <div class="flex items-center h-16">

        <a href="{{ route('orangtua.dashboard') }}"
            class="flex-1 flex flex-col items-center gap-0.5 py-2 {{ $isDashboard ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="w-5 h-5" fill="{{ $isDashboard ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-xs font-medium">Beranda</span>
        </a>

        <a href="{{ $navStudent ? route('orangtua.attendance.history', $navStudent->id) : route('orangtua.dashboard') }}"
            class="flex-1 flex flex-col items-center gap-0.5 py-2 {{ $isAttendance ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="w-5 h-5" fill="{{ $isAttendance ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-xs font-medium">Absensi</span>
        </a>

        <a href="{{ $navStudent ? route('orangtua.conduct.index', $navStudent->id) : route('orangtua.dashboard') }}"
            class="flex-1 flex flex-col items-center gap-0.5 py-2 {{ $isConduct ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="w-5 h-5" fill="{{ $isConduct ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-xs font-medium">Catatan</span>
        </a>

        <a href="{{ $navStudent ? route('orangtua.achievements.index', $navStudent->id) : route('orangtua.dashboard') }}"
            class="flex-1 flex flex-col items-center gap-0.5 py-2 {{ $isAchievement ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="w-5 h-5" fill="{{ $isAchievement ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <span class="text-xs font-medium">Prestasi</span>
        </a>

    </div>
</nav>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
}
</script>

</body>
</html>
