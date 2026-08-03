<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratinjau & Pencocokan Ekstrakurikuler — SIMS | SMA Negeri 1 Gianyar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans min-h-screen p-6">

<div class="max-w-7xl mx-auto space-y-6 pb-16">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.extracurriculars.import') }}" class="w-9 h-9 bg-white rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Pratinjau & Pencocokan Ekstrakurikuler</h1>
                <p class="text-sm text-gray-500 mt-0.5">Periksa hasil pencocokan Guru Pembina dan Siswa (Ketua & Wakil). Sesuaikan pilihan dropdown jika ada yang belum tepat sebelum disimpan.</p>
            </div>
        </div>
        <a href="{{ route('admin.extracurriculars.import') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-300 transition">
            &larr; Batal / Kembali
        </a>
    </div>

    <form action="{{ route('admin.extracurriculars.store') }}" method="POST">
        @csrf

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Total {{ count($previewData) }} Ekstrakurikuler Ditemukan</span>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition shadow-md flex items-center gap-2">
                    <span>💾 Simpan All Data Ekstrakurikuler</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-700 font-bold text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-3.5 w-10 text-center">No</th>
                            <th class="p-3.5 w-56">Nama Ekstra</th>
                            <th class="p-3.5 w-72">Guru Pembina</th>
                            <th class="p-3.5 w-64">Ketua Ekstra (Siswa 1)</th>
                            <th class="p-3.5 w-64">Wakil Ketua (Siswa 2)</th>
                            <th class="p-3.5 w-48">Contact Person (Admin Only)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($previewData as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                            
                            {{-- Nama Ekstra --}}
                            <td class="p-3.5 align-top">
                                <input type="text" name="extracurriculars[{{ $index }}][name]" value="{{ $item['name'] }}" required
                                    class="w-full text-sm font-bold text-gray-900 bg-white border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            </td>

                            {{-- Guru Pembina --}}
                            <td class="p-3.5 align-top space-y-2">
                                @forelse($item['pembinas'] as $pIdx => $pembina)
                                    <div>
                                        <p class="text-[11px] text-gray-400 mb-1">CSV: <span class="italic text-gray-600">{{ $pembina['raw_name'] }}</span></p>
                                        <select name="extracurriculars[{{ $index }}][teacher_ids][]"
                                            class="w-full text-xs bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500 font-medium">
                                            <option value="">-- Pilih Guru Pembina --</option>
                                            @foreach($allTeachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ $pembina['teacher_id'] == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @empty
                                    <span class="text-xs text-gray-400 italic">Tidak ada pembina</span>
                                @endforelse
                            </td>

                            {{-- Ketua Ekstra --}}
                            <td class="p-3.5 align-top">
                                @if($item['ketua'])
                                    <p class="text-[11px] text-gray-400 mb-1">CSV: <span class="italic text-gray-600">{{ $item['ketua']['raw_name'] }}</span></p>
                                    <select name="extracurriculars[{{ $index }}][ketua_id]"
                                        class="w-full text-xs bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 focus:ring-2 focus:ring-emerald-500 font-medium">
                                        <option value="">-- Pilih Ketua Ekstra --</option>
                                        @foreach($allStudents as $student)
                                            <option value="{{ $student->id }}" {{ $item['ketua']['student_id'] == $student->id ? 'selected' : '' }}>
                                                {{ $student->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak ada di CSV</span>
                                @endif
                            </td>

                            {{-- Wakil Ketua Ekstra --}}
                            <td class="p-3.5 align-top">
                                @if($item['wakil_ketua'])
                                    <p class="text-[11px] text-gray-400 mb-1">CSV: <span class="italic text-gray-600">{{ $item['wakil_ketua']['raw_name'] }}</span></p>
                                    <select name="extracurriculars[{{ $index }}][wakil_ketua_id]"
                                        class="w-full text-xs bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 focus:ring-2 focus:ring-sky-500 font-medium">
                                        <option value="">-- Pilih Wakil Ketua --</option>
                                        @foreach($allStudents as $student)
                                            <option value="{{ $student->id }}" {{ $item['wakil_ketua']['student_id'] == $student->id ? 'selected' : '' }}>
                                                {{ $student->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak ada di CSV</span>
                                @endif
                            </td>

                            {{-- Contact Person --}}
                            <td class="p-3.5 align-top">
                                <input type="text" name="extracurriculars[{{ $index }}][contact_person]" value="{{ $item['contact_person'] }}" placeholder="No HP Kontak"
                                    class="w-full text-xs font-mono text-gray-700 bg-white border border-gray-200 rounded-xl px-2.5 py-2">
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('admin.extracurriculars.import') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                    &larr; Kembali / Unggah Ulang
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition shadow-md flex items-center gap-2">
                    <span>💾 Simpan All Data Ekstrakurikuler</span>
                </button>
            </div>
        </div>

    </form>

</div>

</body>
</html>
