<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Peminjaman Buku Perpustakaan - {{ $monthName }} {{ $year }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; background: white !important; }
            .no-print { display: none !important; }
            .print-shadow-none { box-shadow: none !important; border: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-4 md:p-8 text-gray-800 font-sans">

    <!-- Floating Action Toolbar (No Print) -->
    <div class="no-print max-w-6xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-md border border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/admin/library-loans') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                &larr; Kembali ke Admin Panel
            </a>
            <span class="text-xs text-gray-300">|</span>
            <span class="text-xs font-bold text-gray-700">Filter Laporan Rekapitulasi:</span>
        </div>

        <form method="GET" action="{{ route('admin.library.monthly-loan-report') }}" class="flex flex-wrap items-center gap-2">
            <!-- Dropdown Bulan -->
            <select name="month" class="px-3 py-1.5 bg-gray-50 border border-gray-300 text-xs font-bold rounded-lg focus:ring-2 focus:ring-blue-500">
                @foreach([
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ] as $mVal => $mName)
                    <option value="{{ $mVal }}" {{ $month == $mVal ? 'selected' : '' }}>{{ $mName }}</option>
                @endforeach
            </select>

            <!-- Input Tahun -->
            <input type="number" name="year" value="{{ $year }}" min="2020" max="2035" class="w-20 px-3 py-1.5 bg-gray-50 border border-gray-300 text-xs font-bold rounded-lg focus:ring-2 focus:ring-blue-500">

            <!-- Dropdown Status -->
            <select name="status" class="px-3 py-1.5 bg-gray-50 border border-gray-300 text-xs font-bold rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="borrowed" {{ $status == 'borrowed' ? 'selected' : '' }}>Sedang Dipinjam</option>
                <option value="returned" {{ $status == 'returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Terlambat</option>
            </select>

            <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow transition">
                Tampilkan
            </button>
            <button type="button" onclick="window.print()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow transition flex items-center gap-1.5">
                🖨️ Cetak Laporan (Print)
            </button>
        </form>
    </div>

    <!-- Paper Container A4 Landscape -->
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-3xl shadow-xl border border-gray-200 print-shadow-none space-y-6">

        <!-- KOP SURAT RESMI -->
        <div class="flex items-center justify-between border-b-4 border-double border-gray-900 pb-3">
            <img src="{{ asset('img/logo-pemprov-bali.png') }}" alt="Logo Bali" class="h-20 w-auto object-contain shrink-0">
            <div class="text-center flex-1 px-4 font-serif">
                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-800">PEMERINTAH PROVINSI BALI</h3>
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">DINAS PENDIDIKAN KEPEMUDAAN DAN OLAHRAGA</h2>
                <h1 class="text-xl font-black uppercase text-indigo-950 tracking-wider">SMA NEGERI 1 GIANYAR</h1>
                <p class="text-[11px] text-gray-600 font-sans mt-0.5">Jln. Ratna, Tegal Tugu Gianyar, Telp : (0361) 943034</p>
                <p class="text-[10px] text-gray-500 font-sans">Website: https://sman1-gianyar.sch.id &nbsp;•&nbsp; E-mail: sman1.gianyar1963@gmail.com &nbsp;•&nbsp; NPSN: 50102079</p>
            </div>
            <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo Dosman" class="h-20 w-auto object-contain shrink-0">
        </div>

        <!-- HEADER LAPORAN -->
        <div class="text-center space-y-1">
            <h2 class="text-base font-black text-gray-900 uppercase tracking-wide underline">REKAPITULASI PEMINJAMAN BUKU PERPUSTAKAAN</h2>
            <p class="text-xs font-bold text-gray-600">PERIODE: {{ strtoupper($monthName) }} {{ $year }}</p>
        </div>

        <!-- STATS SUMMARY BADGES -->
        <div class="grid grid-cols-4 gap-3 text-center text-xs">
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                <span class="text-blue-600 font-bold block text-[10px] uppercase">Total Peminjaman</span>
                <span class="text-lg font-black text-blue-900">{{ $totalLoans }} Buku</span>
            </div>
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                <span class="text-amber-600 font-bold block text-[10px] uppercase">Sedang Dipinjam</span>
                <span class="text-lg font-black text-amber-900">{{ $borrowedCount }} Buku</span>
            </div>
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                <span class="text-emerald-600 font-bold block text-[10px] uppercase">Sudah Dikembalikan</span>
                <span class="text-lg font-black text-emerald-900">{{ $returnedCount }} Buku</span>
            </div>
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl">
                <span class="text-rose-600 font-bold block text-[10px] uppercase">Terlambat / Jatuh Tempo</span>
                <span class="text-lg font-black text-rose-900">{{ $overdueCount }} Buku</span>
            </div>
        </div>

        <!-- TABEL DETAIL PEMINJAMAN -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-gray-300 border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white text-center font-bold text-[11px]">
                        <th class="p-2 border border-gray-400 w-8">No</th>
                        <th class="p-2 border border-gray-400 w-24">Tgl Pinjam</th>
                        <th class="p-2 border border-gray-400">Nama Siswa</th>
                        <th class="p-2 border border-gray-400 w-20">Kelas</th>
                        <th class="p-2 border border-gray-400">Judul Buku & Kode</th>
                        <th class="p-2 border border-gray-400 w-24">Batas Kembali</th>
                        <th class="p-2 border border-gray-400 w-24">Tgl Kembali</th>
                        <th class="p-2 border border-gray-400 w-28">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($loans as $index => $loan)
                    <tr class="hover:bg-gray-50 text-[11px] {{ $loop->even ? 'bg-gray-50/50' : '' }}">
                        <td class="p-2 border border-gray-300 text-center font-bold">{{ $index + 1 }}</td>
                        <td class="p-2 border border-gray-300 text-center font-mono">{{ $loan->borrowed_at ? $loan->borrowed_at->format('d/m/Y') : '—' }}</td>
                        <td class="p-2 border border-gray-300 font-bold text-gray-900">
                            {{ $loan->student_name }}
                            @if($loan->phone)
                                <span class="block text-[9px] text-gray-500 font-normal">HP: {{ $loan->phone }}</span>
                            @endif
                        </td>
                        <td class="p-2 border border-gray-300 text-center font-semibold">{{ $loan->class_name }}</td>
                        <td class="p-2 border border-gray-300">
                            <span class="font-bold text-blue-900 block">{{ $loan->book_title }}</span>
                            @if($loan->book_code)
                                <span class="text-[9px] font-mono bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-200 inline-block mt-0.5">
                                    Kode: {{ $loan->book_code }}
                                </span>
                            @endif
                        </td>
                        <td class="p-2 border border-gray-300 text-center font-mono">{{ $loan->due_at ? $loan->due_at->format('d/m/Y') : '—' }}</td>
                        <td class="p-2 border border-gray-300 text-center font-mono">
                            {{ $loan->returned_at ? $loan->returned_at->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="p-2 border border-gray-300 text-center">
                            @if($loan->status === 'returned')
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded border border-emerald-300">
                                    ✓ Dikembalikan
                                </span>
                            @elseif($loan->isOverdue())
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold text-[10px] rounded border border-rose-300">
                                    ⚠️ Terlambat
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold text-[10px] rounded border border-amber-300">
                                    📖 Dipinjam
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-400 font-bold text-xs">
                            Tidak ada data peminjaman buku pada periode bulan {{ $monthName }} {{ $year }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TANDA TANGAN LEMBAR LEGALITAS -->
        <div class="pt-6 flex items-start justify-between text-xs font-sans">
            <div class="text-center space-y-16">
                <p>Mengetahui,<br><span class="font-bold">Kepala SMA Negeri 1 Gianyar</span></p>
                <div>
                    <img src="{{ asset('img/ttd_kepsek.png') }}" alt="TTD Kepsek" class="h-16 w-auto mx-auto object-contain">
                    <p class="font-bold underline text-gray-900 mt-1">I Wayan Sudra Astra, S.Pd., M.Pd.</p>
                    <p class="text-[10px] text-gray-500">NIP. 19680512 199103 1 009</p>
                </div>
            </div>

            <div class="text-center space-y-16">
                <p>Gianyar, {{ now()->translatedFormat('d F Y') }}<br><span class="font-bold">Pengelola Perpustakaan</span></p>
                <div>
                    <div class="h-16"></div>
                    <p class="font-bold underline text-gray-900 mt-1">( Petugas Perpustakaan )</p>
                    <p class="text-[10px] text-gray-500">SMA Negeri 1 Gianyar</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
