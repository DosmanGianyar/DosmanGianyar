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
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.fi-topbar {
    background: linear-gradient(135deg, #0f2460 0%, #1a3a8f 50%, #1e3fad 100%) !important;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.4) !important;
}

/* ─── Sidebar — 3D Effect ────────────────────────────────────── */
.fi-sidebar {
    background: linear-gradient(175deg, #040710 0%, #060b16 50%, #05090f 100%) !important;
    border-right: none !important;
    box-shadow:
        6px 0 40px rgba(0, 0, 0, 0.75),
        3px 0 15px rgba(0, 0, 0, 0.55),
        1px 0 4px rgba(0, 0, 0, 0.4);
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
        rgba(59, 130, 246, 0.4) 35%,
        rgba(37, 99, 235, 0.6) 50%,
        rgba(59, 130, 246, 0.4) 65%,
        transparent 95%
    );
    pointer-events: none;
}

/* ─── Sidebar Header ─────────────────────────────────────────── */
.fi-sidebar-header-ctn {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(0, 0, 0, 0.25) !important;
}

/* ─── Nav Group Labels ───────────────────────────────────────── */
.fi-sidebar-group-label {
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.06em !important;
    text-transform: uppercase !important;
    color: rgba(255, 255, 255, 0.9) !important;
    text-shadow:
        0 0 12px rgba(59, 130, 246, 0.55),
        0 0 24px rgba(37, 99, 235, 0.25),
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
    border-radius: 0.75rem !important;
    transition: background 0.18s ease, transform 0.18s ease !important;
    border-left: 3px solid transparent;
}

.fi-sidebar-item-toggle {
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
    gap: 0.5rem !important;
}

.fi-sidebar-item-btn:hover:not(.fi-active) {
    background: rgba(255, 255, 255, 0.08) !important;
    transform: translateX(4px);
    border-left-color: rgba(59, 130, 246, 0.4);
}

.fi-sidebar-item-btn.fi-active,
.fi-sidebar-item-btn[aria-current] {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.25) 0%, rgba(67, 56, 202, 0.25) 100%) !important;
    border-left-color: #3b82f6 !important;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2) !important;
}

.fi-sidebar-item-label {
    font-size: 0.82rem !important;
    font-weight: 600 !important;
}

/* ─── Sidebar Footer ─────────────────────────────────────────── */
.fi-sidebar-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(0, 0, 0, 0.25) !important;
}

/* ─── Page Heading ───────────────────────────────────────────── */
.fi-header-heading {
    font-weight: 800 !important;
    letter-spacing: -0.03em !important;
}

/* ─── Widgets, Stats Cards & Glassmorphism ────────────────────── */
.fi-wi-widget,
.fi-wi-stats-overview-stat-ctn,
.fi-wi-chart,
.fi-wi-account-widget,
.fi-wi-filament-info-widget {
    background: rgba(15, 29, 51, 0.85) !important;
    backdrop-filter: blur(12px) !important;
    border: 1px solid rgba(255, 255, 255, 0.09) !important;
    border-radius: 1.25rem !important;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4) !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease !important;
}

.fi-wi-stats-overview-stat-ctn:hover {
    transform: translateY(-3px) !important;
    border-color: rgba(59, 130, 246, 0.3) !important;
    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.5), 0 0 25px rgba(37, 99, 235, 0.15) !important;
}

/* ─── Dashboard & Widget Icon Fixes ───────────────────────────── */
.fi-wi-stats-overview-stat-icon-ctn,
.fi-wi-stats-overview-stat-icon,
.fi-wi-stats-overview-stat-description-icon {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
}

.fi-wi-stats-overview-stat svg,
.fi-wi-stats-overview-stat-icon svg {
    width: 1.5rem !important;
    height: 1.5rem !important;
    max-width: 1.5rem !important;
    max-height: 1.5rem !important;
    display: inline-block !important;
    flex-shrink: 0 !important;
}

.fi-wi-stats-overview-stat-description svg,
.fi-wi-stats-overview-stat-description-icon svg,
.fi-wi-stats-overview-stat-description-icon {
    width: 1.125rem !important;
    height: 1.125rem !important;
    max-width: 1.125rem !important;
    max-height: 1.125rem !important;
    display: inline-block !important;
    flex-shrink: 0 !important;
    vertical-align: text-bottom !important;
}

/* Bright contrast colors for stat description icons and text */
.fi-wi-stats-overview-stat-description {
    font-weight: 600 !important;
    opacity: 0.95 !important;
}

