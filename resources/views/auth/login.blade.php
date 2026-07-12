<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DOSMAN | SMA Negeri 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">

    <div class="w-full max-w-2xl">
        <div class="flex flex-col sm:flex-row rounded-2xl shadow-2xl overflow-hidden">

            {{-- ─── Panel Kiri (Biru Gelap) ────────────────────────────── --}}
            <div class="w-full sm:w-5/12 flex flex-col items-center justify-center p-8 text-white text-center relative overflow-hidden"
                 style="background: linear-gradient(160deg, #0d2460 0%, #1a3a8a 50%, #0d2460 100%);">

                {{-- Dekorasi --}}
                <div class="absolute -top-10 -left-10 w-40 h-40 rounded-full pointer-events-none"
                     style="background:rgba(255,255,255,.06); filter:blur(40px);"></div>
                <div class="absolute -bottom-8 -right-8 w-32 h-32 rounded-full pointer-events-none"
                     style="background:rgba(99,102,241,.15); filter:blur(40px);"></div>

                {{-- Logo --}}
                <div class="relative w-24 h-24 mb-4 flex-shrink-0">
                    <div class="absolute inset-0 rounded-full" style="background:rgba(255,255,255,.12); box-shadow:0 0 0 3px rgba(255,255,255,.2);"></div>
                    <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar"
                         class="relative w-full h-full object-contain p-2">
                </div>

                {{-- Nama Aplikasi --}}
                <h1 class="text-3xl font-black tracking-widest mb-1" style="color:#fff; letter-spacing:.2em;">DOSMAN</h1>
                <p class="text-xs font-semibold mb-1" style="color:rgba(147,197,253,1); letter-spacing:.08em;">
                    Sistem Informasi Manajemen Siswa
                </p>

                <div class="w-8 h-px my-4" style="background:rgba(255,255,255,.25);"></div>

                {{-- Nama Sekolah --}}
                <p class="font-balinese text-xs whitespace-nowrap" style="color:rgba(255,255,255,.9);">
                    ᭞ᬏᬲ᭄ᬏᬫ᭄ᬅ᭞ᬦᭂᬕᭂᬭᬶ᭞᭑᭞ᬕ᭄ᬬᬜᬃ᭞
                </p>
                <p class="text-xs font-bold tracking-wide mt-0.5" style="color:rgba(255,255,255,.9); letter-spacing:.05em;">
                    SMA NEGERI 1 GIANYAR
                </p>
                <p class="text-xs mt-1" style="color:rgba(147,197,253,.8);">
                    Widya Wahana Bhakti
                </p>
            </div>

            {{-- ─── Panel Kanan (Putih) ─────────────────────────────────── --}}
            <div class="flex-1 bg-white flex flex-col items-center justify-center px-8 py-10">

                {{-- Header kecil --}}
                <div class="hidden sm:block mb-7">
                    <p class="font-balinese text-xs text-gray-400 text-center mb-1 px-2">᭞ᬏᬲ᭄ᬏᬫ᭄ᬅ᭞ᬦᭂᬕᭂᬭᬶ᭞᭑᭞ᬕ᭄ᬬᬜᬃ᭞</p>
                    <div class="flex items-center justify-center gap-2.5">
                        <img src="/img/logo_sekolah.png" alt="Logo" class="w-9 h-9 object-contain">
                        <div class="text-left leading-tight">
                            <p class="text-xs font-extrabold text-gray-700 tracking-widest">SMAN 1 GIANYAR</p>
                            <p class="text-[10px] text-gray-400">Widya Wahana Bhakti</p>
                        </div>
                    </div>
                </div>

                <h2 class="text-2xl font-bold mb-1" style="color:#0d2460;">Masuk ke DOSMAN</h2>
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
                        required autofocus autocomplete="username"
                        placeholder="NISN (Siswa) / NIP atau Email (Guru)"
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('login') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }} focus:bg-white focus:outline-none focus:ring-2 focus:border-transparent text-sm text-gray-700 placeholder-gray-400 transition"
                        style="--tw-ring-color:#0d2460;">

                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            placeholder="Password"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:border-transparent text-sm text-gray-700 placeholder-gray-400 transition"
                            style="--tw-ring-color:#0d2460;">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-blue-900 transition-colors">
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
                            <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded" style="accent-color:#0d2460;">
                            Ingat saya
                        </label>
                        <a href="{{ route('forgot-password') }}" class="text-xs font-semibold" style="color:#0d2460;">Lupa Password?</a>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all text-sm"
                        style="background:linear-gradient(135deg,#0d2460,#1a3a8a); letter-spacing:.03em;">
                        Masuk
                    </button>
                </form>

                <p class="text-[10px] text-gray-300 mt-8">
                    &copy; {{ date('Y') }} SMA Negeri 1 Gianyar &middot; DOSMAN
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
