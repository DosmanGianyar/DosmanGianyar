<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekapitulasi Absensi Siswa Bulanan</title>
<style>
    @page { size: A4 landscape; margin: 8mm 10mm 10mm 10mm; }
    body { font-family: 'Times New Roman', Times, serif; font-size: 8px; color: #000; margin: 0; padding: 0; background: #fff; }
    
    .kop-container { text-align: center; border-bottom: 3px double #000; padding-bottom: 4px; margin-bottom: 8px; position: relative; }
    .kop-logo-left { position: absolute; left: 10px; top: 2px; width: 52px; height: 52px; object-fit: contain; }
    .kop-logo-right { position: absolute; right: 10px; top: 2px; width: 52px; height: 52px; object-fit: contain; }
    .kop-text { text-align: center; line-height: 1.25; }
    .kop-text .l1 { font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .kop-text .l2 { font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .kop-text .l3 { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
    .kop-text .l4 { font-size: 8.5px; font-style: italic; }
    .kop-text .l5 { font-size: 8px; }

    .title-box { text-align: center; margin-bottom: 10px; }
    .title-main { font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: underline; }
    .title-sub { font-size: 9.5px; font-weight: bold; margin-top: 2px; }

    .meta-table { width: 100%; margin-bottom: 6px; font-size: 9px; font-weight: bold; border-collapse: collapse; }
    .meta-table td { padding: 1px 0; }

    table.grid-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-family: Arial, Helvetica, sans-serif; font-size: 7px; }
    table.grid-table th, table.grid-table td { border: 0.5px solid #000; text-align: center; vertical-align: middle; padding: 1px 0; height: 16px; }
    table.grid-table th { background-color: #f3f4f6; font-weight: bold; }
    
    /* Badges */
    .bg-hadir { background-color: #22c55e !important; color: #ffffff !important; font-weight: bold; } /* Hijau */
    .bg-terlambat { background-color: #eab308 !important; color: #ffffff !important; font-weight: bold; } /* Kuning */
    .bg-lupa { background-color: #a855f7 !important; color: #ffffff !important; font-weight: bold; } /* Ungu */
    .bg-sakit { background-color: #c084fc !important; color: #ffffff !important; font-weight: bold; } /* Violet */
    .bg-izin { background-color: #60a5fa !important; color: #ffffff !important; font-weight: bold; } /* Biru */
    .bg-dispensasi { background-color: #14b8a6 !important; color: #ffffff !important; font-weight: bold; } /* Teal */
    .bg-alpa { background-color: #ef4444 !important; color: #ffffff !important; font-weight: bold; } /* Merah */
    .bg-libur { background-color: #e5e7eb !important; color: #9ca3af !important; } /* Abu Libur */
    .bg-future { background-color: #fafafa !important; color: #d1d5db !important; } /* Belum Berjalan */

    .summary-col { font-weight: bold; font-size: 7.5px; }

    .notes-section { margin-top: 8px; font-size: 7.5px; line-height: 1.4; font-family: Arial, sans-serif; }
    
    .signature-container { margin-top: 15px; width: 100%; font-size: 9.5px; font-family: 'Times New Roman', Times, serif; }
    .signature-table { width: 100%; border-collapse: collapse; }
    .signature-table td { width: 50%; vertical-align: top; text-align: center; border: none; }
</style>
</head>
<body>

@php
    use Carbon\Carbon;
    $start        = Carbon::parse($month . '-01');
    $daysInMonth  = $start->daysInMonth;
    $today        = now();
    $monthName    = $start->isoFormat('MMMM');
    $yearNum      = $start->year;

    // Indonesian short day names: 1(Mon)->Sn, 2(Tue)->Sl, 3(Wed)->Rb, 4(Thu)->Km, 5(Fri)->Jm, 6(Sat)->Sb, 7(Sun)->Mg
    $shortDays = [1 => 'Sn', 2 => 'Sl', 3 => 'Rb', 4 => 'Km', 5 => 'Jm', 6 => 'Sb', 7 => 'Mg'];
@endphp

{{-- ── KOP SURAT ─────────────────────────────────────────────────────────── --}}
<div class="kop-container">
    @if(file_exists(public_path('img/logo-pemprov-bali.png')))
        <img class="kop-logo-left" src="{{ public_path('img/logo-pemprov-bali.png') }}" alt="Logo Bali">
    @endif
    <div class="kop-text">
        <div class="l1">PEMERINTAH PROVINSI BALI</div>
        <div class="l2">DINAS PENDIDIKAN, KEPEMUDAAN, DAN OLAHRAGA</div>
        <div class="l3">SMA NEGERI 1 GIANYAR</div>
        <div class="l4">Jl. Ratna No. 1, Gianyar, Bali 80511 | Telp: (0361) 943034 | Website: sman1-gianyar.sch.id</div>
    </div>
    @if(file_exists(public_path('img/logo_sekolah.png')))
        <img class="kop-logo-right" src="{{ public_path('img/logo_sekolah.png') }}" alt="Logo SMAN1">
    @endif
</div>

{{-- ── JUDUL LAPORAN ────────────────────────────────────────────────────── --}}
<div class="title-box">
    <div class="title-main">REKAPITULASI ABSENSI SISWA BULANAN (TGL. 1 S/D {{ $daysInMonth }})</div>
    <div class="title-sub">Bulan: {{ $monthName }} {{ $yearNum }}</div>
</div>

{{-- ── META INFORMATION ──────────────────────────────────────────────────── --}}
<table class="meta-table">
    <tr>
        <td style="text-align:left;">Kelas: {{ $className }}</td>
        <td style="text-align:right;">Guru / Wali Kelas: {{ $homeroomName ?? '—' }}</td>
    </tr>
</table>

{{-- ── TABEL PRESENSI GRID ────────────────────────────────────────────────── --}}
<table class="grid-table">
    <thead>
        <tr>
            <th style="width: 18px;" rowspan="2">No</th>
            <th style="width: 45px;" rowspan="2">NIS</th>
            <th style="text-align: left; padding-left: 4px;" rowspan="2">Nama Siswa</th>
            <th colspan="{{ $daysInMonth }}">Tanggal (Bulan {{ $monthName }} {{ $yearNum }})</th>
            <th style="width: 110px;" colspan="5">Rekap Ketidakberhadiran</th>
            <th style="width: 44px;" colspan="2">Detail</th>
        </tr>
        <tr>
            @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $curDate = $start->copy()->setDay($d);
                    $dayIso  = $curDate->dayOfWeekIso;
                    $dayName = $shortDays[$dayIso] ?? '';
                    $isWknd  = $curDate->isWeekend();
                @endphp
                <th style="{{ $isWknd ? 'background-color: #e5e7eb; color: #6b7280;' : '' }}">
                    {{ $dayName }}<br><span style="font-size: 6.5px;">{{ $d }}</span>
                </th>
            @endfor
            <th style="width: 22px; background-color: #f3e8ff; color: #7e22ce;">S</th>
            <th style="width: 22px; background-color: #dbeafe; color: #1e40af;">I</th>
            <th style="width: 22px; background-color: #fee2e2; color: #991b1b;">A</th>
            <th style="width: 22px; background-color: #ccfbf1; color: #0f766e;">D</th>
            <th style="width: 22px; background-color: #f3f4f6; color: #4b5563;">L</th>
            <th style="width: 22px; background-color: #fef3c7; color: #92400e;">T</th>
            <th style="width: 22px; background-color: #f3e8ff; color: #6b21a8;">Lp</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $idx => $student)
            @php
                $studentGrid = $grid[$student->id] ?? [];
                $sakitCount = 0;
                $izinCount = 0;
                $alpaCount = 0;
                $dispensasiCount = 0;
                $liburCount = 0;
                $terlambatCount = 0;
                $lupaCount = 0;
            @endphp
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td style="font-size: 6.5px;">{{ $student->nis ?? '—' }}</td>
                <td style="text-align: left; padding-left: 4px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $student->name }}
                </td>

                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $curDate = $start->copy()->setDay($d);
                        $dateStr = $curDate->toDateString();
                        $isWeekend = $curDate->isWeekend();
                        $isFuture  = $curDate->gt($today);
                        
                        $attInfo = $studentGrid[$d] ?? null;
                        $status  = is_array($attInfo) ? ($attInfo['status'] ?? null) : $attInfo;
                        $viaLupa = is_array($attInfo) ? ($attInfo['via_lupa_absen'] ?? false) : false;

                        $badgeClass = '';
                        $char = '';

                        if ($isFuture) {
                            $badgeClass = 'bg-future';
                            $char = '-';
                        } elseif ($isWeekend) {
                            $badgeClass = 'bg-libur';
                            $char = 'L';
                            $liburCount++;
                        } elseif ($status === 'hadir') {
                            if ($viaLupa) {
                                $badgeClass = 'bg-lupa';
                                $char = 'H';
                                $lupaCount++;
                            } else {
                                $badgeClass = 'bg-hadir';
                                $char = 'H';
                            }
                        } elseif ($status === 'terlambat') {
                            $badgeClass = 'bg-terlambat';
                            $char = 'H';
                            $terlambatCount++;
                        } elseif ($status === 'sakit') {
                            $badgeClass = 'bg-sakit';
                            $char = 'S';
                            $sakitCount++;
                        } elseif ($status === 'izin') {
                            $badgeClass = 'bg-izin';
                            $char = 'I';
                            $izinCount++;
                        } elseif ($status === 'dispensasi') {
                            $badgeClass = 'bg-dispensasi';
                            $char = 'D';
                            $dispensasiCount++;
                        } else {
                            // Belum Absen Pagi / Alpa untuk tanggal yang sudah berlalu atau hari ini
                            $badgeClass = 'bg-alpa';
                            $char = 'A';
                            $alpaCount++;
                        }
                    @endphp
                    <td class="{{ $badgeClass }}">{{ $char }}</td>
                @endfor

                <td class="summary-col" style="color: #7e22ce;">{{ $sakitCount ?: '-' }}</td>
                <td class="summary-col" style="color: #1e40af;">{{ $izinCount ?: '-' }}</td>
                <td class="summary-col" style="color: #dc2626;">{{ $alpaCount ?: '-' }}</td>
                <td class="summary-col" style="color: #0f766e;">{{ $dispensasiCount ?: '-' }}</td>
                <td class="summary-col" style="color: #4b5563;">{{ $liburCount ?: '-' }}</td>
                <td class="summary-col" style="color: #b45309;">{{ $terlambatCount ?: '-' }}</td>
                <td class="summary-col" style="color: #6b21a8;">{{ $lupaCount ?: '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ── KETERANGAN & CATATAN ──────────────────────────────────────────────── --}}
<div class="notes-section">
    <div><strong>* Keterangan Status:</strong> H = Hadir, S = Sakit, I = Izin, A = Alpa / Tanpa Keterangan, D = Dispensasi, L = Libur Sekolah / Hari Minggu.</div>
    <div><strong>* Indikator Warna Badge:</strong> 
        <span style="color:#16a34a; font-weight:bold;">[H] Hijau</span> = Hadir Tepat Waktu | 
        <span style="color:#ca8a04; font-weight:bold;">[H] Kuning</span> = Terlambat | 
        <span style="color:#9333ea; font-weight:bold;">[H] Ungu</span> = Lupa Absen / Klaim | 
        <span style="color:#dc2626; font-weight:bold;">[A] Merah</span> = Alpa / Belum Absen.
    </div>
    <div style="font-style: italic; color: #4b5563; margin-top: 2px;">
        * Perhitungan Alpa (A) hanya dihitung untuk hari sekolah yang sudah berlalu (tanggal 1 s/d {{ $today->isoFormat('D MMMM Y') }}). Tanggal yang belum berjalan ditandai dengan (-) dan tidak dihitung Alpa.
    </div>
</div>

{{-- ── TANDA TANGAN (TTD) ───────────────────────────────────────────────── --}}
<div class="signature-container">
    <table class="signature-table">
        <tr>
            <td style="text-align: center;">
                Mengetahui,<br>
                <strong>Kepala SMAN 1 Gianyar</strong>
                <br><br><br><br><br>
                <strong><u>{{ $headmasterName ?? 'I Wayan Sutrisna, S.Pd., M.Pd.' }}</u></strong><br>
                NIP. {{ $headmasterNip ?? '19710415 199703 1 007' }}
            </td>
            <td style="text-align: center;">
                Gianyar, {{ $today->isoFormat('D MMMM Y') }}<br>
                <strong>Guru Pengajar / Wali Kelas</strong>
                <br><br><br><br><br>
                <strong><u>{{ $homeroomName ?? '—' }}</u></strong><br>
                NIP. {{ $homeroomNip ?? '—' }}
            </td>
        </tr>
    </table>
</div>

</body>
</html>
