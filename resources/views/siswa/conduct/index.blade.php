@extends('layouts.siswa')
@section('title', 'SIPINTER — Pendidikan Karakter')
@section('page-title', 'SIPINTER (Pendidikan Karakter)')

@section('content')
<div class="space-y-4" x-data="{ openReportModal: false, selectedReason: 'Terlambat Masuk Sekolah' }">

    {{-- Banner Pengajuan Mandiri --}}
    @if(isset($pendingReport) && $pendingReport)
        <div class="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-500/10 border-2 border-amber-300 rounded-2xl p-4 text-amber-950 shadow-xs">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                        </span>
                        <h3 class="font-bold text-sm text-amber-950">
                            Pengajuan Mandiri Sedang Menunggu Verifikasi
                        </h3>
                    </div>
                    <p class="text-xs text-amber-900">
                        Pelanggaran: <span class="font-black text-amber-950">{{ $pendingReport->displayCategoryName() }}</span>
                        @if($pendingReport->parsed_description)
                            <span class="text-amber-800 italic">({{ $pendingReport->parsed_description }})</span>
                        @endif
                    </p>
                    <p class="text-[11px] text-amber-800">
                        Diajukan pada {{ $pendingReport->created_at->isoFormat('D MMM Y, HH:mm') }} WITA. Silakan tunjukkan kepada Guru Piket di gerbang.
                    </p>
                    <p class="text-[10.5px] text-amber-700 font-semibold bg-amber-100/80 px-2.5 py-1 rounded-lg inline-block border border-amber-200">
                        ℹ️ Anda baru dapat melapor pelanggaran mandiri sekali lagi setelah pengajuan ini selesai diverifikasi.
                    </p>
                </div>
                <span class="px-3 py-1.5 bg-amber-200 text-amber-900 font-extrabold text-[11px] rounded-xl shrink-0 border border-amber-300 shadow-2xs">
                    ⏱️ Menunggu Guru
                </span>
            </div>
        </div>
    @else
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl p-4 text-white shadow-sm flex items-center justify-between">
            <div>
                <h3 class="font-bold text-sm flex items-center gap-1.5">
                    <span>⚡ Terlambat / Lapor Pembinaan Mandiri?</span>
                </h3>
                <p class="text-xs text-amber-100 mt-0.5">Ajukan dari HP Anda untuk verifikasi di gerbang.</p>
            </div>
            <button @click="openReportModal = true" type="button"
                class="px-3.5 py-2 bg-white text-amber-700 hover:bg-amber-50 text-xs font-bold rounded-xl transition-all shadow-sm shrink-0 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Ajukan Sekarang</span>
            </button>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-green-50 rounded-2xl p-4 text-center">
            <p class="text-2xl font-bold text-green-700">{{ $prestasiCount }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Prestasi</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-4 text-center">
            <p class="text-2xl font-bold text-red-700">{{ $pelanggaranCount }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Catatan Negatif</p>
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">Riwayat Catatan & Pengajuan</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($logs as $log)
            <div class="flex items-start gap-3 px-4 py-3">
                <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center
                    {{ $log->isPrestasi() ? 'bg-green-100' : ($log->isPending() ? 'bg-amber-100' : 'bg-red-100') }}">
                    @if($log->isPending())
                        <svg class="w-4 h-4 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($log->isPrestasi())
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-medium text-gray-800">{{ $log->displayCategoryName() }}</p>
                        @if($log->isPending())
                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 font-bold text-[10px] rounded-md shrink-0 border border-amber-200">
                                ⏱️ Menunggu Verifikasi Guru
                            </span>
                        @else
                            <span class="text-xs font-semibold shrink-0 {{ $log->isPrestasi() ? 'text-green-700' : 'text-red-700' }}">
                                {{ $log->isPrestasi() ? 'Apresiasi Karakter' : 'Kedisiplinan Karakter' }}
                            </span>
                        @endif
                    </div>
                    @if($log->note || ($log->description && $log->description !== $log->displayCategoryName()))
                        <p class="text-xs text-gray-500 mt-0.5">{{ $log->note ?: $log->description }}</p>
                    @endif
                    <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                        <span>{{ $log->created_at->isoFormat('D MMM Y, HH:mm') }}</span>
                        @if($log->verifier)
                            <span class="text-[11px] text-emerald-600 font-semibold">✓ Diverifikasi oleh {{ $log->verifier->name }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center">
                <p class="text-gray-400 text-sm">Belum ada catatan</p>
            </div>
            @endforelse
        </div>
        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </div>

    {{-- Modal Pengajuan Pembinaan Mandiri (Hanya jika belum ada pending report) --}}
    @if(!isset($pendingReport) || !$pendingReport)
    <div x-show="openReportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="openReportModal = false" class="bg-white rounded-2xl max-w-md w-full p-5 shadow-xl relative space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center text-sm">⚡</span>
                    <h3 class="font-bold text-base text-gray-800">Lapor Pembinaan Mandiri</h3>
                </div>
                <button @click="openReportModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('siswa.conduct.self-report') }}" enctype="multipart/form-data" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Pembinaan Disiplin <span class="text-red-500">*</span></label>
                    <select name="reason" x-model="selectedReason" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                        <option value="Terlambat Masuk Sekolah">Terlambat Masuk Sekolah (Gerbang)</option>
                        <option value="Terlambat Kembali dari Izin Keluar">Terlambat Kembali dari Izin Keluar</option>
                        <option value="Seragam / Atribut Tidak Lengkap">Seragam / Atribut Tidak Lengkap</option>
                        <option value="Lainnya">Alasan Lainnya (Input Sendiri)</option>
                    </select>
                </div>

                {{-- Input teks dinamis jika memilih Lainnya --}}
                <div x-show="selectedReason === 'Lainnya'" x-transition class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Sebutkan Pelanggaran / Alasan Lainnya <span class="text-red-500">*</span></label>
                    <input type="text" name="other_reason" placeholder="Contoh: Meninggalkan kelas tanpa izin / Tidak memakai dasi"
                        :required="selectedReason === 'Lainnya'"
                        class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan / Alasan Tambahan <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <textarea name="description" rows="2" placeholder="Contoh: Terjebak kemacetan di jalan utama / ban bocor."
                        class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Foto Bukti / Pendukung <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="button" @click="openReportModal = false" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
