<x-filament-widgets::widget>
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 p-6 md:p-8 text-white shadow-xl border border-white/10">
        {{-- Accent Light Glow --}}
        <div class="absolute -right-12 -top-12 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            {{-- App Title & Description --}}
            <div class="space-y-3 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-bold border border-blue-400/30">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    SIMS SMAN 1 Gianyar — Enterprise System
                </div>

                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Sistem Informasi Manajemen Sekolah (SIMS)
                </h2>

                <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                    Platform terpadu kelola administrasi akademik, kurikulum, presensi berbasis lokasi, jurnal mengajar, sarana prasarana, serta integrasi real-time aplikasi mobile siswa & guru di SMAN 1 Gianyar.
                </p>

                {{-- Status Badges --}}
                <div class="pt-2 flex flex-wrap gap-2 text-xs">
                    <div class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-slate-200 flex items-center gap-1.5">
                        <span class="text-amber-400">📅</span>
                        <span>Tahun Ajaran: <strong>2026/2027 Ganjil</strong></span>
                    </div>
                    <div class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-slate-200 flex items-center gap-1.5">
                        <span class="text-emerald-400">🔒</span>
                        <span>Keamanan: <strong>HTTPS & Session Encrypted</strong></span>
                    </div>
                    <div class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-slate-200 flex items-center gap-1.5">
                        <span class="text-blue-400">📱</span>
                        <span>Mobile App: <strong>Terhubung (Android/iOS)</strong></span>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full sm:w-auto shrink-0">
                <a href="/admin/import-schedule" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm shadow-lg shadow-blue-600/30 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Jadwal PDF/Excel
                </a>
                <a href="/admin/schedules" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm border border-white/15 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Kelola Jadwal Pelajaran
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