/* General SVG Icon rendering rules across Filament dashboard */
.fi-sidebar-item-icon,
.fi-sidebar-item-icon svg {
    width: 1.25rem !important;
    height: 1.25rem !important;
    max-width: 1.25rem !important;
    max-height: 1.25rem !important;
    flex-shrink: 0 !important;
    display: inline-block !important;
}

.fi-btn svg,
.fi-icon-btn svg {
    width: 1.25rem !important;
    height: 1.25rem !important;
    max-width: 1.5rem !important;
    max-height: 1.5rem !important;
    flex-shrink: 0 !important;
    display: inline-block !important;
}

.fi-topbar svg {
    width: 1.25rem !important;
    height: 1.25rem !important;
    flex-shrink: 0 !important;
}

/* ─── Tables ─────────────────────────────────────────────────── */
.fi-ta-ctn {
    background: rgba(15, 29, 51, 0.85) !important;
    backdrop-filter: blur(12px) !important;
    border: 1px solid rgba(255, 255, 255, 0.09) !important;
    border-radius: 1.25rem !important;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4) !important;
    overflow: visible !important;
    min-height: 380px !important;
}

.fi-ta-header-ctn {
    background: rgba(15, 29, 51, 0.95) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-top-left-radius: 1.25rem !important;
    border-top-right-radius: 1.25rem !important;
    overflow: visible !important;
}

.fi-ta-content-ctn,
.fi-ta-content {
    border-bottom-left-radius: 1.25rem !important;
    border-bottom-right-radius: 1.25rem !important;
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
    background: rgba(15, 29, 51, 0.85) !important;
    backdrop-filter: blur(12px) !important;
    border: 1px solid rgba(255, 255, 255, 0.09) !important;
    border-radius: 1.25rem !important;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4) !important;
}

.fi-section-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}

/* ─── Grade Filter ToggleButtons (Mobile Segment Style) ──────── */
.fi-fo-toggle-buttons {
    background: rgba(15, 29, 51, 0.85) !important;
    backdrop-filter: blur(12px) !important;
    border-radius: 1rem !important;
    padding: 0.35rem !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    display: inline-flex !important;
    flex-wrap: wrap !important;
    gap: 0.5rem !important;
    align-items: center !important;
    margin-bottom: 0.5rem !important;
}

.fi-fo-toggle-buttons label {
    border-radius: 0.75rem !important;
    font-weight: 700 !important;
    transition: all 0.2s ease !important;
    padding: 0.35rem 0.85rem !important;
    font-size: 0.825rem !important;
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
    padding-bottom: 1rem !important;
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
    background: rgba(4, 7, 16, 0.85) !important;
}

.sims-sidebar-user-card {
    display: flex !important;
    flex-direction: column !important;
    gap: 0.625rem !important;
    padding: 0.75rem !important;
    border-radius: 0.875rem !important;
    background: rgba(15, 29, 51, 0.95) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4) !important;
}

.sims-user-profile-header {
    display: flex !important;
    align-items: center !important;
    gap: 0.625rem !important;
    width: 100% !important;
}

.sims-user-avatar {
    width: 2.25rem !important;
    height: 2.25rem !important;
    min-width: 2.25rem !important;
    min-height: 2.25rem !important;
    border-radius: 0.625rem !important;
    background: rgba(245, 158, 11, 0.2) !important;
    border: 1px solid rgba(245, 158, 11, 0.4) !important;
    color: #fbbf24 !important;
    font-weight: 800 !important;
    font-size: 0.9rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
}

.sims-user-details {
    flex: 1 1 0% !important;
    min-width: 0 !important;
}

.sims-user-name {
    font-size: 0.85rem !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    line-height: 1.2 !important;
    margin: 0 !important;
}

.sims-role-badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 0.125rem 0.4rem !important;
    font-size: 0.65rem !important;
    font-weight: 700 !important;
    border-radius: 0.375rem !important;
    border: 1px solid transparent !important;
    margin-top: 0.25rem !important;
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

.sims-user-actions-bar {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    width: 100% !important;
}

.sims-profile-btn,
.sims-logout-btn {
    flex: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.375rem !important;
    padding: 0.4rem 0.5rem !important;
    border-radius: 0.5rem !important;
    font-size: 0.725rem !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    box-sizing: border-box !important;
}

.sims-profile-btn {
    color: #38bdf8 !important;
    background: rgba(56, 189, 248, 0.12) !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
}

.sims-profile-btn:hover {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #38bdf8 !important;
}

