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
<body class="bg-[#f6f8fb] font-sans antialiased text-slate-800">

{{-- ─── Mobile Top Bar (Tabler Top Accent) ──────────────────────────── --}}
<div class="lg:hidden fixed top-0 left-0 right-0 z-30 bg-white border-t-[3px] border-t-[#206bc4] border-b border-slate-200 h-14 flex items-center px-4 justify-between gap-2 shadow-2xs">
    <div class="flex items-center gap-2 shrink-0">
        <button onclick="toggleSidebar()"
            class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition-colors shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-2" title="Dashboard SIMAK DOSMAN">
            <img src="/img/logo_sekolah.png" alt="Logo" class="w-7 h-7 object-contain">
            <span class="text-sm font-bold text-slate-900 tracking-tight">SIMAK DOSMAN</span>
        </a>
    </div>

    {{-- Profile Avatar Mobile --}}
    <a href="{{ route('guru.profile') }}" class="relative flex items-center shrink-0">
        @if(auth()->user()->photo)
            <img src="{{ auth()->user()->photo_url }}"
                class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
        @else
            <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 text-xs font-semibold shrink-0">
                {{ auth()->user()->initials }}
            </div>
        @endif
    </a>
</div>

{{-- ─── Sidebar Overlay (Mobile) ───────────────────────────────────── --}}
<div id="overlay" onclick="toggleSidebar()"
    class="hidden fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-xs lg:hidden"></div>

{{-- ─── Sidebar (Tabler Signature Dark Slate #182433 — Unified with Admin) ─── --}}
<aside id="sidebar"
    class="fixed inset-y-0 left-0 w-64 bg-[#182433] shadow-xl lg:shadow-none z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 flex flex-col overflow-hidden border-r border-[#0f172a]">

    {{-- Logo / Branding SIMAK DOSMAN --}}
    <div class="shrink-0 h-16 flex items-center gap-3 px-4 border-b border-white/10 bg-[#131d2a]">
        <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar"
            class="w-8 h-8 object-contain shrink-0">
        <div class="overflow-hidden">
            <p class="text-white font-bold text-sm tracking-tight truncate leading-none">SMAN 1 Gianyar</p>
            <p class="text-slate-400 text-[11px] font-medium truncate mt-1">Portal Guru · SIMAK</p>
        </div>
    </div>

    {{-- User Card Profile Sidebar --}}
    <div class="shrink-0 px-3 py-2.5 border-b border-white/10 bg-[#182433]">
        <a href="{{ route('guru.profile') }}" class="flex items-center gap-2.5 bg-[#1e2c3f] border border-white/10 rounded-xl p-2 hover:border-blue-400/40 transition-colors group">
            <div class="relative shrink-0">
                @if(auth()->user()->photo)
                    <img src="{{ auth()->user()->photo_url }}" class="w-8 h-8 rounded-lg object-cover border border-white/15">
                @else
                    <div class="w-8 h-8 rounded-lg bg-[#206bc4] flex items-center justify-center text-white font-bold text-xs">
                        {{ auth()->user()->initials }}
                    </div>
                @endif
            </div>
            <div class="overflow-hidden flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate group-hover:text-blue-300 transition-colors">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->subject ?? 'Guru Pengajar' }}</p>
            </div>
        </a>
    </div>

    {{-- Nav Items --}}
    <nav class="px-2.5 py-3 space-y-1"
        style="flex:1 1 0%;min-height:0;overflow-y:scroll;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;">
        @php
            $pendingPermits          = \App\Models\Permit::where('status', 'pending')->count();
            $pendingForgotAttendance = \App\Models\ForgotAttendanceRequest::where('status', 'pending')->count();
            $pendingEarlyCheckout    = \App\Models\EarlyCheckoutRequest::where('status', 'pending')->count();
            $pendingSelfReports      = \App\Models\ConductLog::where('is_self_reported', true)->where('status', 'pending')->whereDate('created_at', now()->format('Y-m-d'))->count();
            $isHomeroomTeacher       = auth()->check() && auth()->user()->isGuru() && auth()->user()->homeroomClass()->exists();
            $chatUnreadGuru          = $isHomeroomTeacher ? app(\App\Services\ChatService::class)->getTotalUnreadCount(auth()->user()) : 0;

            $navGroups = [
                [
                    'label' => null,
                    'items' => [
                        ['route' => 'guru.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['route' => 'guru.layanan.index', 'label' => 'Layanan & Direktori', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0h4m-4 0H9'],
                    ],
                ],
                [
                    'label' => 'Kesiswaan & Pembelajaran',
                    'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'items' => [
                        ['route' => 'guru.conduct.today',           'label' => 'List Pengajuan Hari Ini', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'badge' => $pendingSelfReports],
                        ...($isHomeroomTeacher ? [[
                            'route' => 'guru.chat.index',
                            'label' => 'Chat Siswa & Ortu',
                            'icon'  => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                            'badge' => $chatUnreadGuru,
                        ]] : []),
                        ['route' => 'guru.journal.index',           'label' => 'Jurnal Mengajar',      'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                        ['route' => 'guru.attendance.index',        'label' => 'Absensi Harian Siswa', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route' => 'guru.achievements.index',      'label' => 'Juara Siswa Binaan',   'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
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
                <p class="px-3 pt-3.5 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    {{ $group['label'] }}
                </p>
            @endif

            @foreach($group['items'] as $item)
                @php
                    $active = request()->routeIs($item['route']);
                    $itemBadge = $item['badge'] ?? 0;
                @endphp
                <a href="{{ route($item['route']) }}"
                    class="group flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-semibold transition-all duration-150 mb-0.5 border-l-[3px]
                        {{ $active ? 'bg-[#206bc4] text-white border-l-blue-300' : 'border-l-transparent text-slate-300 hover:bg-[#213043] hover:text-white' }}">
                    <svg class="w-4.5 h-4.5 shrink-0 {{ $active ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span class="truncate flex-1">{{ $item['label'] }}</span>
                    @if($item['route'] === 'guru.chat.index')
                        <span class="chat-unread-badge {{ $itemBadge > 0 ? '' : 'hidden' }} text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 {{ $active ? 'bg-white text-[#206bc4]' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' }}">
                            {{ $itemBadge }}
                        </span>
                    @elseif($itemBadge > 0)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 {{ $active ? 'bg-white text-[#206bc4]' : 'bg-rose-500/20 text-rose-300 border border-rose-400/30' }}">
                            {{ $itemBadge }}
                        </span>
                    @endif
                </a>
            @endforeach
        @endforeach

        {{-- Logout --}}
        <div class="pt-3 mt-3 border-t border-white/10 pb-6">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-semibold text-rose-300 hover:bg-rose-500/15 hover:text-rose-200 transition-all duration-150">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
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
    {{-- Desktop Header Bar (Tabler Top Accent Line) --}}
    <div class="hidden lg:flex items-center justify-between px-6 py-3 bg-white border-t-[3px] border-t-[#206bc4] border-b border-slate-200">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">@yield('page-title', 'Dashboard')</h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
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
@include('chat.partials.unread-poller')
@stack('scripts')
</body>
</html>
