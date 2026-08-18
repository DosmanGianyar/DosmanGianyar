<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poster QR Code Kunjungan Perpustakaan SMAN 1 Gianyar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none !important; }
        }
        @page { size: A4 portrait; margin: 15mm; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-6 font-sans text-gray-800">

    <!-- Print Button Floating -->
    <div class="no-print fixed top-6 right-6 flex gap-3 shadow-lg z-50">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg flex items-center gap-2 shadow transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Poster QR
        </button>
        <button onclick="window.close()" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2.5 px-4 rounded-lg shadow transition">
            Tutup
        </button>
    </div>

    <!-- Poster Card Container A4 aspect ratio -->
    <div class="bg-white border-4 border-blue-900 rounded-3xl p-8 max-w-2xl w-full shadow-2xl flex flex-col items-center text-center space-y-6 relative overflow-hidden">
        
        <!-- Top Decorative Header Bar -->
        <div class="w-full bg-gradient-to-r from-blue-900 via-indigo-800 to-blue-900 text-white py-4 px-6 rounded-2xl shadow-md flex items-center justify-between">
            <img src="{{ asset('img/logo_bali.png') }}" alt="Logo Bali" class="h-16 w-auto object-contain">
            <div class="text-center flex-1 px-4">
                <h2 class="text-xs uppercase tracking-widest font-semibold text-blue-200">PEMERINTAH PROVINSI BALI</h2>
                <h1 class="text-lg font-black text-white tracking-wide">SMA NEGERI 1 GIANYAR</h1>
                <p class="text-[11px] text-blue-100 italic">Perpustakaan Wijaya Kusuma</p>
            </div>
            <img src="{{ asset('img/logo_dosman.png') }}" alt="Logo Dosman" class="h-16 w-auto object-contain">
        </div>

        <!-- Title Header -->
        <div class="space-y-1">
            <span class="inline-block bg-blue-100 text-blue-800 font-extrabold text-xs px-4 py-1 rounded-full uppercase tracking-wider">
                PRESENSI BACA DI TEMPAT
            </span>
            <h2 class="text-2xl font-black text-gray-900 tracking-tight">KUNJUNGAN PERPUSTAKAAN</h2>
            <p class="text-sm text-gray-600">Scan Kode QR di bawah ini untuk mencatat kehadiran membaca di perpustakaan</p>
        </div>

        <!-- Big QR Code Box -->
        <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-dashed border-blue-400 rounded-3xl shadow-inner flex flex-col items-center justify-center space-y-3">
            <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-200">
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(260)->margin(1)->generate('SIMS_PERPUS_VISIT') !!}
            </div>
            <span class="font-mono text-xs font-bold text-blue-900 bg-white px-4 py-1.5 rounded-full border border-blue-200 shadow-sm">
                KODE QR: SIMS_PERPUS_VISIT
            </span>
        </div>

        <!-- Step Instructions -->
        <div class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-5 text-left space-y-3">
            <h3 class="text-xs font-extrabold text-blue-900 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Petunjuk Pencatatan Kunjungan Siswa:
            </h3>
            <ol class="text-xs text-gray-700 space-y-1.5 list-decimal list-inside font-medium">
                <li>Buka aplikasi <span class="font-bold text-blue-800">SIMS Mobile</span> atau Web Siswa SMAN 1 Gianyar.</li>
                <li>Masuk ke menu <span class="font-bold text-blue-800">Perpustakaan</span> &rarr; <span class="font-bold text-blue-800">Kunjungan Perpustakaan</span>.</li>
                <li>Tekan tombol <span class="font-bold text-blue-800">Scan QR Kunjungan</span> lalu arahkan kamera ke QR Code di atas.</li>
                <li>Pilih <span class="font-bold text-blue-800">Keperluan Membaca</span> Anda (Literasi, Tugas, Kerja Kelompok, dll) dan simpan.</li>
            </ol>
        </div>

        <!-- Footer Seal -->
        <div class="w-full flex items-center justify-between text-[11px] text-gray-500 pt-2 border-t border-gray-200">
            <span>SIMS SMAN 1 Gianyar &copy; {{ date('Y') }}</span>
            <span class="font-semibold text-blue-900">Perpustakaan Wijaya Kusuma SMAN 1 Gianyar</span>
        </div>

    </div>

</body>
</html>
