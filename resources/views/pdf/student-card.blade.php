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
    font-size: 0;
}

.card {
    position: relative;
    width: 85.6mm;
    height: 54mm;
    overflow: hidden;
    page-break-after: always;
}

/* ── DEPAN ──────────────────────────────────────────────────────── */
.card-front { background: #f9f8f5; }

/* Header biru */
.front-header {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 14mm;
    background: #1565c0;
}
.header-logo {
    position: absolute;
    top: 1mm; left: 2mm;
    width: 12mm; height: 12mm;
    border-radius: 50%;
    background: white;
    overflow: visible;
    box-shadow: 0 3px 10px rgba(0,0,0,.5);
}
.header-logo img { width: 130%; height: 130%; object-fit: contain;
    position: relative; left: -15%; top: -15%; }

.header-text {
    position: absolute;
    top: 3mm; left: 15.5mm; right: 2mm;
}
.hdr-name {
    font-size: 9.5pt;
    font-weight: bold;
    color: white;
    letter-spacing: .04em;
    line-height: 1.1;
    text-transform: uppercase;
}
.hdr-addr {
    font-size: 3.8pt;
    color: rgba(255,255,255,.78);
    line-height: 1.2;
    margin-top: 1mm;
}

/* Body */
.front-body {
    position: absolute;
    top: 14mm; left: 0; right: 0;
    bottom: 1.8mm;
    background: #f9f8f5;
}

/* Foto */
.photo-wrap {
    position: absolute;
    left: 2.5mm; top: 5.5mm;
    width: 14mm; height: 18.7mm;
    border: 1.5pt solid #dc2626;
    overflow: hidden;
    background: #e9eaec;
}
.photo-wrap img { width: 100%; height: 100%; }
.photo-placeholder {
    width: 100%; height: 100%;
    display: table;
    background: #e9eaec;
    text-align: center;
}
.photo-placeholder span {
    display: table-cell;
    vertical-align: middle;
    font-size: 14pt;
    color: #b0b5bc;
}

/* Watermark logo di body */
.body-watermark {
    position: absolute;
    right: 3mm; top: 50%;
    width: 26mm; height: 26mm;
    margin-top: -13mm;
    opacity: .05;
}

/* Info kanan */
.info-wrap {
    position: absolute;
    left: 18mm; top: 1.5mm;
    right: 2mm; bottom: 1mm;
}

.kp-title {
    font-size: 7pt;
    font-weight: bold;
    color: #0d47a1;
    text-align: center;
    letter-spacing: .1em;
    text-decoration: underline;
    text-transform: uppercase;
    margin-bottom: 1.5mm;
    line-height: 1;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
}
.info-table td {
    vertical-align: top;
    padding: 0;
    padding-bottom: 1.2mm;
    line-height: 1.2;
}
.td-label {
    font-size: 4.8pt;
    color: #4b5563;
    white-space: nowrap;
    width: 21mm;
}
.td-colon {
    font-size: 4.8pt;
    color: #4b5563;
    width: 2mm;
}
.td-value {
    font-size: 4.8pt;
    font-weight: bold;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
}
.td-value-name {
    font-size: 5.5pt;
    font-weight: bold;
    color: #111827;
    padding-bottom: 1.5mm;
    white-space: nowrap;
    overflow: hidden;
}

/* Disclaimer bawah kiri */
.disclaimer {
    position: absolute;
    left: 2.5mm; bottom: 1mm;
    width: 38mm;
    font-size: 3.5pt;
    color: #9ca3af;
    font-style: italic;
    line-height: 1.35;
}

/* Tanda tangan kanan bawah */
.signature-wrap {
    position: absolute;
    right: 2mm; bottom: 1mm;
    text-align: center;
    width: 30mm;
}
.sig-role {
    font-size: 4.2pt;
    color: #374151;
    line-height: 1;
    margin-bottom: 4mm;
}
.sig-line {
    border-top: 0.5pt solid #374151;
    padding-top: 0.5mm;
}
.sig-name {
    font-size: 4.2pt;
    font-weight: bold;
    color: #111827;
    line-height: 1.2;
}
.sig-nip {
    font-size: 3.5pt;
    color: #6b7280;
    line-height: 1.2;
}

/* Strip bawah biru */
.front-strip {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 1.8mm;
    background: #1565c0;
}


/* ── BELAKANG ───────────────────────────────────────────────────── */
.card-back {
    background: #0f172a;
    page-break-after: auto;
}

/* Header belakang */
.back-header {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 11mm;
    background: rgba(0,0,0,.25);
    border-bottom: 0.3pt solid rgba(255,255,255,.1);
}
.back-logo {
    position: absolute;
    top: 1.5mm; left: 2.5mm;
    width: 7.5mm; height: 7.5mm;
    border-radius: 50%;
    background: white;
    overflow: hidden;
}
.back-logo img { width: 100%; height: 100%; }
.back-school {
    position: absolute;
    top: 2mm; left: 11.5mm;
}
.back-school-name {
    font-size: 6pt;
    font-weight: bold;
    color: white;
    line-height: 1.2;
}
.back-school-sub {
    font-size: 3.8pt;
    color: rgba(148,163,184,.7);
    margin-top: .5mm;
}

/* Body belakang: QR */
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
    padding: 1.8mm;
    border-radius: 2mm;
    border: 1pt solid rgba(253,224,71,.35);
    margin-top: 0.5mm;
}
.qr-box img {
    display: block;
    width: 22mm; height: 22mm;
}
.qr-caption {
    font-size: 3.8pt;
    color: rgba(148,163,184,.65);
    letter-spacing: .03em;
    margin-top: 1.5mm;
}

