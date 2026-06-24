<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 15mm 18mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; }

    .header { display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 14px; }
    .logo { width: 48px; height: 48px; }
    .school-name { font-size: 14px; font-weight: bold; color: #1d4ed8; }
    .school-sub { font-size: 10px; color: #6b7280; margin-top: 2px; }

    .title { text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
    .subtitle { text-align: center; font-size: 10px; color: #6b7280; margin-bottom: 14px; }

    .summary { margin-bottom: 12px; font-size: 10px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 7px 10px; }
    .summary span { display: inline-block; margin-right: 20px; }

    table { width: 100%; border-collapse: collapse; }
    th { background: #1d4ed8; color: white; padding: 7px 10px; text-align: left; font-size: 10px; font-weight: bold; }
    td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; font-size: 10px; vertical-align: middle; }
    tr:nth-child(even) td { background: #f8fafc; }
    tr:last-child td { border-bottom: none; }

    .status-hadir     { color: #059669; font-weight: bold; }
    .status-terlambat { color: #d97706; font-weight: bold; }
    .status-alpa      { color: #dc2626; font-weight: bold; }
    .status-izin      { color: #2563eb; font-weight: bold; }
    .status-sakit     { color: #7c3aed; font-weight: bold; }
    .status-dispensasi{ color: #0891b2; font-weight: bold; }

    .footer { margin-top: 18px; font-size: 9px; color: #9ca3af; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    .empty-row td { text-align: center; color: #9ca3af; padding: 16px; }
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

<div class="title">Rekap Absensi Siswa</div>
<div class="subtitle">
    Periode: {{ \Carbon\Carbon::parse($month . '-01')->isoFormat('MMMM Y') }}
    @if($className) · Kelas: {{ $className }} @endif
    @if($statusFilter) · Status: {{ ucfirst($statusFilter) }} @endif
</div>

<div class="summary">
    <span><strong>Total Data:</strong> {{ $records->count() }}</span>
    <span><strong>Hadir:</strong> {{ $records->where('status', 'hadir')->count() }}</span>
    <span><strong>Terlambat:</strong> {{ $records->where('status', 'terlambat')->count() }}</span>
    <span><strong>Alpa:</strong> {{ $records->where('status', 'alpa')->count() }}</span>
    <span><strong>Izin/Sakit:</strong> {{ $records->whereIn('status', ['izin', 'sakit'])->count() }}</span>
</div>

<table>
    <thead>
        <tr>
            <th style="width:28px">#</th>
            <th>Nama Siswa</th>
            <th style="width:80px">NIS</th>
            <th style="width:80px">Kelas</th>
            <th style="width:90px">Tanggal</th>
            <th style="width:70px">Jam Masuk</th>
            <th style="width:80px">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $r)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $r->student?->name ?? '—' }}</td>
            <td>{{ $r->student?->nis ?? '—' }}</td>
            <td>{{ $r->student?->schoolClass?->name ?? '—' }}</td>
            <td>{{ $r->date->isoFormat('D MMM Y') }}</td>
            <td>{{ $r->check_in_time ?? '—' }}</td>
            <td class="status-{{ $r->status }}">{{ ucfirst($r->status) }}</td>
        </tr>
        @empty
        <tr class="empty-row"><td colspan="7">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} · SIMS SMA Negeri 1 Gianyar
</div>

</body>
</html>
