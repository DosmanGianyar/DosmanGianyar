<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

@page {
    size: 85.6mm 54mm;
    margin: 0mm;
}

html, body {
    width: 85.6mm;
    height: 54mm;
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 0; /* reset agar tidak ada whitespace */
}

/* ── Kontainer kartu, satu kartu = satu halaman ─────────────── */
.card {
    position: relative;
    width: 85.6mm;
    height: 54mm;
    overflow: hidden;
    page-break-after: always;
}

/* ── DEPAN: background gradient biru ─────────────────────────── */
.card-front {
    background: #1d4ed8;
}

/* Lingkaran dekorasi */
.deco1 {
    position: absolute;
    top: -15mm; right: -8mm;
    width: 40mm; height: 55mm;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.deco2 {
    position: absolute;
    bottom: -12mm; left: 18mm;
    width: 30mm; height: 40mm;
    background: rgba(99,102,241,.12);
    border-radius: 50%;
}

/* ── Header depan ─────────────────────────────────────────────── */
.front-header {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 13.5mm;
    background: rgba(0,0,0,.18);
    border-bottom: 0.3pt solid rgba(255,255,255,.2);
    padding: 2mm 3mm;
}

.header-logo {
    position: absolute;
    top: 2mm; left: 3mm;
    width: 9mm; height: 9mm;
    border-radius: 50%;
    background: white;
    border: 0.8pt solid rgba(212,175,55,.8);
    overflow: hidden;
}
.header-logo img { width: 100%; height: 100%; }

.header-school {
    position: absolute;
    top: 2.5mm; left: 13.5mm;
}
.school-name {
    font-size: 7pt;
    font-weight: bold;
    color: white;
    letter-spacing: .02em;
    line-height: 1.2;
}
.school-sub {
    font-size: 4.5pt;
    color: rgba(186,230,253,.85);
    margin-top: .5mm;
    line-height: 1.2;
}

.header-badge {
    position: absolute;
    top: 2.5mm; right: 3mm;
    text-align: right;
}
.badge-kp {
    font-size: 5.5pt;
    font-weight: bold;
    color: #fde047;
    letter-spacing: .08em;
    text-transform: uppercase;
    line-height: 1.2;
}
.badge-ta {
    font-size: 4pt;
    color: rgba(255,255,255,.55);
    line-height: 1.2;
    margin-top: .5mm;
}

/* ── Body depan ───────────────────────────────────────────────── */
.front-body {
    position: absolute;
    top: 13.5mm; left: 0; right: 0;
    bottom: 10mm;
    padding: 2mm 3mm;
    background: #f3f4f6;
}

.photo-wrap {
    position: absolute;
    left: 3mm; top: 2mm;
    width: 16mm;
}
.photo-box {
    width: 15.5mm;
    height: 20.7mm; /* 3:4 ratio */
    border: 1.2pt solid #1d4ed8;
    border-radius: 1.5mm;
    overflow: hidden;
    background: #dbeafe;
}
.photo-box img { width: 100%; height: 100%; }
.photo-label {
    text-align: center;
    font-size: 3.5pt;
    color: #9ca3af;
    margin-top: 1mm;
}

.info-wrap {
    position: absolute;
    left: 21mm; top: 1mm;
    right: 3mm; bottom: 0;
    border-left: 0.5pt solid #d1d5db;
    padding-left: 2.5mm;
}
.info-label {
    font-size: 4pt;
    color: #6b7280;
    letter-spacing: .08em;
    text-transform: uppercase;
    line-height: 1;
    margin-bottom: .7mm;
}
.info-name {
    font-size: 8.5pt;
    font-weight: bold;
    color: #111827;
    line-height: 1.15;
    margin-bottom: 2.5mm;
    white-space: nowrap;
    overflow: hidden;
}
.info-grid {
    width: 100%;
    border-collapse: collapse;
}
.info-grid td {
    vertical-align: top;
    padding: 0;
    padding-bottom: 2mm;
    width: 50%;
}
.cell-label {
    font-size: 3.8pt;
    color: #9ca3af;
    letter-spacing: .06em;
    text-transform: uppercase;
    line-height: 1;
    margin-bottom: .8mm;
}
.cell-value {
    font-size: 6pt;
    font-weight: bold;
    color: #1d4ed8;
    line-height: 1.2;
}
.cell-value-sm {
    font-size: 5.5pt;
    font-weight: 600;
    color: #374151;
    line-height: 1.2;
}

/* ── Footer depan ─────────────────────────────────────────────── */
.front-footer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 10mm;
    background: rgba(0,0,0,.22);
    border-top: 0.3pt solid rgba(255,255,255,.15);
    padding: 2mm 3mm;
}
.footer-school {
    font-size: 4pt;
    color: rgba(186,230,253,.55);
    margin-bottom: 1.5mm;
}
.footer-badge {
    display: inline-block;
    background: #d97706;
    color: white;
    font-size: 4.5pt;
    font-weight: bold;
    padding: .8mm 2.5mm;
    border-radius: 3mm;
    letter-spacing: .06em;
}
.footer-right {
    position: absolute;
    right: 3mm; bottom: 2.5mm;
    font-size: 3.5pt;
    color: rgba(255,255,255,.3);
    text-align: right;
}

