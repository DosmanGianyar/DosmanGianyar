<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Anggota Ekstrakurikuler {{ $extracurricular->name }}</title>
<style>
  @page { size: A4 portrait; margin: 12mm 15mm 15mm 15mm; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; line-height: 1.3; }

  /* ── Kop Surat Formal ────────────────────────────────────────── */
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

  /* ── Judul Dokumen ────────────────────────────────────────────── */
  .doc-header { text-align: center; margin-bottom: 14px; }
  .doc-title { font-size: 12pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
  .doc-subtitle { font-size: 11pt; font-weight: bold; margin-top: 2px; }
  .doc-filter { font-size: 9.5pt; font-style: italic; color: #374151; margin-top: 2px; }

  /* ── Info & Summary Table ─────────────────────────────────────── */
  .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10pt; }
  .meta-table td { padding: 4px 6px; border: none; vertical-align: top; }
  .meta-label { width: 130px; font-weight: bold; color: #1f2937; }
  .meta-colon { width: 10px; font-weight: bold; text-align: center; }

  /* ── Data Table ───────────────────────────────────────────────── */
  table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10pt; }
  table.data-table th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e3a8a; text-align: center; font-size: 9.5pt; }
  table.data-table td { padding: 5px 8px; border: 1px solid #9ca3af; vertical-align: middle; }
  table.data-table tr:nth-child(even) td { background-color: #f8fafc; }
  .text-center { text-align: center; }
  .text-left { text-align: left; }
  .badge-role { display: inline-block; padding: 1px 6px; font-size: 8.5pt; font-weight: bold; border-radius: 3px; }
  .role-ketua { background-color: #fef3c7; color: #92400e; border: 0.5px solid #f59e0b; }
  .role-anggota { background-color: #e0e7ff; color: #3730a3; border: 0.5px solid #6366f1; }

  /* ── Tanda Tangan Formal ──────────────────────────────────────── */
  .ttd-table { width: 100%; border-collapse: collapse; margin-top: 24px; page-break-inside: avoid; font-size: 10.5pt; }
  .ttd-table td { border: none; text-align: center; vertical-align: top; width: 50%; padding: 0 10px; }
  .ttd-space { height: 55px; }

  .footer-note { font-size: 8pt; color: #6b7280; text-align: right; border-top: 0.5px solid #d1d5db; padding-top: 4px; margin-top: 20px; font-style: italic; }
</style>
</head>
<body>

{{-- ── KOP SEKOLAH FORMAL ─────────────────────────────────────── --}}
<table class="kop-container">
  <tr>
    <td class="kop-logo">
      @if(file_exists(public_path('img/logo-pemprov-bali.png')))
        <img src="{{ public_path('img/logo-pemprov-bali.png') }}" alt="Pemprov Bali">
      @endif
    </td>
    <td class="kop-text">
      <div class="l1">PEMERINTAH PROVINSI BALI</div>
      <div class="l2">DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA</div>
      <div class="l3">SMA NEGERI 1 GIANYAR</div>
      <div class="l4">Jln. Ratna, Tegal Tugu Gianyar, Telp : (0361) 943034</div>
      <div class="l5">Website: https://sman1-gianyar.sch.id &nbsp;|&nbsp; E-mail: sman1.gianyar1963@gmail.com</div>
    </td>
    <td class="kop-logo">
      @if(file_exists(public_path('img/logo_sekolah.png')))
        <img src="{{ public_path('img/logo_sekolah.png') }}" alt="SMAN 1 Gianyar">
      @endif
    </td>
  </tr>
</table>
<div class="kop-line"></div>

{{-- ── JUDUL DOKUMEN ──────────────────────────────────────────── --}}
<div class="doc-header">
  <div class="doc-title">DAFTAR ANGGOTA EKSTRAKURIKULER</div>
  <div class="doc-subtitle">EKSTRAKURIKULER: {{ strtoupper($extracurricular->name) }}</div>
  @if(!empty($filterGrade) || !empty($filterClass))
    <div class="doc-filter">
      Filter: 
      @if(!empty($filterGrade)) Angkatan / Kelas {{ $filterGrade }} @endif
      @if(!empty($filterClass)) @if(!empty($filterGrade)) &bull; @endif Kelas {{ $filterClass }} @endif
    </div>
  @endif
</div>

{{-- ── METADATA DOKUMEN ───────────────────────────────────────── --}}
<table class="meta-table">
  <tr>
    <td class="meta-label">Nama Ekstrakurikuler</td>
    <td class="meta-colon">:</td>
    <td><strong>{{ $extracurricular->name }}</strong></td>
    <td class="meta-label">Total Anggota Aktif</td>
    <td class="meta-colon">:</td>
    <td><strong>{{ $members->count() }} Orang</strong></td>
  </tr>
  <tr>
    <td class="meta-label">Pembina Ekstrakurikuler</td>
    <td class="meta-colon">:</td>
    <td>{{ $extracurricular->pembina_names }}</td>
    <td class="meta-label">Jumlah Ketua / Anggota</td>
    <td class="meta-colon">:</td>
    <td>{{ $members->where('role', 'ketua')->count() }} Ketua / {{ $members->where('role', 'member')->count() }} Anggota</td>
  </tr>
</table>

{{-- ── TABEL DATA ANGGOTA ─────────────────────────────────────── --}}
<table class="data-table">
  <thead>
    <tr>
      <th style="width: 32px;">NO</th>
      <th style="width: 180px;">NAMA SISWA</th>
      <th style="width: 90px;">NISN / NIS</th>
      <th style="width: 75px;">KELAS</th>
      <th style="width: 75px;">ANGKATAN</th>
      <th style="width: 80px;">PERAN</th>
      <th style="width: 90px;">TGL. BERGABUNG</th>
    </tr>
  </thead>
  <tbody>
    @forelse($members as $i => $member)
    <tr>
      <td class="text-center">{{ $i + 1 }}</td>
      <td><strong>{{ $member->user?->name ?? '—' }}</strong></td>
      <td class="text-center" style="font-family: monospace;">{{ $member->user?->nisn ?? $member->user?->nis ?? '—' }}</td>
      <td class="text-center">{{ $member->user?->schoolClass?->name ?? '—' }}</td>
      <td class="text-center">Kelas {{ $member->user?->schoolClass?->grade ?? '—' }}</td>
      <td class="text-center">
        @if($member->role === 'ketua')
          <span class="badge-role role-ketua">Ketua</span>
        @else
          <span class="badge-role role-anggota">Anggota</span>
        @endif
      </td>
      <td class="text-center">{{ $member->created_at?->format('d/m/Y') ?? '—' }}</td>
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

{{-- ── TANDA TANGAN FORMAL ────────────────────────────────────── --}}
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
  Dicetak otomatis melalui SIMS (Smart Information Management System) SMA Negeri 1 Gianyar pada {{ now()->locale('id')->isoFormat('D MMMM Y HH:mm') }} WITA.
</div>

</body>
</html>
