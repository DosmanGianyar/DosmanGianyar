<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1f2937; }

  .header { text-align: center; padding: 16px 0 12px; border-bottom: 2px solid #dc2626; margin-bottom: 16px; }
  .header .school { font-size: 13px; font-weight: bold; color: #dc2626; letter-spacing: 0.5px; }
  .header .title  { font-size: 16px; font-weight: bold; margin: 4px 0 2px; }
  .header .sub    { font-size: 11px; color: #6b7280; }

  .info-grid { display: flex; gap: 8px; margin-bottom: 16px; }
  .info-box  { flex: 1; padding: 8px 12px; background: #fef2f2; border-radius: 6px; border-left: 3px solid #dc2626; }
  .info-box .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
  .info-box .value { font-size: 14px; font-weight: bold; color: #991b1b; margin-top: 2px; }

  .alert { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 11px; color: #92400e; }

  table { width: 100%; border-collapse: collapse; }
  thead tr th { background: #dc2626; color: #fff; font-size: 10px; padding: 8px 10px; text-align: left; }
  tbody tr td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
  tbody tr:nth-child(even) td { background: #fef2f2; }

  .footer { margin-top: 24px; text-align: right; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>

<div class="header">
  <div class="school">SMA NEGERI 1 GIANYAR · SIMS</div>
  <div class="title">Laporan Siswa Tanpa Ekstrakurikuler</div>
  <div class="sub">{{ $className ? 'Kelas: ' . $className : 'Semua Kelas' }} · Dicetak {{ now()->locale('id')->isoFormat('D MMMM Y') }}</div>
</div>

<div class="info-grid">
  <div class="info-box">
    <div class="label">Total Siswa Tanpa Ekstra</div>
    <div class="value">{{ $students->count() }}</div>
  </div>
  @if($className)
  <div class="info-box">
    <div class="label">Filter Kelas</div>
    <div class="value">{{ $className }}</div>
  </div>
  @endif
</div>

<div class="alert">
  ⚠ Berikut adalah daftar siswa yang <strong>belum memiliki keanggotaan aktif</strong> di ekstrakurikuler manapun. Mohon ditindaklanjuti oleh wali kelas masing-masing.
</div>

<table>
  <thead>
    <tr>
      <th style="width:36px">No</th>
      <th>Nama Siswa</th>
      <th>NIS</th>
      <th>Kelas</th>
    </tr>
  </thead>
  <tbody>
    @foreach($students as $i => $student)
    <tr>
      <td style="text-align:center">{{ $i + 1 }}</td>
      <td>{{ $student->name }}</td>
      <td>{{ $student->nis ?? '—' }}</td>
      <td>{{ $student->schoolClass?->name ?? '—' }}</td>
    </tr>
    @endforeach
    @if($students->isEmpty())
    <tr>
      <td colspan="4" style="text-align:center; padding: 20px; color: #9ca3af;">
        Semua siswa sudah memiliki ekstrakurikuler 🎉
      </td>
    </tr>
    @endif
  </tbody>
</table>

<div class="footer">
  Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} · SIMS SMA Negeri 1 Gianyar
</div>

</body>
</html>
