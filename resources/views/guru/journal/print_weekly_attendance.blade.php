<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekap Absensi Kelas Mingguan — {{ $selectedClass?->name ?? '' }}</title>
<style>
@page {
    size: A4 landscape;
    margin: 1cm 1.2cm;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 10pt;
    color: #000;
    background: #fff;
}

/* ─ Kop Surat ─ */
.kop {
    display: flex;
    align-items: center;
    gap: 14px;
    padding-bottom: 6px;
    border-bottom: 2.5px solid #000;
    margin-bottom: 3px;
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
    letter-spacing: 0.3px;
    color: #222;
}
.kop-tengah .nama-sekolah {
    font-size: 14pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
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
    margin: 10px 0 8px;
}
.judul-blok h1 {
    font-size: 12pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: underline;
}
.judul-blok p {
    font-size: 9.5pt;
    font-weight: bold;
    margin-top: 2px;
    color: #333;
}

/* ─ Info Bar ─ */
.info-table {
    width: 100%;
    margin-bottom: 8px;
    border-collapse: collapse;
}
.info-table td {
    padding: 1px 0;
    font-size: 9.5pt;
    vertical-align: top;
}
.info-table .label { width: 120px; font-weight: normal; }
.info-table .sep   { width: 12px; text-align: center; }
.info-table .val   { font-weight: bold; }

/* ─ Tabel Presensi ─ */
.presensi-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
    font-size: 8.5pt;
}
.presensi-table th {
    background: #f0f0f0;
    border: 1px solid #333;
    padding: 4px 3px;
    text-align: center;
    font-weight: bold;
    vertical-align: middle;
    line-height: 1.2;
}
.presensi-table td {
    border: 1px solid #333;
    padding: 3px 4px;
    vertical-align: middle;
    line-height: 1.2;
}
.presensi-table td.center { text-align: center; }
.presensi-table .no-col   { width: 26px; text-align: center; }
.presensi-table .nis-col  { width: 70px; text-align: center; }
.presensi-table .nama-col { width: auto; }
.presensi-table .day-col  { width: 65px; text-align: center; }
.presensi-table .stat-col { width: 32px; text-align: center; font-weight: bold; }

