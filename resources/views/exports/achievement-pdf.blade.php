<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Rekapitulasi Prestasi Siswa SMAN 1 Gianyar</title>
<style>
  @page { size: A4 landscape; margin: 10mm 12mm 12mm 12mm; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; line-height: 1.3; }

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
  .kop-line { border-bottom: 3px double #000; margin-bottom: 12px; }

  /* ── Judul Dokumen ────────────────────────────────────────────── */
  .doc-header { text-align: center; margin-bottom: 12px; }
  .doc-title { font-size: 13pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
  .doc-subtitle { font-size: 10.5pt; font-weight: bold; margin-top: 2px; }
  .doc-filter { font-size: 9pt; font-style: italic; color: #374151; margin-top: 2px; }

  /* ── Summary Stats ────────────────────────────────────────────── */
  .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9pt; }
  .stats-table td { padding: 5px 8px; border: 1px solid #cbd5e1; text-align: center; background-color: #f8fafc; }
  .stats-val { font-size: 11pt; font-weight: bold; color: #1e3a8a; }
  .stats-lbl { font-size: 8pt; color: #475569; text-transform: uppercase; margin-top: 2px; }

  /* ── Data Table ───────────────────────────────────────────────── */
  table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 9pt; }
  table.data-table th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; padding: 6px 6px; border: 1px solid #1e3a8a; text-align: center; font-size: 8.5pt; }
  table.data-table td { padding: 5px 6px; border: 1px solid #9ca3af; vertical-align: middle; }
  table.data-table tr:nth-child(even) td { background-color: #f8fafc; }
  .text-center { text-align: center; }
  .text-left { text-align: left; }
  
  .badge { display: inline-block; padding: 1px 5px; font-size: 8pt; font-weight: bold; border-radius: 3px; }
  .lvl-internasional { background-color: #fee2e2; color: #991b1b; }
  .lvl-nasional { background-color: #dcfce7; color: #166534; }
  .lvl-provinsi { background-color: #fef3c7; color: #92400e; }
  .lvl-kabupaten { background-color: #e0f2fe; color: #075985; }
  .lvl-sekolah { background-color: #f1f5f9; color: #334155; }

  /* ── Tanda Tangan Formal ──────────────────────────────────────── */
  .ttd-table { width: 100%; border-collapse: collapse; margin-top: 20px; page-break-inside: avoid; font-size: 10pt; }
  .ttd-table td { border: none; text-align: center; vertical-align: top; width: 50%; padding: 0 10px; }
  .ttd-space { height: 50px; }

  .footer-note { font-size: 8pt; color: #6b7280; text-align: right; border-top: 0.5px solid #d1d5db; padding-top: 4px; margin-top: 14px; font-style: italic; }
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
  <div class="doc-title">REKAPITULASI PRESTASI & KEJUARAAN SISWA</div>
  <div class="doc-subtitle">STATUS: PRESTASI DIAKUI SEKOLAH (KURASI RESMI & INTERNAL)</div>
  @if(!empty($selectedClass) || !empty($selectedLevel) || !empty($selectedCategory) || !empty($selectedCuration) || !empty($year))
    <div class="doc-filter">
      Kriteria Filter: 
      @if(!empty($selectedCuration)) Status {{ $selectedCuration }} &bull; @endif
      @if(!empty($selectedLevel)) Tingkat {{ $selectedLevel }} &bull; @endif
      @if(!empty($selectedCategory)) Rumpun {{ $selectedCategory }} &bull; @endif
      @if(!empty($selectedClass)) Kelas {{ $selectedClass }} &bull; @endif
      @if(!empty($year)) Tahun {{ $year }} @endif
    </div>
  @endif
</div>

{{-- ── REKAP RINGKAS ────────────────────────────────────────────── --}}
<table class="stats-table">
  <tr>
    <td>
      <div class="stats-val">{{ $stats['total'] }}</div>
      <div class="stats-lbl">Total Prestasi</div>
    </td>
    <td>
      <div class="stats-val" style="color:#15803d;">{{ $stats['curated'] }}</div>
      <div class="stats-lbl">Kurasi Resmi</div>
    </td>
    <td>
      <div class="stats-val" style="color:#0369a1;">{{ $stats['not_curatable'] }}</div>
      <div class="stats-lbl">Catatan Internal</div>
    </td>
    <td>
      <div class="stats-val" style="color:#991b1b;">{{ $stats['internasional'] }}</div>
      <div class="stats-lbl">Internasional</div>
    </td>
    <td>
      <div class="stats-val" style="color:#166534;">{{ $stats['nasional'] }}</div>
      <div class="stats-lbl">Nasional</div>
    </td>
    <td>
      <div class="stats-val" style="color:#92400e;">{{ $stats['provinsi'] }}</div>
      <div class="stats-lbl">Provinsi</div>
    </td>
    <td>
      <div class="stats-val">{{ $stats['unique_students'] }}</div>
      <div class="stats-lbl">Siswa Berprestasi</div>
    </td>
  </tr>
</table>

{{-- ── TABEL DATA PRESTASI ────────────────────────────────────── --}}
<table class="data-table">
  <thead>
    <tr>
      <th style="width: 25px;">No</th>
      <th style="width: 130px;">Nama Siswa</th>
      <th style="width: 55px;">Kelas</th>
      <th style="width: 160px;">Judul Prestasi / Kejuaraan</th>
      <th style="width: 110px;">Event / Penyelenggara</th>
      <th style="width: 80px;">Rumpun</th>
      <th style="width: 75px;">Tingkat</th>
      <th style="width: 70px;">Peringkat</th>
      <th style="width: 90px;">Status Kurasi</th>
      <th style="width: 65px;">Tanggal</th>
    </tr>
  </thead>
  <tbody>
    @forelse($achievements as $index => $item)
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>
          <strong>{{ $item->student?->name ?? '—' }}</strong><br>
          <span style="font-size: 8pt; color: #4b5563;">NISN: {{ $item->student?->nisn ?? '—' }}</span>
        </td>
        <td class="text-center">{{ $item->student?->schoolClass?->name ?? '—' }}</td>
        <td>{{ $item->title }}</td>
        <td>
          {{ $item->event_name ?? '—' }}<br>
          @if($item->organizer)
            <span style="font-size: 8pt; color: #6b7280;">by {{ $item->organizer }}</span>
          @endif
        </td>
        <td class="text-center">{{ $item->fieldCategoryLabel() }}</td>
        <td class="text-center">
          <span class="badge lvl-{{ $item->level }}">
            {{ $item->levelLabel() }}
          </span>
        </td>
        <td class="text-center"><strong>{{ $item->rank ?? '—' }}</strong></td>
        <td class="text-center">
          <span class="badge" style="{{ $item->curation_status === 'curated' ? 'background-color:#dcfce7; color:#166534;' : 'background-color:#e0f2fe; color:#075985;' }}">
            {{ $item->curationStatusLabel() }}
          </span>
        </td>
        <td class="text-center">{{ $item->achievement_date ? $item->achievement_date->format('d/m/Y') : '—' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="10" class="text-center" style="padding: 15px; color: #6b7280;">
          Tidak ada data prestasi siswa yang memenuhi kriteria filter.
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

{{-- ── TANDA TANGAN FORMAL ──────────────────────────────────────── --}}
<table class="ttd-table">
  <tr>
    <td>
      Mengetahui,<br>
      <strong>Kepala SMAN 1 Gianyar</strong>
      <div class="ttd-space"></div>
      <strong><u>Surawan, S.Pd., M.Pd.</u></strong><br>
      NIP. 19680512 199103 1 008
    </td>
    <td>
      Gianyar, {{ now()->translatedFormat('d F Y') }}<br>
      <strong>Wakasek Kesiswaan / Pembina</strong>
      <div class="ttd-space"></div>
      <strong><u>Pembina Kesiswaan SMAN 1 Gianyar</u></strong><br>
      NIP. —
    </td>
  </tr>
</table>

<div class="footer-note">
  Dicetak otomatis melalui SIMS SMAN 1 Gianyar pada {{ now()->translatedFormat('d F Y, H:i') }} WITA.
</div>

</body>
</html>
