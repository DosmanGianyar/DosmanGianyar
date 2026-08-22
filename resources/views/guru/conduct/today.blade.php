@extends('layouts.guru')
@section('title', 'Daftar Pengajuan Mandiri Hari Ini')
@section('page-title', 'List Pengajuan Pembinaan Disiplin Mandiri (Hari Berjalan)')

@section('content')
<div class="space-y-6">

    {{-- Top Summary Banner --}}
    <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 text-white rounded-3xl p-6 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-xs font-black rounded-lg uppercase tracking-wider">⚡ Hari Berjalan</span>
                <span class="text-xs font-bold text-amber-100">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h2 class="font-black text-2xl mt-2">Daftar Pengajuan Pembinaan Mandiri Siswa</h2>
            <p class="text-xs text-amber-100 mt-1 max-w-2xl leading-relaxed">
                Memudahkan guru melihat seluruh siswa yang terlambat/mengajukan pembinaan mandiri hari ini di semua kelas, lengkap dengan perhitungan akumulasi catatan disiplin siswa.
            </p>
        </div>

        {{-- Counter Cards --}}
        <div class="grid grid-cols-3 gap-2.5 shrink-0">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 text-center border border-white/20">
                <span class="text-2xl font-black block text-white">{{ $pendingCount }}</span>
                <span class="text-[10px] uppercase font-bold text-amber-200">Pending</span>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 text-center border border-white/20">
                <span class="text-2xl font-black block text-emerald-200">{{ $verifiedCount }}</span>
                <span class="text-[10px] uppercase font-bold text-emerald-100">Diverifikasi</span>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 text-center border border-white/20">
                <span class="text-2xl font-black block text-rose-200">{{ $frequentCount }}</span>
                <span class="text-[10px] uppercase font-bold text-rose-100">≥ 3x Catatan</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-xs font-bold flex items-center gap-2.5 shadow-xs">
            <div class="w-7 h-7 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('guru.conduct.today') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            {{-- Filter Kelas --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-gray-600">Kelas:</label>
                <select name="class_id" onchange="this.form.submit()"
                    class="bg-gray-50 border border-gray-200 text-gray-800 text-xs font-bold rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-gray-600">Status:</label>
                <select name="status" onchange="this.form.submit()"
                    class="bg-gray-50 border border-gray-200 text-gray-800 text-xs font-bold rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending (Belum Diverifikasi)</option>
                    <option value="verified" {{ $statusFilter === 'verified' ? 'selected' : '' }}>Diverifikasi</option>
                </select>
            </div>

            @if($selectedClassId || $statusFilter)
                <a href="{{ route('guru.conduct.today') }}" class="text-xs text-amber-600 font-bold hover:underline">Reset Filter</a>
            @endif
        </form>

        <div class="text-xs text-gray-500 font-medium">
            Menampilkan <span class="font-bold text-gray-900">{{ $logs->count() }}</span> pengajuan siswa hari ini
        </div>
    </div>

    {{-- Main Data List Table --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Daftar Pengajuan Pembinaan Mandiri (Semua Kelas)</span>
            </h3>
            <span class="text-xs text-gray-500 font-bold">Hari Berjalan</span>
        </div>

        @if($logs->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-base text-gray-800">Tidak ada data pengajuan mandiri hari ini</h4>
                <p class="text-xs text-gray-400 mt-1">Belum ada siswa yang mengajukan pembinaan mandiri di hari berjalan ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100/70 text-gray-700 font-extrabold uppercase text-[10.5px] tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Waktu</th>
                            <th class="py-3.5 px-4">Siswa</th>
                            <th class="py-3.5 px-4">Kelas</th>
                            <th class="py-3.5 px-4">Alasan / Pengajuan</th>
                            <th class="py-3.5 px-4 text-center">Akumulasi Catatan</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @foreach($logs as $index => $log)
                            @php
                                $cnt = $log->student?->total_pelanggaran_count ?? 1;
                            @endphp
                            <tr class="hover:bg-amber-50/20 transition-colors">
                                {{-- No --}}
                                <td class="py-4 px-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>

                                {{-- Jam --}}
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="font-bold text-gray-900">{{ $log->created_at->format('H:i') }}</span>
                                    <span class="text-[10px] text-gray-400 block">WITA</span>
                                </td>

                                {{-- Siswa --}}
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-black flex items-center justify-center text-xs shrink-0 uppercase">
                                            {{ substr($log->student?->name ?? 'S', 0, 2) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('guru.conduct.student', $log->student_id) }}" class="font-bold text-gray-900 hover:text-amber-600 transition-colors block">
                                                {{ $log->student?->name }}
                                            </a>
                                            <span class="text-[10px] text-gray-400">NISN: {{ $log->student?->nisn ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kelas Siswa --}}
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-blue-600 text-white font-extrabold text-xs rounded-lg shadow-2xs">
                                        {{ $log->student?->schoolClass?->name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Alasan --}}
                                <td class="py-4 px-4 max-w-xs">
                                    <span class="font-bold text-amber-800 block">⚡ {{ $log->displayCategoryName() }}</span>
                                    @if($log->parsed_description)
                                        <p class="text-gray-500 text-[11px] truncate mt-0.5" title="{{ $log->parsed_description }}">
                                            "{{ $log->parsed_description }}"
                                        </p>
                                    @endif
                                </td>

                                {{-- Hitungan Akumulasi Catatan Disiplin --}}
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    @if($cnt <= 1)
                                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold text-xs rounded-lg border border-amber-200 inline-flex items-center gap-1">
                                            <span>⚠️ 1x (Pertama Kali)</span>
                                        </span>
                                    @elseif($cnt < 4)
                                        <span class="px-2.5 py-1 bg-orange-100 text-orange-800 font-bold text-xs rounded-lg border border-orange-200 inline-flex items-center gap-1">
                                            <span>⚠️ Total {{ $cnt }}x Pembinaan</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-red-100 text-red-800 font-black text-xs rounded-lg border border-red-300 animate-pulse inline-flex items-center gap-1">
                                            <span>🚨 {{ $cnt }}x Pembinaan (Sering)</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    @if($log->status === 'pending')
                                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-extrabold text-[11px] rounded-lg border border-amber-200">
                                            ⏱️ Pending
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-extrabold text-[11px] rounded-lg border border-emerald-200 block">
                                            ✓ Diverifikasi
                                        </span>
                                        <span class="text-[9.5px] text-gray-400 block mt-0.5">Oleh: {{ $log->verifier?->name ?? 'Guru' }}</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    @if($log->status === 'pending')
                                        <form method="POST" action="{{ route('guru.conduct.verify', $log->id) }}" class="inline-block">
                                            @csrf
                                            <button type="submit"
                                                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Verifikasi</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs font-bold text-emerald-600 flex items-center justify-end gap-1">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Selesai</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
