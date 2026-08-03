@extends('layouts.app')
@section('title', 'Manajemen Ekstrakurikuler')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-16">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Data Ekstrakurikuler</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data Ekstra, Pembina (Guru), dan Pengurus (Ketua & Wakil Ketua Siswa).</p>
        </div>
        <a href="{{ route('admin.extracurriculars.import') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition shadow-md flex items-center gap-2">
            <span>📥 Import Data CSV (`ekstra.csv`)</span>
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 font-bold text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-3.5 w-10 text-center">No</th>
                        <th class="p-3.5">Nama Ekstrakurikuler</th>
                        <th class="p-3.5">Pembina (Guru)</th>
                        <th class="p-3.5">Ketua & Wakil (Siswa)</th>
                        <th class="p-3.5">Contact Person (No HP - Admin)</th>
                        <th class="p-3.5 w-20 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($extracurriculars as $index => $extra)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3.5 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                        <td class="p-3.5 font-bold text-gray-900">{{ $extra->name }}</td>
                        <td class="p-3.5">
                            @forelse($extra->teachers as $teacher)
                                <span class="inline-block px-2.5 py-1 bg-indigo-50 text-indigo-700 font-semibold rounded-lg text-xs mr-1 mb-1 border border-indigo-100">
                                    👨‍🏫 {{ $teacher->name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 italic">Belum ada pembina</span>
                            @endforelse
                        </td>
                        <td class="p-3.5 space-y-1">
                            @php
                                $ketua = $extra->students->firstWhere('pivot.role', 'ketua');
                                $wakil = $extra->students->firstWhere('pivot.role', 'wakil_ketua');
                            @endphp

                            @if($ketua)
                                <div class="text-xs text-emerald-800 font-semibold flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-[10px] uppercase font-bold text-emerald-700">Ketua</span>
                                    <span>{{ $ketua->name }}</span>
                                </div>
                            @endif

                            @if($wakil)
                                <div class="text-xs text-sky-800 font-semibold flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded bg-sky-100 text-[10px] uppercase font-bold text-sky-700">Wakil</span>
                                    <span>{{ $wakil->name }}</span>
                                </div>
                            @endif

                            @if(!$ketua && !$wakil)
                                <span class="text-xs text-gray-400 italic">Belum ada pengurus</span>
                            @endif
                        </td>
                        <td class="p-3.5 font-mono text-xs text-gray-600">
                            {{ $extra->contact_person ?: '—' }}
                        </td>
                        <td class="p-3.5 text-center">
                            <form action="{{ route('admin.extracurriculars.destroy', $extra->id) }}" method="POST" onsubmit="return confirm('Hapus ekstra ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400 text-sm">
                            Belum ada data ekstrakurikuler. Klik <strong>Import Data CSV (`ekstra.csv`)</strong> untuk mengunggah.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
