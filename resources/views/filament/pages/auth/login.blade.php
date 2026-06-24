<div class="w-full">
    <div class="flex flex-col sm:flex-row rounded-2xl shadow-2xl overflow-hidden">

        {{-- ─── Panel Kiri ─────────────────────────────────────────────── --}}
        <div class="w-full sm:w-5/12 bg-linear-to-br from-blue-600 via-blue-700 to-indigo-800
                    flex flex-col items-center justify-center p-8 text-white text-center relative overflow-hidden">

            <div class="absolute -top-12 -left-12 w-48 h-48 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative w-24 h-24 mb-5">
                <div class="absolute inset-0 bg-white/20 rounded-full ring-4 ring-white/30"></div>
                <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar"
                    class="relative w-full h-full object-contain p-2">
            </div>

            <span class="text-[10px] font-bold tracking-widest uppercase text-blue-200 mb-1">Panel Admin</span>
            <h2 class="text-base font-bold mb-3 leading-snug">SIMS Admin</h2>
            <p class="text-xs text-blue-200 leading-relaxed mb-5 max-w-50">
                Sistem Informasi Manajemen Sekolah<br>SMA Negeri 1 Gianyar
            </p>

            <div class="w-10 h-px bg-white/30 mb-5"></div>

            <p class="text-xs font-semibold italic text-white/70 leading-relaxed max-w-50">
                "Learn, Inovate, and Build The Future"
            </p>
        </div>

        {{-- ─── Panel Kanan ─────────────────────────────────────────────── --}}
        <div class="flex-1 bg-white flex flex-col items-center justify-center px-8 py-10">

            <div class="hidden sm:flex items-center gap-2.5 mb-7">
                <img src="/img/logo_sekolah.png" alt="Logo" class="w-9 h-9 object-contain">
                <div class="text-left leading-tight">
                    <p class="text-xs font-extrabold text-gray-700 tracking-widest">SMAN 1 GIANYAR</p>
                    <p class="text-[10px] text-gray-400">SMA Negeri 1 Gianyar</p>
                </div>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-1">Login Admin</h1>
            <p class="text-xs text-gray-400 mb-7">Masuk ke panel administrasi SIMS</p>

            <div class="w-full">
                {{ $this->content }}
            </div>

            <p class="text-[10px] text-gray-300 mt-6">
                &copy; {{ date('Y') }} SMA Negeri 1 Gianyar · SIMS
            </p>
        </div>

    </div>
</div>
