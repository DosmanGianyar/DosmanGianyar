<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Jurnal Mengajar — {{ $teacher->name }}</title>
<style>
@page {
    size: A4 portrait;
    margin: 1.5cm 2cm;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 10.5pt;
    color: #000;
    background: #fff;
}

.weekly-page {
    page-break-after: always;
}
.weekly-page:last-child {
    page-break-after: auto;
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
    margin: 14px 0 10px;
}
.judul-blok h1 {
    font-size: 12pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: underline;
}
.judul-blok p {
    font-size: 9.5pt;
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
    font-size: 10pt;
    vertical-align: top;
}
.info-table .label { width: 140px; font-weight: normal; }
.info-table .sep   { width: 16px; text-align: center; }
.info-table .val   { font-weight: normal; }

/* ─ Tabel Jurnal ─ */
.jurnal-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
    font-size: 9pt;
}
.jurnal-table th {
    background: #f0f0f0;
    border: 1px solid #444;
    padding: 5px 4px;
    text-align: center;
    font-weight: bold;
    font-size: 8.5pt;
}
.jurnal-table td {
    border: 1px solid #555;
    padding: 5px 5px;
    vertical-align: top;
    line-height: 1.35;
}
.jurnal-table td.center { text-align: center; }

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
    padding: 1px 3px;
    border-radius: 2px;
    margin-right: 2px;
}
.absen-a { background: #fee2e2; color: #991b1b; }
.absen-i { background: #e0f2fe; color: #075985; }
.absen-s { background: #fef3c7; color: #92400e; }

/* Ringkasan */
.summary-row {
    display: flex;
    gap: 20px;
    margin-bottom: 16px;
    font-size: 9.5pt;
}
.summary-item {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    padding: 4px 10px;
    border-radius: 4px;
}
.summary-item .badge {
    font-weight: bold;
    color: #1e40af;
}

/* ─ Tanda Tangan ─ */
.ttd-wrap {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
    page-break-inside: avoid;
}
.ttd-box {
    width: 220px;
    text-align: center;
    font-size: 9.5pt;
}
.ttd-box .ttd-lokasi { margin-bottom: 2px; }
.ttd-box .ttd-jabatan { font-weight: bold; margin-bottom: 48px; }
.ttd-box .ttd-nama { font-weight: bold; text-decoration: underline; }
.ttd-box .ttd-nip { font-size: 8.5pt; color: #333; margin-top: 1px; }

@media print {
    .no-print { display: none !important; }
}
</style>
</head>
<body>

{{-- Tombol Cetak / Kembali (No Print) --}}
<div class="no-print" style="position:fixed; top:15px; right:15px; z-index:9999; display:flex; gap:10px;">
    <button onclick="window.print()" style="padding:8px 16px; background:#2563eb; color:#fff; font-weight:bold; border:none; border-radius:6px; cursor:pointer; shadow:0 2px 4px rgba(0,0,0,0.2);">
        🖨️ Cetak Jurnal Bulanan (PDF)
    </button>
    <button onclick="window.close()" style="padding:8px 16px; background:#64748b; color:#fff; font-weight:bold; border:none; border-radius:6px; cursor:pointer;">
        Tutup
    </button>
</div>

@foreach($weeklyGroups as $group)
<div class="weekly-page">

    {{-- Kop Surat --}}
    <div class="kop">
        <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo SMAN 1 Gianyar"
             onerror="this.src='https://via.placeholder.com/64?text=LOGO'">
        <div class="kop-tengah">
            <div class="instansi">PEMERINTAH PROVINSI BALI<br>DINAS PENDIDIKAN, KEPEMUDAAN, DAN OLAHRAGA</div>
            <div class="nama-sekolah">SMA Negeri 1 Gianyar</div>
            <div class="alamat">Jl. Ratna No. 1, Gianyar, Bali 80511 | Telp: (0361) 943034 | Website: sman1-gianyar.sch.id</div>
        </div>
    </div>

    {{-- Judul --}}
    <div class="judul-blok">
        <h1>LAPORAN JURNAL MENGAJAR GURU</h1>
        <p>Minggu Ke-{{ $group['week_number'] }} · Periode: {{ $group['period_label'] }}</p>
    </div>

    {{-- Info Guru --}}
    <table class="info-table">
        <tr>
            <td class="label">Nama Guru</td>
            <td class="sep">:</td>
            <td class="val"><strong>{{ $teacher->name }}</strong></td>
            <td class="label" style="text-align:right;">Bulan / Tahun</td>
            <td class="sep">:</td>
            <td class="val" style="width:120px;">{{ ($month && $year) ? \Illuminate\Support\Carbon::create($year, $month, 1)->isoFormat('MMMM Y') : ($year ? "Tahun $year" : "Semua Periode") }}</td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td class="sep">:</td>
            <td class="val">{{ $teacher->nip ?? '—' }}</td>
            <td class="label" style="text-align:right;">Kelas Filter</td>
            <td class="sep">:</td>
            <td class="val">{{ $className ?? 'Semua Kelas' }}</td>
        </tr>
    </table>

    {{-- Tabel Jurnal --}}
    @if($group['journals']->isEmpty())
        <div style="padding: 30px; text-align: center; color: #666; font-style: italic; border: 1px dashed #ccc; margin: 15px 0;">
            Tidak ada catatan jurnal mengajar pada Minggu Ke-{{ $group['week_number'] }} ({{ $group['period_label'] }}).
        </div>
    @else
        <table class="jurnal-table">
            <thead>
                <tr>
                    <th style="width:28px">No</th>
                    <th style="width:75px">Tanggal</th>
                    <th style="width:65px">Kelas</th>
                    <th style="width:45px">Jam</th>
                    <th style="width:110px">Mata Pelajaran</th>
                    <th>Tujuan Pembelajaran (TP)</th>
                    <th>Materi / Pokok Bahasan</th>
                    <th>Kegiatan Pembelajaran</th>
                    <th style="width:80px">Catatan</th>
                    <th style="width:110px">Siswa Tidak Hadir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group['journals'] as $i => $journal)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $journal->date?->isoFormat('D MMM Y') }}</td>
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
                                    default                => '',
                                };
                                $lbl = match($abs->status) {
                                    'tidak_hadir', 'alpa' => 'A',
                                    'izin'                 => 'I',
                                    'sakit'                => 'S',
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

        <div class="summary-row">
            <div class="summary-item">Pertemuan Minggu Ini: <span class="badge">{{ $group['total_pertemuan'] }}</span></div>
            <div class="summary-item">Siswa Tidak Hadir: <span class="badge">{{ $group['total_absen'] }}</span></div>
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

</div>
@endforeach

</body>
</html>