/* ─────────────────────────────────────────────────────────────── */
/* ── BELAKANG ────────────────────────────────────────────────── */
/* ─────────────────────────────────────────────────────────────── */
.card-back {
    background: #0f172a;
    page-break-after: auto;
}
.deco-back1 {
    position: absolute;
    top: -10mm; left: -5mm;
    width: 35mm; height: 35mm;
    background: rgba(59,130,246,.07);
    border-radius: 50%;
}
.deco-back2 {
    position: absolute;
    bottom: -10mm; right: -5mm;
    width: 30mm; height: 30mm;
    background: rgba(99,102,241,.09);
    border-radius: 50%;
}

/* ── Header belakang ──────────────────────────────────────────── */
.back-header {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 11mm;
    background: rgba(0,0,0,.25);
    border-bottom: 0.3pt solid rgba(255,255,255,.1);
    padding: 1.8mm 3mm;
}
.back-logo {
    position: absolute;
    top: 1.5mm; left: 3mm;
    width: 7.5mm; height: 7.5mm;
    border-radius: 50%;
    background: white;
    overflow: hidden;
}
.back-logo img { width: 100%; height: 100%; }
.back-school-wrap {
    position: absolute;
    top: 2mm; left: 12mm;
}
.back-school-name {
    font-size: 6pt;
    font-weight: bold;
    color: white;
    line-height: 1.2;
}
.back-school-sub {
    font-size: 4pt;
    color: rgba(148,163,184,.7);
    margin-top: .5mm;
}

/* ── Body belakang: QR besar ──────────────────────────────────── */
.back-body {
    position: absolute;
    top: 11mm; left: 0; right: 0;
    bottom: 10mm;
    text-align: center;
    padding: 1.5mm 0;
}
.qr-box {
    display: inline-block;
    background: white;
    padding: 2mm;
    border-radius: 2mm;
    border: 1pt solid rgba(253,224,71,.4);
    margin-top: 1mm;
}
.qr-box img {
    display: block;
    width: 22mm;
    height: 22mm;
}
.qr-caption {
    font-size: 4pt;
    color: rgba(148,163,184,.65);
    letter-spacing: .04em;
    margin-top: 1.5mm;
}

/* ── Footer belakang ──────────────────────────────────────────── */
.back-footer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 10mm;
    background: rgba(0,0,0,.3);
    border-top: 0.3pt solid rgba(255,255,255,.1);
    padding: 2mm 3mm;
}
.back-name {
    font-size: 7pt;
    font-weight: bold;
    color: white;
    line-height: 1.2;
    margin-bottom: .8mm;
    white-space: nowrap;
    overflow: hidden;
}
.back-nis {
    font-size: 4pt;
    color: rgba(148,163,184,.6);
}
.back-badge {
    position: absolute;
    right: 3mm; bottom: 2.5mm;
    background: #d97706;
    color: white;
    font-size: 4.5pt;
    font-weight: bold;
    padding: .8mm 2.5mm;
    border-radius: 3mm;
    letter-spacing: .06em;
}

