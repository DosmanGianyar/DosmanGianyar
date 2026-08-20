@extends('layouts.siswa')
@section('title', 'Perpustakaan')
@section('page-title', 'Perpustakaan')

@section('content')

{{-- ─── Header Banner ────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden mb-6">
    <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="flex items-start justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3.5">
            <div class="bg-white/20 p-3.5 rounded-2xl shrink-0 backdrop-blur-md border border-white/20">
                <svg class="w-7 h-7 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <span class="bg-white/20 text-yellow-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-white/20">PRASARANA SEKOLAH</span>
                <h2 class="text-xl font-extrabold leading-tight mt-1">Perpustakaan & Peminjaman Buku</h2>
                <p class="text-xs text-blue-100 mt-0.5">SMA Negeri 1 Gianyar · Layanan Pencatatan Buku & Kartu Bebas Perpustakaan</p>
            </div>
        </div>
        <a href="{{ route('siswa.library.clearance-card') }}" target="_blank"
            class="hidden md:inline-flex items-center gap-1.5 px-4 py-2 bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-bold rounded-2xl text-xs transition-colors shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Kartu Bebas Perpustakaan
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl text-xs flex items-center justify-between shadow-sm">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

@if(session('error'))
<div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-xs flex items-center justify-between shadow-sm">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif

{{-- ─── Status Kartu Bebas Perpustakaan & Ringkasan ────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    {{-- Card Status Bebas --}}
    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-500">Status Bebas Perpustakaan</span>
            @if($isClear)
                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[11px] font-extrabold rounded-full border border-emerald-200">BEBAS PERPUSTAKAAN</span>
            @else
                <span class="px-2.5 py-1 bg-rose-100 text-rose-800 text-[11px] font-extrabold rounded-full border border-rose-200">MASIH ADA PINJAMAN</span>
            @endif
        </div>

        <div>
            @if($isClear)
                <p class="text-sm font-bold text-emerald-700">✓ Tidak ada penunggakan buku</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Anda berhak mendapatkan Kartu/Surat Bebas Perpustakaan resmi.</p>
            @else
                <p class="text-sm font-bold text-rose-600">⚠️ Terdapat {{ $activeLoans->count() }} buku belum dikembalikan</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Harap kembalikan buku ke petugas perpustakaan untuk membebaskan tanggungan.</p>
            @endif
        </div>

        <a href="{{ route('siswa.library.clearance-card') }}" target="_blank"
            class="w-full text-center py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs border border-indigo-200 transition-colors">
            🖨️ Cetak / Lihat Kartu Bebas Perpustakaan
        </a>
    </div>

    {{-- Stats Pinjaman --}}
    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm text-center flex flex-col justify-center">
        <p class="text-3xl font-black text-indigo-600">{{ $activeLoans->count() }}</p>
        <p class="text-xs font-bold text-gray-700 mt-1">Buku Sedang Dipinjam</p>
        <p class="text-[11px] text-gray-400 mt-0.5">Aktif dalam tanggungan siswa</p>
    </div>

    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm text-center flex flex-col justify-center">
        <p class="text-3xl font-black text-emerald-600">{{ $returnedLoans->count() }}</p>
        <p class="text-xs font-bold text-gray-700 mt-1">Buku Telah Dikembalikan</p>
        <p class="text-[11px] text-gray-400 mt-0.5">Total riwayat pengembalian</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- ─── Form Cepat Pinjam Buku Baru ──────────────────────────────── --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center text-xs font-bold">➕</span>
                    Catat Peminjaman Buku Baru
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Masukkan judul buku yang sedang/akan Anda pinjam.</p>
            </div>

            <form action="{{ route('siswa.library.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Judul Buku <span class="text-red-500">*</span></label>
                    <input type="text" name="book_title" value="{{ old('book_title') }}" required
                        placeholder="Contoh: Matematika Peminatan Kelas XII"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('book_title') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Kode / No. Inventaris Buku <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="book_code" value="{{ old('book_code') }}"
                        placeholder="Contoh: BIB-2026-088"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <input type="hidden" name="phone_number" value="{{ $siswa->phone ?? '' }}">

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tgl Pinjam <span class="text-red-500">*</span></label>
                        <input type="date" name="borrowed_at" value="{{ old('borrowed_at', date('Y-m-d')) }}" required
                            class="w-full border border-gray-200 rounded-xl px-2 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Batas Kembali <span class="text-red-500">*</span></label>
                        <input type="date" name="due_at" value="{{ old('due_at', date('Y-m-d', strtotime('+7 days'))) }}" required
                            class="w-full border border-gray-200 rounded-xl px-2 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                {{-- KEPERLUAN --}}
                <div class="bg-gray-50/80 p-3.5 rounded-2xl border border-gray-200 space-y-2">
                    <label class="block font-bold text-gray-800 text-xs tracking-wide uppercase">KEPERLUAN <span class="text-red-500">*</span></label>
                    <div class="space-y-2 text-xs font-semibold text-gray-700">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" name="purpose_option" value="BELAJAR" checked
                                onclick="document.getElementById('custom_purpose_wrapper').classList.add('hidden')"
                                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span>BELAJAR</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" name="purpose_option" value="MEMBACA"
                                onclick="document.getElementById('custom_purpose_wrapper').classList.add('hidden')"
                                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span>MEMBACA</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" name="purpose_option" value="MEMINJAM BUKU/REFERENSI"
                                onclick="document.getElementById('custom_purpose_wrapper').classList.add('hidden')"
                                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span>MEMINJAM BUKU/REFERENSI</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" name="purpose_option" value="LAINNYA" id="purpose_lainnya"
                                onclick="document.getElementById('custom_purpose_wrapper').classList.remove('hidden')"
                                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span>LAINNYA</span>
                        </label>
                    </div>

                    <div id="custom_purpose_wrapper" class="hidden pt-1">
                        <input type="text" name="purpose_custom" placeholder="Tuliskan keperluan lainnya secara bebas..."
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Catatan Tambahan <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <textarea name="notes" rows="2" placeholder="Catatan kondisi buku..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-colors shadow">
                    💾 Simpan Catatan Peminjaman
                </button>
            </form>
        </div>
    </div>

    {{-- ─── Daftar Peminjaman Buku Siswa ─────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-900 text-sm">📖 Riwayat Peminjaman Buku Saya</h3>
                <span class="text-xs text-gray-400">{{ $loans->count() }} Peminjaman Tercatat</span>
            </div>

            @if($loans->isEmpty())
                <div class="py-12 text-center space-y-2">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center mx-auto text-xl">
                        📚
                    </div>
                    <p class="text-xs font-bold text-gray-700">Belum Ada Peminjaman Buku</p>
                    <p class="text-[11px] text-gray-400">Gunakan form di samping untuk mencatat pinjaman buku perpustakaan Anda.</p>
                </div>
            @else
                <div class="space-y-3 text-xs">
                    @foreach($loans as $loan)
                        @php
                            $statusBg = match($loan->status) {
                                'returned' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'overdue'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                default    => 'bg-amber-50 text-amber-800 border-amber-200',
                            };
                        @endphp
                        <div class="p-4 rounded-2xl border border-gray-100 hover:border-indigo-100 transition-colors space-y-2 bg-gray-50/50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-sm leading-snug">{{ $loan->book_title }}</h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        @if($loan->book_code)
                                            <span class="text-[11px] font-mono text-gray-500">Kode: {{ $loan->book_code }}</span>
                                        @endif
                                        @if($loan->purpose)
                                            <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                                Keperluan: {{ $loan->purpose }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border shrink-0 {{ $statusBg }}">
                                    {{ $loan->statusLabel() }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 pt-1 border-t border-gray-200/60 text-[11px] text-gray-600">
                                <div>
                                    <span class="text-gray-400 block text-[10px]">Tanggal Pinjam:</span>
                                    <span class="font-semibold">{{ $loan->borrowed_at->isoFormat('D MMMM Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px]">Batas Pengembalian:</span>
                                    <span class="font-semibold {{ $loan->isOverdue() ? 'text-red-600 font-bold' : '' }}">
                                        {{ $loan->due_at->isoFormat('D MMMM Y') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px]">Dikembalikan Pada:</span>
                                    <span class="font-semibold text-emerald-700">
                                        {{ $loan->returned_at ? $loan->returned_at->isoFormat('D MMM Y HH:mm') : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
