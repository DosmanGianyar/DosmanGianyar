<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Kunjungan Perpustakaan - {{ $monthName }} {{ $year }}</title>
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
            <a href="{{ url('/admin/library-visits') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                &larr; Kembali ke Admin Panel
            </a>
            <span class="text-xs text-gray-300">|</span>
            <span class="text-xs font-bold text-gray-700">Filter Laporan Kunjungan:</span>
        </div>

        <form method="GET" action="{{ route('admin.library.visit-report') }}" class="flex flex-wrap items-center gap-2">
            <!-- Dropdown Bulan -->
            <select name="month" class="px-3 py-1.5 bg-gray-50 border border-gray-300 text-xs font-bold rounded-lg focus:ring-2 focus:ring-emerald-500">
                @foreach([
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ] as $mVal => $mName)
                    <option value="{{ $mVal }}" {{ $month == $mVal ? 'selected' : '' }}>{{ $mName }}</option>
                @endforeach
            </select>

            <!-- Input Tahun -->
            <input type="number" name="year" value="{{ $year }}" min="2020" max="2035" class="w-20 px-3 py-1.5 bg-gray-50 border border-gray-300 text-xs font-bold rounded-lg focus:ring-2 focus:ring-emerald-500">

            <!-- Filter Kelas -->
            <select name="class_id" class="px-3 py-1.5 bg-gray-50 border border-gray-300 text-xs font-bold rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Kelas</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow transition">
                Tampilkan
            </button>
            <button type="button" onclick="window.print()" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow transition flex items-center gap-1.5">
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
            <h2 class="text-base font-black text-gray-900 uppercase tracking-wide underline">REKAPITULASI KUNJUNGAN SISWA KE PERPUSTAKAAN</h2>
            <p class="text-xs font-bold text-gray-600">
                PERIODE: {{ strtoupper($monthName) }} {{ $year }}
                @if($selectedClass)
                    &bull; KELAS: {{ strtoupper($selectedClass->name) }}
                @endif
            </p>
        </div>

        <!-- STATS SUMMARY BADGES -->
        <div class="grid grid-cols-3 gap-4 text-center text-xs">
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                <span class="text-emerald-700 font-bold block text-[10px] uppercase">Total Kunjungan</span>
                <span class="text-lg font-black text-emerald-950">{{ $totalVisits }} Kali Kunjungan</span>
            </div>
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                <span class="text-blue-700 font-bold block text-[10px] uppercase">Siswa Membaca (Unik)</span>
                <span class="text-lg font-black text-blue-950">{{ $uniqueStudents }} Siswa</span>
            </div>
            <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl">
                <span class="text-indigo-700 font-bold block text-[10px] uppercase">Filter Kelas Terpilih</span>
                <span class="text-lg font-black text-indigo-950">{{ $selectedClass ? $selectedClass->name : 'Semua Kelas' }}</span>
            </div>
        </div>

        <!-- TABEL DATA KUNJUNGAN -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100 text-gray-800 uppercase text-[10px] font-bold tracking-wider">
                        <th class="border border-gray-300 px-3 py-2 text-center w-10">No</th>
                        <th class="border border-gray-300 px-3 py-2 w-36">Waktu Kunjungan</th>
                        <th class="border border-gray-300 px-3 py-2">Nama Siswa</th>
                        <th class="border border-gray-300 px-3 py-2 w-24">NIS</th>
                        <th class="border border-gray-300 px-3 py-2 w-28 text-center">Kelas</th>
                        <th class="border border-gray-300 px-3 py-2">Keperluan Membaca</th>
                        <th class="border border-gray-300 px-3 py-2">Catatan / Buku</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($visits as $index => $visit)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' }} hover:bg-gray-100/50">
                            <td class="border border-gray-300 px-3 py-2 text-center font-bold text-gray-500">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-gray-700 font-medium">
                                {{ \Carbon\Carbon::parse($visit->visited_at)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 font-bold text-gray-900">
                                {{ $visit->student?->name ?? '—' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-gray-600 font-mono text-[11px]">
                                {{ $visit->student?->nis ?? '—' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center font-bold text-indigo-700">
                                {{ $visit->student?->schoolClass?->name ?? '—' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-gray-800 font-medium">
                                {{ $visit->purpose ?? 'Membaca Buku' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-gray-600 italic">
                                {{ $visit->notes ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border border-gray-300 px-4 py-8 text-center text-gray-400 font-medium italic">
                                tidak ada data kunjungan perpustakaan pada periode dan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- FOOTER TANDA TANGAN -->
        <div class="pt-6 grid grid-cols-2 text-xs font-sans">
            <div></div>
            <div class="text-center space-y-12">
                <div>
                    <p class="text-gray-600">Gianyar, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="font-bold text-gray-900">Pengelola Perpustakaan SMAN 1 Gianyar</p>
                </div>
                <div>
                    <p class="font-bold text-gray-900 underline uppercase tracking-wide">( NIP. .................................................... )</p>
                    <p class="text-[10px] text-gray-500">Stempel & Tanda Tangan Resmi</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
