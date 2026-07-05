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
    .prestasi { color: #059669; font-weight: bold; }
    .pelanggaran { color: #dc2626; font-weight: bold; }
    .footer { margin-top: 16px; font-size: 9px; color: #9ca3af; text-align: right; }
    .summary { margin-bottom: 10px; font-size: 10px; }
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

<div class="title">Rekap Catatan Perilaku Siswa</div>
<div class="subtitle">
    Periode: {{ \Carbon\Carbon::parse($month . '-01')->isoFormat('MMMM Y') }}
    @if($className) · Kelas: {{ $className }} @endif
</div>

<div class="summary">
    Total: <strong>{{ $records->count() }}</strong> catatan ·
    Catatan Positif: <strong>{{ $records->where('category.type','prestasi')->count() }}</strong> ·
    Catatan Negatif: <strong>{{ $records->where('category.type','pelanggaran')->count() }}</strong>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nama Siswa</th>
            <th>NIS</th>
            <th>Kelas</th>
            <th>Kategori</th>
            <th>Tipe</th>
            <th>Dicatat Oleh</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $r)
        @php $isPrestasi = $r->category?->type === 'prestasi'; @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $r->student?->name }}</td>
            <td>{{ $r->student?->nis }}</td>
            <td>{{ $r->student?->schoolClass?->name }}</td>
            <td>{{ $r->category?->name }}</td>
            <td class="{{ $isPrestasi ? 'prestasi' : 'pelanggaran' }}">{{ $isPrestasi ? 'Catatan Positif' : 'Catatan Negatif' }}</td>
            <td>{{ $r->teacher?->name }}</td>
            <td>{{ $r->created_at->isoFormat('D MMM Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:12px;">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} · SIMS SMA Negeri 1 Gianyar
</div>

</body>
</html>
