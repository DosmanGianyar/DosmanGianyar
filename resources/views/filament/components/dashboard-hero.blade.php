@php
    $user = auth()->user();
    $roleName = match($user?->role) {
        'admin'              => 'Super Admin',
        'admin_kesiswaan'    => 'Admin Kesiswaan',
        'admin_kurikulum'    => 'Admin Kurikulum',
        'admin_sarpras'      => 'Admin Sarpras',
        'admin_humas'        => 'Admin Humas',
        'admin_perpustakaan' => 'Admin Perpustakaan',
        'admin_prestasi'     => 'Admin Prestasi',
        'guru'               => 'Tenaga Pendidik',
        default              => ucfirst($user?->role ?? 'Pengguna'),
    };
    $todayFormatted = now()->locale('id')->isoFormat('dddd, D MMMM Y');
@endphp

<div class="sims-dashboard-hero">
    <!-- Ambient Glow Effects -->
    <div class="sims-hero-glow-1"></div>
    <div class="sims-hero-glow-2"></div>

    <div class="sims-hero-content">
        <div class="sims-hero-text-block">
            <div class="sims-hero-status-tag">
                <span class="sims-hero-dot"></span>
                SIMS SMAN 1 Gianyar — Live System
            </div>
            <h1 class="sims-hero-title">
                Selamat Datang, {{ $user?->name ?? 'Pengguna' }} 👋
            </h1>
            <p class="sims-hero-subtitle">
                Panel Manajemen Terpadu Sekolah • <strong>{{ $roleName }}</strong>
            </p>
        </div>

        <div class="sims-hero-date-badge">
            <svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>{{ $todayFormatted }}</span>
        </div>
    </div>
</div>
