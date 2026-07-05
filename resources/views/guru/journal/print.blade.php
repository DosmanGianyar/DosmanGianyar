<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Jurnal Mengajar — {{ $teacher->name }}</title>
<style>
@page {
    size: A4 portrait;
    margin: 2cm 2.5cm;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 11pt;
    color: #000;
    background: #fff;
}

/* ─ Kop Surat ─ */
.kop {
    display: flex;
    align-items: center;
    gap: 14px;
    padding-bottom: 8px;
    border-bottom: 3px solid #000;
    margin-bottom: 4px;
}
.kop img {
    width: 64px;
    height: 64px;
    object-fit: contain;
    flex-shrink: 0;
}
.kop-tengah {
    flex: 1;
    text-align: center;
    line-height: 1.4;
}
.kop-tengah .instansi {
    font-size: 9.5pt;
    font-weight: normal;
    letter-spacing: 0.3px;
    color: #222;
}
.kop-tengah .nama-sekolah {
    font-size: 16pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #000;
}
.kop-tengah .alamat {
    font-size: 9pt;
    color: #333;
    margin-top: 2px;
}

/* ─ Judul ─ */
.judul-blok {
    text-align: center;
    margin: 16px 0 12px;
}
.judul-blok h1 {
    font-size: 13pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-decoration: underline;
}

/* ─ Info Guru ─ */
.info-table {
    width: 100%;
    margin-bottom: 12px;
    border-collapse: collapse;
}
.info-table td {
    padding: 2px 0;
    font-size: 10.5pt;
    vertical-align: top;
}
.info-table .label { width: 140px; font-weight: normal; }
.info-table .sep   { width: 16px; text-align: center; }
.info-table .val   { font-weight: normal; }

/* ─ Tabel Jurnal ─ */
.jurnal-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 9.5pt;
}
.jurnal-table th {
    background: #f0f0f0;
    border: 1px solid #555;
    padding: 5px 4px;
    text-align: center;
    font-weight: bold;
    vertical-align: middle;
    line-height: 1.3;
}
.jurnal-table td {
    border: 1px solid #555;
    padding: 5px 4px;
    vertical-align: top;
    line-height: 1.4;
}
.jurnal-table td.center { text-align: center; }
.jurnal-table .no-col   { width: 26px; text-align: center; }
.jurnal-table .tgl-col  { width: 62px; }
.jurnal-table .kls-col  { width: 52px; text-align: center; }
.jurnal-table .jam-col  { width: 36px; text-align: center; }
.jurnal-table .mp-col   { width: 68px; }
.jurnal-table .tp-col   { width: 100px; }
.jurnal-table .materi-col   { width: auto; }
.jurnal-table .aktivitas-col { width: auto; }
.jurnal-table .catatan-col  { width: 70px; }
.jurnal-table .absen-col    { width: 80px; }

