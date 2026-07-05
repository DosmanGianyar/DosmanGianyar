@extends('layouts.guru')
@section('title', 'Tujuan Pembelajaran')
@section('page-title', 'Tujuan Pembelajaran (TP)')

@section('content')
<div class="space-y-4">

    {{-- ─── Header --}}
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Kelola TP yang Anda buat. TP dari guru lain dengan mapel sama juga tampil.</p>
        <button onclick="document.getElementById('modal-add-tp').classList.remove('hidden')"
            class="flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah TP
        </button>
    </div>

    {{-- ─── Stats --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
            <p class="text-2xl font-extrabold text-blue-600">{{ $tps->where('teacher_id', auth()->id())->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">TP Saya</p>
        </div>
        <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-4">
            <p class="text-2xl font-extrabold text-green-600">{{ $tps->where('teacher_id', '!=', auth()->id())->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Dibagikan Guru Lain</p>
        </div>
    </div>

    {{-- ─── TP List --}}
    @php $grouped = $tps->groupBy(fn($tp) => $tp->subject?->name ?? 'Tanpa Mata Pelajaran'); @endphp

    @if($tps->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center text-gray-400 text-sm">
            Belum ada Tujuan Pembelajaran. Klik "Tambah TP" untuk mulai.
        </div>
    @else
        @foreach($grouped as $subjectName => $items)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ $subjectName }}</p>
                <span class="text-xs text-gray-400">{{ $items->count() }} TP</span>
            </div>
            @foreach($items as $tp)
            <div class="flex items-start gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                <div class="flex-1 min-w-0 pt-0.5">
                    <div class="flex items-start gap-2 flex-wrap">
                        @if($tp->code)
                        <span class="inline-block px-1.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 shrink-0">{{ $tp->code }}</span>
                        @endif
                        <span class="text-sm text-gray-800 leading-relaxed">{{ $tp->description }}</span>
                    </div>
                    @if($tp->teacher_id !== auth()->id())
                    <p class="text-xs text-gray-400 mt-1">oleh {{ $tp->teacher?->name }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    @if($tp->is_active)
                    <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-green-50 text-green-700">Aktif</span>
                    @else
                    <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-400">Nonaktif</span>
                    @endif

                    @if($tp->teacher_id === auth()->id())
                    {{-- Toggle --}}
                    <form method="POST" action="{{ route('guru.tp.toggle', $tp) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-blue-500 transition-colors"
                            title="{{ $tp->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                            </svg>
                        </button>
                    </form>
                    {{-- Edit --}}
                    <button
                        onclick="openEditTp({{ $tp->id }}, {{ json_encode($tp->code ?? '') }}, {{ json_encode($tp->description) }}, {{ $tp->subject_id ?? 'null' }})"
                        class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-blue-500 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    {{-- Delete --}}
                    <form method="POST" action="{{ route('guru.tp.destroy', $tp) }}"
                        onsubmit="return confirm('Hapus TP ini? Jurnal yang menggunakan TP ini tidak ikut terhapus.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
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
        @endforeach
    @endif

</div>

{{-- ─── Modal Tambah TP ──────────────────────────────────────────────────────── --}}
<div id="modal-add-tp" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Tambah Tujuan Pembelajaran</h3>
            <button onclick="document.getElementById('modal-add-tp').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('guru.tp.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mata Pelajaran</label>
                <select name="subject_id"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Tidak spesifik —</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Kode TP <span class="font-normal text-gray-400">(opsional, mis: TP1.1)</span>
                </label>
                <input type="text" name="code" maxlength="30" placeholder="TP1.1"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi *</label>
                <textarea name="description" rows="3" maxlength="500" required
                    placeholder="Siswa mampu menjelaskan..."
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="document.getElementById('modal-add-tp').classList.add('hidden')"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Modal Edit TP ────────────────────────────────────────────────────────── --}}
<div id="modal-edit-tp" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Edit Tujuan Pembelajaran</h3>
            <button onclick="document.getElementById('modal-edit-tp').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="edit-tp-form" method="POST" class="p-5 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mata Pelajaran</label>
                <select id="edit-tp-subject" name="subject_id"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Tidak spesifik —</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kode TP <span class="font-normal text-gray-400">(opsional)</span></label>
                <input id="edit-tp-code" type="text" name="code" maxlength="30"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi *</label>
                <textarea id="edit-tp-desc" name="description" rows="3" maxlength="500" required
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="document.getElementById('modal-edit-tp').classList.add('hidden')"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditTp(id, code, description, subjectId) {
    document.getElementById('edit-tp-form').action = '/guru/tp/' + id;
    document.getElementById('edit-tp-code').value = code || '';
    document.getElementById('edit-tp-desc').value = description || '';
    document.getElementById('edit-tp-subject').value = subjectId || '';
    document.getElementById('modal-edit-tp').classList.remove('hidden');
}
</script>
@endsection
