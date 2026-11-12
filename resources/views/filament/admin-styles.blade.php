<style>
/* ═══════════════════════════════════════════════════════════════════════════
   SIMAK DOSMAN — Tabler + Filament Minimalist Unified Theme
   Clean Cool-Slate Canvas (#F6F8FB) + Tabler Dark-Slate Sidebar (#182433)
   + Crisp White Bordered Cards with Top Accent Indicators
═══════════════════════════════════════════════════════════════════════════ */

:root {
    --tblr-bg: #f6f8fb;
    --tblr-surface: #ffffff;
    --tblr-sidebar: #182433;
    --tblr-sidebar-hover: #213043;
    --tblr-primary: #206bc4;
    --tblr-primary-soft: #ebf3fe;
    --tblr-border: #e2e8f0;
    --tblr-border-strong: #cbd5e1;
    --tblr-text: #1e293b;
    --tblr-muted: #64748b;
}

/* ─── Body & Main Layout Canvas ────────────────────────────────────────── */
.fi-body,
.fi-layout,
.fi-main-ctn,
.fi-main {
    background: var(--tblr-bg) !important;
    color: var(--tblr-text) !important;
}

/* ─── Topbar (Tabler Crisp White + Top Royal Blue Accent Line) ─────────── */
.fi-topbar-ctn {
    border-bottom: 1px solid var(--tblr-border) !important;
}

.fi-topbar {
    background: #ffffff !important;
    border-top: 3px solid var(--tblr-primary) !important;
    border-bottom: 1px solid var(--tblr-border) !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
}

.fi-topbar svg {
    width: 1.25rem !important;
    height: 1.25rem !important;
    flex-shrink: 0 !important;
    color: #475569 !important;
}

/* ─── Sidebar (Tabler Signature Dark Slate #182433 — Tidak Monoton) ────── */
.fi-sidebar {
    background: var(--tblr-sidebar) !important;
    border-right: 1px solid #0f172a !important;
    box-shadow: none !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}

.fi-sidebar svg {
    max-width: 1.35rem !important;
    max-height: 1.35rem !important;
    flex-shrink: 0 !important;
}

.fi-sidebar-header-ctn {
    background: #131d2a !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07) !important;
}

.fi-sidebar-header .fi-logo,
.fi-sidebar-header span,
.fi-sidebar-header a {
    color: #f8fafc !important;
    font-weight: 700 !important;
    letter-spacing: -0.01em !important;
}

/* ─── Sidebar Nav Groups & Items ───────────────────────────────────────── */
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

.fi-sidebar-group-label {
    font-size: 0.68rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.07em !important;
    text-transform: uppercase !important;
    color: #94a3b8 !important;
}

.fi-sidebar-item-btn {
    border-radius: 0.5rem !important;
    transition: background 0.15s ease, color 0.15s ease !important;
    border-left: 3px solid transparent;
}

.fi-sidebar-item-label {
    font-size: 0.8125rem !important;
    font-weight: 500 !important;
    color: #cbd5e1 !important;
}

.fi-sidebar-item-icon,
.fi-sidebar-item-icon svg {
    width: 1.15rem !important;
    height: 1.15rem !important;
    max-width: 1.15rem !important;
    max-height: 1.15rem !important;
    color: #94a3b8 !important;
    flex-shrink: 0 !important;
}

.fi-sidebar-item-btn:hover:not(.fi-active) {
    background: var(--tblr-sidebar-hover) !important;
    border-left-color: rgba(59, 130, 246, 0.5);
}

.fi-sidebar-item-btn:hover:not(.fi-active) .fi-sidebar-item-label,
.fi-sidebar-item-btn:hover:not(.fi-active) .fi-sidebar-item-icon svg {
    color: #ffffff !important;
}

.fi-sidebar-item-btn.fi-active,
.fi-sidebar-item-btn[aria-current] {
    background: var(--tblr-primary) !important;
    border-left-color: #60a5fa !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) !important;
}

