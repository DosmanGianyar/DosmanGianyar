<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Rekapitulasi Prestasi Siswa SMAN 1 Gianyar</title>
<style>
  @page { size: A4 landscape; margin: 10mm 12mm 12mm 12mm; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', 'Times New Roman', serif; font-size: 9.5pt; color: #000; line-height: 1.35; }

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
  .doc-subtitle { font-size: 10pt; font-weight: bold; margin-top: 2px; color: #1e293b; }
  .doc-filter { font-size: 9pt; font-style: italic; color: #374151; margin-top: 2px; }

  /* ── Summary Stats ────────────────────────────────────────────── */
  .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9pt; }
  .stats-table td { padding: 5px 8px; border: 1px solid #cbd5e1; text-align: center; background-color: #f8fafc; }
  .stats-val { font-size: 11pt; font-weight: bold; color: #1e3a8a; }
  .stats-lbl { font-size: 8pt; color: #475569; text-transform: uppercase; margin-top: 2px; }

  /* ── Data Table ───────────────────────────────────────────────── */
  table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 9pt; }
  table.data-table th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; padding: 6px 6px; border: 1px solid #1e3a8a; text-align: center; font-size: 8.5pt; }
  table.data-table td { padding: 6px 7px; border: 1px solid #9ca3af; vertical-align: top; }
  table.data-table tr:nth-child(even) td { background-color: #f8fafc; }
  .text-center { text-align: center; vertical-align: middle !important; }
  .text-left { text-align: left; }

  .badge { display: inline-block; padding: 2px 6px; font-size: 8pt; font-weight: bold; border-radius: 3px; }
  .lvl-internasional { background-color: #fee2e2; color: #991b1b; }
  .lvl-nasional { background-color: #dcfce7; color: #166534; }
  .lvl-provinsi { background-color: #fef3c7; color: #92400e; }
  .lvl-kabupaten { background-color: #e0f2fe; color: #075985; }
  .lvl-sekolah { background-color: #f1f5f9; color: #334155; }

  .member-item { margin-bottom: 2px; line-height: 1.3; }
  .member-item:last-child { margin-bottom: 0; }
  .pembina-item { margin-bottom: 2px; line-height: 1.3; }
  .pembina-item:last-child { margin-bottom: 0; }

  /* ── Tanda Tangan Formal (Cukup Kepala Sekolah Saja) ──────────── */
  .ttd-table { width: 100%; border-collapse: collapse; margin-top: 22px; page-break-inside: avoid; font-size: 10pt; }
  .ttd-table td { border: none; vertical-align: top; }
  .ttd-space { height: 60px; }

  .footer-note { font-size: 8pt; color: #6b7280; text-align: right; border-top: 0.5px solid #d1d5db; padding-top: 4px; margin-top: 14px; font-style: italic; }
</style>
</head>
<body>

{{-- ── KOP SEKOLAH FORMAL ─────────────────────────────────────── --}}
@include('partials.kop-surat-pdf')

{{-- ── JUDUL DOKUMEN ──────────────────────────────────────────── --}}
<div class="doc-header">
  <div class="doc-title">REKAPITULASI PRESTASI & KEJUARAAN SISWA</div>
  <div class="doc-subtitle">SMA NEGERI 1 GIANYAR</div>
  @if(!empty($selectedClass) || !empty($selectedLevel) || !empty($selectedCategory) || !empty($selectedParticipation) || !empty($selectedExtracurricular) || !empty($selectedPembina) || !empty($selectedMonth) || !empty($year) || !empty($fromDate) || !empty($untilDate))
    <div class="doc-filter">
      Kriteria Filter: 
      @if(!empty($fromDate) || !empty($untilDate)) Periode {{ $fromDate ?? 'Awal' }} s.d. {{ $untilDate ?? 'Sekarang' }} &bull; @endif
      @if(!empty($selectedParticipation)) Partisipasi {{ $selectedParticipation }} &bull; @endif
      @if(!empty($selectedExtracurricular)) Ekstra {{ $selectedExtracurricular }} &bull; @endif
      @if(!empty($selectedPembina)) Pembina {{ $selectedPembina }} &bull; @endif
      @if(!empty($selectedLevel)) Tingkat {{ $selectedLevel }} &bull; @endif
      @if(!empty($selectedCategory)) Rumpun {{ $selectedCategory }} &bull; @endif
      @if(!empty($selectedClass)) Kelas {{ $selectedClass }} &bull; @endif
      @if(!empty($selectedMonth)) Bulan {{ $selectedMonth }} &bull; @endif
      @if(!empty($year)) Tahun {{ $year }} @endif
    </div>
  @endif
</div>

{{-- ── REKAP RINGKAS ────────────────────────────────────────────── --}}
<table class="stats-table">
  <tr>
    <td>
      <div class="stats-val">{{ $stats['total'] }}</div>
      <div class="stats-lbl">Total Kejuaraan</div>
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
      <div class="stats-val" style="color:#075985;">{{ $stats['kabupaten'] }}</div>
      <div class="stats-lbl">Kabupaten/Kota</div>
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
      <th style="width: 26px;">No</th>
      <th style="width: 205px;">Nama Lomba / Kejuaraan</th>
      <th style="width: 195px;">Nama Siswa / Kelompok Juara</th>
      <th style="width: 85px;">Bidang / Ekstra</th>
      <th style="width: 72px;">Tingkat</th>
      <th style="width: 68px;">Peringkat</th>
      <th style="width: 155px;">Pembina Ekstrakurikuler</th>
      <th style="width: 64px;">Tanggal</th>
    </tr>
  </thead>
  <tbody>
    @forelse($achievements as $index => $item)
      @php
        $members = $item->grouped_members ?? collect([$item]);
        $isGroup = $members->count() > 1 || $item->isBeregu();
        $pembinaList = $item->pembinaList();
      @endphp
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>

        {{-- Kolom 1: Nama Lomba / Kejuaraan (1 baris per lomba) --}}
        <td class="text-left">
          <strong>{{ $item->title }}</strong>
          @if($item->event_name && mb_strtolower(trim($item->event_name)) !== mb_strtolower(trim($item->title)))
            <br><span style="font-size: 8pt; color: #374151;">Ajang: {{ $item->event_name }}</span>
          @endif
          @if($item->organizer)
            <br><span style="font-size: 8pt; color: #4b5563;">Penyelenggara: {{ $item->organizer }}</span>
          @endif
        </td>

        {{-- Kolom 2 (di kanannya): Nama Siswa / Kelompok Siswa yang Juara --}}
        <td class="text-left">
          @if($isGroup && $members->count() > 1)
            <div style="font-size: 7.5pt; font-weight: bold; color: #5b21b6; margin-bottom: 3px;">
              Kelompok / Beregu ({{ $members->count() }} Siswa):
            </div>
            @foreach($members as $mIdx => $member)
              <div class="member-item">
                <strong>{{ $mIdx + 1 }}. {{ $member->student?->name ?? '—' }}</strong>
                @if($member->student?->schoolClass?->name)
                  <span style="font-size: 8pt; color: #374151;">({{ $member->student->schoolClass->name }})</span>
                @endif
              </div>
            @endforeach
          @else
            <strong>{{ $item->student?->name ?? '—' }}</strong>
            @if($item->student?->schoolClass?->name)
              <span style="font-size: 8.5pt; color: #1e3a8a; font-weight: bold;">({{ $item->student->schoolClass->name }})</span>
            @endif
            @if($item->student?->nisn)
              <br><span style="font-size: 8pt; color: #4b5563;">NISN: {{ $item->student->nisn }}</span>
            @endif
          @endif
        </td>

        {{-- Kolom 3: Bidang / Ekstra --}}
        <td class="text-center">
          <strong>{{ $item->fieldCategoryLabel() }}</strong>
          @if($item->extracurricularName() !== '—')
            <br><span style="font-size: 8pt; color: #1e40af;">{{ $item->extracurricularName() }}</span>
          @endif
        </td>

        {{-- Kolom 4: Tingkat --}}
        <td class="text-center">
          <span class="badge lvl-{{ $item->level }}">
            {{ $item->levelLabel() }}
          </span>
          <br>
          <span style="font-size: 7.5pt; color: #475569;">
            {{ $isGroup ? 'Beregu' : 'Perorangan' }}
          </span>
        </td>

        {{-- Kolom 5: Peringkat / Juara --}}
        <td class="text-center"><strong>{{ $item->rank ?? '—' }}</strong></td>

        {{-- Kolom 6: Pembina Ekstrakurikuler (menggantikan Status Kurasi, tampil semua jika > 1) --}}
        <td class="text-left">
          @if(count($pembinaList) > 1)
            @foreach($pembinaList as $pIdx => $pName)
              <div class="pembina-item">
                <strong>{{ $pIdx + 1 }}. {{ $pName }}</strong>
              </div>
            @endforeach
          @elseif(count($pembinaList) === 1)
            <strong>{{ $pembinaList[0] }}</strong>
          @else
            <span>—</span>
          @endif
          @if($item->extracurricularName() !== '—')
            <div style="font-size: 7.5pt; color: #4338ca; margin-top: 2px;">
              Ekstra: {{ $item->extracurricularName() }}
            </div>
          @endif
        </td>

        {{-- Kolom 7: Tanggal --}}
        <td class="text-center">{{ $item->achievement_date ? \Carbon\Carbon::parse($item->achievement_date)->format('d/m/Y') : '—' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="8" class="text-center" style="padding: 15px; color: #6b7280;">
          Tidak ada data prestasi siswa yang memenuhi kriteria filter.
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

{{-- ── TANDA TANGAN FORMAL (CUKUP KEPALA SEKOLAH SAJA) ─────────── --}}
<table class="ttd-table">
  <tr>
    <td style="width: 60%;"></td>
    <td style="width: 40%; text-align: center;">
      Gianyar, {{ now()->translatedFormat('d F Y') }}<br>
      Mengetahui,<br>
      <strong>Kepala SMAN 1 Gianyar</strong>
      <div class="ttd-space"></div>
      <strong><u>I Wayan Sudra Astra, S.Pd., M.Pd.</u></strong><br>
      NIP. 19680512 199103 1 008
    </td>
  </tr>
</table>

<div class="footer-note">
  Dicetak melalui Sistem Informasi Manajemen SMAN 1 Gianyar (SIMAK DOSMAN) pada {{ now()->translatedFormat('d F Y, H:i') }} WITA.
</div>

</body>
</html>
