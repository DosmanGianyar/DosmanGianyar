<style>
.lc-card {
    display: flex;
    flex-direction: column;
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,.75), 0 0 0 1px rgba(255,255,255,.07);
    width: 100%;
}
.lc-left {
    background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 40%, #2563eb 70%, #4338ca 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 2rem;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 200px;
}
.lc-left::before {
    content: '';
    position: absolute;
    top: -4rem; left: -4rem;
    width: 14rem; height: 14rem;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
    filter: blur(40px);
    pointer-events: none;
}
.lc-left::after {
    content: '';
    position: absolute;
    bottom: -3rem; right: -3rem;
    width: 12rem; height: 12rem;
    background: rgba(99,102,241,.18);
    border-radius: 50%;
    filter: blur(40px);
    pointer-events: none;
}
.lc-logo-ring {
    position: relative;
    width: 5.5rem;
    height: 5.5rem;
    margin-bottom: 1.25rem;
    flex-shrink: 0;
}
.lc-logo-ring::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,.14);
    border-radius: 50%;
    box-shadow: 0 0 0 3px rgba(255,255,255,.22);
}
.lc-logo-ring img {
    position: relative;
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 0.6rem;
}
.lc-badge {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(147,197,253,1);
    margin-bottom: 0.3rem;
    position: relative;
}
.lc-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.7rem;
    line-height: 1.3;
    position: relative;
}
.lc-desc {
    font-size: 0.72rem;
    color: rgba(147,197,253,1);
    line-height: 1.7;
    margin-bottom: 1.25rem;
    max-width: 12rem;
    position: relative;
}
.lc-divider {
    width: 2.5rem;
    height: 1px;
    background: rgba(255,255,255,.25);
    margin-bottom: 1.25rem;
    position: relative;
}
.lc-quote {
    font-size: 0.68rem;
    font-style: italic;
    color: rgba(255,255,255,.55);
    line-height: 1.65;
    max-width: 12rem;
    position: relative;
}
.lc-right {
    flex: 1;
    background: #0d1628;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 2rem;
    border-left: 1px solid rgba(255,255,255,.06);
}
.lc-school-header-wrap {
    display: none;
    margin-bottom: 1.75rem;
}
.lc-school-header {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}
.lc-school-header img {
    width: 2.25rem;
    height: 2.25rem;
    object-fit: contain;
    flex-shrink: 0;
}
.lc-school-name {
    font-size: 0.68rem;
    font-weight: 800;
    color: #e5e7eb;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    line-height: 1.3;
}
.lc-school-sub {
    font-size: 0.6rem;
    color: #6b7280;
    line-height: 1.3;
}
.lc-heading {
    font-size: 1.45rem;
    font-weight: 700;
    color: #f9fafb;
    margin-bottom: 0.3rem;
    text-align: center;
}
.lc-subheading {
    font-size: 0.72rem;
    color: #6b7280;
    margin-bottom: 1.75rem;
    text-align: center;
}
.lc-form { width: 100%; }

