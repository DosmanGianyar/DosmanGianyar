<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Jurnal Mengajar — {{ $teacher->name }}</title>
<style>
@page {
    size: A4 landscape;
    margin: 1cm 1.2cm;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 10pt;
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
    width: 60px;
    height: 60px;
    object-fit: contain;
    flex-shrink: 0;
}
.kop-tengah {
    flex: 1;
    text-align: center;
    line-height: 1.3;
}
.kop-tengah .instansi {
    font-size: 9pt;
    font-weight: normal;
    letter-spacing: 0.3px;
    color: #222;
}
.kop-tengah .nama-sekolah {
    font-size: 15pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #000;
}
.kop-tengah .alamat {
    font-size: 8.5pt;
    color: #333;
    margin-top: 2px;
}

/* ─ Judul ─ */
.judul-blok {
    text-align: center;
    margin: 12px 0 8px;
}
.judul-blok h1 {
    font-size: 13pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: underline;
}
.judul-blok p {
    font-size: 10pt;
    font-weight: bold;
    color: #333;
    margin-top: 2px;
}

/* ─ Info Guru ─ */
.info-table {
    width: 100%;
    margin-bottom: 10px;
    border-collapse: collapse;
}
.info-table td {
    padding: 2px 0;
    font-size: 9.5pt;
    vertical-align: top;
}
.info-table .label { width: 130px; font-weight: normal; }
.info-table .sep   { width: 14px; text-align: center; }
.info-table .val   { font-weight: normal; }

/* ─ Tabel Jurnal ─ */
.jurnal-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
    font-size: 8.5pt;
}
.jurnal-table th {
    background: #f0f0f0;
    border: 1px solid #444;
    padding: 5px 4px;
    text-align: center;
    font-weight: bold;
    font-size: 8.5pt;
    vertical-align: middle;
}
.jurnal-table td {
    border: 1px solid #444;
    padding: 4px 4px;
    vertical-align: top;
    line-height: 1.3;
}
.jurnal-table td.center { text-align: center; }

/* Column Widths for A4 Landscape */
.jurnal-table .col-no     { width: 26px; text-align: center; }
.jurnal-table .col-tgl    { width: 90px; }
.jurnal-table .col-kls    { width: 55px; text-align: center; }
.jurnal-table .col-jam    { width: 45px; text-align: center; }
.jurnal-table .col-mapel  { width: 95px; }
.jurnal-table .col-tp     { width: 140px; }
.jurnal-table .col-materi { width: 150px; }
.jurnal-table .col-akt    { width: auto; }
.jurnal-table .col-cat    { width: 85px; }
.jurnal-table .col-absen  { width: 110px; }

