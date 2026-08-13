<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

@page {
    size: 242.56pt 153.07pt;
    margin: 0pt;
}

html, body {
    width: 242.56pt;
    height: 153.07pt;
    margin: 0;
    padding: 0;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    background: #ffffff;
    color: #1f2937;
}

.card {
    position: relative;
    width: 242.56pt;
    height: 153.07pt;
    overflow: hidden;
    background: #f8f7f4;
    page-break-after: always;
}

/* ══════════════════════════════════════════════════════════
   HEADER (DEPAN)
══════════════════════════════════════════════════════════ */
.front-header-table {
    width: 100%;
    height: 38pt;
    background: #0a3880;
    border-collapse: collapse;
}

.hdr-logo-td {
    width: 32pt;
    text-align: center;
    vertical-align: middle;
    padding-left: 6pt;
}

.hdr-logo-circle {
    width: 26pt;
    height: 26pt;
    background: #ffffff;
    border-radius: 50%;
    text-align: center;
    vertical-align: middle;
    margin: 0 auto;
}

.hdr-logo-img {
    width: 22pt;
    height: 22pt;
    margin-top: 2pt;
}

.hdr-text-td {
    vertical-align: middle;
    padding-left: 4pt;
}

.hdr-title {
    font-size: 8.5pt;
    font-weight: bold;
    color: #ffffff;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    line-height: 1.1;
}

.hdr-sub {
    font-size: 4pt;
    color: #93c5fd;
    margin-top: 1pt;
    line-height: 1.2;
}

.hdr-badge-td {
    width: 48pt;
    text-align: right;
    vertical-align: middle;
    padding-right: 6pt;
}

.hdr-badge-box {
    border: 0.75pt solid rgba(255, 255, 255, 0.4);
    border-radius: 3pt;
    background: rgba(255, 255, 255, 0.18);
    padding: 2pt 4pt;
    text-align: center;
}

.hdr-badge-text {
    font-size: 4.5pt;
    font-weight: bold;
    color: #ffffff;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    line-height: 1.2;
}

.gold-strip {
    height: 3pt;
    background: #b45309;
}

/* ══════════════════════════════════════════════════════════
   BODY (DEPAN)
══════════════════════════════════════════════════════════ */
.front-body {
    position: relative;
    padding: 5pt 6pt 4pt 6pt;
    height: 104pt;
}

.watermark-bg {
    position: absolute;
    right: 4pt;
    top: 50%;
    margin-top: -30pt;
    width: 60pt;
    height: 60pt;
    opacity: 0.04;
}

.body-layout-table {
    width: 100%;
    border-collapse: collapse;
}

.photo-td {
    width: 52pt;
    vertical-align: top;
}

.photo-box {
    width: 48pt;
    height: 64pt;
    border: 1pt solid #1565c0;
    border-radius: 4pt;
    overflow: hidden;
    background: #dce8f8;
    text-align: center;
}

.photo-img {
    width: 48pt;
    height: 64pt;
}

.avatar-placeholder {
    font-size: 14pt;
    font-weight: bold;
    color: #1565c0;
    line-height: 64pt;
    text-align: center;
}

.blood-badge {
    margin-top: 3pt;
    background: #fee2e2;
    border: 0.5pt solid #f87171;
    color: #991b1b;
    font-size: 4.5pt;
    font-weight: bold;
    padding: 1pt 2pt;
    border-radius: 2pt;
    text-align: center;
    width: 48pt;
}

.details-td {
    vertical-align: top;
    padding-left: 6pt;
}

