@extends('layouts.app')
@section('title', 'Upload Extrakurikuler')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Upload Data Ekstrakurikuler</h1>
            <p class="text-sm text-gray-500 mt-1">Unggah file CSV (ekstra.csv) untuk mengimpor data Ekstra, Pembina (Guru), dan Ketua/Wakil Ketua (Siswa).</p>
        </div>
        <a href="{{ route('admin.extracurriculars.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            &larr; Kembali
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
                    <p class="text-xs text-blue-700">Anda dapat langsung melakukan pratinjau dan pencocokan dari file default di server.</p>
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
@endsection
