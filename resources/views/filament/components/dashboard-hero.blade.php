@php
    $user = auth()->user();
    $roleName = match($user?->role) {
        'admin'              => 'Super Admin',
        'admin_kesiswaan'    => 'Admin Kesiswaan',
        'admin_kurikulum'    => 'Admin Kurikulum',
        'admin_sarpras'      => 'Admin Sarpras',
        'admin_humas'        => 'Admin Humas',
        'admin_perpustakaan' => 'Admin Perpustakaan',
        'guru'               => 'Tenaga Pendidik',
        default              => ucfirst($user?->role ?? 'Pengguna'),
    };
    $todayFormatted = now()->locale('id')->isoFormat('dddd, D MMMM Y');
@endphp

<div class="sims-hero-banner relative overflow-hidden rounded-2xl p-6 md:p-8 mb-6 shadow-2xl border border-white/10"
     style="background: linear-gradient(135deg, #0F2460 0%, #1A3A8F 50%, #1E3FAD 100%);">

    <!-- Ambient Glow Effects -->
    <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-blue-400/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-16 -left-16 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-blue-200">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                SIMS SMAN 1 Gianyar — Live System
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
                Selamat Datang, {{ $user?->name ?? 'Pengguna' }} 👋
            </h1>
            <p class="text-sm md:text-base text-blue-100/90 font-medium">
                Panel Manajemen Terpadu Sekolah • <span class="font-semibold text-white">{{ $roleName }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-sm font-semibold text-white shadow-sm">
                <svg class="w-4 h-4 text-blue-300 shrink-0" style="width: 1rem !important; height: 1rem !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ $todayFormatted }}</span>
            </div>
        </div>
    </div>
</div>
