<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<script src="https://cdn.tailwindcss.com"></script>
<style>
    @page { size: A4 portrait; margin: 15mm 15mm 15mm 15mm; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 8px; vertical-align: top; }
    thead th { background-color: #3730a3 !important; color: #fff; font-weight: 700; text-align: center; }
</style>
</head>
<body class="bg-white" style="font-family: Arial, sans-serif; font-size: 8px;">

{{-- KOP --}}
<table style="width:100%; border-collapse:collapse; margin-bottom:8px; padding-bottom:6px; border-bottom:3px double #000; border-top:none; border-left:none; border-right:none;">
    <tr>
        <td style="width:55px; text-align:center; vertical-align:middle; border:none; padding-right:6px;">
            <img src="{{ public_path('img/logo-pemprov-bali.png') }}" style="width:50px; height:50px; object-fit:contain;">
        </td>
        <td style="text-align:center; vertical-align:middle; border:none; line-height:1.5;">
            <div style="font-size:10px; font-weight:bold; font-family:'Times New Roman',serif;">PEMERINTAH PROVINSI BALI</div>
            <div style="font-size:10px; font-weight:bold; font-family:'Times New Roman',serif;">DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA</div>
            <div style="font-size:13px; font-weight:bold; font-family:'Times New Roman',serif;">SMA NEGERI 1 GIANYAR</div>
            <div style="font-size:8px; font-family:'Times New Roman',serif;">Jln. Ratna, Tegal Tugu Gianyar, Telp : (0361) 943034</div>
            <div style="font-size:7px;">Website: https://sman1-gianyar.sch.id &nbsp; E-mail: sman1.gianyar1963@gmail.com</div>
            <div style="font-size:7.5px; font-weight:bold;">NPSN : 50102079</div>
        </td>
        <td style="width:55px; text-align:center; vertical-align:middle; border:none; padding-left:6px;">
            <img src="{{ public_path('img/logo_sekolah.png') }}" style="width:50px; height:50px; object-fit:contain;">
        </td>
    </tr>
</table>

{{-- Judul --}}
<div style="text-align:center; margin-bottom:6px;">
    <div style="font-size:11px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">Jurnal Bimbingan Wali Kelas</div>
    <div style="font-size:8px; color:#6b7280; margin-top:2px;">
        Kelas: <strong style="color:#111;">{{ $class->name }}</strong>
        &nbsp;&middot;&nbsp;
        Wali Kelas: <strong style="color:#111;">{{ $teacher->name }}</strong>
        &nbsp;&middot;&nbsp;
        Periode: <strong style="color:#111;">{{ \Carbon\Carbon::parse($month . '-01')->isoFormat('MMMM Y') }}</strong>
    </div>
</div>

{{-- Tabel --}}
@if($consultations->isEmpty())
<div style="text-align:center; padding:20px; color:#9ca3af; font-size:9px;">
    Tidak ada data bimbingan selesai pada periode ini.
</div>
@else
<table>
    <thead>
        <tr>
            <th style="width:25px;">No</th>
            <th style="width:55px;">Tanggal</th>
            <th style="width:90px;">Nama Siswa</th>
            <th style="width:90px;">Topik</th>
            <th>Catatan Bimbingan</th>
            <th style="width:80px;">Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($consultations as $i => $c)
        <tr style="{{ $i % 2 === 0 ? 'background:#f8fafc;' : '' }}">
            <td style="text-align:center;">{{ $i + 1 }}</td>
            <td style="text-align:center;">{{ $c->conducted_date?->isoFormat('D MMM Y') ?? '-' }}</td>
            <td>{{ $c->student->name }}</td>
            <td>{{ $c->topic }}</td>
            <td>{{ $c->teacher_note ?? '-' }}</td>
            <td>{{ $c->follow_up ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Tanda tangan --}}
<div style="margin-top:24px; display:flex; justify-content:flex-end;">
    <div style="text-align:center; font-size:8px;">
        <p>Gianyar, {{ now()->isoFormat('D MMMM Y') }}</p>
        <p style="margin-top:2px;">Wali Kelas</p>
        <div style="margin-top:40px; border-bottom:1px solid #000; width:140px;"></div>
        <p style="margin-top:3px; font-weight:bold;">{{ $teacher->name }}</p>
        @if($teacher->nip)
            <p style="color:#6b7280;">NIP. {{ $teacher->nip }}</p>
        @endif
    </div>
</div>

{{-- Footer --}}
<div style="margin-top:10px; padding-top:4px; border-top:0.5px solid #e5e7eb; display:flex; justify-content:space-between; font-size:6.5px; color:#9ca3af;">
    <span>Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} WIB</span>
    <span>SIMS — SMA Negeri 1 Gianyar</span>
</div>

</body>
</html>
