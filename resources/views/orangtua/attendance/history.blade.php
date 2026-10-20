@extends('layouts.orangtua')
@section('title', 'Riwayat Presensi')
@section('page-title', 'Riwayat Presensi')

@section('content')
<div class="max-w-lg mx-auto space-y-4 pb-8">

    {{-- ─── Header & Navigasi Bulan ─────────────────────────────────────── --}}
    <div class="rounded-3xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #0f2460 0%, #1a3a8f 50%, #1e3fad 100%) !important; color: #ffffff !important;">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('orangtua.attendance.history', ['studentId' => $student->id, 'month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                class="w-9 h-9 bg-white/15 backdrop-blur-md rounded-xl flex items-center justify-center hover:bg-white/25 transition-colors border border-white/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="text-center">
                <p class="text-lg font-bold">{{ $start->isoFormat('MMMM Y') }}</p>
                <p class="text-blue-200 text-xs mt-0.5 font-medium">{{ $student->name }} (Kelas {{ $student->schoolClass?->name ?? '—' }})</p>
            </div>
            @if($canNext)
            <a href="{{ route('orangtua.attendance.history', ['studentId' => $student->id, 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                class="w-9 h-9 bg-white/15 backdrop-blur-md rounded-xl flex items-center justify-center hover:bg-white/25 transition-colors border border-white/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @else
            <div class="w-9 h-9"></div>
            @endif
        </div>

        {{-- Overall Attendance Percentage Gauge --}}
        <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-3.5 mb-3 flex items-center justify-between">
            <div>
                <p class="text-blue-200 text-[11px] font-semibold uppercase tracking-wider">Tingkat Kehadiran Bulan Ini</p>
                <p class="text-2xl font-black mt-0.5">{{ $percentage }}%</p>
                <p class="text-blue-100 text-[11px] mt-0.5">Total {{ $totalDays }} Hari Efektif Sekolah</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center text-xl shrink-0">
                📊
            </div>
        </div>

        {{-- Ringkasan status bulan ini --}}
        <div class="grid grid-cols-3 gap-2">
            <div class="bg-white/15 backdrop-blur-md rounded-xl py-2.5 text-center border border-white/10">
                <p class="text-emerald-300 font-extrabold text-xl leading-none">{{ $summary['hadir'] }}</p>
                <p class="text-blue-100 text-[11px] mt-1 font-medium">Hadir</p>
            </div>
            <div class="bg-white/15 backdrop-blur-md rounded-xl py-2.5 text-center border border-white/10">
                <p class="text-amber-300 font-extrabold text-xl leading-none">{{ $summary['terlambat'] }}</p>
                <p class="text-blue-100 text-[11px] mt-1 font-medium">Terlambat</p>
            </div>
            <div class="bg-white/15 backdrop-blur-md rounded-xl py-2.5 text-center border border-white/10">
                <p class="text-rose-300 font-extrabold text-xl leading-none">{{ $summary['alpa'] }}</p>
                <p class="text-blue-100 text-[11px] mt-1 font-medium">Alpa</p>
            </div>
            <div class="bg-white/15 backdrop-blur-md rounded-xl py-2.5 text-center border border-white/10">
                <p class="text-sky-300 font-extrabold text-xl leading-none">{{ $summary['izin'] }}</p>
                <p class="text-blue-100 text-[11px] mt-1 font-medium">Izin</p>
            </div>
            <div class="bg-white/15 backdrop-blur-md rounded-xl py-2.5 text-center border border-white/10">
                <p class="text-purple-300 font-extrabold text-xl leading-none">{{ $summary['sakit'] }}</p>
                <p class="text-blue-100 text-[11px] mt-1 font-medium">Sakit</p>
            </div>
            <div class="bg-white/15 backdrop-blur-md rounded-xl py-2.5 text-center border border-white/10">
                <p class="text-teal-300 font-extrabold text-xl leading-none">{{ $summary['dispensasi'] }}</p>
                <p class="text-blue-100 text-[11px] mt-1 font-medium">Dispen</p>
            </div>
        </div>

        {{-- Stat Pengajuan Lupa Absen --}}
        <div class="mt-2.5 bg-white/15 backdrop-blur-md rounded-xl p-2.5 px-3 border border-white/10 flex items-center justify-between text-xs text-white">
            <div class="flex items-center gap-2">
                <span class="text-amber-300 text-sm">📝</span>
                <div>
                    <span class="text-blue-100 font-medium">Pengajuan Lupa Absen:</span>
                    <strong class="text-white ml-1">{{ $summary['lupa_absen'] ?? 0 }} Kali</strong>
                </div>
            </div>
            <div class="text-[11px] text-blue-100">
                <span class="text-emerald-300 font-semibold">{{ $summary['lupa_absen_approved'] ?? 0 }} Disetujui</span>
                @if(($summary['lupa_absen_pending'] ?? 0) > 0)
                    • <span class="text-amber-300 font-semibold">{{ $summary['lupa_absen_pending'] }} Menunggu</span>
                @endif
                @if(($summary['lupa_absen_rejected'] ?? 0) > 0)
                    • <span class="text-rose-300 font-semibold">{{ $summary['lupa_absen_rejected'] }} Ditolak</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Rincian Pengajuan Lupa Absen (Jika Ada) ───────────────────────── --}}
    @if(!empty($forgotRequests) && count($forgotRequests) > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-amber-200/80 p-4 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-base">📝</span>
                <h2 class="text-xs font-bold text-gray-800 uppercase tracking-wide">Pengajuan Lupa Absen Bulan Ini ({{ count($forgotRequests) }})</h2>
            </div>
            <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                Pantauan Orang Tua
            </span>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($forgotRequests as $fr)
            @php
                $statusBadge = match($fr['status']) {
                    'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                    default    => 'bg-amber-100 text-amber-800 border-amber-200',
                };
            @endphp
            <div class="py-2.5 first:pt-0 last:pb-0 space-y-1 text-xs">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-800">
                            {{ \Illuminate\Support\Carbon::parse($fr['date'])->isoFormat('dddd, D MMM Y') }}
                        </span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                            {{ $fr['type_label'] }}
                        </span>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border shrink-0 {{ $statusBadge }}">
                        {{ $fr['status_label'] }}
                    </span>
                </div>
                <p class="text-gray-600 text-[11px]">
                    <span class="text-gray-400">Alasan:</span> {{ $fr['reason'] }}
                </p>
                @if(!empty($fr['teacher_note']))
                <p class="text-[11px] text-blue-700 bg-blue-50/70 p-1.5 rounded-lg border border-blue-100">
                    <span class="font-semibold">Catatan Guru:</span> {{ $fr['teacher_note'] }}
                </p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ─── Informasi Hari Libur & Libur Khusus Bulan Ini ─────────────────── --}}
    @if(!empty($holidays) && count($holidays) > 0)
    <div class="bg-gradient-to-r from-blue-50/90 to-indigo-50/80 border border-blue-200/90 rounded-2xl p-4 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-lg">🏖️</span>
                <h2 class="text-xs font-bold text-blue-950 uppercase tracking-wide">Hari Libur & Libur Khusus Bulan Ini ({{ count($holidays) }})</h2>
            </div>
            <span class="text-[10px] font-semibold text-blue-700 bg-blue-100/90 px-2 py-0.5 rounded-full border border-blue-200">
                Siswa Tidak Sekolah
            </span>
        </div>

        <div class="grid gap-2">
            @foreach($holidays as $h)
            <div class="bg-white/95 backdrop-blur-xs rounded-xl p-3 border border-blue-100 flex items-start justify-between gap-3 shadow-2xs">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-800">
                            {{ \Illuminate\Support\Carbon::parse($h['date'])->isoFormat('dddd, D MMMM Y') }}
                        </span>
                        @if(!empty($h['is_special']))
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 border border-purple-200">
                                Libur Khusus Kelas
                            </span>
                        @else
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 border border-blue-200">
                                Libur Sekolah
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-700 font-medium mt-1">📌 {{ $h['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ─── Daftar Presensi ─────────────────────────────────────────────── --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm font-bold text-gray-800">Detail Rincian Harian</h2>
            <span class="text-xs text-gray-400 font-medium">Klik foto untuk memperbesar</span>
        </div>

        @forelse($records as $rec)
        @if(($rec['status'] ?? null) === 'libur')
        {{-- Card Tanggal Libur Khusus / Libur Sekolah --}}
        <div class="bg-blue-50/60 rounded-2xl shadow-xs border border-blue-200/70 p-4 space-y-2">
            <div class="flex items-center justify-between gap-2 border-b border-blue-100 pb-2">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full shrink-0 bg-blue-500"></div>
                    <p class="text-sm font-bold text-gray-800">
                        {{ \Illuminate\Support\Carbon::parse($rec['date'])->isoFormat('dddd, D MMMM Y') }}
                    </p>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border bg-blue-100 text-blue-800 border-blue-200 shrink-0">
                    🏖️ {{ !empty($rec['is_special']) ? 'Libur Khusus' : 'Hari Libur' }}
                </span>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-600">
                <div class="flex items-center gap-1.5">
                    <span class="text-blue-700 font-semibold">Alasan Libur:</span>
                    <span class="font-bold text-gray-800">{{ $rec['holiday_description'] ?? 'Libur Resmi Sekolah' }}</span>
                </div>
                <span class="text-[11px] text-blue-600 font-medium italic">Siswa tidak bersekolah</span>
            </div>
        </div>
        @else
        @php
            $color = match($rec['status']) {
                'hadir'      => ['dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                'terlambat'  => ['dot' => 'bg-amber-500',   'badge' => 'bg-amber-100 text-amber-800 border-amber-200'],
                'izin'       => ['dot' => 'bg-sky-500',     'badge' => 'bg-sky-100 text-sky-800 border-sky-200'],
                'sakit'      => ['dot' => 'bg-purple-500',  'badge' => 'bg-purple-100 text-purple-800 border-purple-200'],
                'dispensasi' => ['dot' => 'bg-teal-500',    'badge' => 'bg-teal-100 text-teal-800 border-teal-200'],
                default      => ['dot' => 'bg-rose-500',    'badge' => 'bg-rose-100 text-rose-800 border-rose-200'],
            };
            $label = match($rec['status']) {
                'hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin',
                'sakit' => 'Sakit', 'dispensasi' => 'Dispensasi', default => 'Alpa',
            };
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-3">
            <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-2.5">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full shrink-0 {{ $color['dot'] }}"></div>
                    <p class="text-sm font-bold text-gray-800">
                        {{ \Illuminate\Support\Carbon::parse($rec['date'])->isoFormat('dddd, D MMMM Y') }}
                    </p>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border shrink-0 {{ $color['badge'] }}">
                    {{ $label }}
                </span>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-600">
                <div class="flex items-center gap-4">
                    <div>
                        <span class="text-gray-400">Masuk:</span>
                        <span class="font-semibold text-gray-800 ml-1">
                            {{ $rec['check_in_time'] ? \Illuminate\Support\Carbon::parse($rec['check_in_time'])->format('H:i') : '—' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Pulang:</span>
                        <span class="font-semibold text-gray-800 ml-1">
                            {{ $rec['check_out_time'] ? \Illuminate\Support\Carbon::parse($rec['check_out_time'])->format('H:i') : '—' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 flex-wrap justify-end">
                    @if(!empty($rec['via_lupa_absen']))
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md flex items-center gap-1">
                        📝 Via Lupa Absen ({{ match($rec['lupa_absen_type']) { 'pulang' => 'Pulang', 'keduanya' => 'Datang & Pulang', default => 'Datang' } }})
                    </span>
                    @endif

                    @if($rec['is_fake_gps'])
                    <span class="text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md">
                        ⚠️ Fake GPS
                    </span>
                    @endif
                </div>
            </div>

            {{-- Photos Display Section --}}
            @if(!empty($rec['check_in_photo_url']) || !empty($rec['check_out_photo_url']))
            <div class="pt-2 border-t border-gray-100 flex items-center gap-3">
                @if(!empty($rec['check_in_photo_url']))
                <a href="{{ $rec['check_in_photo_url'] }}" target="_blank" class="group flex items-center gap-2 bg-slate-50 border border-slate-200/80 rounded-xl p-1.5 pr-3 hover:bg-blue-50 hover:border-blue-200 transition-colors">
                    <img src="{{ $rec['check_in_photo_url'] }}" alt="Foto Masuk" class="w-12 h-12 rounded-lg object-cover ring-1 ring-slate-200 group-hover:scale-105 transition-transform">
                    <div class="text-[11px]">
                        <p class="font-bold text-slate-700 group-hover:text-blue-700">Foto Masuk</p>
                        <p class="text-slate-400 text-[10px] mt-0.5">Klik Perbesar</p>
                    </div>
                </a>
                @endif

                @if(!empty($rec['check_out_photo_url']))
                <a href="{{ $rec['check_out_photo_url'] }}" target="_blank" class="group flex items-center gap-2 bg-slate-50 border border-slate-200/80 rounded-xl p-1.5 pr-3 hover:bg-blue-50 hover:border-blue-200 transition-colors">
                    <img src="{{ $rec['check_out_photo_url'] }}" alt="Foto Pulang" class="w-12 h-12 rounded-lg object-cover ring-1 ring-slate-200 group-hover:scale-105 transition-transform">
                    <div class="text-[11px]">
                        <p class="font-bold text-slate-700 group-hover:text-blue-700">Foto Pulang</p>
                        <p class="text-slate-400 text-[10px] mt-0.5">Klik Perbesar</p>
                    </div>
                </a>
                @endif
            </div>
            @endif
        </div>
        @endif
        @empty
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 py-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm font-semibold text-gray-600">Tidak ada data presensi bulan ini</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
