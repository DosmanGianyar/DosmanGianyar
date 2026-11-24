<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Meja Piket') — SIMAK DOSMAN | SMAN 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-[#f6f8fb] font-sans antialiased text-slate-800">

@php
    $today = now()->toDateString();
    $pendingPermits          = \App\Models\Permit::where('status', 'pending')->count();
    $pendingEarlyCheckouts   = \App\Models\EarlyCheckoutRequest::where('status', 'pending')->count();
    $pendingForgotAttendance = \App\Models\ForgotAttendanceRequest::where('status', 'pending')->count();
    $pendingLateReports      = \App\Models\ConductLog::where('is_self_reported', true)->where('status', 'pending')->whereDate('created_at', $today)->count();
    $activeExitPasses        = \App\Models\ExitPass::where('status', 'out')->count();

    $activePiketName = session('piket_teacher_name') ?? auth()->user()?->name ?? 'Guru Piket';
    $activePiketParts = preg_split('/\s+/', trim($activePiketName));
    $activePiketInitials = strtoupper(
        substr($activePiketParts[0] ?? 'G', 0, 1) . substr($activePiketParts[1] ?? '', 0, 1)
    );

    $navItems = [
        ['route' => 'piket.dashboard',           'label' => 'Dashboard Piket',          'badge' => 0,                        'color' => 'bg-blue-500',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['route' => 'piket.permits.index',       'label' => 'Izin Tidak Sekolah',       'badge' => $pendingPermits,          'color' => 'bg-amber-500',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['route' => 'piket.early-checkouts.index','label' => 'Izin Pulang Awal',        'badge' => $pendingEarlyCheckouts,   'color' => 'bg-orange-500',  'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'],
        ['route' => 'piket.forgot-attendance.index','label' => 'Lupa Presensi',         'badge' => $pendingForgotAttendance, 'color' => 'bg-sky-500',     'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => 'piket.late-verification.index','label' => 'Piket Gerbang (Telat)',  'badge' => $pendingLateReports,      'color' => 'bg-rose-500',    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['route' => 'piket.dispensation.create', 'label' => 'Input Dispensasi',         'badge' => 0,                        'color' => 'bg-purple-500',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['route' => 'piket.exit-passes.index',   'label' => 'Izin Keluar Kelas',        'badge' => $activeExitPasses,        'color' => 'bg-emerald-500', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
        ['route' => 'piket.logs.index',          'label' => 'Riwayat & Log Piket',      'badge' => 0,                        'color' => 'bg-slate-500',   'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ];
@endphp

{{-- ─── Mobile Top Bar ──────────────────────────────────────────────────── --}}
<div class="lg:hidden fixed top-0 left-0 right-0 z-30 bg-white border-t-[3px] border-t-amber-500 border-b border-slate-200 h-14 flex items-center px-4 justify-between gap-2 shadow-2xs">
    <div class="flex items-center gap-2 shrink-0">
        <button type="button" onclick="togglePiketSidebar()"
            class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <a href="{{ route('piket.dashboard') }}" class="flex items-center gap-2">
            <img src="/img/logo_sekolah.png" alt="Logo" class="w-7 h-7 object-contain">
            <span class="text-sm font-bold text-slate-900 tracking-tight">Meja Piket</span>
        </a>
    </div>
    <a href="{{ route('piket.select-teacher') }}"
       class="px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-[11px] font-bold text-amber-800 truncate max-w-[170px]">
        {{ $activePiketName }} ↻
    </a>
</div>

{{-- ─── Overlay Mobile ──────────────────────────────────────────────────── --}}
<div id="piket-overlay" onclick="togglePiketSidebar()"
    class="hidden fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-xs lg:hidden"></div>

{{-- ─── Sidebar Meja Piket ──────────────────────────────────────────────── --}}
<aside id="piket-sidebar"
    class="fixed inset-y-0 left-0 w-64 bg-[#182433] shadow-xl lg:shadow-none z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 flex flex-col overflow-hidden border-r border-[#0f172a]">

    {{-- Branding --}}
    <div class="shrink-0 h-16 flex items-center gap-3 px-4 border-b border-white/10 bg-[#131d2a]">
        <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar" class="w-8 h-8 object-contain shrink-0">
        <div class="overflow-hidden">
            <p class="text-white font-bold text-sm tracking-tight truncate leading-none">SMAN 1 Gianyar</p>
            <p class="text-amber-300 text-[11px] font-semibold truncate mt-1">Portal Meja Piket · SIMAK</p>
        </div>
    </div>

    {{-- Kartu Guru Bertugas Aktif --}}
    <div class="shrink-0 px-3 py-3 border-b border-white/10 bg-[#182433] space-y-2">
        <div class="flex items-center gap-2.5 bg-[#1e2c3f] border border-amber-400/30 rounded-xl p-2.5">
            <div class="w-9 h-9 rounded-lg bg-amber-500 flex items-center justify-center text-white font-black text-xs shrink-0">
                {{ $activePiketInitials }}
            </div>
            <div class="overflow-hidden flex-1 min-w-0">
                <p class="text-[10px] uppercase tracking-wider font-bold text-amber-300">Guru Bertugas</p>
                <p class="text-xs font-bold text-white truncate">{{ $activePiketName }}</p>
            </div>
        </div>
        <a href="{{ route('piket.select-teacher') }}"
           class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 border border-amber-400/30 text-amber-200 text-[11px] font-bold transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <span>Ganti Guru Piket</span>
        </a>
    </div>

    {{-- Menu Navigasi --}}
    <nav class="px-2.5 py-3 space-y-1 flex-1 overflow-y-auto">
        @foreach($navItems as $item)
            @php $isActive = request()->routeIs($item['route'] . '*'); @endphp
            <a href="{{ route($item['route']) }}"
               class="flex items-center justify-between gap-2.5 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors {{ $isActive ? 'bg-[#206bc4] text-white shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <span class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span class="truncate">{{ $item['label'] }}</span>
                </span>
                @if($item['badge'] > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold text-white {{ $item['color'] }} shrink-0">
                        {{ $item['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Footer Logout --}}
    <div class="shrink-0 p-3 border-t border-white/10 bg-[#131d2a]">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 border border-rose-400/30 text-rose-200 text-xs font-bold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Logout Meja Piket</span>
            </button>
        </form>
    </div>
</aside>

{{-- ─── Main Content Area ───────────────────────────────────────────────── --}}
<div class="lg:pl-64 min-h-screen flex flex-col">
    {{-- Desktop Header Bar --}}
    <header class="hidden lg:flex h-16 bg-white border-b border-slate-200 px-6 items-center justify-between sticky top-0 z-10">
        <div>
            <h1 class="text-base font-extrabold text-slate-900">@yield('page-title', 'Meja Piket')</h1>
            <p class="text-[11px] text-slate-500">{{ now()->locale('id')->translatedFormat('l, d F Y') }} • Akun PC Piket Bersama</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-800">Bertugas: {{ $activePiketName }}</span>
            </div>
            <a href="{{ route('piket.select-teacher') }}"
               class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition-colors">
                Ganti Guru
            </a>
        </div>
    </header>

    <main class="flex-1 p-4 sm:p-6 pt-20 lg:pt-6 max-w-7xl w-full mx-auto">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between gap-2">
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
                {{ session('warning') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
                {{ session('info') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
function togglePiketSidebar() {
    const sidebar = document.getElementById('piket-sidebar');
    const overlay = document.getElementById('piket-overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
</script>
</body>
</html>
