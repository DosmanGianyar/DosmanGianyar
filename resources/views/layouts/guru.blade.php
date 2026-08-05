<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — SIMAK_DOSMAN | SMAN 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-100/90 font-sans antialiased text-slate-800">

{{-- ─── Mobile Top Bar ──────────────────────────────────────────────── --}}
<div class="lg:hidden fixed top-0 left-0 right-0 z-30 bg-white border-b border-slate-200 h-14 flex items-center px-4 justify-between gap-2 shadow-xs">
    <div class="flex items-center gap-2 shrink-0">
        <button onclick="toggleSidebar()"
            class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition-colors shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-2" title="Dashboard SIMAK_DOSMAN">
            <img src="/img/logo_sekolah.png" alt="Logo" class="w-7 h-7 object-contain">
            <span class="text-sm font-black text-indigo-950 tracking-wider">SIMAK_DOSMAN</span>
        </a>
    </div>

    {{-- Profile Avatar Mobile --}}
    <a href="{{ route('guru.profile') }}" class="relative flex items-center shrink-0">
        @if(auth()->user()->photo)
            <img src="{{ auth()->user()->photo_url }}"
                class="w-8 h-8 rounded-full object-cover ring-2 ring-blue-500/40 shrink-0">
        @else
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs font-bold ring-2 ring-blue-500/40 shrink-0">
                {{ auth()->user()->initials }}
            </div>
        @endif
        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-white"></span>
    </a>
</div>

{{-- ─── Sidebar Overlay (Mobile) ───────────────────────────────────── --}}
<div id="overlay" onclick="toggleSidebar()"
    class="hidden fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-xs lg:hidden"></div>

