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
    td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
    tr:nth-child(even) td { background: #f8fafc; }
    .baik { color: #059669; font-weight: bold; }
    .rusak_ringan { color: #d97706; font-weight: bold; }
    .rusak_berat { color: #dc2626; font-weight: bold; }
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

<div class="title">Rekap Data Aset Sekolah</div>
<div class="subtitle">
    Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}
    @if($roomName) · Ruangan: {{ $roomName }} @endif
</div>

<div class="summary">
    <span><strong>Total:</strong> {{ $assets->count() }} aset</span>
    <span><strong>Baik:</strong> {{ $assets->where('condition','baik')->count() }}</span>
    <span><strong>Rusak Ringan:</strong> {{ $assets->where('condition','rusak_ringan')->count() }}</span>
    <span><strong>Rusak Berat:</strong> {{ $assets->where('condition','rusak_berat')->count() }}</span>
</div>

<table>
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:25%">Nama Aset</th>
            <th style="width:14%">Kategori</th>
            <th style="width:14%">Ruangan</th>
            <th style="width:10%">Kondisi</th>
            <th style="width:8%">Jml</th>
            <th style="width:9%">Th. Beli</th>
            <th style="width:16%">Kode QR</th>
        </tr>
    </thead>
    <tbody>
        @forelse($assets as $i => $asset)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $asset->name }}</td>
            <td>{{ $asset->categoryLabel() }}</td>
            <td>{{ $asset->room?->name ?? '—' }}</td>
            <td class="{{ $asset->condition }}">{{ $asset->conditionLabel() }}</td>
            <td>{{ $asset->quantity ?? 1 }}</td>
            <td>{{ $asset->purchase_year ?? '—' }}</td>
            <td style="font-size:8px; color:#6b7280;">{{ $asset->qr_code }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:16px">Tidak ada data aset</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">Dicetak oleh SIMS — SMA Negeri 1 Gianyar · {{ now()->isoFormat('D MMMM Y HH:mm') }}</div>
</body>
</html>
