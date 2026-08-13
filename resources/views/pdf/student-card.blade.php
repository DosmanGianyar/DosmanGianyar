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
    font-family: Arial, Helvetica, sans-serif;
    background: #ffffff;
}

.card {
    position: relative;
    width: 242.56pt;
    height: 153.07pt;
    overflow: hidden;
    background: #f8f7f4;
}

.card-front {
    page-break-after: always;
}

.card-back {
    page-break-after: avoid;
    background: #ffffff;
}

/* Header Box */
.header-box {
    position: absolute;
    top: 0; left: 0; width: 242.56pt; height: 38pt;
    background: #0a3880;
}

.hdr-table {
    width: 100%;
    height: 38pt;
    border-collapse: collapse;
}

.hdr-logo-td {
    width: 42pt;
    text-align: center;
    vertical-align: bottom;
    padding-left: 6pt;
    padding-bottom: 0pt;
}

.hdr-logo-circle {
    width: 30pt;
    height: 30pt;
    background: #ffffff;
    border-radius: 50%;
    text-align: center;
    vertical-align: middle;
    margin: 4pt auto 0 auto;
}

.hdr-logo-img {
    width: 25pt;
    height: 25pt;
    margin-top: 2.5pt;
}

.hdr-text-td {
    vertical-align: middle;
    padding-left: 3pt;
    padding-top: 1pt;
}

.hdr-balinese {
    font-size: 5.5pt;
    color: #93c5fd;
    margin-bottom: 0pt;
    line-height: 1.1;
}

.hdr-title {
    font-size: 8.5pt;
    font-weight: bold;
    color: #ffffff;
    text-transform: uppercase;
    line-height: 1.1;
}

.hdr-sub {
    font-size: 3.8pt;
    color: #93c5fd;
    margin-top: 1pt;
}

.hdr-badge-td {
    width: 44pt;
    text-align: right;
    vertical-align: middle;
    padding-right: 6pt;
    padding-top: 2pt;
}

.hdr-badge-box {
    border: 0.5pt solid rgba(255, 255, 255, 0.4);
    border-radius: 2.5pt;
    background: rgba(255, 255, 255, 0.18);
    padding: 1.5pt 3pt;
    font-size: 4pt;
    font-weight: bold;
    color: #ffffff;
    letter-spacing: 0.06em;
    text-align: center;
    line-height: 1.15;
}

/* Gold strip */
.gold-strip {
    position: absolute;
    top: 38pt; left: 0; width: 242.56pt; height: 2.5pt;
    background: #b45309;
}

/* Body Box */
.body-box {
    position: absolute;
    top: 40.5pt; left: 0; width: 242.56pt; height: 108.5pt;
    padding: 3pt 6pt;
}

.watermark-img {
    position: absolute;
    right: 12pt; top: 10pt;
    width: 65pt; height: 65pt;
    opacity: 0.08;
}

.body-table {
    width: 100%;
    border-collapse: collapse;
}

.photo-td {
    width: 50pt;
    vertical-align: top;
}

.photo-frame {
    width: 44pt;
    height: 58pt;
    border: 1pt solid #1565c0;
    border-radius: 3pt;
    overflow: hidden;
    background: #dce8f8;
    text-align: center;
}

.student-photo {
    width: 44pt;
    height: 58pt;
}

.initials-box {
    font-size: 13pt;
    font-weight: bold;
    color: #1565c0;
    line-height: 58pt;
}

.blood-tag {
    margin-top: 2pt;
    background: #fee2e2;
    border: 0.5pt solid #f87171;
    color: #991b1b;
    font-size: 4pt;
    font-weight: bold;
    padding: 1pt;
    border-radius: 2pt;
    text-align: center;
    width: 44pt;
}

.info-td {
    vertical-align: top;
    padding-left: 5pt;
}

.kp-heading {
    font-size: 6pt;
    font-weight: bold;
    color: #0a3880;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    text-decoration: underline;
    margin-bottom: 1.5pt;
}

.details-table {
    width: 100%;
    border-collapse: collapse;
}

.details-table td {
    font-size: 4.5pt;
    padding: 0.3pt 0;
    vertical-align: top;
    line-height: 1.2;
}

