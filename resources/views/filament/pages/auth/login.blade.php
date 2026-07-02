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
.lc-school-header {
    display: none;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 1.75rem;
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
.lc-form {
    width: 100%;
}
.lc-footer {
    font-size: 0.6rem;
    color: #374151;
    margin-top: 1.5rem;
    text-align: center;
}
@media (min-width: 640px) {
    .lc-card { flex-direction: row; }
    .lc-left { width: 42%; flex-shrink: 0; min-height: unset; }
    .lc-school-header { display: flex; }
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
            <h2 class="lc-title">SIMS Admin</h2>
            <p class="lc-desc">
                Sistem Informasi Manajemen Sekolah<br>SMA Negeri 1 Gianyar
            </p>
            <div class="lc-divider"></div>
            <p class="lc-quote">"Learn, Inovate, and Build The Future"</p>
        </div>

        {{-- ─── Panel Kanan (Form) ──────────────────────────────────── --}}
        <div class="lc-right">
            <div class="lc-school-header">
                <img src="/img/logo_sekolah.png" alt="Logo">
                <div>
                    <p class="lc-school-name">SMAN 1 GIANYAR</p>
                    <p class="lc-school-sub">SMA Negeri 1 Gianyar</p>
                </div>
            </div>

            <h1 class="lc-heading">Login Admin</h1>
            <p class="lc-subheading">Masuk ke panel administrasi SIMS</p>

            <div class="lc-form">
                {{ $this->content }}
            </div>

            <p class="lc-footer">&copy; {{ date('Y') }} SMA Negeri 1 Gianyar &middot; SIMS</p>
        </div>

    </div>
</div>
