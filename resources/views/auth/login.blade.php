<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIMAK DOSMAN | SMA Negeri 1 Gianyar</title>
    <link rel="icon" type="image/png" href="/img/logo_sekolah.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-4 relative overflow-x-hidden selection:bg-blue-600 selection:text-white">

    {{-- ─── Ambient Glow Orbs Latar Belakang ──────────────────────────────── --}}
    <div class="fixed -top-40 -left-40 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed -bottom-40 -right-40 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed inset-0 pointer-events-none opacity-20"
         style="background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 24px 24px;"></div>

    <div class="w-full max-w-4xl relative z-10">
        <div class="flex flex-col sm:flex-row bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">

            {{-- ─── Panel Kiri: Identitas Sekolah & SIMAK ────────────────────── --}}
            <div class="w-full sm:w-5/12 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-8 sm:p-10 flex flex-col justify-between text-white text-center sm:text-left relative overflow-hidden">
                {{-- Decorative light --}}
                <div class="absolute -top-12 -left-12 w-48 h-48 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-indigo-500/15 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10">
                    {{-- Logo & Badging --}}
                    <div class="flex flex-col sm:flex-row items-center gap-3 mb-6">
                        <div class="relative w-16 h-16 sm:w-14 sm:h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 p-2.5 flex items-center justify-center shrink-0 shadow-inner">
                            <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <p class="font-balinese text-xs text-indigo-200/90 leading-tight">᭞ᬏᬲ᭄ᬏᬫ᭄ᬅ᭞ᬦᭂᬕᭂᬭᬶ᭞᭑᭞ᬕ᭄ᬬᬜᬃ᭞</p>
                            <h2 class="text-xs font-black tracking-wider text-white uppercase mt-0.5">SMA Negeri 1 Gianyar</h2>
                            <p class="text-[10px] text-indigo-200/70 font-medium">Widya Wahana Bhakti</p>
                        </div>
                    </div>

                    {{-- Nama Aplikasi --}}
                    <div class="mt-4">
                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-200 border border-blue-400/30 uppercase tracking-widest mb-2">
                            Aplikasi Resmi Sekolah
                        </span>
                        <h1 class="text-3xl font-black tracking-tight text-white leading-tight">SIMAK DOSMAN</h1>
                        <p class="text-xs text-indigo-200/90 mt-1.5 leading-relaxed">
                            Sistem Informasi Manajemen Anak Kolektif. Platform terintegrasi presensi, akademik, konsultasi, dan kesiswaan.
                        </p>
                    </div>
                </div>

                {{-- Fitur Highlight di Bagian Bawah --}}
                <div class="relative z-10 mt-8 pt-6 border-t border-white/10 hidden sm:block">
                    <div class="grid grid-cols-2 gap-2 text-left">
                        <div class="flex items-center gap-2 text-[11px] text-indigo-200">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Presensi Geolocation</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-indigo-200">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Monitoring Rapor</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-indigo-200">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Chat Konsultasi Ortu</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-indigo-200">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Jurnal KBM Mandiri</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Panel Kanan: Form Otentikasi ────────────────────────────── --}}
            <div class="flex-1 bg-white p-8 sm:p-10 flex flex-col justify-center">

                <div class="mb-5">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Selamat Datang</h2>
                    <p class="text-xs text-slate-400 mt-1">Silakan masukkan akun Anda untuk melanjutkan ke portal</p>
                </div>

                {{-- Alert Error --}}
                @if ($errors->any())
                <div class="w-full bg-rose-50 border border-rose-200/80 rounded-2xl p-3.5 mb-4 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-xs font-semibold text-rose-700 leading-tight">{{ $errors->first() }}</p>
                </div>
                @endif

                {{-- Container Akun Tersimpan di Perangkat Ini --}}
                <div id="saved-accounts-container" class="w-full mb-4 hidden bg-slate-50 border border-slate-200/80 rounded-2xl p-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Akun Tersimpan di Perangkat Ini:</span>
                        </span>
                    </div>
                    <div id="saved-accounts-chips" class="flex flex-wrap gap-1.5"></div>
                </div>

                {{-- Form Login --}}
                <form method="POST" action="{{ route('login.submit') }}" class="w-full space-y-3.5">
                    @csrf

                    {{-- Input Identitas --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Identitas / Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="login"
                                value="{{ old('login') }}"
                                required autofocus autocomplete="username" inputmode="text" autocapitalize="none" spellcheck="false"
                                placeholder="NISN (Siswa) / NIP atau Email (Guru) / No. HP (Ortu)"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('login') ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50' }} focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 text-xs sm:text-sm text-slate-800 placeholder-slate-400 transition-all font-medium">
                        </div>
                    </div>

                    {{-- Tip Khusus Siswa --}}
                    <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-100 flex items-start gap-2">
                        <span class="text-xs">💡</span>
                        <p class="text-[11px] text-blue-900 leading-tight">
                            <strong>Siswa:</strong> Gunakan <strong>NISN</strong> (10 digit). Perhatikan huruf kapital pada password bawaan.
                        </p>
                    </div>

                    {{-- Input Password --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                inputmode="text" autocapitalize="none" spellcheck="false"
                                placeholder="Masukkan password Anda"
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 text-xs sm:text-sm text-slate-800 placeholder-slate-400 transition-all font-medium">
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 transition-colors p-0.5"
                                title="Tampilkan / Sembunyikan Password">
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me & Lupa Password --}}
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-1.5 text-xs text-slate-600 cursor-pointer select-none font-medium">
                            <input type="checkbox" name="remember" checked class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500/30 border-slate-300">
                            <span>Ingat saya & Simpan akun</span>
                        </label>
                        <a href="{{ route('forgot-password') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                            Lupa Password?
                        </a>
                    </div>

                    {{-- Tombol Masuk --}}
                    <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98] text-white text-xs sm:text-sm font-bold rounded-xl shadow-md hover:shadow-indigo-500/20 transition-all flex items-center justify-center gap-2 mt-2">
                        <span>Masuk ke Akun</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                {{-- Footer Hak Cipta --}}
                <div class="mt-8 text-center border-t border-slate-100 pt-4 w-full">
                    <p class="text-[11px] text-slate-500 font-semibold">
                        &copy; {{ date('Y') }} SMA Negeri 1 Gianyar &middot; <span class="text-blue-900 font-black">SIMAK DOSMAN</span>
                    </p>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">
                        Sistem Informasi Manajemen Anak Kolektif
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- ─── Script Multi-Account Storage & Password Toggle ───────────────── --}}
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadSavedAccounts();

            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    const loginInput = document.querySelector('input[name="login"]');
                    const passInput  = document.getElementById('password');
                    const rememberCb = document.querySelector('input[name="remember"]');

                    const loginVal = loginInput ? loginInput.value.trim() : '';
                    const passVal  = passInput ? passInput.value : '';
                    const remember = rememberCb ? rememberCb.checked : false;

                    if (remember && loginVal && passVal) {
                        saveAccount(loginVal, passVal);
                    } else if (!remember && loginVal) {
                        removeAccount(loginVal);
                    }
                });
            }
        });

        function getSavedAccounts() {
            try {
                return JSON.parse(localStorage.getItem('dosman_saved_accounts') || '[]');
            } catch (e) {
                return [];
            }
        }

        function saveAccount(login, password) {
            let accounts = getSavedAccounts();
            accounts = accounts.filter(a => a.login.toLowerCase() !== login.toLowerCase());
            accounts.unshift({ login: login, password: password });
            if (accounts.length > 5) accounts.pop();
            localStorage.setItem('dosman_saved_accounts', JSON.stringify(accounts));
        }

        function removeAccount(login) {
            let accounts = getSavedAccounts();
            accounts = accounts.filter(a => a.login.toLowerCase() !== login.toLowerCase());
            localStorage.setItem('dosman_saved_accounts', JSON.stringify(accounts));
            loadSavedAccounts();
        }

        function selectAccount(login, password) {
            const loginInput = document.querySelector('input[name="login"]');
            const passInput  = document.getElementById('password');
            const rememberCb = document.querySelector('input[name="remember"]');

            if (loginInput) loginInput.value = login;
            if (passInput)  passInput.value  = password;
            if (rememberCb) rememberCb.checked = true;

            highlightSelectedChip(login);
        }

        function highlightSelectedChip(login) {
            const chips = document.querySelectorAll('.saved-account-chip');
            chips.forEach(chip => {
                if (chip.dataset.login.toLowerCase() === login.toLowerCase()) {
                    chip.classList.add('bg-blue-100', 'border-blue-400', 'text-blue-900', 'font-bold');
                    chip.classList.remove('bg-white', 'border-slate-200', 'text-slate-700');
                } else {
                    chip.classList.remove('bg-blue-100', 'border-blue-400', 'text-blue-900', 'font-bold');
                    chip.classList.add('bg-white', 'border-slate-200', 'text-slate-700');
                }
            });
        }

        function loadSavedAccounts() {
            const accounts = getSavedAccounts();
            const container = document.getElementById('saved-accounts-container');
            const chipsWrapper = document.getElementById('saved-accounts-chips');

            if (!container || !chipsWrapper) return;

            if (!accounts || accounts.length === 0) {
                container.classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');
            chipsWrapper.innerHTML = '';

            const currentLogin = document.querySelector('input[name="login"]')?.value.trim();

            accounts.forEach((acc, index) => {
                const chip = document.createElement('div');
                const isSelected = currentLogin ? (currentLogin.toLowerCase() === acc.login.toLowerCase()) : (index === 0);

                chip.className = `saved-account-chip flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs cursor-pointer transition ${
                    isSelected ? 'bg-blue-100 border-blue-400 text-blue-900 font-bold' : 'bg-white border-slate-200 text-slate-700 hover:bg-blue-50/50'
                }`;
                chip.dataset.login = acc.login;

                chip.innerHTML = `
                    <span onclick="selectAccount('${acc.login}', '${acc.password}')" class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                        <span>${acc.login}</span>
                    </span>
                    <button type="button" onclick="event.stopPropagation(); removeAccount('${acc.login}')" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-full transition-colors ml-0.5" title="Hapus akun tersimpan">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                `;

                chipsWrapper.appendChild(chip);

                if (index === 0 && !document.querySelector('input[name="login"]')?.value) {
                    selectAccount(acc.login, acc.password);
                }
            });
        }
    </script>
</body>
</html>
