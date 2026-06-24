<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} — SIMS | SMA Negeri 1 Gianyar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4 font-sans">
    <div class="text-center max-w-sm w-full">

        {{-- Logo --}}
        <div class="flex items-center justify-center gap-3 mb-8">
            <img src="/img/logo_sekolah.png" alt="Logo" class="w-12 h-12 object-contain">
            <div class="text-left">
                <p class="font-bold text-blue-700 text-sm leading-tight">SMA Negeri 1 Gianyar</p>
                <p class="text-gray-400 text-xs">SIMS</p>
            </div>
        </div>

        {{-- Error Code --}}
        <div class="mb-6">
            <p class="text-8xl font-black text-blue-200 leading-none select-none">{{ $code }}</p>
            <h1 class="text-xl font-bold text-gray-800 mt-2">{{ $title }}</h1>
            <p class="text-sm text-gray-500 mt-2">{{ $message }}</p>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col gap-2">
            <button onclick="history.back()"
                class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl text-sm">
                Kembali ke Halaman Sebelumnya
            </button>
            <a href="{{ url('/') }}"
                class="w-full bg-white border border-gray-200 text-gray-600 font-medium py-3 rounded-xl text-sm">
                Ke Halaman Utama
            </a>
        </div>
    </div>
</body>
</html>
