<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; }
        .header { text-align: center; border-bottom: 3px double #065f46; padding-bottom: 10px; margin-bottom: 14px; }
        .header img { width: 52px; height: 52px; object-fit: contain; margin-bottom: 4px; }
        .header h1 { font-size: 14px; font-weight: bold; color: #065f46; }
        .header p  { font-size: 8.5px; color: #6b7280; margin-top: 1px; }
        .title { text-align: center; margin-bottom: 12px; }
        .title h2 { font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .title p  { font-size: 8.5px; color: #6b7280; margin-top: 2px; }
        .student-info { display: flex; gap: 16px; margin-bottom: 14px; border: 1px solid #d1fae5; border-radius: 6px; padding: 10px 12px; background: #f0fdf4; }
        .student-info .row { display: flex; flex-direction: column; gap: 4px; flex: 1; }
        .student-info .lbl { font-size: 7.5px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; }
        .student-info .val { font-size: 10px; font-weight: bold; color: #065f46; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th { background: #065f46; color: white; padding: 6px 8px; text-align: left; font-size: 8.5px; }
        th.num { text-align: center; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        td.num { text-align: center; }
        tr:nth-child(even) td { background: #f9fafb; }
        .g { color: #059669; font-weight: bold; }
        .y { color: #d97706; font-weight: bold; }
        .r { color: #dc2626; font-weight: bold; }
        .avg-row td { background: #ecfdf5 !important; font-weight: bold; border-top: 2px solid #6ee7b7; }
        .summary { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; margin-bottom: 14px; background: #fafafa; }
        .summary-grid { display: flex; gap: 12px; }
        .summary-box { flex: 1; text-align: center; padding: 8px; border-radius: 4px; }
        .summary-box .big { font-size: 18px; font-weight: bold; }
        .summary-box .sm  { font-size: 7.5px; color: #6b7280; margin-top: 1px; }
        .footer { margin-top: 30px; display: flex; justify-content: flex-end; font-size: 8.5px; }
        .signature { text-align: center; }
        .signature p { margin-bottom: 40px; }
        .signature .line { border-top: 1px solid #374151; width: 150px; margin: 0 auto; padding-top: 4px; }
        .watermark { font-size: 7px; color: #9ca3af; text-align: center; margin-top: 14px; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    @if(file_exists(public_path('img/logo_sekolah.png')))
    <img src="{{ public_path('img/logo_sekolah.png') }}"><br>
    @endif
    <h1>SMA Negeri 1 Gianyar</h1>
    <p>Jl. Ngurah Rai No. 1, Gianyar, Bali</p>
</div>

{{-- Title --}}
<div class="title">
    <h2>Laporan Hasil Belajar Siswa</h2>
    <p>Semester {{ $semester }} &nbsp;·&nbsp; Tahun Ajaran {{ $academicYear }}</p>
</div>

{{-- Student Info --}}
<div class="student-info">
    <div class="row">
        <span class="lbl">Nama Siswa</span>
        <span class="val">{{ $siswa->name }}</span>
    </div>
    <div class="row">
        <span class="lbl">NIS</span>
        <span class="val">{{ $siswa->nis ?? '—' }}</span>
    </div>
    <div class="row">
        <span class="lbl">Kelas</span>
        <span class="val">{{ $siswa->schoolClass?->name ?? '—' }}</span>
    </div>
    <div class="row">
        <span class="lbl">Tanggal Cetak</span>
        <span class="val">{{ now()->isoFormat('D MMMM Y') }}</span>
    </div>
</div>

{{-- Grade Table --}}
<table>
    <thead>
        <tr>
            <th style="width:30px">No</th>
            <th>Mata Pelajaran</th>
            <th class="num" style="width:45px">UH 1</th>
            <th class="num" style="width:45px">UH 2</th>
            <th class="num" style="width:45px">UH 3</th>
            <th class="num" style="width:50px">Rata UH</th>
            <th class="num" style="width:50px">UTS</th>
            <th class="num" style="width:50px">UAS</th>
            <th class="num" style="width:55px">Nilai Akhir</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; $allFinals = []; @endphp
        @foreach($subjects as $subject)
        @php
            $sg   = $grades->get($subject->id, collect());
            $uhs  = $sg->where('type','UH')->pluck('score')->values();
            $uts  = $sg->firstWhere('type','UTS')?->score;
            $uas  = $sg->firstWhere('type','UAS')?->score;
            $uhA  = $uhs->isNotEmpty() ? round($uhs->average(),1) : null;
            $parts= []; $w = 0;
            if($uhA!==null){ $parts[]=$uhA*0.4;  $w+=0.4; }
            if($uts!==null){  $parts[]=$uts*0.3;  $w+=0.3; }
            if($uas!==null){  $parts[]=$uas*0.3;  $w+=0.3; }
            $final = $w>0 ? round(array_sum($parts)/$w,1) : null;
            if($final!==null) $allFinals[] = $final;
            $cls = fn($v) => $v===null ? '' : ($v>=80 ? 'g' : ($v>=65 ? 'y' : 'r'));
        @endphp
        <tr>
            <td class="num">{{ $no++ }}</td>
            <td>{{ $subject->name }}</td>
            <td class="num {{ $cls($uhs->get(0)) }}">{{ $uhs->get(0) !== null ? number_format($uhs->get(0),0) : '—' }}</td>
            <td class="num {{ $cls($uhs->get(1)) }}">{{ $uhs->get(1) !== null ? number_format($uhs->get(1),0) : '—' }}</td>
            <td class="num {{ $cls($uhs->get(2)) }}">{{ $uhs->get(2) !== null ? number_format($uhs->get(2),0) : '—' }}</td>
            <td class="num {{ $cls($uhA) }}">{{ $uhA ?? '—' }}</td>
            <td class="num {{ $cls($uts) }}">{{ $uts !== null ? number_format($uts,0) : '—' }}</td>
            <td class="num {{ $cls($uas) }}">{{ $uas !== null ? number_format($uas,0) : '—' }}</td>
            <td class="num {{ $cls($final) }}" style="font-size:10.5px">{{ $final ?? '—' }}</td>
        </tr>
        @endforeach
        @if(count($allFinals) > 0)
        @php $overallAvg = round(array_sum($allFinals)/count($allFinals),1); @endphp
        <tr class="avg-row">
            <td colspan="8" style="text-align:right; padding-right:10px;">Rata-rata Keseluruhan</td>
            <td class="num {{ $overallAvg>=80?'g':($overallAvg>=65?'y':'r') }}" style="font-size:11px">{{ $overallAvg }}</td>
        </tr>
        @endif
    </tbody>
</table>

@if(count($allFinals) > 0)
{{-- Summary boxes --}}
<div class="summary">
    <div class="summary-grid">
        @php $above80 = count(array_filter($allFinals, fn($v)=>$v>=80)); @endphp
        @php $above65 = count(array_filter($allFinals, fn($v)=>$v>=65&&$v<80)); @endphp
        @php $below65 = count(array_filter($allFinals, fn($v)=>$v<65)); @endphp
        <div class="summary-box" style="background:#dcfce7;">
            <div class="big g">{{ $above80 }}</div>
            <div class="sm">Mapel ≥ 80</div>
        </div>
        <div class="summary-box" style="background:#fef9c3;">
            <div class="big y">{{ $above65 }}</div>
            <div class="sm">Mapel 65–79</div>
        </div>
        <div class="summary-box" style="background:#fee2e2;">
            <div class="big r">{{ $below65 }}</div>
            <div class="sm">Mapel &lt; 65</div>
        </div>
        <div class="summary-box" style="background:#f0f9ff;">
            <div class="big" style="color:#0369a1">{{ $overallAvg }}</div>
            <div class="sm">Rata-rata</div>
        </div>
    </div>
</div>
@endif

{{-- Signature --}}
<div class="footer">
    <div class="signature">
        <p>Gianyar, {{ now()->isoFormat('D MMMM Y') }}</p>
        <div class="line">Wali Kelas</div>
    </div>
</div>

<div class="watermark">
    Dokumen ini digenerate otomatis oleh SIMS — School Integrated Management System
</div>

</body>
</html>