/* ─ Badges Status Presensi ─ */
.badge-st {
    display: inline-block;
    width: 20px;
    height: 20px;
    line-height: 19px;
    text-align: center;
    border-radius: 4px;
    font-weight: bold;
    font-size: 8pt;
}
.st-H { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
.st-S { background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe; }
.st-I { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
.st-A { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
.st-D { background: #ccfbf1; color: #0f766e; border: 1px solid #5eead4; }

/* ─ Footer / TTD ─ */
.ttd-wrap {
    display: flex;
    justify-content: space-between;
    margin-top: 14px;
    page-break-inside: avoid;
}
.ttd-box {
    text-align: center;
    width: 240px;
    font-size: 9.5pt;
}
.ttd-box .ttd-lokasi { margin-bottom: 2px; }
.ttd-box .ttd-jabatan { font-weight: normal; margin-bottom: 42px; }
.ttd-box .ttd-nama { font-weight: bold; text-decoration: underline; }
.ttd-box .ttd-nip  { font-size: 8.5pt; }

/* ─ Toolbar (hanya layar) ─ */
.print-toolbar {
    position: fixed;
    top: 14px;
    right: 20px;
    display: flex;
    gap: 10px;
    z-index: 999;
}
.btn-cetak {
    background: #059669;
    color: #fff;
    border: none;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-family: sans-serif;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.btn-cetak:hover { background: #047857; }
.btn-tutup {
    background: #e5e7eb;
    color: #374151;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-family: sans-serif;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.filter-bar {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 14px;
    font-family: sans-serif;
    font-size: 13px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: flex-end;
}
.filter-bar label { display: block; font-size: 11px; font-weight: 600; color: #6b7280; margin-bottom: 2px; }
.filter-bar select, .filter-bar input {
    padding: 5px 8px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    font-size: 12px;
    background: #fff;
}

@media print {
    .print-toolbar { display: none !important; }
    .filter-bar    { display: none !important; }
    body { background: #fff; }
}
</style>
</head>
<body>

{{-- ─── Toolbar (hanya layar) ─────────────────────────────────────── --}}
<div class="print-toolbar">
    <button class="btn-cetak" onclick="window.print()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Cetak / Simpan PDF
    </button>
    <button class="btn-tutup" onclick="window.close()">Tutup</button>
</div>

{{-- ─── Filter Bar (hanya layar) ─────────────────────────────────── --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('guru.journal.print-weekly-attendance') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;width:100%">
        @if(auth()->user()?->role !== 'guru' || isset($teachers))
        <div>
            <label>Guru</label>
            <select name="teacher_id" onchange="this.form.submit()">
                @foreach($teachers as $t)
                <option value="{{ $t->id }}" {{ $teacher->id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label>Pilih Tanggal Minggu</label>
            <input type="date" name="week_date" value="{{ $weekDate }}" onchange="this.form.submit()">
        </div>
        <div>
            <label>Kelas</label>
            <select name="class_id" onchange="this.form.submit()">
                @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

{{-- ─── Kop Surat ──────────────────────────────────────────────────── --}}
<div class="kop">
    <img src="{{ asset('img/logo-pemprov-bali.png') }}" alt="Logo Pemprov Bali">
    <div class="kop-tengah">
        <div class="instansi">PEMERINTAH PROVINSI BALI<br>DINAS PENDIDIKAN, KEPEMUDAAN, DAN OLAHRAGA</div>
        <div class="nama-sekolah">SMA Negeri 1 Gianyar</div>
        <div class="alamat">Jl. Ratna No. 1, Gianyar, Bali 80511 &nbsp;|&nbsp; Telp. (0361) 943036<br>
            Email: smansa.gianyar@gmail.com &nbsp;|&nbsp; Website: sman1-gianyar.sch.id</div>
    </div>
    <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo Sekolah">
</div>
<div style="border-bottom:1px solid #888;margin-bottom:8px;"></div>

{{-- ─── Judul ───────────────────────────────────────────────────────── --}}
<div class="judul-blok">
    <h1>REKAPITULASI ABSENSI SISWA DI KELAS (PERMINGGU)</h1>
    <p>Periode: {{ $startOfWeek->isoFormat('D MMMM Y') }} s/d {{ $endOfWeek->isoFormat('D MMMM Y') }}</p>
</div>

{{-- ─── Info Bar ───────────────────────────────────────────────────── --}}
<table class="info-table">
    <tr>
        <td class="label">Kelas</td>
        <td class="sep">:</td>
        <td class="val">{{ $selectedClass?->name ?? '—' }}</td>
        <td class="label" style="text-align:right;">Guru Pengajar</td>
        <td class="sep">:</td>
        <td class="val" style="width:200px;">{{ $teacher->name }}</td>
    </tr>
</table>

{{-- ─── Tabel Presensi Mingguan ──────────────────────────────────────── --}}
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
            <th colspan="6">Hari / Tanggal</th>
            <th colspan="4">Rekap Ketidakhadiran</th>
        </tr>
        <tr>
            @foreach($days as $day)
            <th class="day-col">
                {{ $day['day_name'] }}<br>
                <span style="font-size:7.5pt;font-weight:normal;">{{ \Carbon\Carbon::parse($day['date_str'])->format('d/m') }}</span>
            </th>
            @endforeach
            <th class="stat-col" style="background:#f3e8ff;color:#7e22ce;">S</th>
            <th class="stat-col" style="background:#e0f2fe;color:#0369a1;">I</th>
            <th class="stat-col" style="background:#fee2e2;color:#b91c1c;">A</th>
            <th class="stat-col" style="background:#ccfbf1;color:#0f766e;">D</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attendanceMatrix as $i => $row)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td class="center">{{ $row['student']->nis ?? '—' }}</td>
            <td><strong>{{ $row['student']->name }}</strong></td>
            
            @foreach($days as $day)
            @php $st = $row['days'][$day['date_str']] ?? 'H'; @endphp
            <td class="center">
                <span class="badge-st st-{{ $st }}">{{ $st }}</span>
            </td>
            @endforeach

            <td class="center font-bold" style="background:#faf5ff;">{{ $row['sakit'] ?: '-' }}</td>
            <td class="center font-bold" style="background:#f0f9ff;">{{ $row['izin'] ?: '-' }}</td>
            <td class="center font-bold" style="background:#fef2f2;">{{ $row['alpa'] ?: '-' }}</td>
            <td class="center font-bold" style="background:#f0fdfa;">{{ $row['dispen'] ?: '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<p style="font-size:7.5pt;color:#555;margin-bottom:8px;">
    * <strong>Keterangan Status:</strong> H = Hadir, S = Sakit, I = Izin, A = Alpa / Tanpa Keterangan, D = Dispensasi.
</p>

{{-- ─── Tanda Tangan Ganda ────────────────────────────────────────── --}}
<div class="ttd-wrap">
    <div class="ttd-box">
        <div class="ttd-lokasi">&nbsp;</div>
        <div class="ttd-jabatan">Mengetahui,<br>Kepala SMAN 1 Gianyar</div>
        <div class="ttd-nama">I Wayan Sudra Astra, S.Pd., M.Pd.</div>
        <div class="ttd-nip">NIP. 19710415 199703 1 007</div>
    </div>

    <div class="ttd-box">
        <div class="ttd-lokasi">Gianyar, {{ now()->isoFormat('D MMMM Y') }}</div>
        <div class="ttd-jabatan">Guru Pengajar / Wali Kelas,</div>
        <div class="ttd-nama">{{ $teacher->name }}</div>
        <div class="ttd-nip">NIP. {{ $teacher->nip ?? '—' }}</div>
    </div>
</div>

</body>
</html>
