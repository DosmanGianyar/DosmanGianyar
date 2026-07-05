@extends('layouts.guru')
@section('title', 'Bimbingan Siswa BK')
@section('page-title', 'Bimbingan Siswa BK')

@section('content')
<div class="space-y-4">

{{-- Filter tabs --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
        @php
            $tabs = [
                ''          => ['label' => 'Semua',       'count' => array_sum($counts->toArray())],
                'pending'   => ['label' => 'Menunggu',    'count' => $counts['pending']   ?? 0],
                'scheduled' => ['label' => 'Dijadwalkan', 'count' => $counts['scheduled'] ?? 0],
                'completed' => ['label' => 'Selesai',     'count' => $counts['completed'] ?? 0],
                'cancelled' => ['label' => 'Dibatalkan',  'count' => $counts['cancelled'] ?? 0],
            ];
        @endphp
        @foreach($tabs as $val => $tab)
        <a href="{{ route('guru.bk.consultations', $val !== '' ? ['status' => $val] : []) }}"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-semibold shrink-0 transition-colors
                {{ $status === $val ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $tab['label'] }}
            @if($tab['count'] > 0)
            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $status === $val ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-500' }}">
                {{ $tab['count'] }}
            </span>
            @endif
        </a>
        @endforeach
    </div>
</div>

{{-- Consultation list --}}
@forelse($consultations as $c)
@php
    $colors = [
        'pending'   => ['bg-amber-100 text-amber-700',  'border-amber-100'],
        'scheduled' => ['bg-blue-100 text-blue-700',    'border-blue-100'],
        'completed' => ['bg-green-100 text-green-700',  'border-green-100'],
        'cancelled' => ['bg-gray-100 text-gray-500',    'border-gray-100'],
    ];
    [$badge, $border] = $colors[$c->status] ?? ['bg-gray-100 text-gray-500', 'border-gray-100'];
@endphp
<div class="bg-white rounded-2xl border {{ $border }} shadow-sm p-4">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-2 mb-2">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-gray-800">{{ $c->student?->name }}</p>
            <p class="text-xs text-gray-400">
                {{ $c->student?->nis }}
                @if($c->student?->schoolClass) · {{ $c->student->schoolClass->name }} @endif
            </p>
        </div>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0 {{ $badge }}">
            {{ $c->statusLabel() }}
        </span>
    </div>

    {{-- Topic --}}
    <div class="bg-gray-50 rounded-xl px-3 py-2 mb-3">
        <p class="text-xs font-semibold text-gray-500 mb-0.5">Topik</p>
        <p class="text-sm text-gray-800">{{ $c->topic }}</p>
        @if($c->student_note)
            <p class="text-xs text-gray-500 mt-1">{{ $c->student_note }}</p>
        @endif
    </div>

    <p class="text-xs text-gray-400 mb-3">Diajukan {{ $c->created_at->isoFormat('D MMMM Y') }}</p>

    @if($c->isScheduled() && $c->scheduled_date)
    <div class="bg-blue-50 rounded-xl px-3 py-2 text-xs text-blue-700 mb-3">
        Dijadwalkan: <strong>{{ $c->scheduled_date->isoFormat('D MMMM Y') }}</strong>
    </div>
    @endif

    @if($c->isCompleted())
    <div class="bg-green-50 rounded-xl px-3 py-2 space-y-1.5 text-xs mb-3">
        @if($c->conducted_date)
            <p class="text-gray-500">Dilaksanakan: <strong class="text-gray-700">{{ $c->conducted_date->isoFormat('D MMMM Y') }}</strong></p>
        @endif
        <p class="text-gray-700 font-semibold">Catatan:</p>
        <p class="text-gray-600">{{ $c->teacher_note }}</p>
        @if($c->follow_up)
            <p class="text-gray-700 font-semibold mt-1">Tindak Lanjut:</p>
            <p class="text-gray-600">{{ $c->follow_up }}</p>
        @endif
    </div>
    @endif

    @if($c->isCancelled() && $c->cancelled_reason)
        <p class="text-xs text-gray-400 italic mb-3">{{ $c->cancelled_reason }}</p>
    @endif

    {{-- Actions --}}
    @if($c->isPending())
    <div class="flex gap-2">
        <button onclick="openScheduleModal({{ $c->id }})"
            class="flex-1 text-xs font-semibold py-2 rounded-xl bg-blue-600 text-white">
            Jadwalkan
        </button>
        <button onclick="openCompleteModal({{ $c->id }})"
            class="flex-1 text-xs font-semibold py-2 rounded-xl bg-green-600 text-white">
            Langsung Selesai
        </button>
        <button onclick="openCancelModal({{ $c->id }})"
            class="text-xs font-semibold py-2 px-3 rounded-xl bg-gray-100 text-gray-600">
            Tolak
        </button>
    </div>
    @elseif($c->isScheduled())
    <div class="flex gap-2">
        <button onclick="openCompleteModal({{ $c->id }})"
            class="flex-1 text-xs font-semibold py-2 rounded-xl bg-green-600 text-white">
            Isi Jurnal Bimbingan
        </button>
        <button onclick="openCancelModal({{ $c->id }})"
            class="text-xs font-semibold py-2 px-3 rounded-xl bg-gray-100 text-gray-600">
            Batalkan
        </button>
    </div>
    @endif

</div>
@empty
<div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
    <p class="text-gray-400 text-sm">Belum ada pengajuan bimbingan BK.</p>
</div>
@endforelse

{{-- Pagination --}}
@if($consultations->hasPages())
<div class="bg-white rounded-2xl border border-gray-100 p-3">
    {{ $consultations->links() }}
</div>
@endif

</div>

{{-- ─── Modal: Jadwalkan ─── --}}
<div id="modal-schedule" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4 pb-4 sm:pb-0">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-base font-bold text-gray-800">Jadwalkan Bimbingan</p>
            <button onclick="document.getElementById('modal-schedule').classList.add('hidden')"
                class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="form-schedule" method="POST" action="" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Bimbingan</label>
                <input type="date" name="scheduled_date" required min="{{ today()->toDateString() }}"
                    value="{{ today()->toDateString() }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-schedule').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold">
                    Jadwalkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Modal: Selesaikan / Isi Jurnal ─── --}}
