<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<script src="https://cdn.tailwindcss.com"></script>
<style>
    @page { size: A4 landscape; margin: 10mm 12mm 12mm 12mm; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    table { width: 100%; border-collapse: collapse; }

    thead th {
        background-color: #1e40af !important;
        color: #ffffff;
        font-size: 8px;
        font-weight: 700;
        padding: 5px 7px;
        border: 0.5px solid #1e3a8a;
        text-align: left;
    }
    thead th.c { text-align: center; }

    tbody td {
        padding: 4.5px 7px;
        border: 0.5px solid #e2e8f0;
        font-size: 8.5px;
        vertical-align: middle;
        color: #1f2937;
    }
    tbody td.c { text-align: center; }
    tbody tr:nth-child(even) td { background-color: #f8fafc !important; }

    .class-sep td {
        background-color: #eff6ff !important;
        border-top: 1px solid #bfdbfe;
        border-bottom: 1px solid #bfdbfe;
        color: #1d4ed8;
        font-weight: 700;
        font-size: 7.5px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 3px 7px;
    }

    .b-h { background:#dcfce7; color:#166534; font-weight:700; padding:1px 5px; border-radius:3px; font-size:8px; }
    .b-t { background:#fef9c3; color:#854d0e; font-weight:700; padding:1px 5px; border-radius:3px; font-size:8px; }
    .b-i { background:#dbeafe; color:#1e40af; font-weight:700; padding:1px 5px; border-radius:3px; font-size:8px; }
    .b-s { background:#f3e8ff; color:#6b21a8; font-weight:700; padding:1px 5px; border-radius:3px; font-size:8px; }
    .b-a { background:#fee2e2; color:#991b1b; font-weight:700; padding:1px 5px; border-radius:3px; font-size:8px; }
    .b-d { background:#ccfbf1; color:#065f46; font-weight:700; padding:1px 5px; border-radius:3px; font-size:8px; }

    .pct-high { color:#16a34a; font-weight:700; }
    .pct-mid  { color:#d97706; font-weight:700; }
    .pct-low  { color:#dc2626; font-weight:700; }
</style>
</head>
<body class="bg-white text-gray-800" style="font-family: Arial, sans-serif; font-size: 8px;">

{{-- ── KOP SURAT ─────────────────────────────────────────────────────────── --}}
<div style="text-align:center; padding-bottom:7px; margin-bottom:8px; border-bottom:4px double #000000;">
    <div style="display:inline-flex; align-items:center; gap:14px;">
        <img src="{{ public_path('img/logo-pemprov-bali.png') }}" style="width:58px; height:58px; object-fit:contain; flex-shrink:0;">
        <div style="text-align:center; line-height:1.45;">
            <div style="font-size:11px; font-weight:bold; font-family:'Times New Roman',serif;">PEMERINTAH PROVINSI BALI</div>
            <div style="font-size:11px; font-weight:bold; font-family:'Times New Roman',serif;">DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA</div>
            <div style="font-size:15px; font-weight:bold; font-family:'Times New Roman',serif;">SMA NEGERI 1 GIANYAR</div>
            <div style="font-size:8.5px; font-weight:bold; font-family:'Times New Roman',serif;">Jln. Ratna, Tegal Tugu Gianyar, Telp : (0361) 943034</div>
            <div style="font-size:7.5px;">Website: <span style="text-decoration:underline;">https://sman1-gianyar.sch.id</span> &nbsp; E-mail: <span style="text-decoration:underline;">sman1.gianyar1963@gmail.com</span></div>
            <div style="font-size:8px; font-weight:bold; margin-top:1px;">NPSN : 50102079</div>
        </div>
        <img src="{{ public_path('img/logo_sekolah.png') }}" style="width:58px; height:58px; object-fit:contain; flex-shrink:0;">
    </div>
</div>

{{-- ── JUDUL ─────────────────────────────────────────────────────────────── --}}
<div style="text-align:center; font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px; color:#111827;">
    Laporan Presensi Bulanan
</div>
<div style="text-align:center; font-size:7.5px; color:#6b7280; margin-bottom:8px;">
    Periode: <strong style="color:#111827;">{{ $monthName }}</strong>
    &nbsp;&middot;&nbsp;
    {{ $showClass ? 'Semua Kelas' : 'Kelas <strong style="color:#111827;">' . $className . '</strong>' }}
    &nbsp;&middot;&nbsp;
    Jumlah Siswa: <strong style="color:#111827;">{{ $total }}</strong>
    &nbsp;&middot;&nbsp;
    Hari Efektif: <strong style="color:#111827;">{{ $workingDays }} hari</strong>
    &nbsp;&middot;&nbsp;
    Rata-rata Kehadiran: <strong style="{{ $avgPct >= 90 ? 'color:#16a34a' : ($avgPct >= 75 ? 'color:#d97706' : 'color:#dc2626') }}">{{ $avgPct }}%</strong>
</div>

{{-- ── TABEL ─────────────────────────────────────────────────────────────── --}}
<table>
    <thead>
        <tr>
            <th style="width:22px">#</th>
            <th>Nama Siswa</th>
            <th style="width:80px">NIS</th>
            @if ($showClass) <th style="width:75px">Kelas</th> @endif
            <th class="c" style="width:38px" title="Hadir">H</th>
            <th class="c" style="width:48px" title="Terlambat">T</th>
            <th class="c" style="width:36px" title="Izin">I</th>
            <th class="c" style="width:40px" title="Sakit">S</th>
            <th class="c" style="width:36px" title="Alpa">A</th>
            <th class="c" style="width:50px" title="Dispensasi">D</th>
            <th class="c" style="width:60px">% Hadir</th>
        </tr>
    </thead>
    <tbody>
        @php $prevClass = null; $no = 1; @endphp
        @foreach ($rows as $row)
            @if ($showClass && $row['class'] !== $prevClass)
                <tr class="class-sep">
                    <td colspan="{{ $showClass ? 11 : 10 }}">Kelas {{ $row['class'] }}</td>
                </tr>
                @php $prevClass = $row['class']; @endphp
            @endif
            <tr>
                <td class="c" style="color:#d1d5db; font-size:7.5px;">{{ $no++ }}</td>
                <td style="font-weight:600;">{{ $row['name'] }}</td>
                <td style="color:#9ca3af; font-size:7.5px; font-family:monospace;">{{ $row['nis'] }}</td>
                @if ($showClass) <td style="color:#6b7280;">{{ $row['class'] }}</td> @endif
                <td class="c"><span class="b-h">{{ $row['hadir'] }}</span></td>
                <td class="c"><span class="b-t">{{ $row['terlambat'] }}</span></td>
                <td class="c"><span class="b-i">{{ $row['izin'] }}</span></td>
                <td class="c"><span class="b-s">{{ $row['sakit'] }}</span></td>
                <td class="c"><span class="b-a">{{ $row['alpa'] }}</span></td>
                <td class="c"><span class="b-d">{{ $row['dispensasi'] }}</span></td>
                <td class="c">
                    <span class="{{ $row['pct'] >= 90 ? 'pct-high' : ($row['pct'] >= 75 ? 'pct-mid' : 'pct-low') }}">
                        {{ $row['pct'] }}%
                    </span>
                </td>
            </tr>
        @endforeach

        @if (empty($rows))
            <tr><td colspan="{{ $showClass ? 11 : 10 }}" style="text-align:center;color:#9ca3af;padding:20px;">Tidak ada data presensi untuk periode ini.</td></tr>
        @endif
    </tbody>
</table>

{{-- ── KETERANGAN ────────────────────────────────────────────────────────── --}}
<div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:7px; font-size:7px;">
    <span style="font-weight:bold; color:#374151;">Keterangan:</span>
    @foreach([
        ['Hadir',       '#dcfce7', '#166534'],
        ['Terlambat',   '#fef9c3', '#854d0e'],
        ['Izin',        '#dbeafe', '#1e40af'],
        ['Sakit',       '#f3e8ff', '#6b21a8'],
        ['Alpa',        '#fee2e2', '#991b1b'],
        ['Dispensasi',  '#ccfbf1', '#065f46'],
    ] as [$label, $bg, $col])
    <span style="display:flex; align-items:center; gap:3px;">
        <span style="display:inline-block; width:10px; height:10px; background:{{ $bg }}; border:0.5px solid #d1d5db; border-radius:2px;"></span>
        <span style="color:#374151;">{{ $label }}</span>
    </span>
    @endforeach
    <span style="color:#9ca3af; margin-left:4px;">* % Hadir = (Hadir + Terlambat + Dispensasi) / Hari Efektif</span>
</div>

{{-- ── FOOTER ─────────────────────────────────────────────────────────────── --}}
<div style="display:flex; justify-content:space-between; margin-top:6px; padding-top:4px; border-top:0.5px solid #e5e7eb; font-size:7px; color:#9ca3af;">
    <span>Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} WIB</span>
    <span>SIMS — SMA Negeri 1 Gianyar</span>
</div>

</body>
</html>
