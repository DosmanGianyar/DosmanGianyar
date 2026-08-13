<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekapitulasi Absensi Siswa Bulanan — {{ $selectedClass?->name ?? 'Kelas' }}</title>
<style>
@page {
    size: A4 landscape;
    margin: 1cm 1.2cm;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 9.5pt;
    color: #000;
    background: #fff;
}

/* ─ Kop Surat ─ */
.kop {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 6px;
    border-bottom: 2.5px solid #000;
    margin-bottom: 6px;
}
.kop img {
    width: 52px;
    height: 52px;
    object-fit: contain;
    flex-shrink: 0;
}
.kop-tengah {
    flex: 1;
    text-align: center;
    line-height: 1.25;
}
.kop-tengah .instansi {
    font-size: 8.5pt;
    font-weight: normal;
    color: #222;
}
.kop-tengah .nama-sekolah {
    font-size: 14pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #000;
}
.kop-tengah .alamat {
    font-size: 8pt;
    color: #333;
    margin-top: 1px;
}

/* ─ Judul ─ */
.judul-blok {
    text-align: center;
    margin: 8px 0 6px;
}
.judul-blok h1 {
    font-size: 11pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.judul-blok p {
    font-size: 9pt;
    color: #333;
}

/* ─ Info Bar ─ */
.info-table {
    width: 100%;
    margin-bottom: 6px;
    border-collapse: collapse;
}
.info-table td {
    padding: 1px 0;
    font-size: 9pt;
    vertical-align: top;
}
.info-table .label { font-weight: bold; width: 90px; }
.info-table .sep   { width: 12px; text-align: center; }
.info-table .val   { font-weight: normal; }

/* ─ Tabel Presensi Bulanan ─ */
.presensi-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
    font-size: 8pt;
}
.presensi-table th {
    background: #f1f5f9;
    border: 1px solid #475569;
    padding: 3px 1px;
    text-align: center;
    font-weight: bold;
}
.presensi-table td {
    border: 1px solid #64748b;
    padding: 2.5px 2px;
    vertical-align: middle;
}
.presensi-table td.center { text-align: center; }
.presensi-table th.day-col {
    width: 18px;
    font-size: 7pt;
    line-height: 1.1;
}
.presensi-table th.no-col   { width: 22px; }
.presensi-table th.nis-col  { width: 55px; }
.presensi-table th.nama-col { min-width: 140px; }
.presensi-table th.stat-col { width: 20px; font-size: 8pt; }

