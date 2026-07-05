@extends('layouts.guru')
@section('title', 'Dashboard BK')
@section('page-title', 'Bimbingan Konseling')

@section('content')
<div class="space-y-4">

{{-- ─── Header + Filter ────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <div class="flex flex-col sm:flex-row sm:items-end gap-3">
        <div class="flex-1">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Filter Kelas</p>
            <form method="GET" action="{{ route('guru.bk.index') }}">
                <select name="class_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        @if($isBk)
        <button onclick="document.getElementById('modal-bk').classList.remove('hidden')"
            class="flex items-center gap-1.5 px-4 py-2.5 bg-purple-600 text-white text-sm font-semibold rounded-xl hover:bg-purple-700 transition-colors sm:w-auto shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Catatan BK
        </button>
        @endif
    </div>
</div>

{{-- ─── Stats ───────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
        <p class="text-2xl font-extrabold text-gray-800">{{ $stats['total_students'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Total Siswa</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-4 text-center">
        <p class="text-2xl font-extrabold text-purple-600">{{ $stats['flagged'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Perlu Perhatian</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-orange-100 p-4 text-center">
        <p class="text-2xl font-extrabold text-orange-500">{{ $stats['auto_logs_today'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Alert Hari Ini</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 text-center">
        <p class="text-2xl font-extrabold text-blue-600">{{ $stats['manual_logs'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Catatan Manual</p>
    </div>
</div>

{{-- ─── Siswa Bermasalah ────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-50 flex items-center justify-between">
        <p class="text-sm font-bold text-gray-700">Siswa Perlu Perhatian BK</p>
        <span class="text-xs text-gray-400">{{ $students->count() }} siswa</span>
    </div>

    @if($students->isEmpty())
    <div class="py-10 text-center">
        <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-sm text-gray-500 font-medium">Tidak ada siswa bermasalah</p>
        <p class="text-xs text-gray-400 mt-1">Semua siswa di kelas ini aman</p>
    </div>
    @else
    <div class="divide-y divide-gray-50">
        @foreach($students as $student)
        @php $totalPoint = (int) $student->conduct_logs_sum_point; @endphp
        <div class="flex items-center gap-3 px-4 py-3">
            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
                @if($student->photo)
                    <img src="{{ Storage::url($student->photo) }}" class="w-9 h-9 rounded-xl object-cover">
                @else
                    <span class="text-sm font-bold text-purple-600">{{ strtoupper(substr($student->name,0,1)) }}</span>
                @endif
            </div>
            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $student->name }}</p>
                <p class="text-xs text-gray-400">{{ $student->nis }} · {{ $student->bk_logs_count }} catatan BK</p>
            </div>
            {{-- Poin --}}
            <div class="text-right shrink-0">
                <p class="text-sm font-bold {{ $totalPoint < 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $totalPoint > 0 ? '+' : '' }}{{ $totalPoint }}
                </p>
                <p class="text-[10px] text-gray-400">total poin</p>
            </div>
            {{-- Detail link --}}
            <a href="{{ route('guru.conduct.student', $student) }}"
                class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center hover:bg-gray-100 transition shrink-0">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ─── Riwayat Catatan BK Terbaru ─────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-50">
        <p class="text-sm font-bold text-gray-700">Riwayat Catatan BK</p>
    </div>

    @if($recentLogs->isEmpty())
    <div class="py-8 text-center">
        <p class="text-sm text-gray-400">Belum ada catatan BK untuk kelas ini</p>
    </div>
    @else
    <div class="divide-y divide-gray-50">
        @foreach($recentLogs as $log)
        <div class="px-4 py-3 flex items-start gap-3">
            {{-- Type badge --}}
            <div class="shrink-0 mt-0.5">
                @if($log->is_auto)
                    <span class="text-[10px] font-bold bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full">Auto</span>
                @else
                    <span class="text-[10px] font-bold bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full">Manual</span>
                @endif
            </div>
            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800">{{ $log->student->name }}</p>
                @if($log->coaching_note)
                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $log->coaching_note }}</p>
                @endif
                <p class="text-[10px] text-gray-400 mt-1">
                    {{ $log->date->isoFormat('D MMM Y') }}
                    · Poin saat itu: <span class="{{ $log->point_at_time < 0 ? 'text-red-500' : 'text-green-600' }} font-semibold">{{ $log->point_at_time }}</span>
                </p>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

</div>

{{-- ─── Modal: Tambah Catatan BK (guru BK only) ────────────────────────── --}}
@if($isBk)
<div id="modal-bk" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4 pb-4 sm:pb-0">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-base font-bold text-gray-800">Tambah Catatan BK</p>
            <button onclick="document.getElementById('modal-bk').classList.add('hidden')"
                class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('guru.bk.log.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Siswa</label>
                <select name="student_id" required
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                    <option value="">— Pilih Siswa —</option>
                    @foreach($allStudents as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->nis }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ today()->toDateString() }}" required
                    max="{{ today()->toDateString() }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan Pembinaan</label>
                <textarea name="coaching_note" required rows="4" maxlength="1000"
                    placeholder="Uraikan hasil pembinaan atau konseling..."
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-bk').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@if(session('success'))
<div id="toast-success"
    class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-green-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-lg z-50 transition-opacity">
    {{ session('success') }}
</div>
<script>
    setTimeout(function(){ document.getElementById('toast-success').style.opacity='0'; }, 2500);
</script>
@endif

@endsection