.fi-sidebar-item-btn.fi-active .fi-sidebar-item-label,
.fi-sidebar-item-btn[aria-current] .fi-sidebar-item-label,
.fi-sidebar-item-btn.fi-active .fi-sidebar-item-icon svg,
.fi-sidebar-item-btn[aria-current] .fi-sidebar-item-icon svg {
    color: #ffffff !important;
    font-weight: 600 !important;
}

/* ─── Page Header ──────────────────────────────────────────────────────── */
.fi-header {
    flex-wrap: wrap !important;
    gap: 1rem !important;
    align-items: center !important;
}

.fi-header-heading {
    font-weight: 700 !important;
    color: #0f172a !important;
    letter-spacing: -0.02em !important;
}

.fi-header-subheading {
    color: var(--tblr-muted) !important;
    font-weight: 500 !important;
    font-size: 0.85rem !important;
}

/* ─── Tabler Cards, Stats Overview & Widgets (Clean + Engaging Top Accent) ─ */
.fi-wi-stats-overview-stat-ctn,
.fi-wi-chart,
.fi-wi-account-widget,
.fi-wi-filament-info-widget,
.fi-section {
    background: #ffffff !important;
    border: 1px solid var(--tblr-border) !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease !important;
}

/* Berikan aksen garis atas berwarna pada kartu statistik agar menarik & tidak monoton */
.fi-wi-stats-overview-stat-ctn {
    border-top: 3px solid var(--tblr-primary) !important;
}

.fi-wi-stats-overview-stat-ctn:nth-child(2n) {
    border-top-color: #0ca678 !important; /* Tabler Emerald */
}

.fi-wi-stats-overview-stat-ctn:nth-child(3n) {
    border-top-color: #f59f00 !important; /* Tabler Amber */
}

.fi-wi-stats-overview-stat-ctn:nth-child(4n) {
    border-top-color: #4263eb !important; /* Tabler Indigo */
}

.fi-wi-stats-overview-stat-ctn:hover {
    transform: translateY(-2px) !important;
    border-color: var(--tblr-border-strong) !important;
    box-shadow: 0 6px 16px -4px rgba(15, 23, 42, 0.08) !important;
}

.fi-section-header {
    border-bottom: 1px solid #f1f5f9 !important;
    background: #fcfdfe !important;
    border-top-left-radius: 0.75rem !important;
    border-top-right-radius: 0.75rem !important;
}

/* ─── Icon Sizing Safeguards ───────────────────────────────────────────── */
.fi-wi-stats-overview-stat svg,
.fi-wi-stats-overview-stat-icon svg {
    width: 1.35rem !important;
    height: 1.35rem !important;
    max-width: 1.35rem !important;
    max-height: 1.35rem !important;
    flex-shrink: 0 !important;
}

.fi-wi-stats-overview-stat-description svg,
.fi-wi-stats-overview-stat-description-icon svg,
.fi-wi-stats-overview-stat-description-icon {
    width: 1rem !important;
    height: 1rem !important;
    max-width: 1rem !important;
    max-height: 1rem !important;
    flex-shrink: 0 !important;
}

.fi-btn svg:not(.fi-loading-indicator),
.fi-icon-btn svg:not(.fi-loading-indicator) {
    width: 1.15rem !important;
    height: 1.15rem !important;
    max-width: 1.35rem !important;
    max-height: 1.35rem !important;
    flex-shrink: 0 !important;
}

.fi-loading-indicator:not([wire\:loading]),
[wire\:loading]:not([wire\:loading\.flex]):not([wire\:loading\.block]):not([wire\:loading\.inline-block]):not([wire\:loading\.inline]) {
    display: none !important;
}

/* ─── Tables (Tabler Clean Data Table) ─────────────────────────────────── */
.fi-ta-ctn {
    background: #ffffff !important;
    border: 1px solid var(--tblr-border) !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    overflow: visible !important;
}

.fi-ta-header-ctn {
    background: #f8fafc !important;
    border-bottom: 1px solid var(--tblr-border) !important;
    border-top-left-radius: 0.75rem !important;
    border-top-right-radius: 0.75rem !important;
}

.fi-ta-content-ctn,
.fi-ta-content {
    border-bottom-left-radius: 0.75rem !important;
    border-bottom-right-radius: 0.75rem !important;
    overflow-x: auto !important;
}

