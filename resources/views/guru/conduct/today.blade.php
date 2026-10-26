@extends('layouts.guru')
@section('title', 'Daftar Pengajuan Mandiri Hari Ini')
@section('page-title', 'List Pengajuan Pembinaan Disiplin Mandiri (Hari Berjalan)')

@section('content')
<div class="space-y-6" x-data="{ rejectModal: false, rejectUrl: '', rejectStudent: '', deleteModal: false, deleteUrl: '', deleteStudent: '' }">

    {{-- Top Summary Banner --}}
    <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 text-white rounded-3xl p-6 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-xs font-black rounded-lg uppercase tracking-wider">⚡ Hari Berjalan</span>
                <span class="text-xs font-bold text-amber-100">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h2 class="font-black text-2xl mt-2">Daftar Pengajuan Pembinaan Mandiri Siswa</h2>
            <p class="text-xs text-amber-100 mt-1 max-w-2xl leading-relaxed">
                Memudahkan guru piket memverifikasi, mengembalikan agar diperbaiki siswa, atau menghapus ajuan mandiri hari ini.
            </p>
        </div>

        {{-- Counter Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 shrink-0">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 text-center border border-white/20">
                <span class="text-2xl font-black block text-white">{{ $pendingCount }}</span>
                <span class="text-[10px] uppercase font-bold text-amber-200">Pending</span>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 text-center border border-white/20">
                <span class="text-2xl font-black block text-emerald-200">{{ $verifiedCount }}</span>
                <span class="text-[10px] uppercase font-bold text-emerald-100">Diverifikasi</span>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 text-center border border-white/20">
                <span class="text-2xl font-black block text-rose-200">{{ $rejectedCount }}</span>
                <span class="text-[10px] uppercase font-bold text-rose-100">Dikembalikan</span>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 text-center border border-white/20">
                <span class="text-2xl font-black block text-orange-200">{{ $frequentCount }}</span>
                <span class="text-[10px] uppercase font-bold text-orange-100">≥ 3x Catatan</span>
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
                    <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Dikembalikan</option>
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
                                    @elseif($log->status === 'rejected')
                                        <span class="px-2.5 py-1 bg-rose-100 text-rose-800 font-extrabold text-[11px] rounded-lg border border-rose-200 block">
                                            ✕ Dikembalikan
                                        </span>
                                        @if($log->rejection_note)
                                            <span class="text-[10px] text-rose-600 block mt-1 max-w-[170px] truncate" title="{{ $log->rejection_note }}">
                                                Catatan: {{ $log->rejection_note }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-extrabold text-[11px] rounded-lg border border-emerald-200 block">
                                            ✓ Diverifikasi
                                        </span>
                                        <span class="text-[9.5px] text-gray-400 block mt-0.5">Oleh: {{ $log->verifier?->name ?? 'Guru' }}</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($log->status === 'pending')
                                            <form method="POST" action="{{ route('guru.conduct.verify', $log->id) }}" class="inline-block">
                                                @csrf
                                                <button type="submit" title="Verifikasi ajuan dan izinkan siswa masuk"
                                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Verifikasi</span>
                                                </button>
                                            </form>

                                            <button type="button"
                                                @click="rejectModal = true; rejectUrl = '{{ route('guru.conduct.reject', $log->id) }}'; rejectStudent = '{{ addslashes(str_replace('"', '', $log->student?->name ?? 'Siswa')) }}';"
                                                title="Kembalikan ajuan untuk diperbaiki siswa"
                                                class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition-all inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                <span>Kembalikan</span>
                                            </button>
                                        @elseif($log->status === 'rejected')
                                            <form method="POST" action="{{ route('guru.conduct.verify', $log->id) }}" class="inline-block">
                                                @csrf
                                                <button type="submit" title="Verifikasi langsung ajuan ini"
                                                    class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200 transition-all inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Verifikasi</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Selesai</span>
                                            </span>
                                        @endif

                                        {{-- Tombol Hapus Ajuan --}}
                                        <button type="button"
                                            @click="deleteModal = true; deleteUrl = '{{ route('guru.conduct.destroy', $log->id) }}'; deleteStudent = '{{ addslashes(str_replace('"', '', $log->student?->name ?? 'Siswa')) }}';"
                                            title="Hapus ajuan ini"
                                            class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
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

    {{-- Modal Kembalikan / Tolak Ajuan --}}
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="rejectModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-black">✕</div>
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900">Kembalikan / Tolak Ajuan</h3>
                        <p class="text-xs text-gray-500">Ajuan siswa: <span class="font-bold text-gray-800" x-text="rejectStudent"></span></p>
                    </div>
                </div>
                <button @click="rejectModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" :action="rejectUrl" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Catatan Pengembalian / Alasan Ditolak <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="rejection_note" rows="3" required
                        placeholder="Contoh: Alasan tidak sesuai dengan fakta di gerbang / harap perjelas kronologi izin / sebutkan atribut yang tidak lengkap..."
                        class="w-full px-3.5 py-2.5 rounded-2xl border border-gray-200 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Catatan ini akan muncul di akun siswa agar siswa dapat memperbaiki ajuannya.</p>
                </div>

                <div class="flex items-center gap-2.5 pt-2">
                    <button type="button" @click="rejectModal = false"
                        class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-colors">
                        Kembalikan ke Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus Ajuan --}}
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="deleteModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative space-y-4 text-center">
            <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto text-xl">
                🗑️
            </div>
            <div>
                <h3 class="font-extrabold text-base text-gray-900">Hapus Ajuan Pembinaan?</h3>
                <p class="text-xs text-gray-500 mt-1">
                    Anda akan menghapus ajuan dari <span class="font-bold text-gray-800" x-text="deleteStudent"></span>. Data yang dihapus tidak dapat dipulihkan.
                </p>
            </div>

            <form method="POST" :action="deleteUrl" class="flex items-center gap-2.5 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModal = false"
                    class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-colors">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