/* placeholder foto jika tidak ada foto */
.photo-placeholder {
    width: 100%; height: 100%;
    background: #2563eb;
    display: table;
    text-align: center;
}
.photo-placeholder span {
    display: table-cell;
    vertical-align: middle;
    font-size: 11pt;
    font-weight: bold;
    color: white;
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 1 — DEPAN KARTU                    --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="card card-front">
    <div class="deco1"></div>
    <div class="deco2"></div>

    {{-- Header --}}
    <div class="front-header">
        @if($logoBase64)
        <div class="header-logo">
            <img src="{{ $logoBase64 }}" alt="Logo">
        </div>
        @endif
        <div class="header-school" style="{{ $logoBase64 ? '' : 'left:3mm;' }}">
            <div class="school-name">SMA NEGERI 1 GIANYAR</div>
            <div class="school-sub">Jl. Ratna No.1, Gianyar · NPSN 50102079</div>
        </div>
        <div class="header-badge">
            <div class="badge-kp">KARTU PELAJAR</div>
            <div class="badge-ta">TA 2025/2026</div>
        </div>
    </div>

    {{-- Body --}}
    <div class="front-body">
        {{-- Foto --}}
        <div class="photo-wrap">
            <div class="photo-box">
                @if($photoBase64)
                    <img src="{{ $photoBase64 }}" alt="{{ $siswa->name }}">
                @else
                    <div class="photo-placeholder">
                        <span>{{ $siswa->initials }}</span>
                    </div>
                @endif
            </div>
            <div class="photo-label">3 × 4</div>
        </div>

        {{-- Info --}}
        <div class="info-wrap">
            <div class="info-label">Nama Lengkap</div>
            <div class="info-name">{{ $siswa->name }}</div>

            <table class="info-grid">
                <tr>
                    <td>
                        <div class="cell-label">NIS</div>
                        <div class="cell-value">{{ $siswa->nis ?? '—' }}</div>
                    </td>
                    <td>
                        <div class="cell-label">NISN</div>
                        <div class="cell-value">{{ $siswa->nisn ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="cell-label">Kelas</div>
                        <div class="cell-value">{{ $siswa->schoolClass?->name ?? '—' }}</div>
                    </td>
                    <td>
                        <div class="cell-label">Tgl. Lahir</div>
                        <div class="cell-value-sm">{{ $siswa->birth_date?->isoFormat('D MMM Y') ?? '—' }}</div>
                    </td>
                </tr>
                @if($siswa->gender)
                <tr>
                    <td colspan="2">
                        <div class="cell-label">Jenis Kelamin</div>
                        <div class="cell-value-sm">{{ $siswa->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Footer --}}
    <div class="front-footer">
        <div class="footer-school">SMA Negeri 1 Gianyar · Gianyar, Bali</div>
        <div class="footer-badge">SISWA</div>
        <div class="footer-right">{{ now()->year }}</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 2 — BELAKANG KARTU                 --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="card card-back">
    <div class="deco-back1"></div>
    <div class="deco-back2"></div>

    {{-- Header --}}
    <div class="back-header">
        @if($logoBase64)
        <div class="back-logo">
            <img src="{{ $logoBase64 }}" alt="Logo">
        </div>
        @endif
        <div class="back-school-wrap">
            <div class="back-school-name">SMA NEGERI 1 GIANYAR</div>
            <div class="back-school-sub">NPSN 50102079 · Gianyar, Bali</div>
        </div>
    </div>

    {{-- Body: QR besar --}}
    <div class="back-body">
        <div class="qr-box">
            <img src="{{ $qrPng }}" alt="QR Code Biodata Siswa">
        </div>
        <div class="qr-caption">Scan untuk melihat biodata siswa</div>
    </div>

    {{-- Footer --}}
    <div class="back-footer">
        <div class="back-name">{{ $siswa->name }}</div>
        <div class="back-nis">
            NIS: {{ $siswa->nis ?? '—' }}{{ $siswa->nisn ? ' · NISN: ' . $siswa->nisn : '' }}
        </div>
        <div class="back-badge">SISWA</div>
    </div>
</div>

</body>
</html>