/* Footer belakang */
.back-footer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 10mm;
    background: rgba(0,0,0,.3);
    border-top: 0.3pt solid rgba(255,255,255,.1);
    padding: 2mm 3mm;
}
.back-name {
    font-size: 7pt; font-weight: bold; color: white;
    line-height: 1.2; margin-bottom: .8mm;
    white-space: nowrap; overflow: hidden;
}
.back-nis {
    font-size: 3.8pt;
    color: rgba(148,163,184,.6);
}
.back-badge {
    position: absolute;
    right: 2.5mm; bottom: 2.2mm;
    background: #d97706; color: white;
    font-size: 4.5pt; font-weight: bold;
    padding: .7mm 2.2mm;
    border-radius: 2.5mm;
    letter-spacing: .06em;
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 1 — DEPAN KARTU                    --}}
{{-- ═══════════════════════════════════════════ --}}
@php
    $principalName = '';
    $principalNip  = '';
@endphp
<div class="card card-front">

    {{-- Header biru --}}
    <div class="front-header">
        @if($logoBase64)
        <div class="header-logo">
            <img src="{{ $logoBase64 }}" alt="Logo">
        </div>
        @endif
        <div class="header-text" style="{{ $logoBase64 ? '' : 'left:2.5mm;' }}">
            <div class="hdr-name">SMA Negeri 1 Gianyar</div>
            <div class="hdr-addr">Jl. Ratna No.1, Gianyar, Bali · Telp. (0361) 943443 · NPSN 50102079</div>
        </div>
    </div>

    {{-- Body --}}
    <div class="front-body">

        {{-- Watermark logo --}}
        @if($logoBase64)
        <div class="body-watermark">
            <img src="{{ $logoBase64 }}" alt="" style="width:100%;height:100%;object-fit:contain;">
        </div>
        @endif

        {{-- Foto siswa --}}
        <div class="photo-wrap">
            @if($photoBase64)
                <img src="{{ $photoBase64 }}" alt="{{ $siswa->name }}">
            @else
                <div class="photo-placeholder">
                    <span style="color:#b0b5bc;">{{ $siswa->initials }}</span>
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="info-wrap">
            <div class="kp-title">KARTU PELAJAR</div>

            <table class="info-table">
                <tr>
                    <td class="td-label">NISN</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $siswa->nisn ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Nama</td>
                    <td class="td-colon">:</td>
                    <td class="td-value-name">{{ $siswa->name }}</td>
                </tr>
                <tr>
                    <td class="td-label">NIS</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $siswa->nis ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Kelas</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $siswa->schoolClass?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Tgl. Lahir</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $siswa->birth_date?->isoFormat('D MMMM Y') ?? '—' }}</td>
                </tr>
                @if($siswa->gender)
                <tr>
                    <td class="td-label">Jenis Kelamin</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $siswa->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Disclaimer bawah kiri --}}
        <div class="disclaimer">
            Kartu ini berlaku selama menjadi siswa SMA Negeri 1 Gianyar
        </div>

        {{-- Tanda tangan kanan bawah --}}
        <div class="signature-wrap">
            <div class="sig-role">Kepala Sekolah,</div>
            <div class="sig-line">
                @if($principalName)
                <div class="sig-name">{{ $principalName }}</div>
                @else
                <div class="sig-name" style="color:#c0c0c0;letter-spacing:.05em;">...................</div>
                @endif
                @if($principalNip)
                <div class="sig-nip">NIP. {{ $principalNip }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Strip bawah --}}
    <div class="front-strip"></div>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 2 — BELAKANG KARTU                 --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="card card-back">

    {{-- Header --}}
    <div class="back-header">
        @if($logoBase64)
        <div class="back-logo">
            <img src="{{ $logoBase64 }}" alt="Logo">
        </div>
        @endif
        <div class="back-school">
            <div class="back-school-name">SMA NEGERI 1 GIANYAR</div>
            <div class="back-school-sub">NPSN 50102079 · Gianyar, Bali</div>
        </div>
    </div>

    {{-- QR --}}
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
