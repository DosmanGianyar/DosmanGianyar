<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 15mm 18mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; }
    .header { display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 12px; }
    .logo { width: 48px; height: 48px; }
    .school-name { font-size: 14px; font-weight: bold; color: #1d4ed8; }
    .school-sub { font-size: 10px; color: #6b7280; }
    .title { text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 4px; text-transform: uppercase; }
    .subtitle { text-align: center; font-size: 10px; color: #6b7280; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1d4ed8; color: white; padding: 6px 8px; text-align: left; font-size: 10px; }
    td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; vertical-align: top; }
    tr:nth-child(even) td { background: #f8fafc; }
    .hadir       { color: #059669; font-weight: bold; }
    .tidak_hadir { color: #dc2626; font-weight: bold; }
    .izin        { color: #2563eb; font-weight: bold; }
    .sakit       { color: #7c3aed; font-weight: bold; }
    .footer { margin-top: 16px; font-size: 9px; color: #9ca3af; text-align: right; }
    .summary { margin-bottom: 10px; font-size: 10px; }
    .summary span { display: inline-block; margin-right: 16px; }
</style>
</head>
<body>

<div class="header">
    <img class="logo" src="{{ public_path('img/logo_sekolah.png') }}">
    <div>
        <div class="school-name">SMA Negeri 1 Gianyar</div>
        <div class="school-sub">SIMS — Sistem Informasi Manajemen Sekolah</div>
    </div>
</div>

<div class="title">Rekap Absensi Guru Mengajar</div>
<div class="subtitle">
    Periode: {{ \Carbon\Carbon::parse($month . '-01')->isoFormat('MMMM Y') }}
    @if($teacherName) · Guru: {{ $teacherName }} @endif
</div>

<div class="summary">
    <span><strong>Total:</strong> {{ $records->count() }} sesi</span>
    <span><strong>Hadir:</strong> {{ $records->where('status','hadir')->count() }}</span>
    <span><strong>Tidak Hadir:</strong> {{ $records->where('status','tidak_hadir')->count() }}</span>
    <span><strong>Izin:</strong> {{ $records->where('status','izin')->count() }}</span>
    <span><strong>Sakit:</strong> {{ $records->where('status','sakit')->count() }}</span>
</div>

<table>
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:18%">Nama Guru</th>
            <th style="width:13%">Tanggal</th>
            <th style="width:8%">Jam</th>
            <th style="width:10%">Kelas</th>
            <th style="width:18%">Mata Pelajaran</th>
            <th style="width:9%">Mulai</th>
            <th style="width:9%">Selesai</th>
            <th style="width:11%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $r)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $r->teacher?->name ?? '—' }}</td>
            <td>{{ $r->date->isoFormat('D MMM Y') }}</td>
            <td style="text-align:center">{{ $r->period }}</td>
            <td>{{ $r->schoolClass?->name ?? '—' }}</td>
            <td>{{ $r->subject?->name ?? '—' }}</td>
            <td>{{ $r->start_time ? substr($r->start_time, 0, 5) : '—' }}</td>
            <td>{{ $r->end_time   ? substr($r->end_time, 0, 5)   : '—' }}</td>
            <td class="{{ $r->status }}">{{ $r->statusLabel() }}</td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center;color:#9ca3af;padding:16px">Tidak ada data absensi guru</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} · SIMS SMA Negeri 1 Gianyar</div>
</body>
</html>
