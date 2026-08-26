<div class="sims-sidebar-footer-container">
    <div class="sims-sidebar-user-card">
        {{-- Row 1: Header User Info --}}
        <div class="sims-user-profile-header">
            <div class="sims-user-avatar">
                {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
            </div>
            <div class="sims-user-details">
                <p class="sims-user-name">
                    {{ auth()->user()?->name ?? 'Administrator' }}
                </p>
                @php
                    $role = auth()->user()?->role;
                    $badgeClass = match($role) {
                        'admin'              => 'sims-role-admin',
                        'admin_kesiswaan'    => 'sims-role-kesiswaan',
                        'admin_kurikulum'    => 'sims-role-kurikulum',
                        'admin_sarpras'      => 'sims-role-sarpras',
                        'admin_humas'        => 'sims-role-humas',
                        'admin_perpustakaan' => 'sims-role-perpustakaan',
                        'admin_prestasi'     => 'sims-role-prestasi',
                        default              => 'sims-role-admin',
                    };
                    $badgeLabel = match($role) {
                        'admin'              => 'Super Admin',
                        'admin_kesiswaan'    => 'Admin Kesiswaan',
                        'admin_kurikulum'    => 'Admin Kurikulum',
                        'admin_sarpras'      => 'Admin Sarpras',
                        'admin_humas'        => 'Admin Humas',
                        'admin_perpustakaan' => 'Admin Perpustakaan',
                        'admin_prestasi'     => 'Admin Prestasi',
                        default              => 'Admin',
                    };
                @endphp
                <span class="sims-role-badge {{ $badgeClass }}">
                    {{ $badgeLabel }}
                </span>
            </div>
        </div>

        {{-- Row 2: Action Buttons Bar --}}
        <div class="sims-user-actions-bar">
            {{-- Ubah Password Button --}}
            <a href="{{ url('/admin/profile') }}" title="Ubah Password & Profil Admin" class="sims-profile-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z" />
                </svg>
                <span>Password</span>
            </a>

            {{-- Logout Button --}}
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="sims-logout-form">
                @csrf
                <button type="submit" title="Keluar / Logout Admin" class="sims-logout-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>
