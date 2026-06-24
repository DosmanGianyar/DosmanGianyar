@extends('layouts.siswa')

@section('title', $session->title)
@section('page-title', 'Pilih Kandidat')

@section('content')

{{-- Session Info --}}
<div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 mb-4 text-white">
    <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full font-medium">Sedang Berlangsung</span>
    <h2 class="text-base font-bold mt-2">{{ $session->title }}</h2>
    @if($session->description)
        <p class="text-blue-100 text-xs mt-1">{{ $session->description }}</p>
    @endif
    <p class="text-blue-200 text-xs mt-2">
        Berakhir: {{ $session->end_time->isoFormat('D MMMM Y, HH:mm') }}
    </p>
</div>

<p class="text-sm font-semibold text-gray-700 mb-3">Pilih satu kandidat:</p>

<form method="POST" action="{{ route('siswa.voting.vote', $session) }}" id="vote-form">
    @csrf

    <div class="space-y-3 mb-6">
        @foreach($session->candidates as $candidate)
        <label class="block cursor-pointer">
            <input type="radio" name="candidate_id" value="{{ $candidate->id }}"
                class="sr-only peer" required>
            <div class="bg-white rounded-2xl border-2 border-gray-100 peer-checked:border-blue-500 peer-checked:bg-blue-50 p-4 transition-all">
                <div class="flex items-center gap-3">
                    @if($candidate->photoUrl())
                        <img src="{{ $candidate->photoUrl() }}"
                            class="w-14 h-14 rounded-xl object-cover shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm">{{ $candidate->name }}</p>
                        @if($candidate->vision)
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $candidate->vision }}</p>
                        @endif
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-blue-500 shrink-0 flex items-center justify-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500 hidden peer-checked:block"></div>
                    </div>
                </div>
            </div>
        </label>
        @endforeach
    </div>

    <button type="button" onclick="confirmVote()"
        class="w-full bg-blue-600 text-white font-semibold py-3.5 rounded-xl text-sm active:bg-blue-700">
        Kirim Suara
    </button>
</form>

{{-- Confirmation Dialog --}}
<dialog id="confirm-dialog"
    class="rounded-2xl shadow-xl p-0 backdrop:bg-black/50 w-80 max-w-[90vw]">
    <div class="p-5">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-center font-bold text-gray-800 text-sm mb-1">Konfirmasi Pilihan</h3>
        <p class="text-center text-xs text-gray-500 mb-1">Kamu memilih:</p>
        <p class="text-center font-semibold text-blue-700 text-sm mb-3" id="selected-name">—</p>
        <p class="text-center text-xs text-gray-400 mb-4">Pilihan tidak dapat diubah setelah dikirim.</p>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('confirm-dialog').close()"
                class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 font-medium">
                Batal
            </button>
            <button type="button" onclick="submitVote()"
                class="flex-1 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold">
                Ya, Kirim
            </button>
        </div>
    </div>
</dialog>

<script>
function confirmVote() {
    const selected = document.querySelector('input[name="candidate_id"]:checked');
    if (!selected) {
        swalAlert('Pilih salah satu kandidat terlebih dahulu.');
        return;
    }
    const label = selected.closest('label').querySelector('p.font-semibold').textContent.trim();
    document.getElementById('selected-name').textContent = label;
    document.getElementById('confirm-dialog').showModal();
}

function submitVote() {
    document.getElementById('vote-form').submit();
}

// Style checked radio buttons (CSS peer trick needs JS assist for custom UI)
document.querySelectorAll('input[name="candidate_id"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('input[name="candidate_id"]').forEach(r => {
            const dot = r.closest('label').querySelector('.w-2\\.5');
            if (dot) dot.classList.add('hidden');
        });
        const dot = this.closest('label').querySelector('.w-2\\.5');
        if (dot) dot.classList.remove('hidden');
    });
});
</script>

@endsection
