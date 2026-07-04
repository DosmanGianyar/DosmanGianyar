<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import User — SIMS | SMA Negeri 1 Gianyar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans min-h-screen p-6">

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ url()->previous() }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-lg font-bold text-gray-800">Import User Massal</h1>
            <p class="text-xs text-gray-500">Upload file Excel untuk menambahkan banyak user sekaligus</p>
        </div>
    </div>

    {{-- Success --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
        <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
        @if(session('import_errors') && count(session('import_errors')) > 0)
            <details class="mt-2">
                <summary class="text-xs text-green-600 cursor-pointer">
                    Lihat {{ count(session('import_errors')) }} peringatan
                </summary>
                <ul class="mt-2 space-y-1">
                    @foreach(session('import_errors') as $err)
                        <li class="text-xs text-orange-600">• {{ $err }}</li>
                    @endforeach
                </ul>
            </details>
        @endif
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
        <p class="text-red-700 text-sm">{{ $errors->first() }}</p>
    </div>
    @endif

    {{-- Template Download --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-blue-800">Download Template Excel</p>
            <p class="text-xs text-blue-600 mt-0.5">Gunakan template ini agar format kolom sesuai</p>
        </div>
        <a href="{{ route('admin.users.import.template') }}"
            class="flex items-center gap-2 bg-blue-600 text-white text-xs font-semibold px-4 py-2 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download .xlsx
        </a>
    </div>

    {{-- Panduan kolom --}}
    <div class="bg-white rounded-xl border border-gray-100 p-4 mb-4">
        <p class="text-xs font-semibold text-gray-700 mb-2">Panduan Kolom Excel</p>
        <div class="overflow-x-auto">
            <table class="text-xs w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left p-2 font-semibold text-gray-600">Kolom</th>
                        <th class="text-left p-2 font-semibold text-gray-600">Keterangan</th>
                        <th class="text-left p-2 font-semibold text-gray-600">Wajib</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach([
                        ['nama',      'Nama lengkap',                         true],
                        ['email',     'Email unik (dipakai login)',            true],
                        ['role',      'siswa / guru / admin / pengelola', true],
                        ['nis',       'NIS siswa (jadi password default)',     false],
                        ['nip',       'NIP guru (jadi password default)',      false],
                        ['kelas',     'Nama kelas persis di DB (cth: X IPA 1)', false],
                        ['no_hp',     'Nomor HP siswa/guru',                  false],
                        ['nama_ortu', 'Nama orang tua siswa',                 false],
                        ['hp_ortu',   'HP orang tua',                        false],
                        ['tgl_lahir', 'Format YYYY-MM-DD',                   false],
                        ['alamat',    'Alamat lengkap',                       false],
                        ['mapel',     'Mata pelajaran (khusus guru)',          false],
                    ] as [$col, $desc, $required])
                    <tr>
                        <td class="p-2 font-mono text-blue-700">{{ $col }}</td>
                        <td class="p-2 text-gray-600">{{ $desc }}</td>
                        <td class="p-2">
                            @if($required)
                                <span class="text-red-500 font-semibold">Wajib</span>
                            @else
                                <span class="text-gray-400">Opsional</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 mt-2">* Password default: NIS (siswa), NIP (guru), atau nama tanpa spasi jika keduanya kosong.</p>
    </div>

    {{-- Upload Form --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <p class="text-sm font-semibold text-gray-700 mb-3">Upload File</p>
        <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center mb-4 hover:border-blue-400 transition-colors">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <label class="cursor-pointer">
                    <span class="text-sm text-blue-600 font-medium">Pilih file Excel</span>
                    <span class="text-sm text-gray-500"> atau drag & drop di sini</span>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv"
                        class="hidden" id="file-input"
                        onchange="document.getElementById('file-name').textContent = this.files[0]?.name || ''">
                </label>
                <p id="file-name" class="text-xs text-gray-500 mt-1"></p>
                <p class="text-xs text-gray-400 mt-1">Format: .xlsx, .xls, .csv — Maks 5MB</p>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl text-sm hover:bg-blue-700 transition-colors">
                Import Sekarang
            </button>
        </form>
    </div>

</div>
</body>
</html>
