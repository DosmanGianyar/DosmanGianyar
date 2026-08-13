<div class="px-3.5 py-3 border-t border-slate-800/80 bg-slate-950/60 backdrop-blur-md">
    <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 shadow-md">
        {{-- User info --}}
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <div class="w-8 h-8 rounded-lg bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-xs flex-shrink-0 shadow-xs">
                {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-slate-200 truncate leading-tight">
                    {{ auth()->user()?->name ?? 'Administrator' }}
                </p>
                <div class="mt-0.5 flex items-center gap-1">
                    @php
                        $role = auth()->user()?->role;
                        $roleBadge = match($role) {
                            'admin'           => ['label' => 'Super Admin',     'class' => 'bg-amber-500/20 text-amber-300 border-amber-500/30'],
                            'admin_kesiswaan' => ['label' => 'Admin Kesiswaan', 'class' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'],
                            'admin_kurikulum' => ['label' => 'Admin Kurikulum', 'class' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30'],
                            'admin_sarpras'   => ['label' => 'Admin Sarpras',   'class' => 'bg-purple-500/20 text-purple-300 border-purple-500/30'],
                            'admin_humas'     => ['label' => 'Admin Humas',     'class' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30'],
                            default           => ['label' => 'Admin',           'class' => 'bg-slate-700/40 text-slate-300 border-slate-600/40'],
                        };
                    @endphp
                    <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold rounded-md border {{ $roleBadge['class'] }}">
                        {{ $roleBadge['label'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Logout Button --}}
        <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="flex-shrink-0">
            @csrf
            <button type="submit" title="Keluar / Logout Admin" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-600 border border-rose-500/25 hover:border-rose-500 transition-all duration-200 shadow-xs group cursor-pointer">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</div>
