<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 15mm 18mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1f2937; }
        .header { display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #059669; padding-bottom: 8px; margin-bottom: 12px; }
        .header img { width: 40px; height: 40px; object-fit: contain; }
        .header-text h1 { font-size: 13px; font-weight: bold; color: #065f46; }
        .header-text p { font-size: 8px; color: #6b7280; margin-top: 1px; }
        .meta { margin-bottom: 10px; font-size: 8.5px; color: #374151; }
        .meta span { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #064e3b; color: white; padding: 5px 6px; text-align: left; font-size: 8px; }
        th.num { text-align: center; }
        td { padding: 4px 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        td.num { text-align: center; }
        tr:nth-child(even) td { background: #f9fafb; }
        .score-good { color: #059669; font-weight: bold; }
        .score-mid  { color: #d97706; font-weight: bold; }
        .score-bad  { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 12px; font-size: 7.5px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>

<div class="header">
    @if(file_exists(public_path('img/logo_sekolah.png')))
    <img src="{{ public_path('img/logo_sekolah.png') }}">
    @endif
    <div class="header-text">
        <h1>SMA Negeri 1 Gianyar</h1>
        <p>Rekap Nilai Siswa — Kelas {{ $className }} · Semester {{ $semester }} · TA {{ $academicYear }}</p>
    </div>
</div>

<div class="meta">
    Dicetak: <span>{{ now()->isoFormat('D MMMM Y, HH:mm') }} WIB</span> &nbsp;|&nbsp;
    Jumlah Siswa: <span>{{ $students->count() }}</span> &nbsp;|&nbsp;
    Mata Pelajaran: <span>{{ $subjects->count() }}</span>
</div>

@foreach($students as $student)
@php $studentGrades = $grades->get($student->id, collect())->groupBy('subject_id'); @endphp
@if($studentGrades->isNotEmpty())
<div style="margin-bottom:10px;">
    <p style="font-size:9px;font-weight:bold;background:#ecfdf5;padding:4px 6px;border-radius:4px;margin-bottom:3px;">
        {{ $loop->iteration }}. {{ $student->name }} &nbsp;—&nbsp; NIS: {{ $student->nis }}
    </p>
    <table>
        <thead>
            <tr>
                <th>Mata Pelajaran</th>
                <th class="num" style="width:60px">UH 1</th>
                <th class="num" style="width:60px">UH 2</th>
                <th class="num" style="width:60px">UH 3</th>
                <th class="num" style="width:55px">Rata UH</th>
                <th class="num" style="width:55px">UTS</th>
                <th class="num" style="width:55px">UAS</th>
                <th class="num" style="width:55px">Rerata</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjects as $subject)
            @php
                $sg   = $studentGrades->get($subject->id, collect());
                $uhs  = $sg->where('type','UH')->pluck('score')->values();
                $uts  = $sg->firstWhere('type','UTS')?->score;
                $uas  = $sg->firstWhere('type','UAS')?->score;
                $uhAvg= $uhs->isNotEmpty() ? round($uhs->average(),1) : null;
                $parts= []; $w = 0;
                if($uhAvg!==null){ $parts[]=$uhAvg*0.4; $w+=0.4; }
                if($uts!==null){   $parts[]=$uts*0.3;   $w+=0.3; }
                if($uas!==null){   $parts[]=$uas*0.3;   $w+=0.3; }
                $avg  = $w>0 ? round(array_sum($parts)/$w,1) : null;
                $cls  = fn($v) => $v===null?'':($v>=80?'score-good':($v>=65?'score-mid':'score-bad'));
            @endphp
            @if($sg->isNotEmpty())
            <tr>
                <td>{{ $subject->name }}</td>
                <td class="num {{ $cls($uhs->get(0)) }}">{{ $uhs->get(0) ?? '—' }}</td>
                <td class="num {{ $cls($uhs->get(1)) }}">{{ $uhs->get(1) ?? '—' }}</td>
                <td class="num {{ $cls($uhs->get(2)) }}">{{ $uhs->get(2) ?? '—' }}</td>
                <td class="num {{ $cls($uhAvg) }}">{{ $uhAvg ?? '—' }}</td>
                <td class="num {{ $cls($uts) }}">{{ $uts ?? '—' }}</td>
                <td class="num {{ $cls($uas) }}">{{ $uas ?? '—' }}</td>
                <td class="num {{ $cls($avg) }}" style="font-size:9.5px">{{ $avg ?? '—' }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endforeach

<div class="footer">
    SIMS — School Integrated Management System · {{ now()->format('d/m/Y H:i') }}
</div>
</body>
</html>