.sims-logout-btn {
    color: #f87171 !important;
    background: rgba(239, 68, 68, 0.12) !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
}

.sims-logout-btn:hover {
    background: #dc2626 !important;
    color: #ffffff !important;
    border-color: #ef4444 !important;
}

.sims-profile-btn svg,
.sims-logout-btn svg {
    width: 0.875rem !important;
    height: 0.875rem !important;
    flex-shrink: 0 !important;
}

.sims-logout-form {
    flex: 1 !important;
    margin: 0 !important;
    padding: 0 !important;
    display: flex !important;
}

.sims-logout-form button {
    width: 100% !important;
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
/* ─── Dashboard Hero Banner — Explicit CSS (No Tailwind Dependency) ─── */
.sims-dashboard-hero {
    position: relative !important;
    overflow: hidden !important;
    border-radius: 1.25rem !important;
    padding: 1.5rem 1.75rem !important;
    margin-bottom: 1.5rem !important;
    background: linear-gradient(135deg, #0f2460 0%, #1a3a8f 50%, #1e3fad 100%) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
    color: #ffffff !important;
}

.sims-hero-glow-1 {
    position: absolute !important;
    top: -4rem !important;
    right: -4rem !important;
    width: 16rem !important;
    height: 16rem !important;
    border-radius: 9999px !important;
    background: rgba(96, 165, 250, 0.2) !important;
    filter: blur(40px) !important;
    pointer-events: none !important;
}

.sims-hero-glow-2 {
    position: absolute !important;
    bottom: -4rem !important;
    left: -4rem !important;
    width: 16rem !important;
    height: 16rem !important;
    border-radius: 9999px !important;
    background: rgba(99, 102, 241, 0.2) !important;
    filter: blur(40px) !important;
    pointer-events: none !important;
}

.sims-hero-content {
    position: relative !important;
    z-index: 10 !important;
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 1.25rem !important;
}

.sims-hero-text-block {
    display: flex !important;
    flex-direction: column !important;
    gap: 0.5rem !important;
}

.sims-hero-status-tag {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.25rem 0.75rem !important;
    border-radius: 9999px !important;
    background: rgba(255, 255, 255, 0.12) !important;
    backdrop-filter: blur(8px) !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    color: #bfdbfe !important;
    width: fit-content !important;
}

.sims-hero-dot {
    width: 0.5rem !important;
    height: 0.5rem !important;
    border-radius: 9999px !important;
    background: #34d399 !important;
    box-shadow: 0 0 8px #34d399 !important;
}

.sims-hero-title {
    font-size: 1.5rem !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    letter-spacing: -0.02em !important;
    margin: 0 !important;
    line-height: 1.25 !important;
}

.sims-hero-subtitle {
    font-size: 0.9rem !important;
    color: #93c5fd !important;
    font-weight: 500 !important;
    margin: 0 !important;
}

.sims-hero-subtitle strong {
    color: #ffffff !important;
    font-weight: 700 !important;
}

.sims-hero-date-badge {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.6rem 1rem !important;
    border-radius: 0.75rem !important;
    background: rgba(255, 255, 255, 0.12) !important;
    backdrop-filter: blur(8px) !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    font-size: 0.85rem !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
}

.sims-hero-date-badge svg {
    width: 1.1rem !important;
    height: 1.1rem !important;
    color: #93c5fd !important;
    flex-shrink: 0 !important;
}
</style>

<script>
(function () {
    function applySimsUsersActionsGrid() {
        if (!document.body) return;
        var p = window.location.pathname;
        if (
            p.indexOf('/admin/users') !== -1 ||
            p.indexOf('/admin/student-achievements') !== -1 ||
            p.indexOf('/admin/extracurriculars') !== -1 ||
            p.indexOf('/admin/extracurricular-members') !== -1
        ) {
            document.body.classList.add('sims-users-actions-grid');
        } else {
            document.body.classList.remove('sims-users-actions-grid');
        }
    }
    document.addEventListener('DOMContentLoaded', applySimsUsersActionsGrid);
    document.addEventListener('livewire:navigated', applySimsUsersActionsGrid);
    document.addEventListener('livewire:initialized', applySimsUsersActionsGrid);
    if (window.Livewire) {
        window.Livewire.hook('commit', function (ref) {
            if (ref && ref.respond) ref.respond(applySimsUsersActionsGrid);
        });
    }
    applySimsUsersActionsGrid();
    setInterval(applySimsUsersActionsGrid, 500);
})();
</script>