.fi-ta-table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
}

.fi-ta-table thead tr {
    background: #f8fafc !important;
    border-bottom: 2px solid var(--tblr-border) !important;
}

.fi-ta-table th {
    font-size: 0.75rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    color: #1e293b !important;
    font-weight: 700 !important;
    padding: 0.75rem 1rem !important;
}

.fi-ta-table tbody tr {
    border-bottom: 1px solid #f1f5f9 !important;
    transition: background-color 0.12s ease !important;
}

.fi-ta-table tbody tr:hover {
    background-color: #f8fafc !important;
}

/* Sticky Action Column across ALL Filament tables */
.fi-ta-table th.fi-ta-actions-header-cell {
    position: sticky !important;
    right: 0 !important;
    z-index: 15 !important;
    background: #f8fafc !important;
    border-left: 1px solid #e2e8f0 !important;
    box-shadow: -4px 0 10px rgba(15, 23, 42, 0.06) !important;
    min-width: 200px !important;
    text-align: center !important;
    white-space: nowrap !important;
    padding: 0.6rem 0.75rem !important;
}

.fi-ta-table td.fi-ta-actions-cell,
.fi-ta-table td.fi-ta-cell:has(.fi-ta-actions) {
    position: sticky !important;
    right: 0 !important;
    z-index: 10 !important;
    background: #ffffff !important;
    border-left: 1px solid #e2e8f0 !important;
    box-shadow: -4px 0 10px rgba(15, 23, 42, 0.06) !important;
    min-width: 200px !important;
    text-align: center !important;
    white-space: nowrap !important;
    padding: 0.5rem 0.75rem !important;
}

.fi-ta-table tr:hover td.fi-ta-actions-cell,
.fi-ta-table tr:hover td.fi-ta-cell:has(.fi-ta-actions) {
    background: #f8fafc !important;
}

/* Checkbox Selection Hit Area & High-Visibility Styling across ALL tables */
.fi-ta-table th.fi-ta-selection-cell,
.fi-ta-table td.fi-ta-selection-cell,
.fi-ta-table th:has(input[type="checkbox"]),
.fi-ta-table td:has(input[type="checkbox"]) {
    width: 3.5rem !important;
    min-width: 3.5rem !important;
    max-width: 3.5rem !important;
    text-align: center !important;
    padding: 0 !important;
    vertical-align: middle !important;
    position: relative !important;
    z-index: 2 !important;
}

.fi-ta-table td.fi-ta-selection-cell label,
.fi-ta-table th.fi-ta-selection-cell label,
.fi-ta-table .fi-ta-record-checkbox-ctn,
.fi-ta-table .fi-ta-select-all-checkbox-ctn {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    height: 100% !important;
    min-height: 3rem !important;
    padding: 0.6rem !important;
    cursor: pointer !important;
    margin: 0 !important;
}

.fi-ta-table input[type="checkbox"],
.fi-ta-record-checkbox,
.fi-ta-select-all-checkbox {
    width: 1.3rem !important;
    height: 1.3rem !important;
    min-width: 1.3rem !important;
    min-height: 1.3rem !important;
    border-radius: 0.375rem !important;
    border: 2px solid #64748b !important;
    background-color: #ffffff !important;
    cursor: pointer !important;
    accent-color: #206bc4 !important;
    transition: all 0.15s ease !important;
    margin: 0 !important;
    display: inline-block !important;
    vertical-align: middle !important;
}

.fi-ta-table input[type="checkbox"]:hover {
    border-color: #206bc4 !important;
    box-shadow: 0 0 0 3px rgba(32, 107, 196, 0.25) !important;
    transform: scale(1.08) !important;
}

.fi-ta-table input[type="checkbox"]:checked {
    background-color: #206bc4 !important;
    border-color: #206bc4 !important;
}

/* Table Typography & High Contrast Readability across ALL Filament tables */
.fi-ta-table td {
    color: #1e293b !important;
    font-size: 0.8125rem !important;
    line-height: 1.5 !important;
    padding: 0.75rem 1rem !important;
}

