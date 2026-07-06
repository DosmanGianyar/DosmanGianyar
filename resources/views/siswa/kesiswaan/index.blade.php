@extends('layouts.siswa')

@section('title', 'Kesiswaan')
@section('page-title', 'Kesiswaan')

@section('content')

{{-- ─── Rekap Absensi Bulan Ini ─────────────────────────────────────── --}}
<div class="bg-linear-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 mb-4">
    <div class="flex items-center justify-between mb-3">
        <p class="text-white font-semibold text-sm">Absensi Bulan Ini</p>
        <a href="{{ route('siswa.attendance.history') }}"
            class="text-blue-200 text-xs hover:text-white flex items-center gap-1 transition-colors">
            Riwayat
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    <div class="grid grid-cols-3 gap-2">
        <div class="bg-white/15 rounded-xl py-3 text-center">
            <p class="text-green-300 font-bold text-2xl leading-none">{{ $absensiSummary['hadir'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1.5 font-medium">Hadir</p>
        </div>
        <div class="bg-white/15 rounded-xl py-3 text-center">
            <p class="text-yellow-300 font-bold text-2xl leading-none">{{ $absensiSummary['terlambat'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1.5 font-medium">Terlambat</p>
        </div>
        <div class="bg-white/15 rounded-xl py-3 text-center">
            <p class="text-red-300 font-bold text-2xl leading-none">{{ $absensiSummary['alpa'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1.5 font-medium">Alpa</p>
        </div>
        <div class="bg-white/15 rounded-xl py-3 text-center">
            <p class="text-sky-300 font-bold text-2xl leading-none">{{ $absensiSummary['izin'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1.5 font-medium">Izin</p>
        </div>
        <div class="bg-white/15 rounded-xl py-3 text-center">
            <p class="text-purple-300 font-bold text-2xl leading-none">{{ $absensiSummary['sakit'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1.5 font-medium">Sakit</p>
        </div>
        <div class="bg-white/15 rounded-xl py-3 text-center">
            <p class="text-orange-300 font-bold text-2xl leading-none">{{ $absensiSummary['dispensasi'] }}</p>
            <p class="text-blue-100 text-[11px] mt-1.5 font-medium">Dispensasi</p>
        </div>
    </div>
</div>

{{-- ─── Ringkasan Poin & Prestasi ────────────────────────────────────── --}}
{{--
    [SISTEM POIN DISABLED] Grid 2 kolom diubah sementara menjadi 1 kolom (hanya Prestasi).
    Untuk menampilkan kembali card "Poin Perilaku":
      1. Ubah <div class="grid grid-cols-1 ..."> kembali ke grid-cols-2
      2. Hapus komentar Blade pada blok card "Poin Perilaku" di bawah
    Lihat juga: PROJECT_GUIDE.md bagian "Cara Menampilkan Kembali Sistem Poin"
--}}
<div class="grid grid-cols-1 gap-3 mb-4">

    {{-- [SISTEM POIN DISABLED] Card Poin Perilaku — hapus komentar Blade di bawah untuk mengaktifkan
    <a href="{{ route('siswa.conduct.index') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-500">Poin Perilaku</span>
        </div>
        <p class="text-3xl font-bold text-blue-700 leading-none">{{ $conductSummary['total'] }}</p>
        <p class="text-[11px] text-gray-400 mt-1">Total Poin</p>
        <div class="mt-3 flex gap-2">
            <span class="text-[11px] font-bold text-green-700 bg-green-50 rounded-lg px-2 py-1">
                +{{ $conductSummary['prestasi'] }}
            </span>
            <span class="text-[11px] font-bold text-red-700 bg-red-50 rounded-lg px-2 py-1">
                −{{ $conductSummary['pelanggaran'] }}
            </span>
        </div>
    </a>
    --}}

    <a href="{{ route('siswa.achievements.index') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-yellow-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-500">Prestasi</span>
            </div>
            @if($achievementSummary['pending'] > 0)
                <span class="text-[10px] font-bold bg-yellow-400 text-white rounded-full w-5 h-5 flex items-center justify-center">
                    {{ $achievementSummary['pending'] }}
                </span>
            @endif
        </div>
        <p class="text-3xl font-bold text-yellow-600 leading-none">{{ $achievementSummary['approved'] }}</p>
        <p class="text-[11px] text-gray-400 mt-1">Disetujui</p>
        @if($achievementSummary['pending'] > 0)
        <p class="text-[11px] text-yellow-600 font-medium mt-3">
            {{ $achievementSummary['pending'] }} menunggu verifikasi
        </p>
        @elseif($lastAchievement)
        <p class="text-[11px] text-gray-400 mt-3 truncate leading-tight">{{ $lastAchievement->title }}</p>
        @endif
    </a>

</div>

{{-- ─── Layanan BK ────────────────────────────────────────────────────── --}}
<a href="{{ route('siswa.bk-consultation.index') }}"
    class="flex items-center gap-3 bg-white rounded-2xl shadow-sm border border-purple-100 p-4 mb-4">
    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-gray-800">Bimbingan BK</p>
        <p class="text-xs text-gray-400 mt-0.5">Ajukan bimbingan ke Guru BK</p>
    </div>
    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>

{{-- ─── Tab List ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">

    {{-- Tab Header --}}
    <div class="flex">
        @foreach([['presensi','Presensi'],['catatan','Catatan']] as [$id,$label])
        <button onclick="switchTab('{{ $id }}')" id="tab-btn-{{ $id }}"
            class="flex-1 py-3 text-xs font-semibold border-b-2 transition-colors
                {{ $id === 'presensi' ? 'text-blue-600 border-blue-600' : 'text-gray-400 border-gray-100' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Tab: Presensi --}}
    <div id="tab-presensi">
        @forelse($tabPresensi as $att)
        @php
            $statusMeta = match($att->status) {
                'hadir'      => ['dot' => 'bg-green-500',  'pill' => 'bg-green-100 text-green-700',   'text' => 'Hadir'],
                'terlambat'  => ['dot' => 'bg-yellow-500', 'pill' => 'bg-yellow-100 text-yellow-700', 'text' => 'Terlambat'],
                'izin'       => ['dot' => 'bg-sky-500',    'pill' => 'bg-sky-100 text-sky-700',       'text' => 'Izin'],
                'sakit'      => ['dot' => 'bg-purple-500', 'pill' => 'bg-purple-100 text-purple-700', 'text' => 'Sakit'],
                'dispensasi' => ['dot' => 'bg-orange-500', 'pill' => 'bg-orange-100 text-orange-700', 'text' => 'Dispensasi'],
                default      => ['dot' => 'bg-red-500',    'pill' => 'bg-red-100 text-red-700',       'text' => 'Alpa'],
            };
        @endphp
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0">

            {{-- Dot status --}}
            <div class="w-2 h-2 rounded-full shrink-0 {{ $statusMeta['dot'] }}"></div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-medium text-gray-800 leading-tight">
                        {{ $att->date->isoFormat('ddd, D MMM Y') }}
                    </p>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full shrink-0 {{ $statusMeta['pill'] }}">
                        {{ $statusMeta['text'] }}
                    </span>
                </div>
                <div class="mt-0.5 flex flex-col gap-0.5">
                    @if($att->check_in_time)
                        <p class="text-xs text-gray-400">
                            Masuk {{ \Carbon\Carbon::parse($att->check_in_time)->format('H:i') }}
                            @if($att->check_out_time)
                                · Pulang {{ \Carbon\Carbon::parse($att->check_out_time)->format('H:i') }}
                            @endif
                        </p>
                        @if(!$att->check_out_time)
                            <p class="text-[11px] text-orange-500 font-medium">Belum absen pulang</p>
                        @endif
                    @endif
                </div>
            </div>

        </div>
        @empty
        <div class="py-10 text-center">
            <p class="text-sm text-gray-400">Belum ada data presensi</p>
        </div>
        @endforelse
    </div>

    {{-- Tab: Catatan (gabungan negatif + positif, bisa difilter) --}}
    <div id="tab-catatan" class="hidden">

        {{-- Filter Semua / Negatif / Positif --}}
        <div class="flex gap-2 px-4 pt-3 pb-1">
            @foreach([['semua','Semua'],['negatif','Negatif'],['positif','Positif']] as [$fid,$flabel])
            <button onclick="filterCatatan('{{ $fid }}')" id="filter-btn-{{ $fid }}"
                class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors
                    {{ $fid === 'semua' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ $flabel }}
            </button>
            @endforeach
        </div>

        @forelse($tabCatatan as $item)
        @php $isNegatif = $item['type'] === 'negatif'; @endphp
        <div data-type="{{ $item['type'] }}" class="flex items-start gap-3 px-4 py-3 border-b border-gray-50 last:border-0">
            <div class="w-9 h-9 {{ $isNegatif ? 'bg-red-50' : 'bg-yellow-50' }} rounded-xl shrink-0 flex items-center justify-center mt-0.5">
                @if($isNegatif)
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                @else
                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                @endif
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $item['title'] }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    @if($item['note']){{ $item['note'] }} · @endif{{ $item['date']->isoFormat('D MMM Y') }}
                </p>
            </div>
        </div>
        @empty
        <div class="py-10 text-center">
            <svg class="w-10 h-10 text-green-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-gray-400">Belum ada catatan</p>
        </div>
        @endforelse
    </div>

</div>

<script>
function switchTab(name) {
    ['presensi','catatan'].forEach(function(t) {
        document.getElementById('tab-' + t).classList.add('hidden');
        var btn = document.getElementById('tab-btn-' + t);
        btn.className = btn.className
            .replace('text-blue-600','text-gray-400')
            .replace('border-blue-600','border-gray-100');
    });
    document.getElementById('tab-' + name).classList.remove('hidden');
    var active = document.getElementById('tab-btn-' + name);
    active.className = active.className
        .replace('text-gray-400','text-blue-600')
        .replace('border-gray-100','border-blue-600');
}

function filterCatatan(type) {
    document.querySelectorAll('#tab-catatan [data-type]').forEach(function(el) {
        el.style.display = (type === 'semua' || el.dataset.type === type) ? '' : 'none';
    });
    ['semua','negatif','positif'].forEach(function(t) {
        var btn = document.getElementById('filter-btn-' + t);
        btn.className = btn.className
            .replace('bg-blue-600 text-white', 'bg-gray-100 text-gray-500');
    });
    var active = document.getElementById('filter-btn-' + type);
    active.className = active.className
        .replace('bg-gray-100 text-gray-500', 'bg-blue-600 text-white');
}
</script>

{{-- ─── Izin, Sakit & Dispensasi ─────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-sky-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-700">Izin, Sakit & Dispensasi</p>
                @if($activePermit)
                    <p class="text-xs text-sky-600">{{ $activePermit->typeLabel() }} aktif s.d {{ $activePermit->end_date->format('d M') }}</p>
                @elseif($permitPending > 0)
                    <p class="text-xs text-yellow-600">{{ $permitPending }} menunggu persetujuan</p>
                @else
                    <p class="text-xs text-gray-400">Tidak ada pengajuan aktif</p>
                @endif
            </div>
        </div>
        @if($permitPending > 0)
            <span class="text-xs font-bold bg-yellow-100 text-yellow-700 rounded-full px-2.5 py-1">
                {{ $permitPending }} pending
            </span>
        @endif
    </div>
    <div class="grid grid-cols-3 gap-2">
        <a href="{{ route('siswa.permit.create') }}?type=izin"
            class="flex flex-col items-center gap-1 bg-sky-50 rounded-xl py-3 text-sky-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="text-xs font-semibold">Izin</span>
        </a>
        <a href="{{ route('siswa.permit.create') }}?type=dispensasi"
            class="flex flex-col items-center gap-1 bg-orange-50 rounded-xl py-3 text-orange-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-xs font-semibold">Dispensasi</span>
        </a>
        <a href="{{ route('siswa.permit.index') }}"
            class="flex flex-col items-center gap-1 bg-gray-50 rounded-xl py-3 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <span class="text-xs font-semibold">Riwayat</span>
        </a>
    </div>
</div>

{{-- ─── Izin Pulang Lebih Awal ────────────────────────────────────────── --}}
<a href="{{ route('siswa.early-checkout.index') }}"
    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-700">Izin Pulang Lebih Awal</p>
            @if($earlyCheckoutPending > 0)
                <p class="text-xs text-emerald-600">{{ $earlyCheckoutPending }} pengajuan menunggu persetujuan</p>
            @else
                <p class="text-xs text-gray-400">Ajukan izin pulang sebelum jam normal</p>
            @endif
        </div>
        @if($earlyCheckoutPending > 0)
            <span class="text-xs font-bold bg-emerald-500 text-white rounded-full w-6 h-6 flex items-center justify-center shrink-0">
                {{ $earlyCheckoutPending }}
            </span>
        @endif
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
</a>

{{-- ─── Lupa Absen ───────────────────────────────────────────────────── --}}
<a href="{{ route('siswa.forgot-attendance.index') }}"
    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-700">Lupa Absen</p>
            @if($forgotAttendancePending > 0)
                <p class="text-xs text-amber-600">{{ $forgotAttendancePending }} pengajuan menunggu persetujuan</p>
            @else
                <p class="text-xs text-gray-400">Ajukan koreksi presensi ke wali kelas</p>
            @endif
        </div>
        @if($forgotAttendancePending > 0)
            <span class="text-xs font-bold bg-amber-500 text-white rounded-full w-6 h-6 flex items-center justify-center shrink-0">
                {{ $forgotAttendancePending }}
            </span>
        @endif
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
</a>

{{-- ─── Izin Keluar ──────────────────────────────────────────────────── --}}
<a href="{{ route('siswa.exit-pass.show') }}"
    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
            {{ $activeExitPass ? 'bg-red-100' : 'bg-emerald-100' }}">
            <svg class="w-5 h-5 {{ $activeExitPass ? 'text-red-500' : 'text-emerald-600' }}"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-700">Izin Keluar Kelas</p>
            @if($activeExitPass)
                <p class="text-xs text-red-500">Keluar sejak {{ \Carbon\Carbon::parse($activeExitPass->out_time)->format('H:i') }}</p>
            @else
                <p class="text-xs text-gray-400">Tap untuk membuat izin keluar</p>
            @endif
        </div>
        @if($activeExitPass)
            <span class="flex items-center gap-1 text-xs font-bold text-red-600 bg-red-50 px-2.5 py-1 rounded-lg animate-pulse">
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                Aktif
            </span>
        @else
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        @endif
    </div>
</a>

{{-- ─── E-Voting ─────────────────────────────────────────────────────── --}}
<a href="{{ route('siswa.voting.index') }}"
    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-700">E-Voting</p>
            @if($activeSessions->count() > 0)
                <p class="text-xs {{ $unvotedCount > 0 ? 'text-purple-600' : 'text-emerald-600' }}">
                    {{ $unvotedCount > 0 ? $unvotedCount . ' sesi menunggu suaramu' : 'Sudah memberikan suara' }}
                </p>
            @else
                <p class="text-xs text-gray-400">Tidak ada sesi voting aktif</p>
            @endif
        </div>
        @if($unvotedCount > 0)
            <span class="text-xs font-bold bg-purple-600 text-white rounded-full px-2 py-0.5">
                {{ $unvotedCount }}
            </span>
        @endif
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
</a>

{{-- ─── Prestasi ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-yellow-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-700">Prestasi</h3>
        </div>
        <a href="{{ route('siswa.achievements.report') }}" class="text-xs text-blue-600 font-medium">
            Laporan Sekolah →
        </a>
    </div>
    <div class="grid grid-cols-2 gap-2">
        <a href="{{ route('siswa.achievements.create') }}"
            class="flex items-center gap-2 bg-yellow-50 rounded-xl p-3">
            <svg class="w-4 h-4 text-yellow-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-xs font-semibold text-yellow-800">Laporkan</span>
        </a>
        <a href="{{ route('siswa.achievements.index') }}"
            class="flex items-center gap-2 bg-gray-50 rounded-xl p-3">
            <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="text-xs font-semibold text-gray-700">Prestasi Saya</span>
        </a>
    </div>
</div>

{{-- ─── Verifikasi Prestasi (pengelola) ─────────────────────────────── --}}
@if(auth()->user()->role === 'pengelola')
<a href="{{ route('siswa.achievements.verify') }}"
    class="block bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-3">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-blue-800">Verifikasi Prestasi</p>
            <p class="text-xs {{ $pendingVerify > 0 ? 'text-blue-600' : 'text-blue-400' }}">
                {{ $pendingVerify > 0 ? $pendingVerify . ' prestasi menunggu verifikasi' : 'Semua sudah diverifikasi' }}
            </p>
        </div>
        @if($pendingVerify > 0)
            <span class="text-sm font-bold bg-blue-600 text-white rounded-full w-7 h-7 flex items-center justify-center shrink-0">
                {{ $pendingVerify }}
            </span>
        @endif
        <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
</a>
@endif

@endsection
