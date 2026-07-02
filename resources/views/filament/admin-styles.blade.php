<style>
/* ═══════════════════════════════════════════════════════════════
   Admin Dosman — Custom Theme
   Dark navy sidebar + 3D depth + Plus Jakarta Sans
═══════════════════════════════════════════════════════════════ */

/* ─── Body & Layout ──────────────────────────────────────────── */
.fi-body {
    background: #0d1628 !important;
}

.fi-layout {
    background: #0d1628 !important;
}

.fi-main-ctn,
.fi-main {
    background: #0d1628 !important;
}

/* ─── Topbar ─────────────────────────────────────────────────── */
.fi-topbar-ctn {
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}

.fi-topbar {
    background: #070c18 !important;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.5) !important;
}

/* ─── Sidebar — 3D Effect ────────────────────────────────────── */
.fi-sidebar {
    background: linear-gradient(175deg, #040710 0%, #060b16 50%, #05090f 100%) !important;
    border-right: none !important;
    box-shadow:
        6px 0 40px rgba(0, 0, 0, 0.75),
        3px 0 15px rgba(0, 0, 0, 0.55),
        1px 0 4px rgba(0, 0, 0, 0.4),
        inset -1px 0 0 rgba(255, 255, 255, 0.05) !important;
}

/* Amber glow strip — tepi kanan sidebar */
.fi-sidebar::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 1px;
    background: linear-gradient(
        180deg,
        transparent 5%,
        rgba(251, 191, 36, 0.35) 35%,
        rgba(245, 158, 11, 0.5) 50%,
        rgba(251, 191, 36, 0.35) 65%,
        transparent 95%
    );
    pointer-events: none;
}

/* ─── Sidebar Header ─────────────────────────────────────────── */
.fi-sidebar-header-ctn {
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    background: rgba(0, 0, 0, 0.25) !important;
}

/* ─── Nav Group Labels ───────────────────────────────────────── */
.fi-sidebar-group-label {
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.06em !important;
    text-transform: uppercase !important;
    color: rgba(255, 255, 255, 0.85) !important;
    text-shadow:
        0 0 12px rgba(234, 179, 8, 0.55),
        0 0 24px rgba(234, 179, 8, 0.25),
        0 1px 4px rgba(0, 0, 0, 0.6) !important;
}

.fi-sidebar-group-btn {
    opacity: 0.85;
    transition: opacity 0.2s !important;
}

.fi-sidebar-group-btn:hover {
    opacity: 1;
}

/* ─── Nav Items ──────────────────────────────────────────────── */
.fi-sidebar-item-btn {
    border-radius: 0.5rem !important;
    transition: background 0.18s ease, transform 0.18s ease !important;
    border-left: 2px solid transparent;
}

.fi-sidebar-item-toggle {
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
    gap: 0.5rem !important;
}

.fi-sidebar-item-btn:hover:not(.fi-active) {
    background: rgba(255, 255, 255, 0.06) !important;
    transform: translateX(3px);
    border-left-color: rgba(251, 191, 36, 0.3);
}

.fi-sidebar-item-btn.fi-active,
.fi-sidebar-item-btn[aria-current] {
    background: rgba(245, 158, 11, 0.12) !important;
    border-left-color: rgb(245, 158, 11) !important;
}

.fi-sidebar-item-label {
    font-size: 0.82rem !important;
    font-weight: 500 !important;
}

/* ─── Sidebar Footer ─────────────────────────────────────────── */
.fi-sidebar-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
    background: rgba(0, 0, 0, 0.2) !important;
}

/* ─── Page Heading ───────────────────────────────────────────── */
.fi-header-heading {
    font-weight: 800 !important;
    letter-spacing: -0.03em !important;
}

/* ─── Widgets & Cards ────────────────────────────────────────── */
.fi-wi-account-widget,
.fi-wi-filament-info-widget {
    background: #0f1d33 !important;
    border: 1px solid rgba(255, 255, 255, 0.07) !important;
    border-radius: 1rem !important;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.35) !important;
}

/* ─── Tables ─────────────────────────────────────────────────── */
.fi-ta-ctn {
    background: #0f1d33 !important;
    border: 1px solid rgba(255, 255, 255, 0.07) !important;
    border-radius: 1rem !important;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3) !important;
    overflow: hidden;
}

.fi-ta-header-ctn {
    background: #0f1d33 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}

/* ─── Section / Form Panels ──────────────────────────────────── */
.fi-section {
    background: #0f1d33 !important;
    border: 1px solid rgba(255, 255, 255, 0.07) !important;
    border-radius: 1rem !important;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3) !important;
}

.fi-section-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}

/* ─── Sidebar scroll fix ──────────────────────────────────────── */
/* Sidebar luar: flex column dengan tinggi tetap, tidak boleh scroll */
.fi-sidebar {
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}

/* Nav dalam: min-height:0 wajib agar flex child mau shrink & scroll */
.fi-sidebar-nav {
    flex: 1 1 0% !important;
    min-height: 0 !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    scrollbar-width: none !important;
}
.fi-sidebar-nav::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
}
</style>