.fi-ta-table td .fi-ta-text-item-label {
    color: #0f172a !important;
    font-weight: 600 !important;
}

.fi-ta-table td .fi-ta-text-item-description {
    color: #475569 !important;
    font-size: 0.75rem !important;
    line-height: 1.35 !important;
    margin-top: 0.15rem !important;
}

.fi-ta-table .fi-badge {
    font-weight: 600 !important;
    font-size: 0.72rem !important;
    padding: 0.2rem 0.55rem !important;
    border-radius: 0.375rem !important;
    letter-spacing: 0.01em !important;
}

/* Table Row Action Buttons (Flex layout side-by-side with crisp borders, zero overlap) */
.fi-ta-table td .fi-ta-actions {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.5rem !important;
    white-space: nowrap !important;
    flex-wrap: nowrap !important;
    width: auto !important;
}

.fi-ta-table td .fi-ta-actions > * {
    position: static !important;
    flex-shrink: 0 !important;
    margin: 0 !important;
}

.fi-ta-table td .fi-ta-actions .fi-btn {
    white-space: nowrap !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.35rem !important;
    padding: 0.25rem 0.65rem !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    border-radius: 0.5rem !important;
    line-height: 1.25rem !important;
    border-width: 1px !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
}

.fi-ta-table td .fi-ta-actions .fi-icon-btn,
.fi-ta-table td .fi-ta-actions .fi-ta-action-group .fi-icon-btn {
    border-radius: 0.5rem !important;
    background: #ffffff !important;
    border: 1px solid var(--tblr-border) !important;
    width: 2rem !important;
    height: 2rem !important;
    min-width: 2rem !important;
    min-height: 2rem !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #475569 !important;
    transition: all 0.15s ease !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
}

.fi-ta-table td .fi-ta-actions .fi-icon-btn:hover,
.fi-ta-table td .fi-ta-actions .fi-ta-action-group .fi-icon-btn:hover {
    background: var(--tblr-primary-soft) !important;
    border-color: #93c5fd !important;
    color: var(--tblr-primary) !important;
}

body.sims-users-actions-grid .fi-ta-table td .fi-ta-actions {
    display: inline-flex !important;
    flex-wrap: wrap !important;
    max-width: 6.8rem !important;
    gap: 0.35rem !important;
    justify-content: center !important;
}

/* Bulk Actions & Header Toolbar Buttons (Prevent dual mobile+desktop trigger overlap) */
@media (min-width: 40rem) {
    .fi-dropdown-trigger > .fi-icon-btn:has(+ .fi-btn.fi-labeled-from-sm) {
        display: none !important;
    }
}

@media (max-width: 39.99rem) {
    .fi-dropdown-trigger > .fi-btn.fi-labeled-from-sm {
        display: none !important;
    }
}

.fi-ta-header-toolbar .fi-ac-btn-group.fi-btn {
    background: #ffffff !important;
    border: 1px solid var(--tblr-border-strong) !important;
    color: #1e293b !important;
    border-radius: 0.5rem !important;
    font-size: 0.8125rem !important;
    font-weight: 600 !important;
    padding: 0.4rem 0.75rem !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
}

.fi-ta-header-toolbar .fi-ac-btn-group.fi-btn:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
}

/* ─── Sidebar Footer / User Card ───────────────────────────────────────── */
.sims-sidebar-footer-container {
    padding: 0.75rem !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: #131d2a !important;
}

.sims-sidebar-user-card {
    display: flex !important;
    flex-direction: column !important;
    gap: 0.6rem !important;
    padding: 0.7rem !important;
    border-radius: 0.625rem !important;
    background: #1e2c3f !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.sims-user-profile-header {
    display: flex !important;
    align-items: center !important;
    gap: 0.6rem !important;
}

