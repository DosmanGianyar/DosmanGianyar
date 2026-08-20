@extends('layouts.siswa')
@section('title', 'Kunjungan Perpustakaan')
@section('page-title', 'Kunjungan Perpustakaan')

@section('content')

{{-- ─── Header Banner ────────────────────────────────────────────────── --}}
<div class="rounded-3xl p-6 text-white shadow-lg relative overflow-hidden mb-6" style="background: linear-gradient(135deg, #0f2460 0%, #1a3a8f 50%, #1e3fad 100%) !important; color: #ffffff !important;">
    <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="flex items-start justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3.5">
            <div class="bg-white/20 p-3.5 rounded-2xl shrink-0 backdrop-blur-md border border-white/20">
                <svg class="w-7 h-7 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <span class="bg-white/20 text-yellow-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-white/20">BACA DI TEMPAT</span>
                <h2 class="text-xl font-extrabold leading-tight mt-1">Kunjungan Perpustakaan</h2>
                <p class="text-xs text-blue-100 mt-0.5">SMA Negeri 1 Gianyar · Scan Kode QR untuk mencatat kehadiran membaca di perpustakaan</p>
            </div>
        </div>
    </div>
</div>

{{-- ─── Navigation Tabs ──────────────────────────────────────────────── --}}
<div class="flex items-center gap-2 mb-6 border-b border-gray-200 pb-3 overflow-x-auto">
    <a href="{{ route('siswa.library.index') }}"
        class="px-4 py-2 text-xs font-bold rounded-xl transition-all bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 flex items-center gap-2 shrink-0">
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        Peminjaman Buku
    </a>
    <a href="{{ route('siswa.library.visit') }}"
        class="px-4 py-2 text-xs font-bold rounded-xl transition-all shadow-sm border border-blue-600 bg-blue-600 text-white flex items-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Kunjungan Perpustakaan (Baca)
    </a>
    <a href="{{ route('siswa.library.catalog') }}"
        class="px-4 py-2 text-xs font-bold rounded-xl transition-all bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 flex items-center gap-2 shrink-0">
        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        Katalog Buku (E-Katalog)
    </a>
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

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    {{-- Left: Form Scan & Catat Kunjungan --}}
    <div class="lg:col-span-5 space-y-6">
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900">Catat Kunjungan Membaca</h3>
                    <p class="text-[11px] text-gray-500">Scan Kode QR di perpustakaan atau masukkan kode manual</p>
                </div>
                <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </span>
            </div>

            <form action="{{ route('siswa.library.visit.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                {{-- QR Code Field --}}
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Kode QR Kunjungan <span class="text-rose-500">*</span></label>
                    
                    {{-- Camera Scan Action Button --}}
                    <div class="mb-2">
                        <button type="button" onclick="openQrScannerModal()" class="w-full py-2.5 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl shadow-md border border-emerald-500/20 flex items-center justify-center gap-2 text-xs transition-all active:scale-[0.98]">
                            <svg class="w-4 h-4 text-emerald-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>📷 Scan QR via Kamera HP</span>
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <input type="text" name="qr_code" id="qr_code_input" required
                            value="{{ old('qr_code', 'PERPUSTAKAAN DOSMAN') }}"
                            placeholder="Contoh: PERPUSTAKAAN DOSMAN"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <button type="button" onclick="autoFillQR()" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold rounded-xl shrink-0 transition" title="Autofill Kode Resmi">
                            Autofill
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Kode resmi banner perpustakaan: <code class="font-bold text-blue-600">PERPUSTAKAAN DOSMAN</code></p>
                </div>

                {{-- Waktu Kunjungan --}}
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Tanggal & Waktu Kunjungan <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="visited_at" required
                        value="{{ old('visited_at', now()->format('Y-m-d\TH:i')) }}"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                {{-- Keperluan Membaca --}}
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Keperluan / Tujuan Membaca <span class="text-rose-500">*</span></label>
                    <select name="purpose_option" id="purpose_option" onchange="toggleCustomPurpose(this.value)"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="Membaca Buku Paket / Literasi">Membaca Buku Paket / Literasi</option>
                        <option value="Mengerjakan Tugas / Kliping">Mengerjakan Tugas / Kliping</option>
                        <option value="Kerja Kelompok">Kerja Kelompok</option>
                        <option value="Mencari Referensi / Jurnal">Mencari Referensi / Jurnal</option>
                        <option value="Lainnya">Lainnya...</option>
                    </select>
                </div>

                {{-- Custom Purpose --}}
                <div id="custom_purpose_wrapper" class="hidden">
                    <label class="block font-bold text-gray-700 mb-1">Tentukan Keperluan Lainnya</label>
                    <input type="text" name="purpose_custom" placeholder="Tuliskan keperluan membaca di tempat..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                {{-- Catatan Opsional --}}
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Catatan / Judul Buku (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Contoh: Membaca Buku Fisika Kelas XII Bab 3"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Kehadiran Kunjungan
                </button>
            </form>
        </div>
    </div>

    {{-- Right: Tabel Riwayat Kunjungan --}}
    <div class="lg:col-span-7 space-y-4">
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900">Riwayat Kunjungan Saya</h3>
                    <p class="text-[11px] text-gray-500">Daftar presensi membaca di tempat di Perpustakaan SMAN 1 Gianyar</p>
                </div>
                <span class="px-3 py-1 bg-blue-50 text-blue-700 font-extrabold text-xs rounded-full border border-blue-100">
                    Total: {{ $visits->count() }} Kunjungan
                </span>
            </div>

            @if($visits->isEmpty())
                <div class="py-12 text-center text-gray-400 space-y-2">
                    <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-xs font-bold text-gray-500">Belum Ada Riwayat Kunjungan</p>
                    <p class="text-[11px]">Silakan scan Kode QR Kunjungan saat membaca di perpustakaan sekolah.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 font-bold border-b border-gray-100">
                                <th class="p-3">Waktu Kunjungan</th>
                                <th class="p-3">Keperluan</th>
                                <th class="p-3">Catatan / Judul</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($visits as $visit)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="p-3 font-semibold text-gray-900 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>{{ $visit->visited_at ? $visit->visited_at->translatedFormat('d M Y, H:i') : '—' }}</span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 font-bold text-[11px] rounded-lg border border-blue-100 inline-block">
                                        {{ $visit->purpose }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-600">
                                    {{ $visit->notes ?: '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Modal Camera QR Scanner --}}
<div id="scanner-modal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl border border-gray-100 flex flex-col">
        {{-- Modal Header --}}
        <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-extrabold">Scan Kode QR Perpustakaan</h4>
                    <p class="text-[10px] text-slate-300">Arahkan kamera HP ke banner QR Code Perpustakaan</p>
                </div>
            </div>
            <button type="button" onclick="closeQrScannerModal()" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-sm font-bold transition">
                ✕
            </button>
        </div>

        {{-- Camera Viewport --}}
        <div class="p-4 space-y-3 bg-slate-950">
            <div id="modal-qr-reader" class="w-full rounded-2xl overflow-hidden border-2 border-emerald-500/50 bg-black min-h-[250px]"></div>
            <div id="scanner-status" class="text-center text-xs text-emerald-400 font-semibold py-1">
                Membuka kamera HP...
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="p-3 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button type="button" onclick="closeQrScannerModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold rounded-xl transition">
                Tutup Kamera
            </button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let html5QrScanner = null;

    function openQrScannerModal() {
        const modal = document.getElementById('scanner-modal');
        const statusEl = document.getElementById('scanner-status');
        modal.classList.remove('hidden');
        statusEl.textContent = 'Membuka kamera HP...';

        if (!html5QrScanner) {
            html5QrScanner = new Html5Qrcode("modal-qr-reader");
        }

        const config = { fps: 10, qrbox: { width: 220, height: 220 } };

        html5QrScanner.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                statusEl.textContent = '✅ QR Code Terdeteksi: ' + decodedText;
                document.getElementById('qr_code_input').value = decodedText;
                
                if (navigator.vibrate) {
                    navigator.vibrate(100);
                }

                setTimeout(() => {
                    closeQrScannerModal();
                }, 600);
            },
            (errorMessage) => {
                // Ignore scan frame error
            }
        ).catch((err) => {
            statusEl.textContent = '⚠️ Kamera tidak dapat diakses. Pastikan izin kamera diaktifkan di browser Anda.';
        });
    }

    function closeQrScannerModal() {
        const modal = document.getElementById('scanner-modal');
        modal.classList.add('hidden');
        if (html5QrScanner) {
            html5QrScanner.stop().catch(() => {});
        }
    }

    function autoFillQR() {
        document.getElementById('qr_code_input').value = 'PERPUSTAKAAN DOSMAN';
    }

    function toggleCustomPurpose(val) {
        const wrapper = document.getElementById('custom_purpose_wrapper');
        if (val === 'Lainnya') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }
</script>

@endsection