.lbl { width: 38pt; color: #4b5563; font-weight: bold; }
.col { width: 5pt; color: #6b7280; }
.val { color: #111827; font-weight: bold; }
.val-name { color: #0a3880; font-weight: 900; font-size: 5.2pt; text-transform: uppercase; }
.val-gold { color: #92400e; font-weight: bold; }

/* Signature Table */
.sig-table {
    width: 100%;
    margin-top: 1pt;
    border-collapse: collapse;
}

.validity-td {
    vertical-align: bottom;
    font-size: 3.6pt;
    color: #6b7280;
    font-style: italic;
    line-height: 1.15;
    padding-bottom: 1pt;
}

.sig-td {
    vertical-align: bottom;
    text-align: center;
    width: 85pt;
    border: none !important;
}

.sig-date { font-size: 3.8pt; color: #374151; }
.sig-title { font-size: 3.8pt; font-weight: bold; color: #374151; }

.sig-img-container {
    height: 20pt;
    text-align: center;
    margin: -2pt 0 -2pt 0;
    border: none !important;
    outline: none !important;
    background: transparent !important;
}

.sig-img {
    height: 24pt;
    width: auto;
    max-width: 85pt;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
}

.sig-name {
    font-size: 4.2pt;
    font-weight: bold;
    color: #111827;
    text-decoration: underline;
    white-space: nowrap;
}

.sig-nip {
    font-size: 3.5pt;
    font-weight: bold;
    color: #4b5563;
    white-space: nowrap;
}

/* Bottom Strip */
.bottom-strip {
    position: absolute;
    bottom: 0; left: 0; width: 242.56pt; height: 4pt;
    background: #0a3880;
}

/* Back Card Header (Identical Logo Positioning) */
.back-header-box {
    position: absolute;
    top: 0; left: 0; width: 242.56pt; height: 38pt;
    background: #0a3880;
    z-index: 10;
}

.back-hdr-table {
    width: 100%; height: 38pt; border-collapse: collapse;
}

.back-logo-td {
    width: 42pt;
    text-align: center;
    vertical-align: bottom;
    padding-left: 6pt;
    padding-bottom: 0pt;
}

.back-logo-circle {
    width: 30pt;
    height: 30pt;
    background: #ffffff;
    border-radius: 50%;
    text-align: center;
    vertical-align: middle;
    margin: 4pt auto 0 auto;
}

.back-logo-img {
    width: 25pt;
    height: 25pt;
    margin-top: 2.5pt;
}

.back-title-td {
    vertical-align: middle;
    color: #ffffff;
    padding-left: 3pt;
    padding-top: 1pt;
}

.back-hdr-title {
    font-size: 8.5pt;
    font-weight: bold;
    color: #ffffff;
    text-transform: uppercase;
    line-height: 1.1;
}

.back-npsn-td {
    vertical-align: middle;
    text-align: right;
    color: rgba(255, 255, 255, 0.8);
    font-size: 4pt;
    padding-right: 6pt;
    padding-top: 2pt;
}

/* Flanking Logos on Left & Right of QR Code */
.back-logo-flank-left {
    position: absolute;
    left: 15pt;
    top: 48pt;
    width: 48pt;
    height: 48pt;
    opacity: 0.12;
    z-index: 1;
}

.back-logo-flank-right {
    position: absolute;
    right: 15pt;
    top: 48pt;
    width: 48pt;
    height: 48pt;
    opacity: 0.12;
    z-index: 1;
}

.back-body-box {
    position: absolute;
    top: 38pt; left: 0; width: 242.56pt; height: 105pt;
    text-align: center; vertical-align: middle; padding-top: 4pt;
    z-index: 5;
}

.qr-frame {
    display: inline-block;
    padding: 2.5pt;
    border: 0.5pt solid #e5e7eb;
    border-radius: 3pt;
    background: #ffffff;
}

.qr-code { width: 42pt; height: 42pt; }
.qr-text { font-size: 3.8pt; color: #9ca3af; margin-top: 2pt; }
.divider-line { width: 80pt; height: 0.5pt; background: #e5e7eb; margin: 2.5pt auto; }
.back-name-text { font-size: 6.8pt; font-weight: bold; color: #111827; margin-top: 1pt; }
.back-sub-text { font-size: 4.2pt; color: #6b7280; margin-top: 1pt; line-height: 1.2; }

/* Slogan Sekolah Widya Wahana Bhakti */
.back-slogan-text {
    font-size: 7.5pt;
    font-weight: 900;
    color: #0a3880;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    margin-top: 4pt;
    font-style: italic;
}

.back-footer-bar {
    position: absolute;
    bottom: 0; left: 0; width: 242.56pt; height: 10pt;
    background: #b45309;
    text-align: center;
    line-height: 10pt;
    color: #ffffff;
    font-size: 4.5pt;
    font-weight: bold;
    letter-spacing: 0.12em;
    z-index: 10;
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 1 — DEPAN KARTU                    --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="card card-front">

    <!-- Header -->
    <div class="header-box">
        <table class="hdr-table">
            <tr>
                <td class="hdr-logo-td">
                    @if($logoBase64)
                    <div class="hdr-logo-circle">
                        <img src="{{ $logoBase64 }}" class="hdr-logo-img" alt="Logo">
                    </div>
                    @endif
                </td>
                <td class="hdr-text-td">
                    @if(!empty($aksaraBaliBase64))
                        <img src="{{ $aksaraBaliBase64 }}" style="height: 6.5pt; width: auto; display: block; margin-bottom: 0.5pt;" alt="Aksara Bali">
                    @else
                        <div class="hdr-balinese">᭞ᬏᬲ᭄ᬏᬫ᭄ᬅ᭞ᬦᭂᬕᭂᬭᬶ᭞᭑᭞ᬕ᭄ᬬᬜᬃ᭞</div>
                    @endif
                    <div class="hdr-title">SMA Negeri 1 Gianyar</div>
                    <div class="hdr-sub">Jl. Ratna No.1, Gianyar, Bali 80511 · Telp. (0361) 943443</div>
                </td>
                <td class="hdr-badge-td">
                    <div class="hdr-badge-box">
                        KARTU<br>PELAJAR
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Gold Strip -->
    <div class="gold-strip"></div>

    <!-- Body -->
    <div class="body-box">
        @if($logoBase64)
        <img src="{{ $logoBase64 }}" class="watermark-img" alt="">
        @endif

        <table class="body-table">
            <tr>
                <!-- Left: Photo -->
                <td class="photo-td">
                    <div class="photo-frame">
                        @if($photoBase64)
                            <img src="{{ $photoBase64 }}" class="student-photo" alt="{{ $siswa->name }}">
                        @else
                            <div class="initials-box">{{ $siswa->initials }}</div>
                        @endif
                    </div>
                    @if($siswa->blood_type)
                        <div class="blood-tag">Goldar: {{ strtoupper($siswa->blood_type) }}</div>
                    @endif
                </td>

                <!-- Right: Details & Signature -->
                <td class="info-td">
                    <div class="kp-heading">KARTU PELAJAR</div>
                    <table class="details-table">
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
                            <td class="val-gold">{{ $siswa->angkatan }}</td>
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

                    <!-- Signature Table -->
                    <table class="sig-table">
                        <tr>
                            <td class="validity-td">
                                Berlaku selama menjadi<br>siswa SMAN 1 Gianyar
                            </td>
                            <td class="sig-td">
                                <div class="sig-date">Gianyar, 13 Juli 2026</div>
                                <div class="sig-title">Kepala Sekolah,</div>
                                <div class="sig-img-container">
                                    @if($ttdBase64)
                                        <img src="{{ $ttdBase64 }}" class="sig-img" alt="TTD & Stempel">
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

    <!-- Bottom Strip -->
    <div class="bottom-strip"></div>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- HALAMAN 2 — BELAKANG KARTU                 --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="card card-back">

    <!-- Header -->
    <div class="back-header-box">
        <table class="back-hdr-table">
            <tr>
                <td class="back-logo-td">
                    @if($logoBase64)
                    <div class="back-logo-circle">
                        <img src="{{ $logoBase64 }}" class="back-logo-img" alt="Logo">
                    </div>
                    @endif
                </td>
                <td class="back-title-td">
                    @if(!empty($aksaraBaliBase64))
                        <img src="{{ $aksaraBaliBase64 }}" style="height: 6pt; width: auto; display: block; margin-bottom: 0.5pt;" alt="Aksara Bali">
                    @else
                        <div class="hdr-balinese">᭞ᬏᬲ᭄ᬏᬫ᭄ᬅ᭞ᬦᭂᬕᭂᬭᬶ᭞᭑᭞ᬕ᭄ᬬᬜᬃ᭞</div>
                    @endif
                    <div class="back-hdr-title">SMA NEGERI 1 GIANYAR</div>
                </td>
                <td class="back-npsn-td">NPSN 50102079</td>
            </tr>
        </table>
    </div>

    <!-- Flanking Watermark Logos (Left & Right of QR Code) -->
    @if($logoBase64)
    <img src="{{ $logoBase64 }}" class="back-logo-flank-left" alt="">
    <img src="{{ $logoBase64 }}" class="back-logo-flank-right" alt="">
    @endif

    <!-- Body -->
    <div class="back-body-box">
        <div class="qr-frame">
            <img src="{{ $qrPng }}" class="qr-code" alt="QR Code">
        </div>
        <div class="qr-text">Scan untuk verifikasi identitas resmi siswa</div>
        <div class="divider-line"></div>
        <div class="back-name-text">{{ $siswa->name }}</div>
        <div class="back-sub-text">
            NIS: {{ $siswa->nis ?? '—' }} @if($siswa->nisn) · NISN: {{ $siswa->nisn }} @endif
            <br>
            Kelas {{ $siswa->schoolClass?->name ?? '—' }} @if($siswa->angkatan) · {{ $siswa->angkatan }} @endif
        </div>

        <!-- Slogan Sekolah Widya Wahana Bhakti -->
        <div class="back-slogan-text">" WIDYA WAHANA BHAKTI "</div>
    </div>

    <!-- Footer Bar -->
    <div class="back-footer-bar">
        SISWA {{ $siswa->angkatan ? '· ' . strtoupper($siswa->angkatan) : '' }}
    </div>

</div>

</body>
</html>