.sims-user-avatar {
    width: 2.1rem !important;
    height: 2.1rem !important;
    border-radius: 0.5rem !important;
    background: var(--tblr-primary) !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    font-size: 0.85rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.sims-user-details {
    flex: 1 1 0% !important;
    min-width: 0 !important;
}

.sims-user-name {
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    color: #f8fafc !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    margin: 0 !important;
}

.sims-role-badge {
    display: inline-flex !important;
    padding: 0.1rem 0.4rem !important;
    font-size: 0.65rem !important;
    font-weight: 600 !important;
    border-radius: 0.35rem !important;
    background: rgba(59, 130, 246, 0.2) !important;
    color: #93c5fd !important;
    margin-top: 0.15rem !important;
}

.sims-user-actions-bar {
    display: flex !important;
    gap: 0.45rem !important;
}

.sims-profile-btn,
.sims-logout-btn {
    flex: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.3rem !important;
    padding: 0.35rem 0.5rem !important;
    border-radius: 0.45rem !important;
    font-size: 0.7rem !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    cursor: pointer !important;
}

.sims-profile-btn {
    color: #e2e8f0 !important;
    background: rgba(255, 255, 255, 0.08) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
}

.sims-profile-btn:hover {
    background: var(--tblr-primary) !important;
    color: #ffffff !important;
}

.sims-logout-btn {
    color: #fca5a5 !important;
    background: rgba(239, 68, 68, 0.14) !important;
    border: 1px solid rgba(239, 68, 68, 0.25) !important;
}

.sims-logout-btn:hover {
    background: #dc2626 !important;
    color: #ffffff !important;
}

.sims-profile-btn svg,
.sims-logout-btn svg {
    width: 0.85rem !important;
    height: 0.85rem !important;
}

.sims-logout-form {
    flex: 1 !important;
    margin: 0 !important;
    display: flex !important;
}

.sims-logout-form button {
    width: 100% !important;
}

/* ─── Dashboard Hero Banner (Tabler Clean Card + Top Royal Accent) ──────── */
.sims-dashboard-hero {
    position: relative !important;
    overflow: hidden !important;
    border-radius: 0.75rem !important;
    padding: 1.25rem 1.5rem !important;
    margin-bottom: 1.25rem !important;
    background: #ffffff !important;
    border: 1px solid var(--tblr-border) !important;
    border-top: 3px solid var(--tblr-primary) !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    color: var(--tblr-text) !important;
}

.sims-hero-glow-1,
.sims-hero-glow-2 {
    display: none !important;
}

.sims-hero-content {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 1rem !important;
}

.sims-hero-text-block {
    display: flex !important;
    flex-direction: column !important;
    gap: 0.25rem !important;
}

.sims-hero-status-tag {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.45rem !important;
    padding: 0.2rem 0.65rem !important;
    border-radius: 9999px !important;
    background: var(--tblr-primary-soft) !important;
    border: 1px solid #bfdbfe !important;
    font-size: 0.7rem !important;
    font-weight: 700 !important;
    color: var(--tblr-primary) !important;
    width: fit-content !important;
}

.sims-hero-dot {
    width: 0.45rem !important;
    height: 0.45rem !important;
    border-radius: 9999px !important;
    background: #10b981 !important;
}

.sims-hero-title {
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    letter-spacing: -0.02em !important;
    margin: 0.15rem 0 0 0 !important;
}

.sims-hero-subtitle {
    font-size: 0.825rem !important;
    color: var(--tblr-muted) !important;
    margin: 0 !important;
}

.sims-hero-subtitle strong {
    color: var(--tblr-primary) !important;
    font-weight: 600 !important;
}

.sims-hero-date-badge {
    display: flex !important;
    align-items: center !important;
    gap: 0.45rem !important;
    padding: 0.5rem 0.85rem !important;
    border-radius: 0.5rem !important;
    background: #f8fafc !important;
    border: 1px solid var(--tblr-border) !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    color: #334155 !important;
}

.sims-hero-date-badge svg {
    width: 1rem !important;
    height: 1rem !important;
    color: var(--tblr-primary) !important;
}

/* ─── Override Custom Pages (.ad-*, .ar-*, .sc-*, .imp-*) into Tabler Light ─ */
.fi-body .ad-filter-bar,
.fi-body .ar-filter-bar,
.fi-body .sc-toolbar,
.fi-body .ad-stat,
.fi-body .ar-stat-card,
.fi-body .sc-stat-card,
.fi-body .sc-action-card,
.fi-body .imp-card,
.fi-body .imp-table-container,
.fi-body .ad-table-wrap,
.fi-body .ar-table-wrap,
.fi-body .sc-table-card {
    background: #ffffff !important;
    border: 1px solid var(--tblr-border) !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    color: var(--tblr-text) !important;
}

.fi-body .ad-stat,
.fi-body .ar-stat-card,
.fi-body .sc-stat-card {
    border-top: 3px solid var(--tblr-primary) !important;
}

.fi-body .sc-notice-box {
    background: #fffbeb !important;
    border: 1px solid #fde68a !important;
    border-left: 4px solid #f59f00 !important;
    color: #92400e !important;
    box-shadow: none !important;
}

.fi-body .ad-filter-label,
.fi-body .ar-filter-label,
.fi-body .sc-label,
.fi-body .ad-stat-label,
.fi-body .ar-stat-label,
.fi-body .sc-stat-label,
.fi-body .sc-action-desc,
.fi-body .ar-legend {
    color: var(--tblr-muted) !important;
}

.fi-body .ad-select,
.fi-body .ad-input,
.fi-body .ar-select,
.fi-body .sc-input,
.fi-body .sc-select,
.fi-body .imp-input,
.fi-body .imp-select-trigger {
    background: #f8fafc !important;
    border: 1px solid var(--tblr-border) !important;
    color: #0f172a !important;
}

.fi-body .imp-dropdown-menu {
    background: #ffffff !important;
    border: 1px solid var(--tblr-border) !important;
    color: #0f172a !important;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12) !important;
}

.fi-body .ad-stat-value,
.fi-body .ar-stat-value,
.fi-body .sc-stat-val,
.fi-body .sc-action-title,
.fi-body .imp-card-title {
    color: #0f172a !important;
}

.fi-body .ar-stat-value.amber { color: #d97706 !important; }
.fi-body .ar-stat-value.green { color: #059669 !important; }
.fi-body .ar-stat-value.yellow { color: #ca8a04 !important; }
.fi-body .ar-stat-value.red { color: #dc2626 !important; }

.fi-body .imp-table-header,
.fi-body .imp-pagination {
    background: #f8fafc !important;
    border-bottom: 1px solid var(--tblr-border) !important;
    color: #334155 !important;
}

.fi-body .ad-table th,
.fi-body .ar-table th,
.fi-body .sc-table th,
.fi-body .imp-table th {
    background: #f8fafc !important;
    color: #475569 !important;
    border-bottom: 1px solid var(--tblr-border) !important;
}

.fi-body .ad-table td,
.fi-body .ar-table td,
.fi-body .sc-table td,
.fi-body .imp-table td {
    color: #334155 !important;
    border-bottom: 1px solid #f1f5f9 !important;
}

.fi-body .ad-table td.name,
.fi-body .ar-table td.name {
    color: #0f172a !important;
}

.fi-body .ad-table td.nis,
.fi-body .ar-table td.nis,
.fi-body .ar-table td.kelas {
    color: #64748b !important;
}

.fi-body .ad-table tbody tr:hover,
.fi-body .ar-table tbody tr:hover,
.fi-body .sc-table tr:hover td,
.fi-body .imp-table tr:hover td {
    background: #f8fafc !important;
}
</style>

<script>
(function () {
    function applySimsUsersActionsGrid() {
        if (!document.body) return;
        var p = window.location.pathname;
        if (p.indexOf('/admin/users') !== -1) {
            document.body.classList.add('sims-users-actions-grid');
        } else {
            document.body.classList.remove('sims-users-actions-grid');
        }
    }

    // Make entire selection checkbox cell reliably clickable across all Filament tables
    function handleCheckboxCellClick(e) {
        var cell = e.target.closest('.fi-ta-selection-cell, td:has(input[type="checkbox"]), th:has(input[type="checkbox"])');
        if (cell) {
            var checkbox = cell.querySelector('input[type="checkbox"]');
            if (checkbox && e.target !== checkbox) {
                e.preventDefault();
                e.stopPropagation();
                checkbox.click();
            }
        }
    }

    document.addEventListener('click', handleCheckboxCellClick, true);
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
