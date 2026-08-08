<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Siswa Tanpa Ekstrakurikuler</title>
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
  .doc-subtitle { font-size: 11pt; font-weight: bold; margin-top: 2px; color: #991b1b; }
  .doc-filter { font-size: 9.5pt; font-style: italic; color: #374151; margin-top: 2px; }

  /* ── Info Table ──────────────────────────────────────────────── */
  .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10pt; }
  .meta-table td { padding: 4px 6px; border: none; vertical-align: top; }
  .meta-label { width: 180px; font-weight: bold; color: #1f2937; }
  .meta-colon { width: 10px; font-weight: bold; text-align: center; }

  /* ── Data Table ───────────────────────────────────────────────── */
  table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10pt; }
  table.data-table th { background-color: #991b1b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #991b1b; text-align: center; font-size: 9.5pt; }
  table.data-table td { padding: 5px 8px; border: 1px solid #9ca3af; vertical-align: middle; }
  table.data-table tr:nth-child(even) td { background-color: #fef2f2; }
  .text-center { text-align: center; }

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
  <div class="doc-title">LAPORAN SISWA TANPA EKSTRAKURIKULER</div>
  @if(!empty($className) || !empty($gradeName))
    <div class="doc-filter">
      Filter: 
      @if(!empty($gradeName)) {{ $gradeName }} @endif
      @if(!empty($className)) @if(!empty($gradeName)) &bull; @endif Kelas {{ $className }} @endif
    </div>
  @else
    <div class="doc-filter">Semua Kelas & Angkatan</div>
  @endif
</div>

{{-- ── METADATA DOKUMEN ───────────────────────────────────────── --}}
<table class="meta-table">
  <tr>
    <td class="meta-label">Total Siswa Belum Memiliki Ekstra</td>
    <td class="meta-colon">:</td>
    <td><strong style="color: #991b1b;">{{ $students->count() }} Orang Siswa</strong></td>
  </tr>
  <tr>
    <td class="meta-label">Tanggal Cetak Laporan</td>
    <td class="meta-colon">:</td>
    <td>{{ now()->locale('id')->isoFormat('D MMMM Y') }}</td>
  </tr>
</table>

{{-- ── TABEL DATA ANGGOTA ─────────────────────────────────────── --}}
<table class="data-table">
  <thead>
    <tr>
      <th style="width: 32px;">NO</th>
      <th style="width: 200px;">NAMA SISWA</th>
      <th style="width: 110px;">NISN / NIS</th>
      <th style="width: 90px;">KELAS</th>
      <th style="width: 90px;">ANGKATAN</th>
    </tr>
  </thead>
  <tbody>
    @forelse($students as $i => $student)
    <tr>
      <td class="text-center">{{ $i + 1 }}</td>
      <td><strong>{{ $student->name }}</strong></td>
      <td class="text-center" style="font-family: monospace;">{{ $student->nisn ?? $student->nis ?? '—' }}</td>
      <td class="text-center">{{ $student->schoolClass?->name ?? '—' }}</td>
      <td class="text-center">Kelas {{ $student->schoolClass?->grade ?? '—' }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="5" class="text-center" style="padding: 16px; color: #16a34a; font-weight: bold;">
        🎉 Seluruh siswa sudah terdaftar aktif dalam kegiatan ekstrakurikuler.
      </td>
    </tr>
    @endforelse
  </tbody>
</table>

{{-- ── TANDA TANGAN FORMAL ────────────────────────────────────── --}}
<table class="ttd-table">
  <tr>
    <td>
      Mengetahui,<br>
      <strong>Wakasek Kesiswaan</strong>
      <div class="ttd-space"></div>
      <u><strong>I Made Subagia, S.Pd., M.Pd.</strong></u><br>
      <span>NIP. 19750512 200012 1 003</span>
    </td>
    <td>
      Gianyar, {{ now()->locale('id')->isoFormat('D MMMM Y') }}<br>
      <strong>Kepala SMAN 1 Gianyar</strong>
      <div class="ttd-space"></div>
      <u><strong>Surya Natha, S.Pd., M.Pd.</strong></u><br>
      <span>NIP. 19700101 199503 1 002</span>
    </td>
  </tr>
</table>

<div class="footer-note">
  Dicetak otomatis melalui SIMS (Smart Information Management System) SMA Negeri 1 Gianyar pada {{ now()->locale('id')->isoFormat('D MMMM Y HH:mm') }} WITA.
</div>

</body>
</html>
