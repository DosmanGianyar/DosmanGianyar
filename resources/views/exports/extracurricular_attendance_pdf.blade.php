<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1f2937; }

  .header { text-align: center; padding: 16px 0 12px; border-bottom: 2px solid #1d4ed8; margin-bottom: 16px; }
  .header .school { font-size: 13px; font-weight: bold; color: #1d4ed8; letter-spacing: 0.5px; }
  .header .title  { font-size: 16px; font-weight: bold; margin: 4px 0 2px; }
  .header .sub    { font-size: 11px; color: #6b7280; }

  .info-grid { display: flex; gap: 0; margin-bottom: 16px; }
  .info-box  { flex: 1; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; margin-right: 8px; }
  .info-box:last-child { margin-right: 0; }
  .info-box .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
  .info-box .value { font-size: 12px; font-weight: bold; color: #1d4ed8; margin-top: 2px; }

  .summary { display: flex; gap: 8px; margin-bottom: 16px; }
  .summary-card { flex: 1; text-align: center; padding: 8px; border-radius: 6px; }
  .summary-card.hadir { background: #dcfce7; color: #14532d; }
  .summary-card.alpa  { background: #fee2e2; color: #7f1d1d; }
  .summary-card .count { font-size: 22px; font-weight: bold; }
  .summary-card .label { font-size: 10px; }

  table { width: 100%; border-collapse: collapse; }
  thead tr th { background: #1d4ed8; color: #fff; font-size: 10px; padding: 8px 10px; text-align: left; }
  tbody tr td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; }
  tbody tr:nth-child(even) td { background: #f9fafb; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; }
  .badge-hadir { background: #dcfce7; color: #14532d; }
  .badge-alpa  { background: #fee2e2; color: #7f1d1d; }
  .badge-ketua { background: #fef3c7; color: #78350f; }

  .footer { margin-top: 24px; text-align: right; font-size: 9px; color: #9ca3af; }
</style>
</head>
<body>

<div class="header">
  <div class="school">SMA NEGERI 1 GIANYAR · SIMS</div>
  <div class="title">Rekap Absensi Ekstrakurikuler</div>
  <div class="sub">{{ $session->extracurricular->name ?? '—' }}</div>
</div>

<div class="info-grid">
  <div class="info-box">
    <div class="label">Sesi</div>
    <div class="value">{{ $session->title }}</div>
  </div>
  <div class="info-box">
    <div class="label">Tanggal</div>
    <div class="value">{{ $session->session_date->locale('id')->isoFormat('D MMMM Y') }}</div>
  </div>
  <div class="info-box">
    <div class="label">Waktu</div>
    <div class="value">{{ substr($session->start_time, 0, 5) }} – {{ substr($session->end_time, 0, 5) }}</div>
  </div>
  @if($session->location)
  <div class="info-box">
    <div class="label">Lokasi</div>
    <div class="value">{{ $session->location }}</div>
  </div>
  @endif
</div>

<div class="summary">
  <div class="summary-card hadir">
    <div class="count">{{ $hadirCount }}</div>
    <div class="label">Hadir</div>
  </div>
  <div class="summary-card alpa">
    <div class="count">{{ $alpaCount }}</div>
    <div class="label">Alpa</div>
  </div>
  <div class="summary-card" style="background:#f3f4f6;color:#374151;">
    <div class="count">{{ $totalCount }}</div>
    <div class="label">Total Anggota</div>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th style="width:36px">No</th>
      <th>Nama Siswa</th>
      <th>NIS</th>
      <th>Kelas</th>
      <th>Peran</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $i => $row)
    <tr>
      <td style="text-align:center">{{ $i + 1 }}</td>
      <td>{{ $row->user?->name }}</td>
      <td>{{ $row->user?->nis ?? '—' }}</td>
      <td>{{ $row->user?->schoolClass?->name ?? '—' }}</td>
      <td>
        @if($row->role === 'ketua')
          <span class="badge badge-ketua">Ketua</span>
        @else
          Anggota
        @endif
      </td>
      <td>
        @if($row->attendance_status === 'hadir')
          <span class="badge badge-hadir">Hadir</span>
        @else
          <span class="badge badge-alpa">Alpa</span>
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="footer">
  Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} · SIMS SMA Negeri 1 Gianyar
</div>

</body>
</html>