/* Form fields */
.lc-field { margin-bottom: 1rem; }
.lc-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: #9ca3af;
    margin-bottom: 0.375rem;
}
.lc-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 0.5rem;
    color: #f9fafb;
    font-size: 0.875rem;
    outline: none;
    box-sizing: border-box;
    transition: border-color .15s;
}
.lc-input:focus {
    border-color: #f59e0b;
    background: rgba(255,255,255,.09);
}
.lc-input::placeholder { color: #4b5563; }
.lc-error {
    color: #f87171;
    font-size: 0.7rem;
    margin-top: 0.3rem;
    display: block;
}
.lc-remember {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}
.lc-remember input[type="checkbox"] { accent-color: #f59e0b; }
.lc-remember label { font-size: 0.78rem; color: #9ca3af; cursor: pointer; }
.lc-btn {
    width: 100%;
    padding: 0.7rem;
    background: #d97706;
    color: white;
    font-size: 0.9rem;
    font-weight: 600;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background .15s;
    letter-spacing: 0.02em;
}
.lc-btn:hover { background: #b45309; }
.lc-footer {
    font-size: 0.6rem;
    color: #374151;
    margin-top: 1.5rem;
    text-align: center;
}
@media (min-width: 640px) {
    .lc-card { flex-direction: row; }
    .lc-left { width: 42%; flex-shrink: 0; min-height: unset; }
    .lc-school-header-wrap { display: block; }
}
@font-face {
    font-family: 'Noto Sans Balinese';
    src: url('/fonts/NotoSansBalinese-Regular.ttf') format('truetype');
    font-weight: 400;
    font-style: normal;
    font-display: swap;
}
.lc-balinese {
    font-family: 'Noto Sans Balinese', serif;
}
</style>

<div style="width:100%">
    <div class="lc-card">

        {{-- ─── Panel Kiri (Biru) ───────────────────────────────────── --}}
        <div class="lc-left">
            <div class="lc-logo-ring">
                <img src="/img/logo_sekolah.png" alt="Logo SMAN 1 Gianyar">
            </div>
            <span class="lc-badge">Panel Admin</span>
            <h2 class="lc-title" style="font-size:1.7rem;font-weight:900;letter-spacing:.18em;">DOSMAN</h2>
            <p class="lc-desc">
                Sistem Informasi Manajemen Siswa<br>SMA Negeri 1 Gianyar
            </p>
            <div class="lc-divider"></div>
            <p class="lc-balinese" style="font-size:0.85rem;color:rgba(255,255,255,.7);position:relative;padding:0 0.5rem;">
                ᭞ᬏᬲ᭄ᬏᬫ᭄ᬅ᭞ᬦᭂᬕᭂᬭᬶ᭞᭑᭞ᬕ᭄ᬬᬜᬃ᭞
            </p>
            <p style="font-size:0.68rem;font-weight:700;letter-spacing:.06em;color:rgba(255,255,255,.7);position:relative;margin-top:0.15rem;">
                SMA NEGERI 1 GIANYAR
            </p>
            <p style="font-size:0.6rem;color:rgba(147,197,253,.65);margin-top:0.25rem;position:relative;">
                Widya Wahana Bhakti
            </p>
        </div>

        {{-- ─── Panel Kanan (Form) ──────────────────────────────────── --}}
        <div class="lc-right">
            <div class="lc-school-header-wrap">
                <p class="lc-balinese" style="font-size:0.7rem;color:#9ca3af;text-align:center;margin-bottom:0.3rem;">᭞ᬏᬲ᭄ᬏᬫ᭄ᬅ᭞ᬦᭂᬕᭂᬭᬶ᭞᭑᭞ᬕ᭄ᬬᬜᬃ᭞</p>
                <div class="lc-school-header">
                    <img src="/img/logo_sekolah.png" alt="Logo">
                    <div>
                        <p class="lc-school-name">SMAN 1 GIANYAR</p>
                        <p class="lc-school-sub">Widya Wahana Bhakti</p>
                    </div>
                </div>
            </div>

            <h1 class="lc-heading">Masuk Panel Admin</h1>
            <p class="lc-subheading">Akses panel administrasi DOSMAN</p>

            {{-- Plain HTML form → LoginController (tidak pakai Livewire) --}}
            <form class="lc-form" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="lc-field">
                    <label class="lc-label" for="lc-email">Alamat Email</label>
                    <input id="lc-email" class="lc-input" type="email" name="login"
                        value="{{ old('login') }}" required autofocus
                        placeholder="admin@sims.sch.id">
                    @error('login')
                        <span class="lc-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="lc-field">
                    <label class="lc-label" for="lc-password">Kata Sandi</label>
                    <input id="lc-password" class="lc-input" type="password" name="password"
                        required placeholder="••••••••">
                    @error('password')
                        <span class="lc-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="lc-remember">
                    <input type="checkbox" id="lc-remember" name="remember">
                    <label for="lc-remember">Ingat saya</label>
                </div>

                <button type="submit" class="lc-btn">Masuk</button>
            </form>

            <p class="lc-footer">&copy; {{ date('Y') }} SMA Negeri 1 Gianyar &middot; DOSMAN</p>
        </div>

    </div>
</div>
