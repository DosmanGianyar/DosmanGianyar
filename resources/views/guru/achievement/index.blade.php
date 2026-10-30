@extends('layouts.guru')
@section('title', 'Prestasi & Juara Siswa Binaan')
@section('page-title', 'Prestasi & Juara Siswa Binaan')

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

    {{-- Banner Header Pembina & Rekap Juara Binaan --}}
    <div class="rounded-3xl p-6 text-white shadow-lg relative overflow-hidden"
         style="background: linear-gradient(135deg, #064e3b 0%, #047857 55%, #059669 100%) !important; color: #ffffff !important;">
        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 border border-white/20 text-[11px] font-extrabold uppercase tracking-wider text-emerald-100">
                    <span>🏆</span> Daftar Juara & Prestasi Siswa Binaan
                </div>
                <h2 class="text-xl md:text-2xl font-black leading-tight text-white">
                    Rekapitulasi Kejuaraan Siswa Binaan Anda
                </h2>
                <p class="text-xs text-emerald-100 leading-relaxed max-w-2xl">
                    Menampilkan seluruh capaian juara & prestasi siswa yang mencantumkan Anda sebagai <strong>Guru Pembina / Pendamping</strong> maupun yang terhubung dengan ekstrakurikuler binaan Anda.
                </p>
                @if($myExtracurriculars->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-[11px] text-emerald-200 font-semibold">Ekstrakurikuler Binaan:</span>
                        @foreach($myExtracurriculars as $ex)
                            <span class="px-2.5 py-0.5 rounded-lg bg-white/20 text-white text-xs font-bold border border-white/25">
                                🏅 {{ $ex->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="shrink-0">
                <a href="{{ route('guru.layanan.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white text-emerald-900 font-extrabold text-xs shadow-md hover:bg-emerald-50 transition">
                    ← Menu Layanan
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik Ringkas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs">
            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total Kejuaraan</p>
            <p class="text-2xl font-black text-gray-900 mt-1">{{ $stats['total'] }}</p>
            <p class="text-[11px] text-emerald-700 font-semibold mt-0.5">{{ $stats['total_students'] }} Siswa Berprestasi</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-200 shadow-xs">
            <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Disetujui / Valid</p>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ $stats['approved'] }}</p>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Menunggu: {{ $stats['pending'] }}</p>
        </div>
        <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200 shadow-xs">
            <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Nasional & Internl.</p>
            <p class="text-2xl font-black text-amber-700 mt-1">{{ $stats['nasional_inter'] }}</p>
            <p class="text-[11px] text-amber-600 font-medium mt-0.5">Capaian Level Tinggi</p>
        </div>
        <div class="bg-blue-50 rounded-2xl p-4 border border-blue-200 shadow-xs">
            <p class="text-[11px] font-bold text-blue-800 uppercase tracking-wider">Provinsi & Kab/Kota</p>
            <p class="text-2xl font-black text-blue-700 mt-1">{{ $stats['provinsi_kabupaten'] }}</p>
            <p class="text-[11px] text-blue-600 font-medium mt-0.5">Kejuaraan Daerah</p>
        </div>
    </div>

    {{-- Filter Pencarian --}}
    <form method="GET" action="{{ route('guru.achievements.index') }}"
          class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div class="md:col-span-2">
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Cari Siswa / Lomba / Juara</label>
                <input type="text" name="q" value="{{ $search }}" placeholder="Ketik nama siswa, judul lomba, atau peringkat..."
                       class="w-full border border-gray-300 rounded-xl px-3.5 py-2 text-xs font-medium focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Ekstrakurikuler</label>
                <select name="extracurricular_id" onchange="this.form.submit()"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold">
                    <option value="">Semua Ekstra</option>
                    @foreach($myExtracurriculars as $ex)
                        <option value="{{ $ex->id }}" {{ (string) $extracurricularId === (string) $ex->id ? 'selected' : '' }}>
                            {{ $ex->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Tingkat Lomba</label>
                <select name="level" onchange="this.form.submit()"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold">
                    <option value="">Semua Tingkat</option>
                    <option value="internasional" {{ $level === 'internasional' ? 'selected' : '' }}>Internasional</option>
                    <option value="nasional"      {{ $level === 'nasional'      ? 'selected' : '' }}>Nasional</option>
                    <option value="provinsi"      {{ $level === 'provinsi'      ? 'selected' : '' }}>Provinsi</option>
                    <option value="kabupaten"     {{ $level === 'kabupaten'     ? 'selected' : '' }}>Kabupaten/Kota</option>
                    <option value="sekolah"       {{ $level === 'sekolah'       ? 'selected' : '' }}>Sekolah</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Tahun</label>
                <select name="year" onchange="this.form.submit()"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ (string) $year === (string) $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between pt-1">
            <div class="flex flex-wrap items-center gap-1.5">
                @php
                    $statusTabs = [
                        ''         => 'Semua Status',
                        'approved' => '✅ Disetujui',
                        'pending'  => '⏳ Menunggu Verifikasi',
                    ];
                @endphp
                @foreach($statusTabs as $stKey => $stLabel)
                    <a href="{{ route('guru.achievements.index', array_filter(['q' => $search, 'extracurricular_id' => $extracurricularId, 'level' => $level, 'year' => $year, 'status' => $stKey])) }}"
                       class="px-3 py-1 rounded-xl text-xs font-bold border transition {{ $status === $stKey ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100' }}">
                        {{ $stLabel }}
                    </a>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                @if($search || $extracurricularId || $level || $status || $year)
                    <a href="{{ route('guru.achievements.index') }}" class="text-xs text-gray-500 hover:text-gray-800 font-semibold">
                        Reset Filter
                    </a>
                @endif
                <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                    Terapkan
                </button>
            </div>
        </div>
    </form>

    {{-- Daftar Juara & Prestasi Siswa Binaan --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-sm font-extrabold text-gray-800">
                Menampilkan {{ $achievements->count() }} Data Juara / Prestasi Binaan
            </h3>
        </div>

        @forelse($achievements as $item)
            @php
                $certUrl = $item->certificateUrl();
                $photoUrl = $item->photoUrl();
                $isBeregu = $item->isBeregu();
                $teamMembers = $isBeregu ? $item->team_members : collect();
            @endphp
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-xs hover:border-emerald-300 transition space-y-3">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-3">
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            @if($item->rank)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                                    🥇 {{ $item->rank }}
                                </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold {{ $item->levelBadgeClass() }}">
                                Tingkat {{ $item->levelLabel() }}
                            </span>
                            @if($item->extracurricularName() !== '—')
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                    🏅 {{ $item->extracurricularName() }}
                                </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $item->statusBadgeClass() }}">
                                {{ $item->statusLabel() }}
                            </span>
                            @if($item->curation_status === 'curated')
                                <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✓ Lolos Kurasi
                                </span>
                            @endif
                        </div>

                        <h4 class="text-base font-extrabold text-gray-900 leading-snug">
                            {{ $item->title }}
                        </h4>

                        <p class="text-xs text-gray-600">
                            @if($item->event_name && $item->event_name !== $item->title)
                                <span class="font-semibold text-gray-800">Ajang:</span> {{ $item->event_name }} ·
                            @endif
                            @if($item->organizer)
                                <span class="font-semibold text-gray-800">Penyelenggara:</span> {{ $item->organizer }} ·
                            @endif
                            <span class="font-semibold text-gray-800">Tanggal:</span> {{ $item->achievement_date?->translatedFormat('d F Y') ?? '—' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        @if($photoUrl)
                            <a href="{{ $photoUrl }}" target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition">
                                📷 Foto Kegiatan ↗
                            </a>
                        @endif
                        @if($certUrl)
                            <a href="{{ $certUrl }}" target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 text-xs font-bold transition">
                                📄 Piagam / Sertifikat ↗
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Info Siswa / Tim Binaan & Guru Pembina --}}
                <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div>
                        @if($isBeregu && $teamMembers->count() > 1)
                            <p class="font-bold text-purple-900 mb-1">
                                👥 Beregu / Tim ({{ $teamMembers->count() }} Siswa):
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($teamMembers as $tm)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-900 border border-purple-200 font-semibold text-xs">
                                        👤 {{ $tm->student?->name ?? 'Siswa' }}
                                        @if($tm->student?->schoolClass?->name)
                                            <span class="text-purple-600 font-normal">({{ $tm->student->schoolClass->name }})</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="inline-flex items-center gap-2">
                                <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-xs">
                                    {{ $item->student?->initials ?? 'S' }}
                                </span>
                                <div>
                                    <span class="font-extrabold text-gray-900">{{ $item->student?->name ?? '—' }}</span>
                                    @if($item->student?->schoolClass?->name)
                                        <span class="ml-1 px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-bold text-[11px]">
                                            Kelas {{ $item->student->schoolClass->name }}
                                        </span>
                                    @endif
                                    @if($item->student?->nisn)
                                        <span class="text-gray-400 text-[11px] ml-1">NISN: {{ $item->student->nisn }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="text-xs text-gray-500 sm:text-right shrink-0">
                        <span class="font-semibold text-emerald-800">👨‍🏫 Pembina: {{ $item->pembinaName() }}</span>
                        @if($item->achievement_code)
                            <span class="block text-[10px] font-mono text-gray-400 mt-0.5">Kode: {{ $item->achievement_code }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-10 text-center border border-gray-200 shadow-xs space-y-2">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-3xl">
                    🏆
                </div>
                <h4 class="text-base font-extrabold text-gray-800">Belum Ada Data Juara Siswa Binaan</h4>
                <p class="text-xs text-gray-500 max-w-md mx-auto">
                    Saat siswa melaporkan prestasi dan memilih nama Anda sebagai Guru Pembina atau terkait ekstrakurikuler yang Anda bina, daftar juara akan otomatis tampil di halaman ini.
                </p>
            </div>
        @endforelse
    </div>

</div>
@endsection

