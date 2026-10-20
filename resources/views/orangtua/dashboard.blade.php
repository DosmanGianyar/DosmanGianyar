@extends('layouts.orangtua')
@section('title', 'Beranda Orang Tua')
@section('page-title', 'Beranda Orang Tua')

@section('content')
<div class="max-w-lg mx-auto space-y-5 pb-8">

    {{-- Welcome Card --}}
    <div class="rounded-3xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #0f2460 0%, #1a3a8f 50%, #1e3fad 100%) !important; color: #ffffff !important;">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-blue-200 uppercase tracking-wider">Portal Orang Tua</p>
                <h1 class="text-lg font-bold mt-0.5">Selamat datang, {{ auth()->user()->name }}</h1>
                <p class="text-blue-100 text-xs mt-1">Pantau kehadiran, catatan kedisiplinan & prestasi putra/putri Anda.</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white text-xl shrink-0">
                👨‍👩‍👧‍👦
            </div>
        </div>
    </div>

    @forelse($childrenData as $data)
    @php
        $child = $data['student'];
        $summary = $data['monthly_summary'];
        $pct = $data['percentage'];
        $today = $data['today_attendance'];
        $pctColor = $pct >= 90 ? 'text-emerald-600' : ($pct >= 80 ? 'text-amber-600' : 'text-rose-600');
        $pctBg    = $pct >= 90 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($pct >= 80 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');
    @endphp

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 space-y-4">
        {{-- Profile Header --}}
        <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3">
            <div class="flex items-center gap-3 min-w-0">
                @if($child->photo)
                    <img src="{{ $child->photo_url }}" class="w-12 h-12 rounded-2xl object-cover ring-2 ring-blue-100 shrink-0">
                @else
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base shrink-0 border border-blue-100">
                        {{ $child->initials }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h2 class="font-bold text-gray-900 text-base truncate">{{ $child->name }}</h2>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-gray-500 font-medium">Kelas {{ $child->schoolClass?->name ?? '—' }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full border font-semibold {{ $pctBg }}">
                            Kehadiran {{ $pct }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today Attendance Status Card --}}
        <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <span>📅</span> Absensi Hari Ini ({{ now()->isoFormat('D MMM Y') }})
                </span>
                @if($today)
                    @php
                        $st = match($today->status) {
                            'hadir'      => ['bg' => 'bg-emerald-100 text-emerald-800', 'label' => 'Hadir'],
                            'terlambat'  => ['bg' => 'bg-amber-100 text-amber-800', 'label' => 'Terlambat'],
                            'izin'       => ['bg' => 'bg-sky-100 text-sky-800', 'label' => 'Izin'],
                            'sakit'      => ['bg' => 'bg-purple-100 text-purple-800', 'label' => 'Sakit'],
                            'dispensasi' => ['bg' => 'bg-teal-100 text-teal-800', 'label' => 'Dispen'],
                            default      => ['bg' => 'bg-rose-100 text-rose-800', 'label' => 'Alpa'],
                        };
                    @endphp
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $st['bg'] }}">
                        {{ $st['label'] }}
                    </span>
                @elseif(!empty($data['today_holiday']))
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                        🏖️ Libur: {{ $data['today_holiday']->description }}
                    </span>
                @elseif(!empty($data['is_weekend']))
                    <span class="text-[11px] font-medium text-slate-500 bg-slate-200/60 px-2 py-0.5 rounded-full">
                        🏖️ Libur Akhir Pekan
                    </span>
                @else
                    <span class="text-[11px] font-medium text-slate-400 bg-slate-200/60 px-2 py-0.5 rounded-full">
                        Belum Absen
                    </span>
                @endif
            </div>

            @if($today)
                <div class="flex items-center justify-between text-xs text-slate-600 mt-1">
                    <div class="flex items-center gap-3">
                        <div>
                            <span class="text-slate-400">Masuk:</span>
                            <span class="font-semibold text-slate-800 ml-1">{{ $today->check_in_time ? \Carbon\Carbon::parse($today->check_in_time)->format('H:i') : '—' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Pulang:</span>
                            <span class="font-semibold text-slate-800 ml-1">{{ $today->check_out_time ? \Carbon\Carbon::parse($today->check_out_time)->format('H:i') : '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Photos Thumbnail if available --}}
                @if($today->photo_url || $today->check_out_photo_url)
                <div class="flex items-center gap-3 mt-3 pt-2 border-t border-slate-200/60">
                    @if($today->photo_url)
                    <div class="flex items-center gap-1.5">
                        <a href="{{ $today->photo_url }}" target="_blank" class="shrink-0">
                            <img src="{{ $today->photo_url }}" alt="Foto Masuk" class="w-11 h-11 rounded-lg object-cover ring-1 ring-slate-200 hover:opacity-90 transition-opacity">
                        </a>
                        <div class="text-[10px] leading-tight">
                            <p class="font-bold text-slate-700">Foto Masuk</p>
                            <p class="text-slate-400">{{ \Carbon\Carbon::parse($today->check_in_time)->format('H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($today->check_out_photo_url)
                    <div class="flex items-center gap-1.5">
                        <a href="{{ $today->check_out_photo_url }}" target="_blank" class="shrink-0">
                            <img src="{{ $today->check_out_photo_url }}" alt="Foto Pulang" class="w-11 h-11 rounded-lg object-cover ring-1 ring-slate-200 hover:opacity-90 transition-opacity">
                        </a>
                        <div class="text-[10px] leading-tight">
                            <p class="font-bold text-slate-700">Foto Pulang</p>
                            <p class="text-slate-400">{{ \Carbon\Carbon::parse($today->check_out_time)->format('H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            @else
                @if(!empty($data['today_holiday']))
                    <div class="mt-2 bg-blue-50/80 border border-blue-100 rounded-xl p-2.5 text-xs text-blue-800 flex items-start gap-2">
                        <span class="text-sm">📌</span>
                        <div>
                            <p class="font-bold">Hari ini Libur: {{ $data['today_holiday']->description }}</p>
                            <p class="text-[11px] text-blue-600 mt-0.5">
                                {{ $data['today_holiday']->applies_to === 'kelas_tertentu' ? 'Libur khusus untuk kelas ini.' : 'Hari libur resmi seluruh sekolah.' }} Siswa tidak ada kegiatan belajar mengajar.
                            </p>
                        </div>
                    </div>
                @elseif(!empty($data['is_weekend']))
                    <p class="text-xs text-slate-500 italic mt-1">Hari ini hari Minggu (akhir pekan), kegiatan belajar mengajar libur.</p>
                @else
                    <p class="text-xs text-slate-400 italic mt-1">Ananda belum tercatat melakukan absensi hari ini.</p>
                @endif
            @endif
        </div>

        {{-- Jadwal Hari Libur & Libur Khusus Bulan Ini --}}
        @if(!empty($data['monthly_holidays']) && count($data['monthly_holidays']) > 0)
        <div class="bg-gradient-to-br from-blue-50/80 to-indigo-50/40 rounded-2xl p-3.5 border border-blue-100/90 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-blue-950 flex items-center gap-1.5">
                    <span>🏖️</span> Jadwal Libur & Libur Khusus Bulan Ini ({{ count($data['monthly_holidays']) }})
                </span>
                <span class="text-[10px] font-semibold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">Informasi Resmi</span>
            </div>
            <div class="space-y-1.5">
                @foreach($data['monthly_holidays'] as $h)
                <div class="bg-white/90 backdrop-blur-xs rounded-xl p-2.5 px-3 border border-blue-100 flex items-center justify-between gap-2 text-xs shadow-2xs">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($h['date'])->isoFormat('D MMMM Y') }}</span>
                            @if(!empty($h['is_special']))
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-purple-100 text-purple-700 border border-purple-200">Khusus Kelas</span>
                            @else
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-100 text-blue-700 border border-blue-200">Libur Sekolah</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-gray-600 truncate mt-0.5 font-medium">📌 {{ $h['description'] }}</p>
                    </div>
                    <span class="text-[11px] text-gray-400 font-medium shrink-0">
                        {{ \Carbon\Carbon::parse($h['date'])->isoFormat('dddd') }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Monthly Attendance Card --}}
        <div onclick="window.location='{{ route('orangtua.attendance.history', $child->id) }}'" class="bg-white rounded-2xl p-3 border border-gray-100 shadow-sm cursor-pointer hover:border-blue-200 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wide">Ringkasan Absen Bulan {{ now()->isoFormat('MMMM Y') }}</p>
                <span class="text-xs font-semibold text-blue-600">
                    Lihat Detail &rarr;
                </span>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div class="bg-emerald-50/70 border border-emerald-100 rounded-2xl p-2.5 text-center">
                    <p class="text-lg font-bold text-emerald-700 leading-none">{{ $summary['hadir'] }}</p>
                    <p class="text-[11px] font-medium text-emerald-600 mt-1">Hadir</p>
                </div>
                <div class="bg-amber-50/70 border border-amber-100 rounded-2xl p-2.5 text-center">
                    <p class="text-lg font-bold text-amber-700 leading-none">{{ $summary['terlambat'] }}</p>
                    <p class="text-[11px] font-medium text-amber-600 mt-1">Terlambat</p>
                </div>
                <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-2.5 text-center">
                    <p class="text-lg font-bold text-rose-700 leading-none">{{ $summary['alpa'] }}</p>
                    <p class="text-[11px] font-medium text-rose-600 mt-1">Alpa</p>
                </div>
                <div class="bg-sky-50/70 border border-sky-100 rounded-2xl p-2.5 text-center">
                    <p class="text-lg font-bold text-sky-700 leading-none">{{ $summary['izin'] }}</p>
                    <p class="text-[11px] font-medium text-sky-600 mt-1">Izin</p>
                </div>
                <div class="bg-purple-50/70 border border-purple-100 rounded-2xl p-2.5 text-center">
                    <p class="text-lg font-bold text-purple-700 leading-none">{{ $summary['sakit'] }}</p>
                    <p class="text-[11px] font-medium text-purple-600 mt-1">Sakit</p>
                </div>
                <div class="bg-teal-50/70 border border-teal-100 rounded-2xl p-2.5 text-center">
                    <p class="text-lg font-bold text-teal-700 leading-none">{{ $summary['dispensasi'] }}</p>
                    <p class="text-[11px] font-medium text-teal-600 mt-1">Dispen</p>
                </div>
            </div>

            {{-- Pengajuan Lupa Absen Info --}}
            <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between bg-amber-50/60 border border-amber-100 rounded-xl px-3 py-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold shrink-0">📝</span>
                    <div>
                        <span class="font-bold text-gray-800">Pengajuan Lupa Absen:</span>
                        <span class="text-gray-600 ml-1">
                            @if(($summary['lupa_absen'] ?? 0) > 0)
                                <strong class="text-amber-800">{{ $summary['lupa_absen'] }} kali diajukan</strong>
                                <span class="text-[11px] text-gray-500">({{ $summary['lupa_absen_approved'] ?? 0 }} disetujui{{ ($summary['lupa_absen_pending'] ?? 0) > 0 ? ', ' . $summary['lupa_absen_pending'] . ' menunggu' : '' }})</span>
                            @else
                                <span class="text-gray-500 font-medium">0 kali (tidak ada)</span>
                            @endif
                        </span>
                    </div>
                </div>
                <span class="text-[11px] font-semibold text-amber-700 shrink-0">
                    Detail &rarr;
                </span>
            </div>

            {{-- Alert for Alpa or Terlambat --}}
            @if($summary['alpa'] > 0 || $summary['terlambat'] >= 3)
            <div class="mt-2.5 p-2.5 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2 text-xs text-rose-800">
                <span class="text-sm shrink-0">⚠️</span>
                <div>
                    <p class="font-bold">Perhatian Untuk Orang Tua:</p>
                    <p class="text-[11px] mt-0.5">
                        @if($summary['alpa'] > 0)
                            Ananda memiliki <strong>{{ $summary['alpa'] }} kali Alpa</strong> bulan ini.
                        @endif
                        @if($summary['terlambat'] >= 3)
                            Tercatat <strong>{{ $summary['terlambat'] }} kali Terlambat</strong>. Mohon perhatikan waktu keberangkatan.
                        @endif
                    </p>
                </div>
            </div>
            @endif
        </div>

        {{-- Important Information Summary Badges (Conduct & Achievement) --}}
        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-gray-100">
            <a href="{{ route('orangtua.conduct.index', $child->id) }}"
               class="bg-gray-50 border border-gray-100 rounded-2xl p-3 flex items-center justify-between hover:bg-rose-50/60 hover:border-rose-200 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-semibold uppercase">Catatan Negatif</p>
                    <p class="text-base font-bold text-gray-800 mt-0.5">
                        {{ $data['violation_count'] ?? $data['violation_points'] }} <span class="text-xs font-normal text-gray-500">Catatan</span>
                    </p>
                </div>
                <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm">⚠️</span>
            </a>

            <a href="{{ route('orangtua.achievements.index', $child->id) }}"
               class="bg-gray-50 border border-gray-100 rounded-2xl p-3 flex items-center justify-between hover:bg-emerald-50/60 hover:border-emerald-200 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-semibold uppercase">Prestasi Siswa</p>
                    <p class="text-base font-bold text-gray-800 mt-0.5">
                        {{ $data['achievement_count'] }} <span class="text-xs font-normal text-gray-500">Prestasi</span>
                    </p>
                </div>
                <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm">🏆</span>
            </a>
        </div>

        {{-- Tombol Chat Konsultasi Wali Kelas --}}
        @php
            $childChatRoom = app(\App\Services\ChatService::class)->getOrCreateRoomForStudent($child);
            $childChatUnread = $childChatRoom ? $childChatRoom->unreadCountFor(auth()->id()) : 0;
        @endphp
        @if($childChatRoom)
        <a href="{{ route('orangtua.chat.show', $childChatRoom->id) }}"
           class="flex items-center justify-between p-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-xs sm:text-sm shadow-sm hover:shadow-md hover:from-emerald-700 hover:to-teal-700 transition-all group">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-base shrink-0">💬</span>
                <div class="text-left">
                    <p class="leading-tight">Chat Wali Kelas & {{ Str::words($child->name, 2, '') }}</p>
                    <p class="text-[10px] text-emerald-100 font-normal mt-0.5">Komunikasi langsung dengan Wali Kelas</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($childChatUnread > 0)
                    <span class="px-2 py-0.5 bg-rose-500 text-white text-[10px] font-black rounded-full shadow-xs">
                        {{ $childChatUnread }} baru
                    </span>
                @endif
                <span class="text-white/80 group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-3xl p-8 text-center shadow-sm border border-gray-100">
        <p class="text-gray-700 font-semibold">Belum ada data anak yang terhubung</p>
        <p class="text-gray-400 text-xs mt-1">Hubungi admin sekolah untuk menghubungkan akun anak Anda.</p>
    </div>
    @endforelse

</div>
@endsection
