<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Bebas Perpustakaan - {{ $siswa->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-card { border: none !important; shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-4 md:p-8 flex flex-col items-center justify-center font-sans text-gray-800">

    {{-- Tombol Aksi Cetak (No Print) --}}
    <div class="no-print max-w-2xl w-full flex items-center justify-between mb-4">
        <a href="javascript:history.back()" class="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-gray-900 font-semibold bg-white px-3 py-2 rounded-xl shadow-sm border border-gray-200">
            ← Kembali
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-xl shadow transition-colors">
            🖨️ Cetak / Download PDF
        </button>
    </div>

    {{-- Dokumen Kartu Bebas Perpustakaan --}}
    <div class="print-card max-w-2xl w-full bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-gray-200 space-y-6 relative overflow-hidden">
        
        {{-- Kop Surat SMAN 1 Gianyar --}}
        <div class="flex items-center gap-4 border-b-2 border-gray-900 pb-4">
            <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo SMAN 1 Gianyar" class="w-16 h-16 object-contain shrink-0">
            <div class="text-center flex-1">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-600">PEMERINTAH PROVINSI BALI</h4>
                <h3 class="text-base font-extrabold uppercase tracking-wide text-gray-900">DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA</h3>
                <h2 class="text-lg font-black uppercase text-indigo-950">SMA NEGERI 1 GIANYAR</h2>
                <p class="text-[10px] text-gray-500 mt-0.5">Jl. Ratna No. 1 Gianyar, Bali · Telp: (0361) 943034 · Website: sman1-gianyar.sch.id</p>
            </div>
        </div>

        {{-- Judul Surat --}}
        <div class="text-center space-y-1">
            <h1 class="text-base font-black text-gray-900 uppercase tracking-wide underline">SURAT KETERANGAN BEBAS PERPUSTAKAAN</h1>
            <p class="text-xs font-mono text-gray-500">Nomor: {{ date('Y') }}/PERPUS/DOSMAN/{{ sprintf('%04d', $siswa->id) }}</p>
        </div>

        <p class="text-xs text-gray-700 leading-relaxed">
            Pengelola Perpustakaan SMA Negeri 1 Gianyar dengan ini menerangkan bahwa siswa di bawah ini:
        </p>

        {{-- Biodata Siswa --}}
        <div class="bg-gray-50/80 rounded-2xl p-4 border border-gray-200 text-xs space-y-2">
            <div class="grid grid-cols-3">
                <span class="text-gray-500 font-medium">Nama Lengkap</span>
                <span class="col-span-2 font-bold text-gray-900">: {{ $siswa->name }}</span>
            </div>
            <div class="grid grid-cols-3">
                <span class="text-gray-500 font-medium">NIS / NISN</span>
                <span class="col-span-2 font-bold text-gray-900">: {{ $siswa->nisn ?? $siswa->username ?? '—' }}</span>
            </div>
            <div class="grid grid-cols-3">
                <span class="text-gray-500 font-medium">Kelas</span>
                <span class="col-span-2 font-bold text-gray-900">: {{ $siswa->schoolClass?->name ?? '—' }}</span>
            </div>
            <div class="grid grid-cols-3">
                <span class="text-gray-500 font-medium">No. HP / Kontak</span>
                <span class="col-span-2 font-bold text-gray-900">: {{ $siswa->phone ?? '—' }}</span>
            </div>
        </div>

        {{-- Status Bebas Perpustakaan Box --}}
        @if($isClear)
            <div class="bg-emerald-50 border-2 border-emerald-500 rounded-2xl p-4 text-center space-y-1">
                <span class="inline-block bg-emerald-600 text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">
                    ✓ BEBAS PERPUSTAKAAN
                </span>
                <p class="text-xs font-semibold text-emerald-900">
                    Siswa bersangkutan TIDAK MEMILIKI pinjaman buku atau tanggungan administrasi perpustakaan.
                </p>
            </div>
        @else
            <div class="bg-rose-50 border-2 border-rose-500 rounded-2xl p-4 text-center space-y-2">
                <span class="inline-block bg-rose-600 text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">
                    ⚠️ BELUM BEBAS PERPUSTAKAAN
                </span>
                <p class="text-xs font-semibold text-rose-900">
                    Siswa bersangkutan MASIH MEMILIKI {{ $activeLoans->count() }} pinjaman buku aktif yang belum dikembalikan:
                </p>
                <div class="bg-white rounded-xl p-3 text-left border border-rose-200 space-y-1 text-xs">
                    @foreach($activeLoans as $loan)
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-gray-800">• {{ $loan->book_title }}</span>
                            <span class="text-rose-600 font-semibold">Batas: {{ $loan->due_at->format('d M Y') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Tanggal & Tanda Tangan --}}
        <div class="pt-4 flex items-end justify-between text-xs">
            <div class="space-y-1 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-xl p-1.5 border border-gray-200 mx-auto flex items-center justify-center">
                    <img src="{{ (new \chillerlan\QRCode\QRCode(new \chillerlan\QRCode\QROptions(['outputType' => 'svg', 'scale' => 3])))->render(url("/admin/library/clearance-card/{$siswa->id}")) }}" alt="QR Code" class="w-full h-full object-contain">
                </div>
                <span class="text-[9px] font-mono text-gray-400 block">Scan Verifikasi Keabsahan</span>
            </div>

            <div class="text-center space-y-12">
                <div>
                    <p class="text-gray-600">Gianyar, {{ date('d F Y') }}</p>
                    <p class="font-bold text-gray-900">Pengelola Perpustakaan SMAN 1 Gianyar</p>
                </div>
                <div>
                    <p class="font-bold text-gray-900 underline uppercase">Dra. Ni Made Suwenti, M.Pd.</p>
                    <p class="text-[10px] text-gray-500">NIP. 19680512 199403 2 008</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
