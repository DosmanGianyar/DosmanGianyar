<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — SIMS | SMA Negeri 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans pb-20">

{{-- ─── Top Header ──────────────────────────────────────────────────── --}}
<header class="fixed top-0 left-0 right-0 z-20 bg-white border-b border-gray-200 h-14 flex items-center justify-between px-4">
    @php
        $isRootPage = request()->routeIs(
            'siswa.dashboard', 'siswa.kesiswaan',
            'siswa.kurikulum', 'siswa.prasarana', 'siswa.humas'
        );
    @endphp

    {{-- Kiri: logo di halaman utama, tombol back di sub-halaman --}}
    @if($isRootPage)
        <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2">
            <img src="/img/logo_sekolah.png" alt="Logo"
                class="w-8 h-8 rounded-lg object-contain">
            <div class="leading-tight">
                <p class="font-bold text-blue-700 text-xs leading-none">SMA N 1 Gianyar</p>
                <p class="text-gray-400 text-[10px]">SIMS</p>
            </div>
        </a>
    @else
        <button onclick="history.back()"
            class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-colors -ml-1">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    @endif

    <h2 class="text-sm font-semibold text-gray-700">@yield('page-title', 'Beranda')</h2>

    <div class="flex items-center gap-2">
        {{-- Bell notifikasi --}}
        @php
            $unreadCount = \App\Models\AppNotification::forUser(auth()->id())->unread()->count();
        @endphp
        <a href="{{ route('siswa.notifications.index') }}" class="relative w-8 h-8 flex items-center justify-center">
            <svg class="w-5 h-5 {{ $unreadCount > 0 ? 'text-blue-600' : 'text-gray-400' }}"
                fill="{{ $unreadCount > 0 ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center px-1 leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
            @endif
        </a>

        <a href="{{ route('siswa.profile') }}" class="flex items-center">
            @if(auth()->user()->photo)
                <img src="{{ auth()->user()->photo_url }}" class="w-8 h-8 rounded-full object-cover border-2 border-blue-200">
            @else
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ auth()->user()->initials }}
                </div>
            @endif
        </a>
    </div>
</header>

{{-- ─── Flash Messages via SweetAlert2 ─────────────────────────────── --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded',()=>swalToast('success',@json(session('success'))));</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded',()=>swalToast('error',@json(session('error'))));</script>
@endif
@if(session('warning'))
<script>document.addEventListener('DOMContentLoaded',()=>swalToast('warning',@json(session('warning'))));</script>
@endif

{{-- ─── Main Content ────────────────────────────────────────────────── --}}
<main class="pt-16 px-4 pb-4">
    @yield('content')
</main>

{{-- ─── Bottom Navigation ───────────────────────────────────────────── --}}
<nav class="fixed bottom-0 left-0 right-0 z-20 bg-white border-t border-gray-200 safe-area-bottom">
    @php
        $isKesiswaan = request()->routeIs(
            'siswa.kesiswaan',
            'siswa.conduct.*', 'siswa.permit.*', 'siswa.achievements.*',
            'siswa.voting.*', 'siswa.exit-pass.*'
        );
        $isKurikulum = request()->routeIs('siswa.kurikulum', 'siswa.teacher-attendance.index', 'siswa.homeroom-consultation.*');
        $isPrasarana = request()->routeIs('siswa.prasarana', 'siswa.sarpras.*');
        $isHumas     = request()->routeIs('siswa.humas', 'siswa.announcements.*');
    @endphp
    <div class="flex items-center h-16">

        {{-- Kesiswaan --}}
        <a href="{{ route('siswa.kesiswaan') }}"
            class="flex-1 flex flex-col items-center gap-0.5 py-2 {{ $isKesiswaan ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="w-5 h-5" fill="{{ $isKesiswaan ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-xs font-medium">Kesiswaan</span>
        </a>

        {{-- Kurikulum --}}
        <a href="{{ route('siswa.kurikulum') }}"
            class="flex-1 flex flex-col items-center gap-0.5 py-2 {{ $isKurikulum ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="w-5 h-5" fill="{{ $isKurikulum ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span class="text-xs font-medium">Kurikulum</span>
        </a>

        {{-- Presensi (tombol tengah besar) --}}
        <div class="flex-1 relative flex flex-col items-center justify-center">
            <a href="{{ route('siswa.attendance.location') }}"
                class="w-13 h-13 bg-blue-600 rounded-full flex items-center justify-center shadow-lg -mt-5 border-4 border-white">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
            <span class="text-xs font-medium text-blue-600 mt-0.5">Presensi</span>
        </div>

        {{-- Prasarana --}}
        <a href="{{ route('siswa.prasarana') }}"
            class="flex-1 flex flex-col items-center gap-0.5 py-2 {{ $isPrasarana ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="w-5 h-5" fill="{{ $isPrasarana ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="text-xs font-medium">Prasarana</span>
        </a>

        {{-- Humas --}}
        <a href="{{ route('siswa.humas') }}"
            class="flex-1 flex flex-col items-center gap-0.5 py-2 {{ $isHumas ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="w-5 h-5" fill="{{ $isHumas ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            <span class="text-xs font-medium">Humas</span>
        </a>

    </div>
</nav>

{{-- ─── PWA Install Prompt ──────────────────────────────────────────── --}}
<div id="pwa-install-banner"
    class="fixed bottom-20 left-4 right-4 z-30 bg-white rounded-2xl shadow-lg border border-blue-100 p-3 hidden">
    <div class="flex items-center gap-3">
        <img src="/img/logo_sekolah.png" alt="SIMS" class="w-10 h-10 object-contain rounded-xl shrink-0">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-gray-800">Pasang SIMS di Layar Utama</p>
            <p class="text-[10px] text-gray-400 mt-0.5">Akses lebih cepat tanpa browser</p>
        </div>
        <button id="pwa-install-btn"
            class="shrink-0 bg-blue-600 text-white text-xs font-semibold px-3 py-1.5 rounded-xl">
            Pasang
        </button>
        <button id="pwa-install-dismiss"
            class="shrink-0 w-6 h-6 flex items-center justify-center text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

<script>
// Service Worker
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
}

// PWA Install Prompt
(function () {
    var deferredPrompt;
    var banner = document.getElementById('pwa-install-banner');
    var installBtn = document.getElementById('pwa-install-btn');
    var dismissBtn = document.getElementById('pwa-install-dismiss');

    if (!banner || !installBtn || !dismissBtn) return;

    if (sessionStorage.getItem('pwa-dismissed')) return;

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        banner.classList.remove('hidden');
    });

    installBtn.addEventListener('click', function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function () {
            deferredPrompt = null;
            banner.classList.add('hidden');
        });
    });

    dismissBtn.addEventListener('click', function () {
        banner.classList.add('hidden');
        sessionStorage.setItem('pwa-dismissed', '1');
    });
})();
</script>

</body>
</html>
