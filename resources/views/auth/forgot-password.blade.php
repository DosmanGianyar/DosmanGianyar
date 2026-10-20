<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — SIMAK DOSMAN | SMA Negeri 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-4 relative overflow-x-hidden selection:bg-blue-600 selection:text-white">

    {{-- ─── Ambient Glow Orbs Latar Belakang ──────────────────────────────── --}}
    <div class="fixed -top-40 -left-40 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed -bottom-40 -right-40 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed inset-0 pointer-events-none opacity-20"
         style="background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 24px 24px;"></div>

    <div class="w-full max-w-md relative z-10">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">

            {{-- ─── Header Card ─────────────────────────────────────────── --}}
            <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 px-8 pt-8 pb-6 text-center text-white relative overflow-hidden">
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 p-2.5 flex items-center justify-center mb-3 shadow-inner">
                        <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar" class="w-full h-full object-contain">
                    </div>
                    <p class="font-balinese text-xs text-indigo-200/90 leading-tight mb-0.5">᭞ᬏᬲ᭄ᬏᬫ᭄ᬅ᭞ᬦᭂᬕᭂᬭᬶ᭞᭑᭞ᬕ᭄ᬬᬜᬃ᭞</p>
                    <h1 class="text-lg font-bold text-white tracking-tight">Permintaan Reset Password</h1>
                    <p class="text-xs text-indigo-200/80 mt-0.5 font-medium">SIMAK DOSMAN &middot; SMAN 1 Gianyar</p>
                </div>
            </div>

            {{-- ─── Body Form ────────────────────────────────────────────── --}}
            <div class="p-6 sm:p-8">
                <p class="text-xs text-slate-500 mb-5 text-center leading-relaxed">
                    Masukkan <strong>NISN / NIP / No. HP</strong> beserta <strong>Tanggal Lahir</strong> Anda yang terdaftar untuk mengajukan reset kata sandi ke Admin Sekolah.
                </p>

                {{-- Alert Sukses --}}
                @if (session('status'))
                <div class="w-full bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 mb-4 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-xs font-semibold text-emerald-800 leading-tight">{{ session('status') }}</p>
                </div>
                @endif

                {{-- Alert Error --}}
                @if ($errors->any())
                <div class="w-full bg-rose-50 border border-rose-200 rounded-2xl p-3.5 mb-4 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-xs font-semibold text-rose-700 leading-tight">{{ $errors->first() }}</p>
                </div>
                @endif

                <form method="POST" action="{{ route('forgot-password.submit') }}" class="w-full space-y-3.5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Pengenal Akun</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="identifier"
                                value="{{ old('identifier') }}"
                                required autofocus
                                placeholder="NISN / NIP / Nomor HP"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('identifier') ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50' }} focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 text-xs sm:text-sm text-slate-800 placeholder-slate-400 transition-all font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir Terdaftar</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                type="date"
                                name="birth_date"
                                value="{{ old('birth_date') }}"
                                required
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('birth_date') ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50' }} focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 text-xs sm:text-sm text-slate-800 transition-all font-medium">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98] text-white text-xs sm:text-sm font-bold rounded-xl shadow-md hover:shadow-indigo-500/20 transition-all flex items-center justify-center gap-2 mt-2">
                        <span>Kirim Permintaan Reset</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-6 text-center border-t border-slate-100 pt-4">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Kembali ke Halaman Login</span>
                    </a>
                </div>
            </div>
        </div>

        <p class="text-[11px] text-slate-500 mt-4 text-center font-medium">
            &copy; {{ date('Y') }} SMA Negeri 1 Gianyar &middot; SIMAK DOSMAN
        </p>
    </div>
</body>
</html>
