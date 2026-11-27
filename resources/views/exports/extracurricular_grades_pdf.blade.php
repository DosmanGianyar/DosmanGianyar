<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Nilai Ekstrakurikuler {{ $extracurricular->name }}</title>
<style>
  @page { size: A4 portrait; margin: 14mm 15mm 14mm 15mm; }
  * { box-sizing: border-box; }
  body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; line-height: 1.3; margin: 0; padding: 0; }

  .kop-container { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
  .kop-container td { border: none; vertical-align: middle; }
  .kop-logo { width: 65px; text-align: center; }
  .kop-logo img { width: 58px; height: auto; max-height: 65px; object-fit: contain; }
  .kop-text { text-align: center; }
  .kop-text .l1 { font-size: 11pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
  .kop-text .l2 { font-size: 11pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
  .kop-text .l3 { font-size: 15pt; font-weight: bold; text-transform: uppercase; margin: 2px 0 1px; }
  .kop-text .l4 { font-size: 8.5pt; }
  .kop-text .l5 { font-size: 8pt; font-style: italic; }
  .kop-line { border-bottom: 3px double #000; margin-bottom: 14px; }

  .doc-header { text-align: center; margin-bottom: 12px; }
  .doc-title { font-size: 12pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
  .doc-subtitle { font-size: 11pt; font-weight: bold; margin-top: 2px; }
  .doc-filter { font-size: 9.5pt; color: #374151; margin-top: 2px; }

  .meta-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5pt; }
  .meta-table td { padding: 3px 4px; border: none; vertical-align: top; word-wrap: break-word; }
  .meta-label { width: 22%; font-weight: bold; color: #1f2937; }
  .meta-colon { width: 2%; font-weight: bold; text-align: center; }

  table.data-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 16px; font-size: 9pt; }
  table.data-table th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; padding: 6px 4px; border: 1px solid #1e3a8a; text-align: center; font-size: 8.5pt; word-wrap: break-word; }
  table.data-table td { padding: 5px 5px; border: 1px solid #9ca3af; vertical-align: top; word-wrap: break-word; }
  table.data-table tr:nth-child(even) td { background-color: #f8fafc; }
  .text-center { text-align: center; }

  .pred-badge { display: inline-block; padding: 2px 6px; font-size: 8.5pt; font-weight: bold; border-radius: 3px; }
  .pred-a { background-color: #dcfce7; color: #166534; }
  .pred-b { background-color: #dbeafe; color: #1e40af; }
  .pred-c { background-color: #fef3c7; color: #92400e; }
  .pred-d { background-color: #fee2e2; color: #991b1b; }

  .ttd-table { width: 100%; border-collapse: collapse; margin-top: 24px; page-break-inside: avoid; font-size: 10.5pt; }
  .ttd-table td { border: none; text-align: center; vertical-align: top; width: 50%; padding: 0 10px; }
  .ttd-space { height: 55px; }

  .footer-note { font-size: 8pt; color: #6b7280; text-align: right; border-top: 0.5px solid #d1d5db; padding-top: 4px; margin-top: 20px; font-style: italic; }
</style>
</head>
<body>

@include('partials.kop-surat-pdf')

<div class="doc-header">
  <div class="doc-title">DAFTAR NILAI EKSTRAKURIKULER</div>
  <div class="doc-subtitle">EKSTRAKURIKULER: {{ strtoupper($extracurricular->name) }}</div>
  <div class="doc-filter">
    Tahun Ajaran {{ $academicYear }} &bull; Semester {{ $semester == 1 ? '1 (Ganjil)' : '2 (Genap)' }}
  </div>
</div>

<table class="meta-table">
  <tr>
    <td class="meta-label">Nama Ekstrakurikuler</td>
    <td class="meta-colon">:</td>
    <td><strong>{{ $extracurricular->name }}</strong></td>
    <td class="meta-label">Total Anggota Aktif</td>
    <td class="meta-colon">:</td>
    <td><strong>{{ $rows->count() }} Orang</strong></td>
  </tr>
  <tr>
    <td class="meta-label">Pembina Ekstrakurikuler</td>
    <td class="meta-colon">:</td>
    <td>{{ $extracurricular->pembina_names }}</td>
    <td class="meta-label">Sudah Dinilai</td>
    <td class="meta-colon">:</td>
    <td><strong>{{ $rows->filter(fn ($r) => $r->grade !== null)->count() }} / {{ $rows->count() }}</strong></td>
  </tr>
</table>

<table class="data-table">
  <thead>
    <tr>
      <th style="width: 5%;">NO</th>
      <th style="width: 22%;">NAMA SISWA</th>
      <th style="width: 13%;">NISN / NIS</th>
      <th style="width: 9%;">KELAS</th>
      <th style="width: 10%;">HADIR</th>
      <th style="width: 14%;">PREDIKAT</th>
      <th style="width: 27%;">DESKRIPSI / KETERANGAN</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $i => $row)
    @php
      $m = $row->member;
      $g = $row->grade;
      $pred = $g?->predicate;
      $predClass = match($pred) {
        'A' => 'pred-a',
        'B' => 'pred-b',
        'C' => 'pred-c',
        'D' => 'pred-d',
        default => '',
      };
    @endphp
    <tr>
      <td class="text-center">{{ $i + 1 }}</td>
      <td><strong>{{ $m->user?->name ?? '—' }}</strong></td>
      <td class="text-center" style="font-family: monospace; font-size: 8.5pt;">{{ $m->user?->nisn ?? $m->user?->nis ?? '—' }}</td>
      <td class="text-center">{{ $m->user?->schoolClass?->name ?? '—' }}</td>
      <td class="text-center">{{ $row->total_sessions > 0 ? "{$row->hadir_count}/{$row->total_sessions}" : '—' }}</td>
      <td class="text-center">
        @if($pred)
          <span class="pred-badge {{ $predClass }}">{{ $pred }} ({{ $g->predicateLabel() }})</span>
        @else
          <span style="color: #9ca3af;">—</span>
        @endif
      </td>
      <td>{{ $g?->description ?: '—' }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="7" class="text-center" style="padding: 16px; color: #6b7280; font-style: italic;">
        Belum ada data anggota aktif yang terdaftar.
      </td>
    </tr>
    @endforelse
  </tbody>
</table>

@php
    $singlePembina = $extracurricular->teachers->first() ?? $extracurricular->pembina;
    $singlePembinaName = $singlePembina?->name ?? trim(explode(',', $extracurricular->pembina_names)[0] ?? '—');
    $singlePembinaNip  = $singlePembina?->nip ? "NIP. " . $singlePembina->nip : null;
@endphp

<table class="ttd-table">
  <tr>
    <td style="width: 50%;"></td>
    <td style="width: 50%; text-align: center;">
      Gianyar, {{ now()->locale('id')->isoFormat('D MMMM Y') }}<br>
      <strong>Pembina Ekstrakurikuler</strong>
      <div class="ttd-space"></div>
      <u><strong>{{ $singlePembinaName }}</strong></u>
      @if($singlePembinaNip)
        <br><span>{{ $singlePembinaNip }}</span>
      @endif
    </td>
  </tr>
</table>

<div class="footer-note">
  Dicetak otomatis melalui SIMAK DOSMAN SMA Negeri 1 Gianyar pada {{ now()->locale('id')->isoFormat('D MMMM Y HH:mm') }} WITA.
</div>

</body>
</html>
