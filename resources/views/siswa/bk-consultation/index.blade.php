@extends('layouts.siswa')
@section('title', 'Bimbingan BK')
@section('page-title', 'Bimbingan BK')

@section('content')
<div class="space-y-4">

    {{-- Active banner --}}
    @if($active)
    @php
        $activeColors = [
            'pending'   => ['from-amber-500 to-orange-600', 'text-amber-100', 'bg-amber-400/30'],
            'scheduled' => ['from-blue-600 to-indigo-700',  'text-blue-100',  'bg-blue-400/30'],
        ];
        [$grad, $sub, $iconBg] = $activeColors[$active->status] ?? ['from-gray-500 to-gray-600', 'text-gray-100', 'bg-gray-400/30'];
    @endphp
    <div class="bg-gradient-to-br {{ $grad }} rounded-2xl p-4 text-white">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full {{ $iconBg }} flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs {{ $sub }} mb-0.5">Pengajuan Aktif · {{ $active->statusLabel() }}</p>
                <p class="font-semibold text-sm leading-snug">{{ $active->topic }}</p>
                <p class="text-xs {{ $sub }} mt-0.5">Guru BK: {{ $active->teacher?->name }}</p>
                @if($active->isScheduled() && $active->scheduled_date)
                    <p class="text-xs {{ $sub }} mt-1">Jadwal: {{ $active->scheduled_date->isoFormat('D MMMM Y') }}</p>
                @endif
            </div>
        </div>

        @if($active->isPending())
        <div class="mt-3 flex gap-2">
            {{-- Change teacher --}}
            <button onclick="document.getElementById('modal-change').classList.remove('hidden')"
                class="flex-1 text-xs font-semibold py-2 rounded-xl bg-white/20 text-white text-center">
                Ganti Guru BK
            </button>
            {{-- Cancel --}}
            <form method="POST" action="{{ route('siswa.bk-consultation.cancel', $active) }}"
                data-confirm="Batalkan pengajuan bimbingan BK ini?">
                @csrf @method('PATCH')
                <button type="submit" class="text-xs font-semibold py-2 px-3 rounded-xl bg-white/10 text-white">
                    Batalkan
                </button>
            </form>
        </div>
        @endif
    </div>
    @endif

    {{-- Form Ajukan --}}
    @if(!$active)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Ajukan Bimbingan BK</p>
        <form method="POST" action="{{ route('siswa.bk-consultation.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Guru BK <span class="text-red-500">*</span></label>
                <select name="teacher_id" required
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white
                        @error('teacher_id') border-red-400 @enderror">
                    <option value="">— Pilih Guru BK —</option>
                    @foreach($bkTeachers as $t)
                        <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
                @error('teacher_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Topik / Permasalahan <span class="text-red-500">*</span></label>
                <input type="text" name="topic" value="{{ old('topic') }}" required maxlength="200"
                    placeholder="Contoh: Masalah pertemanan, motivasi belajar, dll."
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                        @error('topic') border-red-400 @enderror">
                @error('topic') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Keterangan Tambahan <span class="text-gray-400 font-normal">(opsional)</span></label>
                <textarea name="student_note" rows="3" maxlength="1000"
                    placeholder="Ceritakan lebih detail tentang permasalahan Anda…"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none">{{ old('student_note') }}</textarea>
            </div>
            <button type="submit"
                class="w-full bg-purple-600 text-white font-semibold py-3 rounded-xl text-sm">
                Kirim Pengajuan
            </button>
        </form>
    </div>
    @endif

    {{-- Riwayat --}}
    <div>
        <p class="text-sm font-semibold text-gray-700 mb-3">Riwayat Bimbingan BK</p>

        @php
            $historyColors = [
                'pending'   => ['bg-amber-100 text-amber-700',  'border-amber-100'],
                'scheduled' => ['bg-blue-100 text-blue-700',    'border-blue-100'],
                'completed' => ['bg-green-100 text-green-700',  'border-green-100'],
                'cancelled' => ['bg-gray-100 text-gray-500',    'border-gray-100'],
            ];
            $history = $consultations->filter(fn($c) => !$active || $c->id !== $active->id)->values();
        @endphp

        @forelse($history as $c)
        @php
            [$badge, $border] = $historyColors[$c->status] ?? ['bg-gray-100 text-gray-500', 'border-gray-100'];
        @endphp
        <div class="bg-white rounded-2xl border {{ $border }} shadow-sm p-4 mb-3">
            <div class="flex items-start justify-between gap-2 mb-1">
                <p class="text-sm font-semibold text-gray-800">{{ $c->topic }}</p>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0 {{ $badge }}">
                    {{ $c->statusLabel() }}
                </span>
            </div>

            <p class="text-xs text-gray-400 mb-2">
                {{ $c->teacher?->name }} · {{ $c->created_at->isoFormat('D MMMM Y') }}
            </p>

            @if($c->student_note)
                <p class="text-xs text-gray-600 mb-2">{{ $c->student_note }}</p>
            @endif

            @if($c->isScheduled() && $c->scheduled_date)
            <div class="bg-blue-50 rounded-xl px-3 py-2 text-xs text-blue-700 mb-2">
                Dijadwalkan: <strong>{{ $c->scheduled_date->isoFormat('D MMMM Y') }}</strong>
            </div>
            @endif

            @if($c->isCompleted())
            <div class="bg-green-50 rounded-xl px-3 py-2 space-y-1 text-xs mb-2">
                @if($c->conducted_date)
                    <p class="text-gray-500">Dilaksanakan: <strong class="text-gray-700">{{ $c->conducted_date->isoFormat('D MMMM Y') }}</strong></p>
                @endif
                @if($c->teacher_note)
                    <p class="text-gray-700 font-medium">Catatan Guru BK:</p>
                    <p class="text-gray-600">{{ $c->teacher_note }}</p>
                @endif
                @if($c->follow_up)
                    <p class="text-gray-700 font-medium mt-1">Tindak Lanjut:</p>
                    <p class="text-gray-600">{{ $c->follow_up }}</p>
                @endif
            </div>
            @endif

            @if($c->isCancelled() && $c->cancelled_reason)
                <p class="text-xs text-gray-400 italic">{{ $c->cancelled_reason }}</p>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center">
            <p class="text-gray-400 text-sm">Belum ada riwayat bimbingan BK.</p>
        </div>
        @endforelse
    </div>

</div>

{{-- Modal: Ganti Guru BK --}}
@if($active && $active->isPending())
<div id="modal-change" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4 pb-4 sm:pb-0">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-base font-bold text-gray-800">Ganti Guru BK</p>
            <button onclick="document.getElementById('modal-change').classList.add('hidden')"
                class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('siswa.bk-consultation.change-teacher', $active) }}" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Guru BK Baru</label>
                <select name="teacher_id" required
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                    @foreach($bkTeachers as $t)
                        <option value="{{ $t->id }}" {{ $active->teacher_id == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-change').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 rounded-xl bg-purple-600 text-white text-sm font-semibold">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Toasts --}}
@if(session('success'))
<div id="toast-ok" class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-green-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-lg z-50">
    {{ session('success') }}
</div>
<script>setTimeout(function(){ document.getElementById('toast-ok').style.opacity='0'; }, 2500);</script>
@endif

@if(session('error'))
<div id="toast-err" class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-red-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-lg z-50">
    {{ session('error') }}
</div>
<script>setTimeout(function(){ document.getElementById('toast-err').style.opacity='0'; }, 3000);</script>
@endif

<script>
document.querySelectorAll('[data-confirm]').forEach(function(form){
    form.addEventListener('submit', function(e){
        if(!confirm(form.dataset.confirm)) e.preventDefault();
    });
});
</script>

@endsection