.tp-kode {
    display: inline-block;
    background: #e8e8e8;
    border: 1px solid #bbb;
    border-radius: 2px;
    padding: 0 3px;
    font-size: 8pt;
    font-weight: bold;
    margin-bottom: 2px;
}
.absen-badge {
    display: inline-block;
    font-size: 8pt;
    font-weight: bold;
    padding: 1px 4px;
    border-radius: 2px;
    margin: 1px 1px 1px 0;
    line-height: 1.4;
}
.absen-a { background: #fee2e2; color: #b91c1c; }
.absen-i { background: #e0f2fe; color: #0369a1; }
.absen-s { background: #f3e8ff; color: #7e22ce; }

/* ─ Footer / TTD ─ */
.ttd-wrap {
    display: flex;
    justify-content: flex-end;
    margin-top: 8px;
}
.ttd-box {
    text-align: center;
    width: 200px;
    font-size: 10.5pt;
}
.ttd-box .ttd-lokasi { margin-bottom: 4px; }
.ttd-box .ttd-jabatan { font-weight: normal; margin-bottom: 56px; }
.ttd-box .ttd-nama { font-weight: bold; text-decoration: underline; }
.ttd-box .ttd-nip  { font-size: 9.5pt; }

/* ─ Ringkasan ─ */
.summary-row {
    display: flex;
    gap: 20px;
    margin-bottom: 12px;
    font-size: 10.5pt;
}
.summary-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.summary-item span.badge {
    display: inline-block;
    background: #f0f0f0;
    border: 1px solid #ccc;
    padding: 1px 8px;
    border-radius: 3px;
    font-weight: bold;
}

/* ─ Halaman baru antar entry besar ─ */
.page-break { page-break-after: always; }

/* ─ Tombol cetak (disembunyikan saat print) ─ */
.print-toolbar {
    position: fixed;
    top: 16px;
    right: 20px;
    display: flex;
    gap: 10px;
    z-index: 999;
}
.btn-cetak {
    background: #1d4ed8;
    color: #fff;
    border: none;
    padding: 9px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-family: sans-serif;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.btn-cetak:hover { background: #1e40af; }
.btn-tutup {
    background: #e5e7eb;
    color: #374151;
    border: none;
    padding: 9px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-family: sans-serif;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.btn-tutup:hover { background: #d1d5db; }

/* ─ Filter bar (hanya layar) ─ */
.filter-bar {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-family: sans-serif;
    font-size: 13px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: flex-end;
}
.filter-bar label { display: block; font-size: 11px; font-weight: 600; color: #6b7280; margin-bottom: 3px; }
.filter-bar select, .filter-bar input {
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 13px;
    background: #fff;
}
.filter-bar button {
    padding: 7px 16px;
    background: #1d4ed8;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    align-self: flex-end;
}

@media print {
    .print-toolbar { display: none !important; }
    .filter-bar    { display: none !important; }
    body { background: #fff; }
}
</style>
</head>
<body>

{{-- ─── Toolbar (hanya layar) ─────────────────────────────────────── --}}
<div class="print-toolbar">
    <button class="btn-cetak" onclick="window.print()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Cetak / Simpan PDF
    </button>
    <button class="btn-tutup" onclick="window.close()">Tutup</button>
</div>

{{-- ─── Filter Bar (hanya layar) ─────────────────────────────────── --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('guru.journal.print') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;width:100%">
        @php
            $months = ['','Januari','Februari','Maret','April','Mei','Juni',
                       'Juli','Agustus','September','Oktober','November','Desember'];
        @endphp
        <div>
            <label>Bulan</label>
            <select name="month" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label>Tahun</label>
            <select name="year" onchange="this.form.submit()">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label>Kelas</label>
            <select name="class_id" onchange="this.form.submit()">
                <option value="">— Semua Kelas —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

{{-- ─── Kop Surat ──────────────────────────────────────────────────── --}}
<div class="kop">
    <img src="{{ asset('img/logo-pemprov-bali.png') }}" alt="Logo Pemprov Bali">
    <div class="kop-tengah">
        <div class="instansi">PEMERINTAH PROVINSI BALI<br>DINAS PENDIDIKAN, KEPEMUDAAN, DAN OLAHRAGA</div>
        <div class="nama-sekolah">SMA Negeri 1 Gianyar</div>
        <div class="alamat">Jl. Ratna No. 1, Gianyar, Bali 80511 &nbsp;|&nbsp; Telp. (0361) 943036<br>
            Email: smansa.gianyar@gmail.com &nbsp;|&nbsp; Website: sman1-gianyar.sch.id</div>
    </div>
    <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo Sekolah">
</div>
<div style="border-bottom:1px solid #888;margin-bottom:14px;"></div>

{{-- ─── Judul ───────────────────────────────────────────────────────── --}}
<div class="judul-blok">
    <h1>Laporan Jurnal Mengajar</h1>
</div>

{{-- ─── Info Guru ──────────────────────────────────────────────────── --}}
@php
    $months2 = ['','Januari','Februari','Maret','April','Mei','Juni',
                'Juli','Agustus','September','Oktober','November','Desember'];
    $totalPertemuan = $journals->count();
    $totalAbsen     = $journals->sum(fn($j) => $j->absences->count());
    $subjectNames   = $journals->pluck('subject.name')->filter()->unique()->implode(', ');
    if (!$subjectNames) {
        $subjectNames = $journals->pluck('tp.subject.name')->filter()->unique()->implode(', ');
    }
    if (!$subjectNames && $teacher->subjects->isNotEmpty()) {
        $subjectNames = $teacher->subjects->pluck('name')->implode(', ');
    }
    if (!$subjectNames) $subjectNames = $teacher->subject ?? '—';
@endphp
<table class="info-table">
    <tr>
        <td class="label">Nama Guru</td>
        <td class="sep">:</td>
        <td class="val">{{ $teacher->name }}</td>
    </tr>
    <tr>
        <td class="label">NIP</td>
        <td class="sep">:</td>
        <td class="val">{{ $teacher->nip ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Mata Pelajaran</td>
        <td class="sep">:</td>
        <td class="val">{{ $subjectNames }}</td>
    </tr>
    <tr>
        <td class="label">Kelas</td>
        <td class="sep">:</td>
        <td class="val">{{ $className ?? 'Semua Kelas' }}</td>
    </tr>
    <tr>
        <td class="label">Periode</td>
        <td class="sep">:</td>
        <td class="val">{{ $months2[$month] }} {{ $year }}</td>
    </tr>
    <tr>
        <td class="label">Jumlah Pertemuan</td>
        <td class="sep">:</td>
        <td class="val">{{ $totalPertemuan }} pertemuan</td>
    </tr>
</table>

{{-- ─── Tabel Jurnal ────────────────────────────────────────────────── --}}
@if($journals->isEmpty())
<p style="text-align:center;padding:30px 0;color:#555;font-style:italic;">
    Tidak ada data jurnal untuk periode {{ $months2[$month] }} {{ $year }}.
</p>
@else
<table class="jurnal-table">
    <thead>
        <tr>
            <th class="no-col">No</th>
            <th class="tgl-col">Tanggal</th>
            <th class="kls-col">Kelas</th>
            <th class="jam-col">Jam Ke</th>
            <th class="mp-col">Mata Pelajaran</th>
            <th class="tp-col">Tujuan Pembelajaran</th>
            <th class="materi-col">Materi Pokok</th>
            <th class="aktivitas-col">Aktivitas Pembelajaran</th>
            <th class="catatan-col">Catatan</th>
            <th class="absen-col">Siswa Tidak Hadir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($journals as $i => $journal)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $journal->date?->isoFormat('D MMM Y') }}</td>
            <td class="center">{{ $journal->schoolClass?->name ?? '—' }}</td>
            <td class="center">
                @if($journal->period)
                    {{ $journal->period }}{{ $journal->period_end && $journal->period_end > $journal->period ? '–'.$journal->period_end : '' }}
                @else
                    —
                @endif
            </td>
            <td>{{ $journal->subject?->name ?? '—' }}</td>
            <td>
                @if($journal->tp)
                    @if($journal->tp->code)
                    <span class="tp-kode">{{ $journal->tp->code }}</span><br>
                    @endif
                    {{ $journal->tp->description }}
                @elseif($journal->learning_objectives)
                    {{ $journal->learning_objectives }}
                @else
                    —
                @endif
            </td>
            <td>{{ $journal->material }}</td>
            <td>{{ $journal->activity }}</td>
            <td>{{ $journal->notes ?: '—' }}</td>
            <td>
                @if($journal->absences->isEmpty())
                    <span style="color:#888;font-style:italic">Hadir semua</span>
                @else
                    @foreach($journal->absences as $abs)
                    @php
                        $cls = match($abs->status) {
                            'tidak_hadir' => 'absen-a',
                            'izin'        => 'absen-i',
                            'sakit'       => 'absen-s',
                            default       => '',
                        };
                        $lbl = match($abs->status) {
                            'tidak_hadir' => 'A',
                            'izin'        => 'I',
                            'sakit'       => 'S',
                            default       => '?',
                        };
                    @endphp
                    <span class="absen-badge {{ $cls }}">{{ $lbl }}</span> {{ $abs->student?->name ?? '—' }}<br>
                    @endforeach
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ─── Ringkasan ───────────────────────────────────────────────────── --}}
<div class="summary-row">
    <div class="summary-item">Total Pertemuan: <span class="badge">{{ $totalPertemuan }}</span></div>
    <div class="summary-item">Total Siswa Tidak Hadir: <span class="badge">{{ $totalAbsen }}</span></div>
</div>
@endif

{{-- ─── Tanda Tangan ────────────────────────────────────────────────── --}}
<div class="ttd-wrap">
    <div class="ttd-box">
        <div class="ttd-lokasi">Gianyar, {{ now()->isoFormat('D MMMM Y') }}</div>
        <div class="ttd-jabatan">Guru Mata Pelajaran,</div>
        <div class="ttd-nama">{{ $teacher->name }}</div>
        <div class="ttd-nip">NIP. {{ $teacher->nip ?? '—' }}</div>
    </div>
</div>

</body>
</html>