.card-heading {
    font-size: 6.5pt;
    font-weight: bold;
    color: #0a3880;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    text-decoration: underline;
    margin-bottom: 2pt;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table td {
    font-size: 4.8pt;
    padding: 0.5pt 0;
    vertical-align: top;
    line-height: 1.25;
}

.lbl {
    width: 42pt;
    color: #4b5563;
    font-weight: 600;
}

.col {
    width: 6pt;
    color: #6b7280;
}

.val {
    color: #111827;
    font-weight: 700;
}

.val-name {
    color: #0a3880;
    font-weight: 900;
    font-size: 5.5pt;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.val-highlight {
    color: #92400e;
    font-weight: 800;
}

/* ══════════════════════════════════════════════════════════
   FOOTER (DEPAN — TTD & STEMPEL KEPSEK)
══════════════════════════════════════════════════════════ */
.footer-table {
    width: 100%;
    margin-top: 1pt;
    border-collapse: collapse;
}

.footer-validity-td {
    vertical-align: bottom;
    font-size: 4pt;
    color: #6b7280;
    font-style: italic;
    line-height: 1.2;
    padding-bottom: 1pt;
}

.footer-sig-td {
    vertical-align: bottom;
    text-align: center;
    width: 85pt;
}

.sig-date {
    font-size: 4.2pt;
    color: #374151;
}

.sig-role {
    font-size: 4.2pt;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1pt;
}

.sig-image-wrap {
    height: 22pt;
    text-align: center;
    margin: -2pt 0 -2pt 0;
}

.sig-image {
    height: 24pt;
    width: auto;
    max-width: 80pt;
}

.sig-name {
    font-size: 4.8pt;
    font-weight: bold;
    color: #111827;
    text-decoration: underline;
    white-space: nowrap;
}

.sig-nip {
    font-size: 4pt;
    font-weight: bold;
    color: #4b5563;
    white-space: nowrap;
    margin-top: 0.5pt;
}

.bottom-strip {
    height: 5pt;
    background: #0a3880;
}

/* ══════════════════════════════════════════════════════════
   HALAMAN BELAKANG
══════════════════════════════════════════════════════════ */
.card-back {
    background: #ffffff;
    page-break-after: auto;
}

.back-header-table {
    width: 100%;
    height: 18pt;
    background: #0a3880;
    border-collapse: collapse;
    padding: 0 6pt;
}

.back-header-td {
    vertical-align: middle;
    color: #ffffff;
    font-size: 5.5pt;
    font-weight: bold;
    letter-spacing: 0.05em;
    padding-left: 6pt;
}

.back-npsn-td {
    vertical-align: middle;
    text-align: right;
    color: rgba(255, 255, 255, 0.7);
    font-size: 4.2pt;
    padding-right: 6pt;
}

.back-body-center {
    height: 124pt;
    text-align: center;
    vertical-align: middle;
    padding: 6pt;
}

.qr-container {
    display: inline-block;
    padding: 3pt;
    border: 0.75pt solid #e5e7eb;
    border-radius: 4pt;
    background: #ffffff;
}

.qr-img {
    width: 50pt;
    height: 50pt;
}

.qr-hint {
    font-size: 4.2pt;
    color: #9ca3af;
    margin-top: 3pt;
}

.back-student-name {
    font-size: 7.5pt;
    font-weight: bold;
    color: #111827;
    margin-top: 4pt;
}

.back-student-details {
    font-size: 5pt;
    color: #6b7280;
    margin-top: 1.5pt;
}

.back-footer-bar {
    height: 11pt;
    background: #b45309;
    text-align: center;
    line-height: 11pt;
    color: #ffffff;
    font-size: 5pt;
    font-weight: bold;
    letter-spacing: 0.15em;
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 1 — DEPAN KARTU                    --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="card">

    {{-- Header --}}
    <table class="front-header-table">
        <tr>
            <td class="hdr-logo-td">
                @if($logoBase64)
                <div class="hdr-logo-circle">
                    <img src="{{ $logoBase64 }}" class="hdr-logo-img" alt="Logo">
                </div>
                @endif
            </td>
            <td class="hdr-text-td">
                <div class="hdr-title">SMA Negeri 1 Gianyar</div>
                <div class="hdr-sub">Jl. Ratna No.1, Gianyar, Bali 80511 · Telp. (0361) 943443</div>
            </td>
            <td class="hdr-badge-td">
                <div class="hdr-badge-box">
                    <div class="hdr-badge-text">KARTU<br>PELAJAR</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="gold-strip"></div>

    {{-- Body --}}
    <div class="front-body">
        @if($logoBase64)
        <img src="{{ $logoBase64 }}" class="watermark-bg" alt="">
        @endif

        <table class="body-layout-table">
            <tr>
                {{-- Left: Photo --}}
                <td class="photo-td">
                    <div class="photo-box">
                        @if($photoBase64)
                            <img src="{{ $photoBase64 }}" class="photo-img" alt="{{ $siswa->name }}">
                        @else
                            <div class="avatar-placeholder">{{ $siswa->initials }}</div>
                        @endif
                    </div>
                    @if($siswa->blood_type)
                        <div class="blood-badge">Goldar: {{ strtoupper($siswa->blood_type) }}</div>
                    @endif
                </td>

                {{-- Right: Details & Signature --}}
                <td class="details-td">
                    <div class="card-heading">KARTU PELAJAR</div>
                    
                    <table class="info-table">
                        <tr>
                            <td class="lbl">Nama</td>
                            <td class="col">:</td>
                            <td class="val-name">{{ strtoupper($siswa->name) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">NIS / NISN</td>
                            <td class="col">:</td>
                            <td class="val">{{ $siswa->nis ?? '—' }} / {{ $siswa->nisn ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Kelas</td>
                            <td class="col">:</td>
                            <td class="val">{{ $siswa->schoolClass?->name ?? '—' }}</td>
                        </tr>
                        @if($siswa->angkatan)
                        <tr>
                            <td class="lbl">Angkatan</td>
                            <td class="col">:</td>
                            <td class="val-highlight">{{ $siswa->angkatan }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="lbl">Tgl. Lahir</td>
                            <td class="col">:</td>
                            <td class="val">{{ $siswa->birth_date?->isoFormat('D MMMM Y') ?? '—' }}</td>
                        </tr>
                        @if($siswa->gender)
                        <tr>
                            <td class="lbl">Gender</td>
                            <td class="col">:</td>
                            <td class="val">{{ $siswa->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        @endif
                    </table>

                    {{-- Footer TTD & Masa Berlaku --}}
                    <table class="footer-table">
                        <tr>
                            <td class="footer-validity-td">
                                Berlaku selama menjadi<br>siswa SMAN 1 Gianyar
                            </td>
                            <td class="footer-sig-td">
                                <div class="sig-date">Gianyar, 13 Juli 2026</div>
                                <div class="sig-role">Kepala Sekolah,</div>
                                
                                {{-- Official TTD & Stempel Image --}}
                                <div class="sig-image-wrap">
                                    @if($ttdBase64)
                                        <img src="{{ $ttdBase64 }}" class="sig-image" alt="TTD & Stempel">
                                    @endif
                                </div>

                                <div class="sig-name">I Wayan Sudra Astra, S.Pd., M.Pd.</div>
                                <div class="sig-nip">NIP. 19710415 199703 1 007</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="bottom-strip"></div>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 2 — BELAKANG KARTU                 --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="card card-back">

    <table class="back-header-table">
        <tr>
            <td class="back-header-td">SMA NEGERI 1 GIANYAR</td>
            <td class="back-npsn-td">NPSN 50102079</td>
        </tr>
    </table>

    <div class="back-body-center">
        <div class="qr-container">
            <img src="{{ $qrPng }}" class="qr-img" alt="QR Code">
        </div>
        <div class="qr-hint">Scan untuk verifikasi identitas resmi siswa</div>

        <div class="back-student-name">{{ $siswa->name }}</div>
        <div class="back-student-details">
            NIS: {{ $siswa->nis ?? '—' }} @if($siswa->nisn) · NISN: {{ $siswa->nisn }} @endif
            <br>
            Kelas {{ $siswa->schoolClass?->name ?? '—' }} @if($siswa->angkatan) · {{ $siswa->angkatan }} @endif
        </div>
    </div>

    <div class="back-footer-bar">
        SISWA {{ $siswa->angkatan ? '· ' . strtoupper($siswa->angkatan) : '' }}
    </div>

</div>

</body>
</html>
