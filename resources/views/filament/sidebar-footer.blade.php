<div class="sims-sidebar-footer-container">
    <div class="sims-sidebar-user-card">
        {{-- User info --}}
        <div class="sims-user-info" style="display: flex; align-items: center; gap: 0.625rem; min-width: 0; flex: 1;">
            <div class="sims-user-avatar">
                {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
            </div>
            <div style="min-width: 0; flex: 1;">
                <p class="sims-user-name">
                    {{ auth()->user()?->name ?? 'Administrator' }}
                </p>
                <div>
                    @php
                        $role = auth()->user()?->role;
                        $badgeClass = match($role) {
                            'admin'           => 'sims-role-admin',
                            'admin_kesiswaan' => 'sims-role-kesiswaan',
                            'admin_kurikulum' => 'sims-role-kurikulum',
                            'admin_sarpras'   => 'sims-role-sarpras',
                            'admin_humas'     => 'sims-role-humas',
                            default           => 'sims-role-admin',
                        };
                        $badgeLabel = match($role) {
                            'admin'           => 'Super Admin',
                            'admin_kesiswaan' => 'Admin Kesiswaan',
                            'admin_kurikulum' => 'Admin Kurikulum',
                            'admin_sarpras'   => 'Admin Sarpras',
                            'admin_humas'     => 'Admin Humas',
                            default           => 'Admin',
                        };
                    @endphp
                    <span class="sims-role-badge {{ $badgeClass }}">
                        {{ $badgeLabel }}
                    </span>
                </div>
            </div>
        </div>

        <div style="display: flex; items-center; gap: 0.375rem; flex-shrink: 0;">
            {{-- Ubah Password Button --}}
            <a href="{{ url('/admin/profile') }}" title="Ubah Password & Profil Admin" class="sims-logout-btn" style="text-decoration: none;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 1.125rem; height: 1.125rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z" />
                </svg>
                <span>Password</span>
            </a>

            {{-- Logout Button --}}
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}" style="margin: 0; padding: 0;">
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
