@extends('layouts.piket')
@section('title', 'Input Dispensasi Siswa')
@section('page-title', 'Input Dispensasi Siswa (Oleh Guru Piket)')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
     x-data="{
         search: '',
         selectedClass: ''
     }">

    {{-- Form Input Dispensasi --}}
    <form method="POST" action="{{ route('piket.dispensation.store') }}" enctype="multipart/form-data"
          class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-5">
        @csrf

        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Formulir Dispensasi Kegiatan Siswa</h2>
            <p class="text-sm text-slate-500 mt-1">
                Dispensasi akan langsung disetujui atas nama <strong>{{ $teacher->name }}</strong> dan otomatis mencatat status presensi <em>Dispensasi</em> pada tanggal kegiatan.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Kegiatan / Alasan Dispensasi *</label>
                <input type="text" name="activity_name" value="{{ old('activity_name') }}" required
                       placeholder="Contoh: Lomba O2SN Kabupaten / Tugas OSIS"
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-base">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal *</label>
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-base">
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">Lampiran Surat Tugas / Bukti (Opsional, PDF/JPG/PNG maks 2MB)</label>
            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png"
                   class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-100 file:font-bold">
        </div>

        <div class="border-t border-slate-200 pt-4">
            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Siswa Peserta Dispensasi *</label>
            <div class="flex flex-col sm:flex-row gap-2.5 mb-3">
                <input type="text" x-model="search" placeholder="Cari nama siswa..."
                       class="flex-1 rounded-xl border border-slate-300 px-3.5 py-2.5 text-base">
                <select x-model="selectedClass" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-base">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="max-h-80 overflow-y-auto border border-slate-200 rounded-xl p-3 space-y-2.5 bg-slate-50">
                @foreach($classes as $class)
                    @if($class->students->isNotEmpty())
                        <div x-show="!selectedClass || selectedClass == '{{ $class->id }}'" class="space-y-1.5">
                            <p class="text-xs font-extrabold text-slate-500 uppercase px-2 pt-1">{{ $class->name }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($class->students as $stu)
                                    @php $stuKey = strtolower($stu->name . ' ' . ($stu->nisn ?? '') . ' ' . $class->name); @endphp
                                    <label x-show="!search || {{ json_encode($stuKey) }}.includes(search.toLowerCase())"
                                           class="flex items-center gap-2.5 bg-white px-3 py-2 rounded-lg border border-slate-200 cursor-pointer hover:border-blue-400">
                                        <input type="checkbox" name="student_ids[]" value="{{ $stu->id }}"
                                               class="w-4 h-4 rounded border-slate-300 text-blue-600">
                                        <span class="text-sm font-medium text-slate-800 truncate">{{ $stu->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <button type="submit"
            class="w-full sm:w-auto px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-base font-bold shadow-xs">
            Simpan Dispensasi ({{ $teacher->name }})
        </button>
    </form>

    {{-- Riwayat Dispensasi Terbaru --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
        <h3 class="text-lg font-extrabold text-slate-900 mb-3.5">Dispensasi Terbaru</h3>
        <div class="space-y-3 max-h-[540px] overflow-y-auto">
            @forelse($recentDispensations as $disp)
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                    <p class="text-base font-bold text-slate-900">{{ $disp->activity_name }}</p>
                    <p class="text-sm text-slate-500">
                        {{ $disp->date?->locale('id')->translatedFormat('d M Y') }} • {{ $disp->students->count() }} siswa
                    </p>
                    <p class="text-sm text-slate-600">
                        Oleh: <strong>{{ $disp->approver?->name ?? '—' }}</strong>
                    </p>
                    @if($disp->file)
                        <a href="{{ asset('storage/' . $disp->file) }}" target="_blank" class="text-sm text-blue-600 hover:underline font-semibold inline-block mt-1">
                            📎 Surat Dispensasi ↗
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400 text-center py-6">Belum ada data dispensasi.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
