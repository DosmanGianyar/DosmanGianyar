<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Ekstrakurikuler — SIMS | SMA Negeri 1 Gianyar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans min-h-screen p-6">

<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/admin/extracurriculars" class="w-9 h-9 bg-white rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Upload Data Ekstrakurikuler</h1>
                <p class="text-sm text-gray-500 mt-0.5">Unggah file CSV (`ekstra.csv`) untuk mengimpor data Ekstra, Pembina (Guru), dan Ketua/Wakil (Siswa).</p>
            </div>
        </div>
        <a href="/admin/extracurriculars" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-300 transition">
            &larr; Kembali ke Admin
        </a>
    </div>

    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-medium">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 space-y-6">

        @if($defaultFileExists)
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📄</span>
                <div>
                    <p class="font-bold text-blue-900 text-sm">File Default Ditemukan (`public/ekstra.csv`)</p>
                    <p class="text-xs text-blue-700 mt-0.5">Anda dapat langsung melakukan pratinjau dan pencocokan otomatis dari file di server.</p>
                </div>
            </div>
            <form action="{{ route('admin.extracurriculars.preview') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                    Gunakan Default `ekstra.csv` &rarr;
                </button>
            </form>
        </div>
        @endif

        <form action="{{ route('admin.extracurriculars.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Atau Unggah File CSV Baru</label>
                <input type="file" name="file" accept=".csv,.txt" required
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-200 rounded-2xl">
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow-md flex items-center gap-2">
                    <span>Pratinjau & Sesuai &rarr;</span>
                </button>
            </div>
        </form>

    </div>

</div>

</body>
</html>
