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
    th { background: #dc2626; color: white; padding: 6px 8px; text-align: left; font-size: 10px; }
    td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; vertical-align: top; }
    tr:nth-child(even) td { background: #fef2f2; }
    .pending { color: #d97706; font-weight: bold; }
    .in_progress { color: #2563eb; font-weight: bold; }
    .resolved { color: #059669; font-weight: bold; }
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

<div class="title">Laporan Kerusakan Aset Sekolah</div>
<div class="subtitle">Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</div>

<div class="summary">
    <span><strong>Total:</strong> {{ $reports->count() }} laporan</span>
    <span><strong>Menunggu:</strong> {{ $reports->where('status','pending')->count() }}</span>
    <span><strong>Ditangani:</strong> {{ $reports->where('status','in_progress')->count() }}</span>
    <span><strong>Selesai:</strong> {{ $reports->where('status','resolved')->count() }}</span>
</div>

<table>
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:16%">Tanggal</th>
            <th style="width:22%">Aset</th>
            <th style="width:12%">Ruangan</th>
            <th style="width:16%">Pelapor</th>
            <th style="width:10%">Status</th>
            <th style="width:20%">Catatan Penanganan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($reports as $i => $report)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $report->created_at->isoFormat('D MMM Y') }}</td>
            <td>{{ $report->asset->name ?? '—' }}</td>
            <td>{{ $report->asset?->room?->name ?? '—' }}</td>
            <td>{{ $report->reporter->name ?? '—' }}</td>
            <td class="{{ $report->status }}">{{ $report->statusLabel() }}</td>
            <td style="font-size:9px">{{ $report->resolution_note ?? $report->description }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:16px">Tidak ada laporan kerusakan</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">Dicetak oleh SIMS — SMA Negeri 1 Gianyar · {{ now()->isoFormat('D MMMM Y HH:mm') }}</div>
</body>
</html>