/* Status Badges */
.badge-st {
    display: inline-block;
    width: 15px;
    height: 15px;
    line-height: 15px;
    text-align: center;
    border-radius: 3px;
    font-weight: bold;
    font-size: 7.5pt;
}
.st-H { color: #166534; font-weight: bold; }
.st-S { background: #fef3c7; color: #92400e; }
.st-I { background: #e0f2fe; color: #075985; }
.st-A { background: #fee2e2; color: #991b1b; }
.st-D { background: #ccfbf1; color: #0f766e; }
.st-L { background: #f3f4f6; color: #9ca3af; font-weight: bold; }

.bg-holiday { background: #f8fafc; }
.bg-sunday  { background: #fef2f2; }

/* Legenda & Catatan Libur */
.legend-box {
    font-size: 7.5pt;
    color: #334155;
    margin-bottom: 8px;
    line-height: 1.4;
}

/* ─ Tanda Tangan Ganda ─ */
.ttd-wrap {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    page-break-inside: avoid;
}
.ttd-box {
    width: 240px;
    text-align: center;
    font-size: 8.5pt;
}
.ttd-box .ttd-lokasi { margin-bottom: 2px; }
.ttd-box .ttd-jabatan { font-weight: bold; margin-bottom: 36px; }
.ttd-box .ttd-nama { font-weight: bold; text-decoration: underline; }
.ttd-box .ttd-nip { font-size: 8pt; color: #333; margin-top: 1px; }

@media print {
    .no-print { display: none !important; }
}
</style>
</head>
<body>

{{-- Tombol Cetak / Kembali (No Print) --}}
<div class="no-print" style="position:fixed; top:15px; right:15px; z-index:9999; display:flex; gap:10px;">
    <button onclick="window.print()" style="padding:8px 16px; background:#10b981; color:#fff; font-weight:bold; border:none; border-radius:6px; cursor:pointer;">
        🖨️ Cetak Rekap Absen Bulanan (PDF)
    </button>
    <button onclick="window.close()" style="padding:8px 16px; background:#64748b; color:#fff; font-weight:bold; border:none; border-radius:6px; cursor:pointer;">
        Tutup
    </button>
</div>

{{-- ─── Kop Surat ───────────────────────────────────────────────────────── --}}
<div class="kop">
    <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo SMAN 1 Gianyar"
         onerror="this.src='https://via.placeholder.com/52?text=LOGO'">
    <div class="kop-tengah">
        <div class="instansi">PEMERINTAH PROVINSI BALI<br>DINAS PENDIDIKAN, KEPEMUDAAN, DAN OLAHRAGA</div>
        <div class="nama-sekolah">SMA Negeri 1 Gianyar</div>
        <div class="alamat">Jl. Ratna No. 1, Gianyar, Bali 80511 | Telp: (0361) 943034 | Website: sman1-gianyar.sch.id</div>
    </div>
</div>

{{-- ─── Judul ───────────────────────────────────────────────────────── --}}
<div class="judul-blok">
    <h1>REKAPITULASI ABSENSI SISWA BULANAN (TGL 1 S/D {{ count($days) }})</h1>
    <p>Bulan: {{ $startOfMonth->isoFormat('MMMM Y') }}</p>
</div>

{{-- ─── Info Bar ───────────────────────────────────────────────────── --}}
<table class="info-table">
    <tr>
        <td class="label">Kelas</td>
        <td class="sep">:</td>
        <td class="val"><strong>{{ $selectedClass?->name ?? '—' }}</strong></td>
        <td class="label" style="text-align:right;">Guru / Wali Kelas</td>
        <td class="sep">:</td>
        <td class="val" style="width:200px;">{{ $teacher->name }}</td>
    </tr>
</table>

{{-- ─── Tabel Presensi Bulanan ──────────────────────────────────────── --}}
@if($attendanceMatrix->isEmpty())
<p style="text-align:center;padding:25px 0;color:#555;font-style:italic;">
    Tidak ada data siswa untuk kelas {{ $selectedClass?->name ?? '' }}.
</p>
@else
<table class="presensi-table">
    <thead>
        <tr>
            <th class="no-col" rowspan="2">No</th>
            <th class="nis-col" rowspan="2">NIS</th>
            <th class="nama-col" rowspan="2">Nama Siswa</th>
            <th colspan="{{ count($days) }}">Tanggal (Bulan {{ $startOfMonth->isoFormat('MMMM Y') }})</th>
            <th colspan="5">Rekap Ketidakhadiran</th>
        </tr>
        <tr>
            @foreach($days as $day)
            <th class="day-col {{ $day['is_sunday'] ? 'bg-sunday' : ($day['is_holiday'] ? 'bg-holiday' : '') }}">
                <span style="font-size:6.5pt;display:block;">{{ $day['day_short'] }}</span>
                <strong>{{ $day['day_num'] }}</strong>
            </th>
            @endforeach
            <th class="stat-col" style="background:#fef3c7;color:#92400e;">S</th>
            <th class="stat-col" style="background:#e0f2fe;color:#0369a1;">I</th>
            <th class="stat-col" style="background:#fee2e2;color:#b91c1c;">A</th>
            <th class="stat-col" style="background:#ccfbf1;color:#0f766e;">D</th>
            <th class="stat-col" style="background:#f3f4f6;color:#64748b;">L</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attendanceMatrix as $i => $row)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td class="center">{{ $row['student']->nis ?? '—' }}</td>
            <td><strong>{{ $row['student']->name }}</strong></td>
            
            @foreach($days as $day)
            @php 
                $st = $row['days'][$day['date_str']] ?? '—';
                $cellBg = $day['is_sunday'] ? '#fef2f2' : ($day['is_holiday'] ? '#f8fafc' : '');
            @endphp
            <td class="center" style="{{ $cellBg ? 'background:'.$cellBg.';' : '' }}">
                <span class="badge-st st-{{ $st }}">{{ $st }}</span>
            </td>
            @endforeach

            <td class="center font-bold" style="background:#fffbeb;">{{ $row['sakit'] ?: '-' }}</td>
            <td class="center font-bold" style="background:#f0f9ff;">{{ $row['izin'] ?: '-' }}</td>
            <td class="center font-bold" style="background:#fef2f2;">{{ $row['alpa'] ?: '-' }}</td>
            <td class="center font-bold" style="background:#f0fdfa;">{{ $row['dispen'] ?: '-' }}</td>
            <td class="center font-bold" style="background:#f8fafc;color:#64748b;">{{ $row['libur'] ?: '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="legend-box">
    <p>* <strong>Keterangan Status:</strong> H = Hadir, S = Sakit, I = Izin, A = Alpa / Tanpa Keterangan, D = Dispensasi, L = Libur Sekolah / Hari Minggu.</p>
    @if(!empty($holidayNotes))
    <p style="margin-top:2px;">
        * <strong>Daftar Hari Libur Bulan Ini:</strong> {{ implode(' | ', $holidayNotes) }}
    </p>
    @endif
</div>

{{-- ─── Tanda Tangan ────────────────────────────────────────── --}}
<div class="ttd-wrap">
    <div class="ttd-box">&nbsp;</div>

    <div class="ttd-box">
        <div class="ttd-lokasi">Gianyar, {{ now()->isoFormat('D MMMM Y') }}</div>
        <div class="ttd-jabatan">Guru Pengajar / Wali Kelas,</div>
        <div class="ttd-nama">{{ $teacher->name }}</div>
        <div class="ttd-nip">NIP. {{ $teacher->nip ?? '—' }}</div>
    </div>
</div>

</body>
</html>
