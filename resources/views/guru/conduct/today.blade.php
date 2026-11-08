@extends('layouts.guru')
@section('title', 'Piket Gerbang & Data Pelanggaran Bulanan')
@section('page-title', 'Piket Gerbang — Pelaporan Mandiri & Pelanggaran Bulanan')

@section('content')
<div class="space-y-5" x-data="{ tab: '{{ $activeTab ?? 'mandiri' }}', rejectModal: false, rejectUrl: '', rejectStudent: '', deleteModal: false, deleteUrl: '', deleteStudent: '' }">

    {{-- Top Summary Card --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-bold rounded-lg">
                    Periode: {{ $monthLabel }}
                </span>
                @if($selectedMonth === now()->format('Y-m'))
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold rounded-lg">
                        Bulan Berjalan
                    </span>
                @endif
            </div>
            <h2 class="font-bold text-lg text-slate-900 mt-2">Piket Gerbang & Rekap Pelanggaran Bulanan</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Daftar siswa yang melakukan pelaporan mandiri serta data catatan pelanggaran siswa pada periode <strong>{{ $monthLabel }}</strong>.
            </p>
        </div>

        {{-- Counter Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 shrink-0">
            <div class="bg-amber-50/70 rounded-xl px-4 py-3 text-center border border-amber-200/80">
                <span class="text-xl font-bold block text-amber-800">{{ $pendingCount }}</span>
                <span class="text-[11px] font-semibold text-amber-700">Pending Mandiri</span>
            </div>
            <div class="bg-emerald-50/70 rounded-xl px-4 py-3 text-center border border-emerald-200/80">
                <span class="text-xl font-bold block text-emerald-800">{{ $verifiedCount }}</span>
                <span class="text-[11px] font-semibold text-emerald-700">Diverifikasi</span>
            </div>
            <div class="bg-slate-50 rounded-xl px-4 py-3 text-center border border-slate-200">
                <span class="text-xl font-bold block text-slate-800">{{ $monthlyViolations->count() }}</span>
                <span class="text-[11px] font-semibold text-slate-600">Pelanggaran Bulan Ini</span>
            </div>
            <div class="bg-rose-50/70 rounded-xl px-4 py-3 text-center border border-rose-200/80">
                <span class="text-xl font-bold block text-rose-800">{{ $frequentCount }}</span>
                <span class="text-[11px] font-semibold text-rose-700">Siswa ≥ 3x Bulan Ini</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-3.5 text-xs font-semibold flex items-center gap-2.5">
            <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filter Bar (Bulan, Kelas, Status, Cari Siswa) --}}
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <form method="GET" action="{{ route('guru.conduct.today') }}" class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <input type="hidden" name="tab" :value="tab">

            {{-- Filter Bulan --}}
            <div class="flex items-center gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Bulan:</label>
                <select name="month" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    @foreach($availableMonths as $m)
                        <option value="{{ $m['value'] }}" {{ $selectedMonth === $m['value'] ? 'selected' : '' }}>
                            {{ $m['label'] }}{{ $m['value'] === now()->format('Y-m') ? ' (Bulan Ini)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Kelas --}}
            <div class="flex items-center gap-1.5">
                <label class="text-xs font-semibold text-slate-600">Kelas:</label>
                <select name="class_id" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status (untuk Pelaporan Mandiri) --}}
            <div class="flex items-center gap-1.5" x-show="tab === 'mandiri'">
                <label class="text-xs font-semibold text-slate-600">Status:</label>
                <select name="status" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="verified" {{ $statusFilter === 'verified' ? 'selected' : '' }}>Diverifikasi</option>
                    <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Dikembalikan</option>
                </select>
            </div>

            {{-- Cari Nama / NISN --}}
            <div class="flex items-center gap-1.5">
                <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Cari nama / NISN siswa..."
                    class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-medium rounded-xl px-3 py-2 w-48 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">
                    Cari
                </button>
            </div>

            @if($selectedClassId || $statusFilter || !empty($searchQuery) || $selectedMonth !== now()->format('Y-m'))
                <a href="{{ route('guru.conduct.today') }}" class="text-xs text-blue-600 font-semibold hover:underline px-1">
                    Reset ke Bulan Ini
                </a>
            @endif
        </form>

        {{-- Switcher Tab --}}
        <div class="inline-flex bg-slate-100 p-1 rounded-xl border border-slate-200 self-start lg:self-auto">
            <button type="button" @click="tab = 'mandiri'"
                :class="tab === 'mandiri' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                Pelaporan Mandiri ({{ $logs->count() }})
            </button>
            <button type="button" @click="tab = 'pelanggaran'"
                :class="tab === 'pelanggaran' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                Data Pelanggaran Bulanan ({{ $monthlyViolations->count() }})
            </button>
        </div>
    </div>

    {{-- TAB 1: DAFTAR PELAPORAN MANDIRI SISWA --}}
    <div x-show="tab === 'mandiri'" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900">
                Daftar Siswa yang Melakukan Pelaporan Mandiri — {{ $monthLabel }}
            </h3>
            <span class="text-xs text-slate-500 font-semibold">{{ $logs->count() }} Data</span>
        </div>

        @if($logs->isEmpty())
            <div class="p-12 text-center">
                <h4 class="font-bold text-sm text-slate-700">Belum ada data pelaporan mandiri pada {{ $monthLabel }}</h4>
                <p class="text-xs text-slate-400 mt-1">Tidak ditemukan pengajuan pembinaan mandiri sesuai filter yang dipilih.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[10.5px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-10 text-center">No</th>
                            <th class="py-3 px-4">Tanggal & Jam</th>
                            <th class="py-3 px-4">Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Alasan / Pengajuan</th>
                            <th class="py-3 px-4 text-center">Frekuensi Bulan Ini</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($logs as $index => $log)
                            @php
                                $monthCnt = $log->student?->monthly_pelanggaran_count ?? 1;
                                $totalCnt = $log->student?->total_pelanggaran_count ?? 1;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 text-center font-semibold text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-800 block">{{ $log->created_at->translatedFormat('d M Y') }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $log->created_at->format('H:i') }} WITA</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('guru.conduct.student', $log->student_id) }}" class="font-bold text-slate-900 hover:text-blue-600 transition-colors block">
                                        {{ $log->student?->name ?? '—' }}
                                    </a>
                                    <span class="text-[11px] text-slate-400">NISN: {{ $log->student?->nisn ?? '—' }}</span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold text-xs rounded-lg border border-slate-200">
                                        {{ $log->student?->schoolClass?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <span class="font-bold text-slate-800 block">{{ $log->displayCategoryName() }}</span>
                                    @if($log->parsed_description)
                                        <p class="text-slate-500 text-[11px] truncate mt-0.5" title="{{ $log->parsed_description }}">
                                            "{{ $log->parsed_description }}"
                                        </p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 {{ $monthCnt >= 3 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-200' }} font-bold text-xs rounded-lg border inline-block">
                                        {{ $monthCnt }}x Bulan Ini
                                    </span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">Total: {{ $totalCnt }}x</span>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($log->status === 'pending')
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-800 font-bold text-[11px] rounded-lg border border-amber-200">
                                            Pending
                                        </span>
                                    @elseif($log->status === 'rejected')
                                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 font-bold text-[11px] rounded-lg border border-rose-200 block">
                                            Dikembalikan
                                        </span>
                                        @if($log->rejection_note)
                                            <span class="text-[10px] text-rose-600 block mt-1 max-w-[170px] truncate" title="{{ $log->rejection_note }}">
                                                {{ $log->rejection_note }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold text-[11px] rounded-lg border border-emerald-200 block">
                                            Diverifikasi
                                        </span>
                                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ $log->verifier?->name ?? 'Guru' }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($log->status === 'pending')
                                            <form method="POST" action="{{ route('guru.conduct.verify', $log->id) }}" class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-all">
                                                    Verifikasi
                                                </button>
                                            </form>
                                            <button type="button"
                                                @click="rejectModal = true; rejectUrl = '{{ route('guru.conduct.reject', $log->id) }}'; rejectStudent = '{{ addslashes(str_replace('"', '', $log->student?->name ?? 'Siswa')) }}';"
                                                class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs rounded-lg border border-rose-200 transition-all">
                                                Kembalikan
                                            </button>
                                        @elseif($log->status === 'rejected')
                                            <form method="POST" action="{{ route('guru.conduct.verify', $log->id) }}" class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs rounded-lg border border-emerald-200 transition-all">
                                                    Verifikasi
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button"
                                            @click="deleteModal = true; deleteUrl = '{{ route('guru.conduct.destroy', $log->id) }}'; deleteStudent = '{{ addslashes(str_replace('"', '', $log->student?->name ?? 'Siswa')) }}';"
                                            title="Hapus ajuan ini"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- TAB 2: REKAP & DATA PELANGGARAN SISWA PER BULAN --}}
    <div x-show="tab === 'pelanggaran'" x-cloak class="space-y-5">
        {{-- Rekap Siswa Pelanggar Bulan Ini --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">
                        Rekap Siswa yang Melakukan Pelaporan Mandiri / Pelanggaran — {{ $monthLabel }}
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Diurutkan dari frekuensi tertinggi pada bulan {{ $monthLabel }}</p>
                </div>
                <span class="text-xs text-slate-500 font-semibold">{{ $monthlyStudentRecap->count() }} Siswa</span>
            </div>

            @if($monthlyStudentRecap->isEmpty())
                <div class="p-10 text-center">
                    <h4 class="font-bold text-sm text-slate-700">Belum ada catatan pelanggaran pada {{ $monthLabel }}</h4>
                    <p class="text-xs text-slate-400 mt-1">Tidak ada siswa yang tercatat melanggar pada periode ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[10.5px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 w-10 text-center">No</th>
                                <th class="py-3 px-4">Nama Siswa</th>
                                <th class="py-3 px-4">Kelas</th>
                                <th class="py-3 px-4 text-center">Lapor Mandiri ({{ $monthLabel }})</th>
                                <th class="py-3 px-4 text-center">Total Pelanggaran ({{ $monthLabel }})</th>
                                <th class="py-3 px-4">Catatan Terakhir</th>
                                <th class="py-3 px-4 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($monthlyStudentRecap as $idx => $recap)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 text-center font-semibold text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('guru.conduct.student', $recap->student_id) }}" class="font-bold text-slate-900 hover:text-blue-600 block">
                                            {{ $recap->student?->name ?? '—' }}
                                        </a>
                                        <span class="text-[11px] text-slate-400">NISN: {{ $recap->student?->nisn ?? '—' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold text-xs rounded-lg border border-slate-200">
                                            {{ $recap->student?->schoolClass?->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-bold text-slate-700">{{ $recap->monthly_self_reports }}x</span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 {{ $recap->monthly_violation_count >= 3 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-200' }} font-bold text-xs rounded-lg border">
                                            {{ $recap->monthly_violation_count }}x Bulan Ini
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-slate-800 block">{{ $recap->latest_log->displayCategoryName() }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $recap->latest_log->created_at->translatedFormat('d M Y, H:i') }} WITA</span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('guru.conduct.student', $recap->student_id) }}"
                                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg inline-block">
                                            Lihat Riwayat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Log Detail Seluruh Pelanggaran Bulan Ini --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-900">
                    Rincian Seluruh Catatan Pelanggaran Siswa — {{ $monthLabel }}
                </h3>
                <span class="text-xs text-slate-500 font-semibold">{{ $monthlyViolations->count() }} Catatan</span>
            </div>

            @if($monthlyViolations->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[10.5px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 w-10 text-center">No</th>
                                <th class="py-3 px-4">Tanggal & Jam</th>
                                <th class="py-3 px-4">Siswa</th>
                                <th class="py-3 px-4">Kelas</th>
                                <th class="py-3 px-4">Jenis / Keterangan Pelanggaran</th>
                                <th class="py-3 px-4 text-center">Sumber</th>
                                <th class="py-3 px-4">Pencatat / Verifikator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($monthlyViolations as $idx => $vLog)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 text-center font-semibold text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="font-bold text-slate-800 block">{{ $vLog->created_at->translatedFormat('d M Y') }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $vLog->created_at->format('H:i') }} WITA</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('guru.conduct.student', $vLog->student_id) }}" class="font-bold text-slate-900 hover:text-blue-600 block">
                                            {{ $vLog->student?->name ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold text-xs rounded-lg border border-slate-200">
                                            {{ $vLog->student?->schoolClass?->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs">
                                        <span class="font-bold text-slate-800 block">{{ $vLog->displayCategoryName() }}</span>
                                        @if($vLog->parsed_description)
                                            <p class="text-slate-500 text-[11px] truncate mt-0.5">{{ $vLog->parsed_description }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($vLog->is_self_reported)
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-md text-[10.5px] font-semibold">
                                                Lapor Mandiri
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-md text-[10.5px] font-semibold">
                                                Catat Guru {{ $vLog->severity ? '(' . ucfirst($vLog->severity) . ')' : '' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">
                                        {{ $vLog->verifier?->name ?? $vLog->teacher?->name ?? 'Mandiri' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Kembalikan / Tolak Ajuan --}}
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="rejectModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-base text-slate-900">Kembalikan / Tolak Ajuan</h3>
                    <p class="text-xs text-slate-500">Siswa: <span class="font-bold text-slate-800" x-text="rejectStudent"></span></p>
                </div>
                <button @click="rejectModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
            </div>

            <form method="POST" :action="rejectUrl" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Catatan Pengembalian <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="rejection_note" rows="3" required
                        placeholder="Contoh: Alasan kurang jelas / harap perbaiki kronologi..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2.5 pt-2">
                    <button type="button" @click="rejectModal = false"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl">
                        Kembalikan ke Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus Ajuan --}}
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="deleteModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl relative space-y-4 text-center">
            <div>
                <h3 class="font-bold text-base text-slate-900">Hapus Ajuan Pembinaan?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Anda akan menghapus ajuan dari <span class="font-bold text-slate-800" x-text="deleteStudent"></span>.
                </p>
            </div>

            <form method="POST" :action="deleteUrl" class="flex items-center gap-2.5 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModal = false"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
