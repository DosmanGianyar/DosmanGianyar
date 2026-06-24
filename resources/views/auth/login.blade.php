<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIMS | SMA Negeri 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">

    <div class="w-full max-w-2xl">
        <div class="flex flex-col sm:flex-row rounded-2xl shadow-2xl overflow-hidden">

            {{-- ─── Panel Atas / Kiri (Biru–Indigo) ───────────────────── --}}
            <div class="w-full sm:w-5/12 bg-linear-to-br from-blue-600 via-blue-700 to-indigo-800
                        flex flex-col items-center justify-center p-8 text-white text-center relative overflow-hidden">

                {{-- Dekorasi lingkaran blur --}}
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-indigo-400/20 rounded-full blur-2xl pointer-events-none"></div>

                {{-- Logo --}}
                <div class="relative w-24 h-24 mb-5">
                    <div class="absolute inset-0 bg-white/20 rounded-full ring-4 ring-white/30"></div>
                    <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar"
                        class="relative w-full h-full object-contain p-2">
                </div>

                <h2 class="text-sm font-bold mb-3 leading-snug tracking-wide uppercase text-blue-100">
                    Visi SMAN 1 Gianyar
                </h2>
                <p class="text-xs text-blue-200 leading-relaxed mb-5 max-w-50">
                    Insan Cerdas, Sarat Prestasi, Berkarakter, Berbudaya,
                    Peduli Lingkungan, dan Berwawasan Global
                </p>

                <div class="w-8 h-px bg-white/40 mb-5"></div>

                <p class="text-xs font-semibold italic text-white/80 leading-relaxed">
                    "Learn, Inovate, and Build The Future"
                </p>
            </div>

            {{-- ─── Panel Bawah / Kanan (Putih) ───────────────────────── --}}
            <div class="flex-1 bg-white flex flex-col items-center justify-center px-8 py-10">

                {{-- Logo kecil horizontal --}}
                <div class="hidden sm:flex items-center gap-2.5 mb-7">
                    <img src="/img/logo_sekolah.png" alt="Logo" class="w-9 h-9 object-contain">
                    <div class="text-left leading-tight">
                        <p class="text-xs font-extrabold text-gray-700 tracking-widest">SMAN 1 GIANYAR</p>
                        <p class="text-[10px] text-gray-400">SMA Negeri 1 Gianyar</p>
                    </div>
                </div>

                <h1 class="text-2xl font-bold text-gray-800 mb-1">Login SIMS</h1>
                <p class="text-xs text-gray-400 mb-7">Silakan masukkan kredensial Anda</p>

                @if ($errors->any())
                <div class="w-full bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 mb-4">
                    <p class="text-red-600 text-xs">{{ $errors->first() }}</p>
                </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="w-full space-y-3">
                    @csrf

                    <input
                        type="text"
                        name="login"
                        value="{{ old('login') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Email / NIS / NIP"
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('login') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }} focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm text-gray-700 placeholder-gray-400 transition">

                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            placeholder="Password"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm text-gray-700 placeholder-gray-400 transition">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-blue-500 transition-colors">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-3.5 h-3.5 accent-blue-600 rounded">
                            Ingat saya
                        </label>
                        <span class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline cursor-default transition-colors">
                            Lupa Password?
                        </span>
                    </div>

                    <button type="submit"
                        class="px-8 py-2.5 bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all text-sm mt-1">
                        Login
                    </button>
                </form>

                <p class="text-[10px] text-gray-300 mt-8">
                    &copy; {{ date('Y') }} SMA Negeri 1 Gianyar · SIMS
                </p>
            </div>

        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