/* Badges */
.tp-kode {
    display: inline-block;
    background: #e2e8f0;
    color: #1e293b;
    font-weight: bold;
    font-size: 7.5pt;
    padding: 1px 4px;
    border-radius: 3px;
    margin-bottom: 2px;
}
.absen-badge {
    display: inline-block;
    font-size: 7.5pt;
    font-weight: bold;
    padding: 1px 4px;
    border-radius: 2px;
    margin-right: 2px;
}
.absen-a { background: #fee2e2; color: #991b1b; }
.absen-i { background: #e0f2fe; color: #075985; }
.absen-s { background: #fef3c7; color: #92400e; }
.absen-d { background: #ccfbf1; color: #0f766e; }

/* Summary Row */
.summary-bar {
    display: flex;
    gap: 16px;
    margin-bottom: 14px;
    font-size: 9pt;
}
.summary-item {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    padding: 4px 10px;
    border-radius: 4px;
}
.summary-item strong {
    color: #1e40af;
}

/* ─ Tanda Tangan ─ */
.ttd-wrap {
    display: flex;
    justify-content: space-between;
    margin-top: 16px;
    page-break-inside: avoid;
}
.ttd-box {
    width: 220px;
    text-align: center;
    font-size: 9.5pt;
}
.ttd-box .ttd-lokasi { margin-bottom: 2px; }
.ttd-box .ttd-jabatan { font-weight: normal; margin-bottom: 46px; }
.ttd-box .ttd-nama { font-weight: bold; text-decoration: underline; }
.ttd-box .ttd-nip { font-size: 8.5pt; color: #333; margin-top: 1px; }

/* ─ Screen Only Elements ─ */
.no-print {
    font-family: system-ui, -apple-system, sans-serif;
}
.print-toolbar {
    position: fixed;
    top: 15px;
    right: 15px;
    z-index: 9999;
    display: flex;
    gap: 10px;
}
.btn-action {
    padding: 8px 16px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.btn-print { background: #2563eb; color: #fff; }
.btn-print:hover { background: #1d4ed8; }
.btn-close { background: #64748b; color: #fff; }
.btn-close:hover { background: #475569; }

.filter-bar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 16px;
}
.filter-bar form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: flex-end;
}
.filter-bar label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 3px;
}
.filter-bar select, .filter-bar input {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    font-size: 12px;
    background: #fff;
}

@media print {
    .no-print { display: none !important; }
    body { background: #fff; }
}
</style>
</head>
<body>

{{-- Toolbar Cetak (No Print) --}}
<div class="print-toolbar no-print">
    <button onclick="window.print()" class="btn-action btn-print">
        🖨️ Cetak / Simpan PDF (A4 Landscape)
    </button>
    <button onclick="window.close()" class="btn-action btn-close">
        Tutup
    </button>
</div>

{{-- Filter Bar (No Print) --}}
<div class="filter-bar no-print">
    <form method="GET" action="{{ route('guru.journal.print') }}">
        @if(auth()->user()?->role !== 'guru' || isset($teachers))
        <div>
            <label>Guru</label>
            <select name="teacher_id" onchange="this.form.submit()">
                @foreach($teachers as $t)
                <option value="{{ $t->id }}" {{ $teacher->id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label>Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()">
        </div>

        <div>
            <label>Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()">
        </div>

        <div>
            <label>Bulan</label>
            <select name="month" onchange="this.form.submit()">
                <option value="">— Semua / Pilih Tanggal —</option>
                @php
                    $months = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                @endphp
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
                @endfor
            </select>
        </div>

        <div>
            <label>Tahun</label>
            <select name="year" onchange="this.form.submit()">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" {{ ($year == $y || (!$year && now()->year == $y)) ? 'selected' : '' }}>{{ $y }}</option>
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

        <button type="submit" class="btn-action btn-print" style="padding: 6px 12px; font-size: 12px;">Filter</button>
    </form>
</div>

{{-- Kop Surat --}}
<div class="kop">
    <img src="{{ asset('img/logo-pemprov-bali.png') }}" alt="Logo Pemprov Bali" onerror="this.style.display='none'">
    <div class="kop-tengah">
        <div class="instansi">PEMERINTAH PROVINSI BALI<br>DINAS PENDIDIKAN, KEPEMUDAAN, DAN OLAHRAGA</div>
        <div class="nama-sekolah">SMA Negeri 1 Gianyar</div>
        <div class="alamat">Jl. Ratna No. 1, Gianyar, Bali 80511 | Telp: (0361) 943034 | Website: sman1-gianyar.sch.id</div>
    </div>
    <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo SMAN 1 Gianyar" onerror="this.src='https://via.placeholder.com/60?text=LOGO'">
</div>

{{-- Judul Laporan --}}
<div class="judul-blok">
    <h1>LAPORAN JURNAL MENGAJAR GURU</h1>
    <p>Periode: {{ $periodLabel }}</p>
</div>

{{-- Info Guru --}}
@php
    $totalPertemuan = $journals->count();
    $totalAbsen     = $journals->sum(fn($j) => $j->absences->count());
    $subjectNames   = $journals->pluck('subject.name')->filter()->unique()->implode(', ');
    if (!$subjectNames) {
        $subjectNames = $teacher->subjects->pluck('name')->implode(', ');
    }
    if (!$subjectNames) $subjectNames = $teacher->subject ?? '—';
@endphp
<table class="info-table">
    <tr>
        <td class="label">Nama Guru</td>
        <td class="sep">:</td>
        <td class="val"><strong>{{ $teacher->name }}</strong></td>
        <td class="label" style="text-align:right;">Mata Pelajaran</td>
        <td class="sep">:</td>
        <td class="val" style="width:200px;">{{ $subjectNames }}</td>
    </tr>
    <tr>
        <td class="label">NIP</td>
        <td class="sep">:</td>
        <td class="val">{{ $teacher->nip ?? '—' }}</td>
        <td class="label" style="text-align:right;">Kelas / Filter</td>
        <td class="sep">:</td>
        <td class="val">{{ $className ?? 'Semua Kelas' }}</td>
    </tr>
</table>

{{-- Tabel Jurnal --}}
@if($journals->isEmpty())
    <div style="padding: 30px; text-align: center; color: #666; font-style: italic; border: 1px dashed #ccc; margin: 15px 0;">
        Tidak ada catatan jurnal mengajar pada periode {{ $periodLabel }}.
    </div>
@else
    <table class="jurnal-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tgl">Hari / Tanggal</th>
                <th class="col-kls">Kelas</th>
                <th class="col-jam">Jam</th>
                <th class="col-mapel">Mapel</th>
                <th class="col-tp">Tujuan Pembelajaran (TP)</th>
                <th class="col-materi">Materi / Pokok Bahasan</th>
                <th class="col-akt">Kegiatan Pembelajaran</th>
                <th class="col-cat">Catatan</th>
                <th class="col-absen">Siswa Tidak Hadir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($journals as $i => $journal)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $journal->date?->isoFormat('dddd') }}</strong><br>
                    {{ $journal->date?->isoFormat('D MMM Y') }}
                </td>
                <td class="center"><strong>{{ $journal->schoolClass?->name ?? '—' }}</strong></td>
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
                        <span style="color:#666;font-style:italic">Hadir semua</span>
                    @else
                        @foreach($journal->absences as $abs)
                        @php
                            $cls = match($abs->status) {
                                'tidak_hadir', 'alpa' => 'absen-a',
                                'izin'                 => 'absen-i',
                                'sakit'                => 'absen-s',
                                'dispensasi'           => 'absen-d',
                                default                => '',
                            };
                            $lbl = match($abs->status) {
                                'tidak_hadir', 'alpa' => 'A',
                                'izin'                 => 'I',
                                'sakit'                => 'S',
                                'dispensasi'           => 'D',
                                default                => '?',
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

    <div class="summary-bar">
        <div class="summary-item">Total Pertemuan: <strong>{{ $totalPertemuan }}</strong></div>
        <div class="summary-item">Total Absen Siswa: <strong>{{ $totalAbsen }}</strong></div>
    </div>
@endif

{{-- Tanda Tangan Ganda --}}
<div class="ttd-wrap">
    <div class="ttd-box">
        <div class="ttd-lokasi">&nbsp;</div>
        <div class="ttd-jabatan">Mengetahui,<br>Kepala SMAN 1 Gianyar</div>
        <div class="ttd-nama">I Wayan Sudra Astra, S.Pd., M.Pd.</div>
        <div class="ttd-nip">NIP. 19710415 199703 1 007</div>
    </div>

    <div class="ttd-box">
        <div class="ttd-lokasi">Gianyar, {{ now()->isoFormat('D MMMM Y') }}</div>
        <div class="ttd-jabatan">Guru Mata Pelajaran,</div>
        <div class="ttd-nama">{{ $teacher->name }}</div>
        <div class="ttd-nip">NIP. {{ $teacher->nip ?? '—' }}</div>
    </div>
</div>

</body>
</html>