<div id="modal-complete" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4 pb-4 sm:pb-0">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-base font-bold text-gray-800">Isi Jurnal Bimbingan</p>
            <button onclick="document.getElementById('modal-complete').classList.add('hidden')"
                class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="form-complete" method="POST" action="" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Pelaksanaan</label>
                <input type="date" name="conducted_date" required max="{{ today()->toDateString() }}"
                    value="{{ today()->toDateString() }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan Pembinaan <span class="text-red-500">*</span></label>
                <textarea name="teacher_note" rows="4" required maxlength="2000"
                    placeholder="Uraikan hasil bimbingan dan pembinaan…"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tindak Lanjut <span class="text-gray-400 font-normal">(opsional)</span></label>
                <textarea name="follow_up" rows="2" maxlength="1000"
                    placeholder="Rencana tindak lanjut jika ada…"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-complete').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold">
                    Simpan Jurnal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Modal: Batalkan ─── --}}
<div id="modal-cancel" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4 pb-4 sm:pb-0">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-base font-bold text-gray-800">Batalkan Pengajuan</p>
            <button onclick="document.getElementById('modal-cancel').classList.add('hidden')"
                class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="form-cancel" method="POST" action="" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Alasan Pembatalan <span class="text-gray-400 font-normal">(opsional)</span></label>
                <textarea name="cancelled_reason" rows="3" maxlength="300"
                    placeholder="Tuliskan alasan pembatalan…"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-cancel').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold">
                    Batalkan Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

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
function openScheduleModal(id) {
    document.getElementById('form-schedule').action = '/guru/bk/consultation/' + id + '/schedule';
    document.getElementById('modal-schedule').classList.remove('hidden');
}
function openCompleteModal(id) {
    document.getElementById('form-complete').action = '/guru/bk/consultation/' + id + '/complete';
    document.getElementById('modal-complete').classList.remove('hidden');
}
function openCancelModal(id) {
    document.getElementById('form-cancel').action = '/guru/bk/consultation/' + id + '/cancel';
    document.getElementById('modal-cancel').classList.remove('hidden');
}
</script>
@endsection
