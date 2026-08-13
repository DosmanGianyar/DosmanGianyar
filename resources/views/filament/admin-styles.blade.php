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
}

/* Sidebar SVG Icon size cap fix */
.fi-sidebar svg {
    max-width: 1.5rem !important;
    max-height: 1.5rem !important;
    flex-shrink: 0 !important;
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
    overflow: visible !important;
    min-height: 380px !important;
}

.fi-ta-header-ctn {
    background: #0f1d33 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
    overflow: visible !important;
}

.fi-ta-content-ctn,
.fi-ta-content {
    border-bottom-left-radius: 1rem !important;
    border-bottom-right-radius: 1rem !important;
    overflow-x: auto !important;
}

.fi-dropdown-panel,
.fi-popover,
.fi-ta-filters-dropdown,
[data-filament-dropdown-panel] {
    z-index: 99 !important;
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

/* ─── Sidebar Footer / User Profile Card ─────────────────────── */
.sims-sidebar-footer-container {
    padding: 0.75rem 0.875rem !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(4, 7, 16, 0.8) !important;
}

.sims-sidebar-user-card {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 0.75rem !important;
    padding: 0.625rem 0.75rem !important;
    border-radius: 0.75rem !important;
    background: rgba(15, 29, 51, 0.85) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
}

.sims-user-avatar {
    width: 2rem !important;
    height: 2rem !important;
    min-width: 2rem !important;
    min-height: 2rem !important;
    border-radius: 0.5rem !important;
    background: rgba(245, 158, 11, 0.15) !important;
    border: 1px solid rgba(245, 158, 11, 0.3) !important;
    color: #fbbf24 !important;
    font-weight: 700 !important;
    font-size: 0.82rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
}

.sims-user-info {
    flex: 1 1 0% !important;
    min-width: 0 !important;
}

.sims-user-name {
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    color: #e2e8f0 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    line-height: 1.2 !important;
    margin: 0 !important;
}

.sims-role-badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 0.1rem 0.4rem !important;
    font-size: 0.65rem !important;
    font-weight: 700 !important;
    border-radius: 0.375rem !important;
    border: 1px solid transparent !important;
    margin-top: 0.2rem !important;
}

.sims-role-admin {
    background: rgba(245, 158, 11, 0.2) !important;
    color: #fcd34d !important;
    border-color: rgba(245, 158, 11, 0.35) !important;
}

.sims-role-kesiswaan {
    background: rgba(16, 185, 129, 0.2) !important;
    color: #6ee7b7 !important;
    border-color: rgba(16, 185, 129, 0.35) !important;
}

.sims-role-kurikulum {
    background: rgba(99, 102, 241, 0.2) !important;
    color: #a5b4fc !important;
    border-color: rgba(99, 102, 241, 0.35) !important;
}

.sims-role-sarpras {
    background: rgba(168, 85, 247, 0.2) !important;
    color: #d8b4fe !important;
    border-color: rgba(168, 85, 247, 0.35) !important;
}

.sims-role-humas {
    background: rgba(6, 182, 212, 0.2) !important;
    color: #67e8f9 !important;
    border-color: rgba(6, 182, 212, 0.35) !important;
}

.sims-logout-btn {
    display: flex !important;
    align-items: center !important;
    gap: 0.375rem !important;
    padding: 0.375rem 0.625rem !important;
    border-radius: 0.5rem !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    color: #f87171 !important;
    background: rgba(239, 68, 68, 0.12) !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    flex-shrink: 0 !important;
}

.sims-logout-btn:hover {
    background: #dc2626 !important;
    color: #ffffff !important;
    border-color: #ef4444 !important;
}

.sims-logout-btn svg {
    width: 1rem !important;
    height: 1rem !important;
    min-width: 1rem !important;
    min-height: 1rem !important;
    flex-shrink: 0 !important;
}

/* ─── Data Siswa: tombol aksi grid 2x2 ───────────────────────── */
/* Di-scope lewat class di <body> (lihat script di bawah), bukan lewat
   request()->routeIs() -- ketahuan tidak konsisten di dalam render hook
   Filament (kemungkinan konteks request internal Livewire berbeda). */
body.sims-users-actions-grid .fi-ta-actions {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(1.75rem, auto)) !important;
    grid-auto-flow: row !important;
    align-items: center !important;
    justify-items: center !important;
    gap: 0.25rem !important;
    width: fit-content !important;
}
body.sims-users-actions-grid .fi-ta-actions > * {
    grid-column: auto !important;
    grid-row: auto !important;
    position: relative !important;
}
</style>

<script>
(function () {
    function applySimsUsersActionsGrid() {
        if (!document.body) return;
        if (window.location.pathname.indexOf('/admin/users') !== -1) {
            document.body.classList.add('sims-users-actions-grid');
        } else {
            document.body.classList.remove('sims-users-actions-grid');
        }
    }
    document.addEventListener('DOMContentLoaded', applySimsUsersActionsGrid);
    document.addEventListener('livewire:navigated', applySimsUsersActionsGrid);
    applySimsUsersActionsGrid();
})();
</script>
