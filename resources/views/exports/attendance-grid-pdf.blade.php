<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<style>
    @page { size: A4 landscape; margin: 10mm 12mm 12mm 12mm; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    /* Status colors */
    .st-H { background-color: #86efac !important; }
    .st-T { background-color: #fcd34d !important; }
    .st-A { background-color: #fca5a5 !important; }
    .st-I { background-color: #93c5fd !important; }
    .st-S { background-color: #c4b5fd !important; }
    .st-D { background-color: #6ee7b7 !important; }
    .st-W { background-color: #e2e8f0 !important; }

    table { width: 100%; border-collapse: collapse; table-layout: fixed; }

    thead tr:first-child th {
        background-color: #1e40af !important;
        color: #ffffff;
        font-size: 8px; font-weight: 700;
        padding: 4px 2px;
        border: 0.5px solid #1e3a8a;
    }
    thead tr:last-child th {
        background-color: #1d4ed8 !important;
        color: #bfdbfe;
        font-size: 6.5px; font-weight: 700;
        padding: 2px 0;
        border: 0.5px solid #1e3a8a;
        text-align: center;
    }
    thead tr:last-child th.wknd {
        background-color: #64748b !important;
        color: #e2e8f0;
    }
    tbody td {
        border: 0.5px solid #e2e8f0;
        height: 14px; line-height: 14px;
        text-align: center; font-size: 6.5px;
        overflow: hidden; padding: 0;
    }
    tbody td.cell-info { padding: 0 3px; text-align: left; color: #374151; }
    tbody td.cell-center { text-align: center; }
    tbody tr:nth-child(even) td.cell-info { background-color: #f8fafc !important; }
    .hdr-col { background-color: #dcfce7 !important; color: #166534; font-weight: 700; }
    .alp-col { background-color: #fee2e2 !important; color: #991b1b; font-weight: 700; }
</style>
</head>
<body class="bg-white text-gray-800" style="font-family: Arial, sans-serif; font-size: 7px;">

{{-- ── KOP SURAT ─────────────────────────────────────────────────────────── --}}
<div style="text-align:center; padding-bottom:7px; margin-bottom:6px; border-bottom:4px double #000000;">
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

{{-- ── Judul Laporan ─────────────────────────────────────────────────────── --}}
@php
    use Carbon\Carbon;
    $start       = Carbon::parse($month . '-01');
    $daysInMonth = $start->daysInMonth;
    $dateColW    = round(172 / $daysInMonth, 2);
@endphp

<div style="text-align:center; font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px; color:#111827;">
    Rekap Absensi Siswa
</div>
<div style="text-align:center; font-size:7.5px; color:#6b7280; margin-bottom:5px;">
    Kelas: <strong style="color:#111827;">{{ $className }}</strong>
    &nbsp;&middot;&nbsp;
    Periode: <strong style="color:#111827;">{{ $start->isoFormat('MMMM Y') }}</strong>
    &nbsp;&middot;&nbsp;
    Jumlah Siswa: <strong style="color:#111827;">{{ $students->count() }}</strong>
</div>

{{-- ── Tabel Grid ────────────────────────────────────────────────────────── --}}
<table>
    <colgroup>
        <col style="width:5mm">
        <col style="width:15mm">
        <col style="width:50mm">
        <col style="width:16mm">
        @for($d = 1; $d <= $daysInMonth; $d++)
            <col style="width:{{ $dateColW }}mm">
        @endfor
        <col style="width:8mm">
        <col style="width:8mm">
    </colgroup>

    <thead>
        <tr>
            <th style="text-align:left; padding-left:3px;" rowspan="2">No</th>
            <th style="text-align:left; padding-left:3px;" rowspan="2">Kelas</th>
            <th style="text-align:left; padding-left:3px;" rowspan="2">Nama Siswa</th>
            <th style="text-align:center;" rowspan="2">NISN / NIS</th>
            <th style="text-align:center; font-size:9px; letter-spacing:0.3px;" colspan="{{ $daysInMonth }}">
                {{ $start->isoFormat('MMMM Y') }}
            </th>
            <th style="text-align:center; background-color:#064e3b !important; font-size:6.5px;" rowspan="2">Hdr</th>
            <th style="text-align:center; background-color:#7f1d1d !important; font-size:6.5px;" rowspan="2">Alp</th>
        </tr>
        <tr>
            @for($d = 1; $d <= $daysInMonth; $d++)
            @php $isWknd = $start->copy()->setDay($d)->isWeekend(); @endphp
            <th class="{{ $isWknd ? 'wknd' : '' }}">{{ $d }}</th>
            @endfor
        </tr>
    </thead>

    <tbody>
        @foreach($students as $i => $student)
        @php
            $dayMap     = $grid[$student->id] ?? [];
            $hadirCount = 0;
            $alpaCount  = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $s = $dayMap[$d] ?? null;
                if (in_array($s, ['hadir','terlambat'])) $hadirCount++;
                if ($s === 'alpa') $alpaCount++;
            }
        @endphp
        <tr>
            <td class="cell-info cell-center">{{ $i + 1 }}</td>
            <td class="cell-info" style="font-size:6px;">{{ $student->schoolClass?->name ?? '' }}</td>
            <td class="cell-info">{{ $student->name }}</td>
            <td class="cell-info cell-center" style="font-size:6px;">{{ $student->nis ?? '—' }}</td>

            @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $status = $dayMap[$d] ?? null;
                $isWknd = $start->copy()->setDay($d)->isWeekend();
                $cls = $isWknd ? 'st-W' : match($status) {
                    'hadir'      => 'st-H',
                    'terlambat'  => 'st-T',
                    'alpa'       => 'st-A',
                    'izin'       => 'st-I',
                    'sakit'      => 'st-S',
                    'dispensasi' => 'st-D',
                    default      => '',
                };
            @endphp
            <td class="{{ $cls }}"></td>
            @endfor

            <td class="hdr-col" style="text-align:center; font-size:7px;">{{ $hadirCount }}</td>
            <td class="alp-col" style="text-align:center; font-size:7px;">{{ $alpaCount ?: '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ── Keterangan ────────────────────────────────────────────────────────── --}}
<div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:6px; font-size:7px;">
    <span style="font-weight:bold; color:#374151;">Keterangan:</span>
    @foreach([
        ['Hadir',        '#86efac'],
        ['Terlambat',    '#fcd34d'],
        ['Alpa',         '#fca5a5'],
        ['Izin',         '#93c5fd'],
        ['Sakit',        '#c4b5fd'],
        ['Dispensasi',   '#6ee7b7'],
        ['Libur/Minggu', '#e2e8f0'],
    ] as [$label, $color])
    <span style="display:flex; align-items:center; gap:3px;">
        <span style="display:inline-block; width:10px; height:10px; background:{{ $color }}; border:0.5px solid #d1d5db; border-radius:2px;"></span>
        <span style="color:#374151;">{{ $label }}</span>
    </span>
    @endforeach
    <span style="color:#9ca3af; margin-left:4px;">Hdr = Total Hadir &nbsp;&middot;&nbsp; Alp = Total Alpa</span>
</div>

{{-- ── Footer ────────────────────────────────────────────────────────────── --}}
<div style="display:flex; justify-content:space-between; margin-top:5px; padding-top:4px; border-top:0.5px solid #e5e7eb; font-size:6.5px; color:#9ca3af;">
    <span>Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} WIB</span>
    <span>SIMS — SMA Negeri 1 Gianyar</span>
</div>

</body>
</html>