{{-- ─── Sidebar ─────────────────────────────────────────────────────── --}}
<aside id="sidebar"
    class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl lg:shadow-md z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 flex flex-col overflow-hidden border-r border-slate-200">

    {{-- Logo / Branding SIMAK_DOSMAN --}}
    <div class="shrink-0 h-20 flex items-center gap-3 px-4 pt-2.5 border-b border-slate-100 bg-gradient-to-r from-slate-900 via-slate-850 to-indigo-950 text-white">
        <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar"
            class="w-9 h-9 rounded-xl object-contain shrink-0 ring-2 ring-blue-400/40 bg-white/10 p-0.5">
        <div class="overflow-hidden">
            <p class="text-white font-black text-base tracking-wider truncate leading-none">SIMAK_DOSMAN</p>
            <p class="text-blue-300 text-[10.5px] font-semibold tracking-tight truncate mt-1">Portal Guru SMAN 1 Gianyar</p>
        </div>
    </div>

    {{-- User Card Profile Sidebar --}}
    <div class="shrink-0 px-3 py-3 border-b border-slate-100 bg-slate-50/50">
        <a href="{{ route('guru.profile') }}" class="flex items-center gap-3 bg-white border border-slate-200/80 rounded-2xl p-2.5 shadow-2xs hover:border-blue-300 transition-all group">
            <div class="relative shrink-0">
                @if(auth()->user()->photo)
                    <img src="{{ auth()->user()->photo_url }}" class="w-9 h-9 rounded-xl object-cover ring-2 ring-blue-500/30 group-hover:ring-blue-500">
                @else
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-black text-xs ring-2 ring-blue-500/30">
                        {{ auth()->user()->initials }}
                    </div>
                @endif
                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-white"></span>
            </div>
            <div class="overflow-hidden flex-1 min-w-0">
                <p class="text-xs font-black text-slate-900 truncate group-hover:text-blue-600 transition-colors">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">{{ auth()->user()->subject ?? 'Guru Pengajar' }}</p>
            </div>
        </a>
    </div>

    {{-- Nav Items --}}
    <nav class="px-2 py-3"
        style="flex:1 1 0%;min-height:0;overflow-y:scroll;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;">
        @php
            $pendingPermits          = \App\Models\Permit::where('status', 'pending')->count();
            $pendingForgotAttendance = \App\Models\ForgotAttendanceRequest::where('status', 'pending')->count();
            $pendingEarlyCheckout   = \App\Models\EarlyCheckoutRequest::where('status', 'pending')->count();

            $navGroups = [
                [
                    'label' => null,
                    'items' => [
                        ['route' => 'guru.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ],
                ],
                [
                    'label' => 'Kesiswaan & Pembelajaran',
                    'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'items' => [
                        ['route' => 'guru.journal.index',           'label' => 'Jurnal Mengajar',      'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                        ['route' => 'guru.attendance.index',        'label' => 'Absensi Harian Siswa', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route' => 'guru.conduct.index',           'label' => 'Rekap Karakter Siswa', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['route' => 'guru.conduct.choose',          'label' => 'Catat Prestasi/Peln.', 'icon' => 'M12 4v16m8-8H4'],
                        ['route' => 'guru.attendance.permits',      'label' => 'Approval Izin/Sakit',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'badge' => $pendingPermits],
                        ['route' => 'guru.forgot-attendance.index', 'label' => 'Lupa Absen Siswa',     'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'badge' => $pendingForgotAttendance],
                        ['route' => 'guru.early-checkout.index',    'label' => 'Izin Pulang Awal',     'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1', 'badge' => $pendingEarlyCheckout],
                        ...(\App\Models\AppSetting::isEvotingActive() ? [['route' => 'siswa.voting.index', 'label' => 'E-Voting OSIS', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z']] : []),
                    ],
                ],
                [
                    'label' => 'Akademik & Nilai',
                    'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    'items' => [
                        ['route' => 'guru.tp.index',                'label' => 'Tujuan Pembelajaran',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        ['route' => 'guru.grades.index',            'label' => 'Input Nilai Siswa',    'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                        ['route' => 'guru.export.grades.form',      'label' => 'Export Rekap Nilai',   'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'],
                    ],
                ],
            ];
        @endphp

        @foreach($navGroups as $group)
            @if($group['label'])
                <p class="px-3 pt-4 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    {{ $group['label'] }}
                </p>
            @endif

            @foreach($group['items'] as $item)
                @php
                    $active = request()->routeIs($item['route']);
                    $itemBadge = $item['badge'] ?? 0;
                @endphp
                <a href="{{ route($item['route']) }}"
                    class="group flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 mb-1
                        {{ $active ? 'bg-blue-600 text-white shadow-sm shadow-blue-200' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-4.5 h-4.5 shrink-0 {{ $active ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span class="truncate flex-1">{{ $item['label'] }}</span>
                    @if($itemBadge > 0)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full shrink-0 {{ $active ? 'bg-white text-blue-600' : 'bg-red-500 text-white' }}">
                            {{ $itemBadge }}
                        </span>
                    @endif
                </a>
            @endforeach
        @endforeach

        {{-- Logout --}}
        <div class="pt-3 mt-3 border-t border-slate-100 pb-6">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-red-50 hover:text-red-600 transition-all duration-150">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar Sistem
                </button>
            </form>
        </div>
    </nav>
</aside>

{{-- ─── Main Content ────────────────────────────────────────────────── --}}
<main class="lg:ml-64 min-h-screen pt-14 lg:pt-0">
    {{-- Desktop Header Bar --}}
    <div class="hidden lg:flex items-center justify-between px-6 py-3.5 bg-white border-b border-slate-200 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-black text-base shadow-2xs">
                🎓
            </div>
            <div>
                <h1 class="text-base font-black text-slate-900 tracking-tight">@yield('page-title', 'Dashboard')</h1>
                <p class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                </p>
            </div>
        </div>

        {{-- Header Sebelah Kanan: Foto Profil & Menu Guru --}}
        <div class="flex items-center gap-4" x-data="{ openProfile: false }">
            {{-- Profile Card Avatar --}}
            <div class="relative">
                <button @click="openProfile = !openProfile" @click.outside="openProfile = false"
                    class="flex items-center gap-3 p-1.5 pr-3.5 rounded-2xl bg-slate-50/80 hover:bg-slate-100 border border-slate-200/80 transition-all shadow-2xs group">
                    
                    {{-- Avatar Photo --}}
                    <div class="relative shrink-0">
                        @if(auth()->user()->photo)
                            <img src="{{ auth()->user()->photo_url }}"
                                class="w-10 h-10 rounded-xl object-cover ring-2 ring-blue-500/30 group-hover:ring-blue-500 shadow-xs">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-black text-sm ring-2 ring-blue-500/30 group-hover:ring-blue-500 shadow-xs">
                                {{ auth()->user()->initials }}
                            </div>
                        @endif
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full ring-2 ring-white"></span>
                    </div>

                    {{-- Name & Subject --}}
                    <div class="text-left hidden md:block">
                        <p class="text-xs font-black text-slate-900 leading-tight truncate max-w-[160px]">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-[11px] font-semibold text-blue-600 mt-0.5 truncate max-w-[160px]">
                            {{ auth()->user()->subject ?? 'Guru Pengajar' }}
                        </p>
                    </div>

                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 ml-1" :class="openProfile ? 'rotate-180 text-blue-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Profile Dropdown Popup --}}
                <div x-show="openProfile" x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                    class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200 p-2 z-50">
                    
                    <div class="px-3.5 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl mb-1.5 border border-blue-100">
                        <p class="text-xs font-black text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-600 font-medium mt-0.5">NIP. {{ auth()->user()->nip ?? '—' }}</p>
                        @if(auth()->user()->homeroomClass)
                            <span class="inline-block mt-1 px-2 py-0.5 bg-blue-600 text-white text-[10px] font-bold rounded-md">
                                Wali Kelas {{ auth()->user()->homeroomClass->name }}
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('guru.profile') }}"
                        class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Saya
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="mt-1 pt-1 border-t border-slate-100">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Keluar Sistem
                        </button>
                    </form>
                </div>
            </div>
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
@include('components.image-lightbox')
</body>
</html>
