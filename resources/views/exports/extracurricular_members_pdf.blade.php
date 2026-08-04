<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1f2937; }

  .header { text-align: center; padding: 16px 0 12px; border-bottom: 2px solid #7c3aed; margin-bottom: 16px; }
  .header .school { font-size: 13px; font-weight: bold; color: #7c3aed; letter-spacing: 0.5px; }
  .header .title  { font-size: 16px; font-weight: bold; margin: 4px 0 2px; }
  .header .sub    { font-size: 12px; color: #6b7280; }

  .info-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 12px; }
  .info-box  { padding: 8px 12px; background: #f5f3ff; border-radius: 6px; border-left: 3px solid #7c3aed; }
  .info-box .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
  .info-box .value { font-size: 12px; font-weight: bold; color: #5b21b6; margin-top: 2px; }

  .summary-card { text-align: center; padding: 8px; border-radius: 6px; background: #f5f3ff; }
  .summary-card .count { font-size: 20px; font-weight: bold; color: #7c3aed; }
  .summary-card .label { font-size: 10px; color: #6b7280; }

  table.data-table { width: 100%; border-collapse: collapse; }
  table.data-table thead tr th { background: #7c3aed; color: #fff; font-size: 10px; padding: 8px 10px; text-align: left; }
  table.data-table tbody tr td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
  table.data-table tbody tr:nth-child(even) td { background: #faf5ff; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; }
  .badge-ketua   { background: #fef3c7; color: #78350f; }
  .badge-anggota { background: #ede9fe; color: #5b21b6; }

  .footer { margin-top: 24px; text-align: right; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>

<div class="header">
  <div class="school">SMA NEGERI 1 GIANYAR · SIMS</div>
  <div class="title">Daftar Anggota Ekstrakurikuler</div>
  <div class="sub">{{ $extracurricular->name }}</div>
</div>

<table class="info-table">
  <tr>
    <td style="width:33.3%;">
      <div class="info-box">
        <div class="label">Nama Ekstra</div>
        <div class="value">{{ $extracurricular->name }}</div>
      </div>
    </td>
    <td style="width:33.3%;">
      <div class="info-box">
        <div class="label">Guru Pembina</div>
        <div class="value">{{ $extracurricular->pembina?->name ?? '—' }}</div>
      </div>
    </td>
    <td style="width:33.3%;">
      <div class="info-box">
        <div class="label">Kuota</div>
        <div class="value">{{ $extracurricular->max_members ?? 'Tidak Terbatas' }}</div>
      </div>
    </td>
  </tr>
</table>

<table class="info-table" style="margin-bottom:16px;">
  <tr>
    <td style="width:33.3%;">
      <div class="summary-card">
        <div class="count">{{ $members->count() }}</div>
        <div class="label">Total Anggota Aktif</div>
      </div>
    </td>
    <td style="width:33.3%;">
      <div class="summary-card">
        <div class="count">{{ $members->where('role', 'ketua')->count() }}</div>
        <div class="label">Ketua</div>
      </div>
    </td>
    <td style="width:33.3%;">
      <div class="summary-card">
        <div class="count">{{ $members->where('role', 'member')->count() }}</div>
        <div class="label">Anggota</div>
      </div>
    </td>
  </tr>
</table>

<table class="data-table">
  <thead>
    <tr>
      <th style="width:36px">No</th>
      <th>Nama Siswa</th>
      <th>NIS</th>
      <th>Kelas</th>
      <th>Peran</th>
      <th>Tgl. Bergabung</th>
    </tr>
  </thead>
  <tbody>
    @foreach($members as $i => $member)
    <tr>
      <td style="text-align:center">{{ $i + 1 }}</td>
      <td>{{ $member->user?->name ?? '—' }}</td>
      <td>{{ $member->user?->nis ?? '—' }}</td>
      <td>{{ $member->user?->schoolClass?->name ?? '—' }}</td>
      <td>
        @if($member->role === 'ketua')
          <span class="badge badge-ketua">Ketua</span>
        @else
          <span class="badge badge-anggota">Anggota</span>
        @endif
      </td>
      <td>{{ $member->created_at?->format('d M Y') ?? '—' }}</td>
    </tr>
    @endforeach
    @if($members->isEmpty())
    <tr>
      <td colspan="6" style="text-align:center; padding: 20px; color: #9ca3af;">
        Belum ada anggota aktif
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
