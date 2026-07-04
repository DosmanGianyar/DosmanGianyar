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

/* ══════════════════════════════════════════════════════════
   DEPAN
══════════════════════════════════════════════════════════ */
.card-front { background: #F8F7F4; }

/* Header biru gradient */
.front-header {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 13.5mm;
    background: linear-gradient(135deg, #0A3880 0%, #1565C0 50%, #1976D2 100%);
    overflow: hidden;
}

/* Logo di header */
.hdr-logo {
    position: absolute;
    top: 1.8mm; left: 2mm;
    width: 10mm; height: 10mm;
    border-radius: 50%;
    background: white;
    padding: 1.5pt;
    overflow: hidden;
}
.hdr-logo img {
    width: 100%; height: 100%;
    object-fit: contain;
}

/* Nama sekolah */
.hdr-text {
    position: absolute;
    top: 2.8mm; left: 13.5mm; right: 18mm;
}
.hdr-school {
    font-size: 7.5pt;
    font-weight: bold;
    color: white;
    letter-spacing: .04em;
    line-height: 1.15;
    text-transform: uppercase;
}
.hdr-addr {
    font-size: 3.5pt;
    color: rgba(191,219,254,.85);
    line-height: 1.3;
    margin-top: .6mm;
}

/* Badge KARTU PELAJAR di kanan */
.hdr-badge {
    position: absolute;
    top: 2.5mm; right: 2mm;
    border: 0.6pt solid rgba(255,255,255,.38);
    border-radius: 2pt;
    background: rgba(255,255,255,.12);
    padding: 1.2mm 2mm;
    font-size: 4.2pt;
    font-weight: bold;
    color: white;
    letter-spacing: .08em;
    line-height: 1.3;
    text-align: center;
    white-space: nowrap;
}

/* Strip emas */
.gold-strip {
    position: absolute;
    top: 13.5mm; left: 0; right: 0;
    height: .9mm;
    background: linear-gradient(to right, #B45309, #F59E0B, #FBBF24, #F59E0B, #B45309);
}

/* Body */
.front-body {
    position: absolute;
    top: 14.4mm; left: 0; right: 0;
    bottom: 4.5mm;
}

/* Foto */
.photo-wrap {
    position: absolute;
    left: 2.2mm; top: 2mm;
    width: 14mm; height: 18.7mm;
    border: 1.5pt solid #1565C0;
    overflow: hidden;
    background: #DCE8F8;
}
.photo-wrap img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
.photo-placeholder {
    width: 100%; height: 100%;
    text-align: center;
    padding-top: 4mm;
}
.photo-placeholder span {
    display: block;
    font-size: 14pt;
    color: #6FA3D8;
}

/* Watermark */
.body-watermark {
    position: absolute;
    right: 2mm; top: 50%;
    width: 20mm; height: 20mm;
    margin-top: -10mm;
    opacity: .04;
}

/* Info kanan */
.info-wrap {
    position: absolute;
    left: 17.5mm; top: 1mm;
    right: 1.5mm; bottom: 0;
}
.kp-title {
    font-size: 5.5pt;
    font-weight: bold;
    color: #0A3880;
    text-align: center;
    letter-spacing: .12em;
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
    padding-bottom: .9mm;
    line-height: 1.25;
}
.td-label {
    font-size: 3.8pt;
    color: #6B7280;
    white-space: nowrap;
    width: 18mm;
}
.td-colon {
    font-size: 3.8pt;
    color: #6B7280;
    width: 1.5mm;
}
.td-value {
    font-size: 3.8pt;
    font-weight: bold;
    color: #111827;
}
.td-value-name {
    font-size: 4.5pt;
    font-weight: bold;
    color: #111827;
    padding-bottom: 1mm;
}

/* Footer */
.front-footer {
    position: absolute;
    bottom: 0; left: 2.2mm; right: 2mm;
    height: 4.5mm;
    display: table;
    width: calc(100% - 4.2mm);
}
.footer-left {
    display: table-cell;
    vertical-align: bottom;
    font-size: 3pt;
    color: #9CA3AF;
    font-style: italic;
    line-height: 1.4;
    padding-bottom: .5mm;
}
.footer-right {
    display: table-cell;
    vertical-align: bottom;
    text-align: center;
    width: 22mm;
    padding-bottom: .5mm;
}
.sig-role {
    font-size: 3.8pt;
    color: #374151;
    line-height: 1;
    margin-bottom: 2.5mm;
}
.sig-line {
    border-top: .5pt solid #374151;
    padding-top: .4mm;
    font-size: 3.8pt;
    font-weight: bold;
    color: #c0c0c0;
    letter-spacing: .04em;
}

/* Strip bawah biru */
.front-strip {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2mm;
    background: linear-gradient(to right, #0A3880, #1565C0, #1976D2);
}


/* ══════════════════════════════════════════════════════════
   BELAKANG — white background, matching Flutter _IdBack
══════════════════════════════════════════════════════════ */
.card-back {
    background: white;
    page-break-after: auto;
}

/* Strip atas biru */
.back-header {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 7mm;
    background: linear-gradient(to right, #0A3880, #1565C0, #1976D2);
    overflow: hidden;
}
.back-logo {
    position: absolute;
    top: 1mm; left: 2.5mm;
    width: 5mm; height: 5mm;
    border-radius: 50%;
    background: white;
    overflow: hidden;
    padding: .6pt;
}
.back-logo img { width: 100%; height: 100%; object-fit: contain; }
.back-school-name {
    position: absolute;
    top: 1.8mm; left: 9mm;
    font-size: 5.5pt;
    font-weight: bold;
    color: white;
    letter-spacing: .04em;
    line-height: 1;
}
.back-npsn {
    position: absolute;
    top: 2mm; right: 2.5mm;
    font-size: 3.8pt;
    color: rgba(255,255,255,.65);
    line-height: 1;
}

/* Body tengah */
.back-body {
    position: absolute;
    top: 7mm; left: 0; right: 0;
    bottom: 5mm;
    text-align: center;
    display: table;
    width: 100%;
}
.back-body-inner {
    display: table-cell;
    vertical-align: middle;
}

/* QR */
.qr-box {
    display: inline-block;
    background: white;
    padding: 1.5mm;
    border-radius: 1.5mm;
    border: .8pt solid #E5E7EB;
}
.qr-box img {
    display: block;
    width: 21mm; height: 21mm;
}

.qr-caption {
    font-size: 3.5pt;
    color: #9CA3AF;
    letter-spacing: .03em;
    margin-top: 1.2mm;
}

.back-divider {
    width: 30mm;
    margin: 1.2mm auto;
    border-top: .5pt solid #F3F4F6;
}

.back-name {
    font-size: 6pt;
    font-weight: bold;
    color: #1F2937;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
}
.back-nis {
    font-size: 3.5pt;
    color: #6B7280;
    margin-top: .5mm;
}
.back-class {
    font-size: 3pt;
    color: #9CA3AF;
    margin-top: .4mm;
}

/* Strip bawah emas dengan tulisan SISWA */
.back-footer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 5mm;
    background: linear-gradient(to right, #B45309, #F59E0B, #FBBF24, #F59E0B, #B45309);
    text-align: center;
    display: table;
    width: 100%;
}
.back-footer-inner {
    display: table-cell;
    vertical-align: middle;
}
.back-siswa {
    font-size: 5.5pt;
    font-weight: bold;
    color: white;
    letter-spacing: .25em;
    text-transform: uppercase;
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 1 — DEPAN KARTU                    --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="card card-front">

    {{-- Header biru --}}
    <div class="front-header">
        @if($logoBase64)
        <div class="hdr-logo">
            <img src="{{ $logoBase64 }}" alt="Logo">
        </div>
        @endif
        <div class="hdr-text" style="{{ $logoBase64 ? '' : 'left:2mm;' }}">
            <div class="hdr-school">SMA Negeri 1 Gianyar</div>
            <div class="hdr-addr">Jl. Ratna No.1, Gianyar, Bali · NPSN 50102079</div>
        </div>
        <div class="hdr-badge">KARTU<br>PELAJAR</div>
    </div>

    {{-- Strip emas --}}
    <div class="gold-strip"></div>

    {{-- Body --}}
    <div class="front-body">

        {{-- Watermark --}}
        @if($logoBase64)
        <div class="body-watermark">
            <img src="{{ $logoBase64 }}" alt="" style="width:100%;height:100%;object-fit:contain;">
        </div>
        @endif

        {{-- Foto --}}
        <div class="photo-wrap">
            @if($photoBase64)
                <img src="{{ $photoBase64 }}" alt="{{ $siswa->name }}">
            @else
                <div class="photo-placeholder">
                    <span>{{ $siswa->initials }}</span>
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="info-wrap">
            <div class="kp-title">KARTU PELAJAR</div>
            <table class="info-table">
                <tr>
                    <td class="td-label">Nama</td>
                    <td class="td-colon">:</td>
                    <td class="td-value-name">{{ $siswa->name }}</td>
                </tr>
                <tr>
                    <td class="td-label">NIS / NISN</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $siswa->nis ?? '—' }} / {{ $siswa->nisn ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Tgl. Lahir</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $siswa->birth_date?->isoFormat('D MMMM Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Kelas</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $siswa->schoolClass?->name ?? '—' }}</td>
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
    </div>

    {{-- Footer --}}
    <div class="front-footer">
        <div class="footer-left">
            Berlaku selama menjadi<br>siswa SMA Negeri 1 Gianyar
        </div>
        <div class="footer-right">
            <div class="sig-role">Kepala Sekolah,</div>
            <div class="sig-line">.............................</div>
        </div>
    </div>

    {{-- Strip bawah biru --}}
    <div class="front-strip"></div>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 2 — BELAKANG KARTU                 --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="card card-back">

    {{-- Strip atas biru --}}
    <div class="back-header">
        @if($logoBase64)
        <div class="back-logo">
            <img src="{{ $logoBase64 }}" alt="Logo">
        </div>
        @endif
        <div class="back-school-name">SMA NEGERI 1 GIANYAR</div>
        <div class="back-npsn">NPSN 50102079</div>
    </div>

    {{-- Body tengah --}}
    <div class="back-body">
        <div class="back-body-inner">
            <div class="qr-box">
                <img src="{{ $qrPng }}" alt="QR Biodata">
            </div>
            <div class="qr-caption">Scan untuk verifikasi identitas siswa</div>
            <div class="back-divider"></div>
            <div class="back-name">{{ $siswa->name }}</div>
            <div class="back-nis">
                NIS: {{ $siswa->nis ?? '—' }}{{ $siswa->nisn ? '  ·  NISN: ' . $siswa->nisn : '' }}
            </div>
            <div class="back-class">
                {{ $siswa->schoolClass?->name ?? '' }}{{ $siswa->schoolClass && $siswa->gender ? '  ·  ' : '' }}{{ $siswa->gender === 'L' ? 'Laki-laki' : ($siswa->gender === 'P' ? 'Perempuan' : '') }}
            </div>
        </div>
    </div>

    {{-- Strip bawah emas + SISWA --}}
    <div class="back-footer">
        <div class="back-footer-inner">
            <div class="back-siswa">SISWA</div>
        </div>
    </div>

</div>

</body>
</html>
