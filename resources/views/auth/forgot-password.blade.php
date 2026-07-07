<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — DOSMAN | SMA Negeri 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="rounded-2xl shadow-2xl overflow-hidden bg-white">

            <div class="flex flex-col items-center px-8 pt-10 pb-6 text-center"
                 style="background: linear-gradient(160deg, #0d2460 0%, #1a3a8a 50%, #0d2460 100%);">
                <div class="relative w-16 h-16 mb-3 flex-shrink-0">
                    <div class="absolute inset-0 rounded-full" style="background:rgba(255,255,255,.12); box-shadow:0 0 0 3px rgba(255,255,255,.2);"></div>
                    <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar" class="relative w-full h-full object-contain p-2">
                </div>
                <h1 class="text-white text-lg font-bold">Lupa Password</h1>
                <p class="text-xs mt-1" style="color:rgba(147,197,253,.9);">DOSMAN — SMA Negeri 1 Gianyar</p>
            </div>

            <div class="px-8 py-8">
                <p class="text-sm text-gray-500 mb-6 text-center">
                    Masukkan <strong>NISN</strong> (siswa) atau <strong>NIP</strong> (guru) Anda.
                    Permintaan akan dikirim ke admin untuk diproses.
                </p>

                @if (session('status'))
                <div class="w-full bg-green-50 border border-green-200 rounded-xl px-4 py-2.5 mb-4">
                    <p class="text-green-700 text-xs">{{ session('status') }}</p>
                </div>
                @endif

                @if ($errors->any())
                <div class="w-full bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 mb-4">
                    <p class="text-red-600 text-xs">{{ $errors->first() }}</p>
                </div>
                @endif

                <form method="POST" action="{{ route('forgot-password.submit') }}" class="w-full space-y-3">
                    @csrf

                    <input
                        type="text"
                        name="identifier"
                        value="{{ old('identifier') }}"
                        required autofocus
                        placeholder="NISN atau NIP"
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('identifier') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }} focus:bg-white focus:outline-none focus:ring-2 focus:border-transparent text-sm text-gray-700 placeholder-gray-400 transition"
                        style="--tw-ring-color:#0d2460;">

                    <button type="submit"
                        class="w-full py-2.5 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all text-sm"
                        style="background:linear-gradient(135deg,#0d2460,#1a3a8a); letter-spacing:.03em;">
                        Kirim Permintaan
                    </button>
                </form>

                <p class="text-center mt-5">
                    <a href="{{ route('login') }}" class="text-xs font-semibold" style="color:#0d2460;">&larr; Kembali ke halaman login</a>
                </p>
            </div>
        </div>

        <p class="text-[10px] text-gray-400 mt-4 text-center">
            &copy; {{ date('Y') }} SMA Negeri 1 Gianyar &middot; DOSMAN
        </p>
    </div>
</body>
</html>
