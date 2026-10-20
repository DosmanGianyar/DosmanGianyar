@extends('layouts.guru')
@section('title', 'Tujuan Pembelajaran')
@section('page-title', 'Tujuan Pembelajaran (TP)')

@section('content')
<div class="space-y-4 pb-12">

    {{-- ─── 1. Header Banner & Quick Action ────────────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-sm font-bold shadow-2xs">
                    🎯
                </span>
                <h2 class="text-base sm:text-lg font-black text-slate-900">Tujuan Pembelajaran (TP)</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                Kelola tujuan pembelajaran yang Anda buat. TP dari rekan guru pada mata pelajaran yang sama juga otomatis dapat Anda gunakan di jurnal mengajar.
            </p>
        </div>
        <button type="button" onclick="document.getElementById('modal-add-tp').classList.remove('hidden')"
            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition-all shadow-xs shadow-blue-500/20 active:scale-95 shrink-0 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>+ Tambah TP</span>
        </button>
    </div>

    {{-- ─── 2. Stats Summary ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl border border-blue-100 shadow-2xs p-4 flex items-center justify-between">
            <div>
                <p class="text-2xl sm:text-3xl font-black text-blue-600">{{ $tps->where('teacher_id', auth()->id())->count() }}</p>
                <p class="text-xs font-semibold text-slate-500 mt-0.5">TP Saya</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                📝
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-100 shadow-2xs p-4 flex items-center justify-between">
            <div>
                <p class="text-2xl sm:text-3xl font-black text-emerald-600">{{ $tps->where('teacher_id', '!=', auth()->id())->count() }}</p>
                <p class="text-xs font-semibold text-slate-500 mt-0.5">Dibagikan Guru Lain</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                🤝
            </div>
        </div>
    </div>

    {{-- ─── 3. TP List Grouped by Subject ───────────────────────────────── --}}
    @php $grouped = $tps->groupBy(fn($tp) => $tp->subject?->name ?? 'Tanpa Mata Pelajaran'); @endphp

    @if($tps->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-10 text-center">
            <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl mx-auto mb-3 shadow-2xs">
                🎯
            </div>
            <h3 class="text-base font-bold text-slate-800">Belum Ada Tujuan Pembelajaran</h3>
            <p class="text-slate-400 text-xs sm:text-sm mt-1 max-w-sm mx-auto">
                Mulai susun Tujuan Pembelajaran untuk mempermudah pengisian Jurnal Mengajar dan pelaporan nilai.
            </p>
            <button type="button" onclick="document.getElementById('modal-add-tp').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 mt-4 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition-all shadow-xs shadow-blue-500/20 active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Buat TP Pertama</span>
            </button>
        </div>
    @else
        @foreach($grouped as $subjectName => $items)
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-4 sm:px-5 py-3 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <p class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wide">{{ $subjectName }}</p>
                </div>
                <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-100 shadow-2xs">
                    {{ $items->count() }} TP
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($items as $tp)
                <div class="flex items-start justify-between gap-3 px-4 sm:px-5 py-3.5 hover:bg-slate-50/60 transition-colors">
                    <div class="flex-1 min-w-0 space-y-1">
                        <div class="flex items-start gap-2 flex-wrap">
                            @if($tp->code)
                            <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-black bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs shrink-0">
                                {{ $tp->code }}
                            </span>
                            @endif
                            <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 shadow-2xs shrink-0">
                                {{ $tp->gradeLabel() }}
                            </span>
                            <span class="text-xs sm:text-sm text-slate-800 leading-relaxed font-medium">
                                {{ $tp->description }}
                            </span>
                        </div>
                        @if($tp->teacher_id !== auth()->id())
                        <p class="text-[11px] text-slate-400 font-medium">Oleh rekan guru: <span class="text-slate-600 font-semibold">{{ $tp->teacher?->name }}</span></p>
                        @endif
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0 pt-0.5">
                        @if($tp->is_active)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">Aktif</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-400 border border-slate-200 shadow-2xs">Nonaktif</span>
                        @endif

                        @if($tp->teacher_id === auth()->id())
                        {{-- Toggle Status Aktif --}}
                        <form method="POST" action="{{ route('guru.tp.toggle', $tp) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-400 hover:text-blue-600 transition-all shadow-2xs cursor-pointer"
                                title="{{ $tp->is_active ? 'Nonaktifkan TP' : 'Aktifkan TP' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                </svg>
                            </button>
                        </form>

                        {{-- Edit --}}
                        <button type="button"
                            onclick="openEditTp({{ $tp->id }}, {{ json_encode($tp->code ?? '') }}, {{ json_encode($tp->description) }}, {{ $tp->subject_id ?? 'null' }}, {{ json_encode($tp->grade_level ?? '') }})"
                            class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-slate-200 hover:bg-blue-50 text-slate-400 hover:text-blue-600 hover:border-blue-300 transition-all shadow-2xs cursor-pointer"
                            title="Edit TP">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>

                        {{-- Hapus --}}
                        <form method="POST" action="{{ route('guru.tp.destroy', $tp) }}"
                            onsubmit="return confirm('Hapus TP ini? Jurnal yang menggunakan TP ini tidak ikut terhapus.')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Hapus TP"
                                class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-slate-200 hover:bg-rose-50 text-slate-400 hover:text-rose-600 hover:border-rose-300 transition-all shadow-2xs cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    @endif

</div>

{{-- ─── 4. Modal Tambah TP ───────────────────────────────────────────── --}}
<div id="modal-add-tp" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-white/20">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/80">
            <h3 class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
                <span>🎯</span>
                <span>Tambah Tujuan Pembelajaran</span>
            </h3>
            <button type="button" onclick="document.getElementById('modal-add-tp').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-200/80 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer">
                ✕
            </button>
        </div>
        <form method="POST" action="{{ route('guru.tp.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Mata Pelajaran</label>
                <select name="subject_id"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Tidak spesifik —</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tingkatan Kelas <span class="font-normal text-slate-400">(opsional)</span></label>
                <select name="grade_level"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Semua Tingkatan —</option>
                    <option value="10">Kelas 10 (X)</option>
                    <option value="11">Kelas 11 (XI)</option>
                    <option value="12">Kelas 12 (XII)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Kode TP <span class="font-normal text-slate-400">(opsional, misal: TP1.1)</span>
                </label>
                <input type="text" name="code" maxlength="30" placeholder="TP1.1"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Tujuan Pembelajaran *</label>
                <textarea name="description" rows="3" maxlength="500" required
                    placeholder="Siswa mampu menjelaskan dan menganalisis..."
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>
            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="document.getElementById('modal-add-tp').classList.add('hidden')"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer">
                    Simpan TP
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ─── 5. Modal Edit TP ────────────────────────────────────────────── --}}
<div id="modal-edit-tp" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-white/20">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/80">
            <h3 class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
                <span>✏️</span>
                <span>Edit Tujuan Pembelajaran</span>
            </h3>
            <button type="button" onclick="document.getElementById('modal-edit-tp').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-200/80 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer">
                ✕
            </button>
        </div>
        <form id="edit-tp-form" method="POST" class="p-5 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Mata Pelajaran</label>
                <select id="edit-tp-subject" name="subject_id"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Tidak spesifik —</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tingkatan Kelas <span class="font-normal text-slate-400">(opsional)</span></label>
                <select id="edit-tp-grade" name="grade_level"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Semua Tingkatan —</option>
                    <option value="10">Kelas 10 (X)</option>
                    <option value="11">Kelas 11 (XI)</option>
                    <option value="12">Kelas 12 (XII)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode TP <span class="font-normal text-slate-400">(opsional)</span></label>
                <input id="edit-tp-code" type="text" name="code" maxlength="30"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Tujuan Pembelajaran *</label>
                <textarea id="edit-tp-desc" name="description" rows="3" maxlength="500" required
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>
            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-tp').classList.add('hidden')"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer">
                    Perbarui TP
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditTp(id, code, description, subjectId, gradeLevel) {
    document.getElementById('edit-tp-form').action = '/guru/tp/' + id;
    document.getElementById('edit-tp-code').value = code || '';
    document.getElementById('edit-tp-desc').value = description || '';
    document.getElementById('edit-tp-subject').value = subjectId || '';
    document.getElementById('edit-tp-grade').value = gradeLevel || '';
    document.getElementById('modal-edit-tp').classList.remove('hidden');
}
</script>
@endsection
