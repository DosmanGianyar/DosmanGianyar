@extends('layouts.siswa')
@section('title', 'Scan QR Aset')
@section('page-title', 'Scan Aset')

@section('content')
<div class="space-y-4">

    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <p class="text-sm text-blue-700">Arahkan kamera ke QR Code yang ada di aset sekolah untuk melihat detail dan melaporkan kerusakan.</p>
    </div>

    {{-- QR Scanner --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div id="qr-reader" class="w-full"></div>
        <div id="qr-result" class="hidden p-4 text-center">
            <p class="text-sm text-green-700 font-medium">QR terdeteksi, mengalihkan...</p>
        </div>
    </div>

    {{-- Manual input fallback --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-3 font-medium">Atau masukkan kode aset secara manual:</p>
        <form id="manual-form" class="flex gap-2">
            <input type="text" id="manual-code" placeholder="Masukkan kode UUID aset..."
                class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
            <button type="submit"
                class="px-4 py-2.5 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition-colors">
                Cari
            </button>
        </form>
    </div>

    <a href="{{ route('siswa.sarpras.loans') }}"
        class="flex items-center gap-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:bg-gray-50 transition-colors">
        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-800">Riwayat Peminjaman Saya</p>
            <p class="text-xs text-gray-500">Cek status permintaan pinjam aset</p>
        </div>
        <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const scannerEl = document.getElementById('qr-reader');
    const resultEl  = document.getElementById('qr-result');

    const html5QrCode = new Html5Qrcode('qr-reader');

    html5QrCode.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
            resultEl.classList.remove('hidden');
            html5QrCode.stop();
            window.location.href = decodedText;
        },
        (errorMessage) => { /* ignore scan errors */ }
    ).catch((err) => {
        scannerEl.innerHTML = '<div class="p-6 text-center text-gray-400 text-sm">Kamera tidak dapat diakses. Gunakan input manual di bawah.</div>';
    });

    document.getElementById('manual-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const code = document.getElementById('manual-code').value.trim();
        if (code) {
            window.location.href = '/siswa/sarpras/asset/' + code;
        }
    });
</script>
@endsection
