@extends(auth()->user()->isGuru() ? 'layouts.guru' : 'layouts.siswa')

@section('title', $session->title)
@section('page-title', 'Bilik Suara E-Voting OSIS')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Banner Utama Sesi Pemilihan --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 p-6 md:p-8 text-white shadow-2xl border border-white/10">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold uppercase tracking-wider mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Bilik Suara Digital Terbuka
                </div>
                <h1 class="text-xl md:text-2xl font-extrabold tracking-tight text-white leading-tight">
                    {{ $session->title }}
                </h1>
                @if($session->description)
                    <p class="text-slate-300 text-xs md:text-sm mt-2 max-w-2xl leading-relaxed">
                        {{ $session->description }}
                    </p>
                @endif
                <p class="text-xs text-blue-200/80 mt-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Batas Akhir Voting: <span class="font-bold text-white">{{ $session->end_time->isoFormat('dddd, D MMMM Y — HH:mm') }} WITA</span>
                </p>
            </div>
            <div class="shrink-0 bg-white/10 backdrop-blur-md rounded-2xl p-4 text-center border border-white/10 hidden sm:block">
                <p class="text-xs text-slate-300 font-medium">Asas Pemilihan</p>
                <p class="text-sm font-extrabold text-amber-300 tracking-wide mt-1">LUBER JURDIL</p>
                <p class="text-[10px] text-slate-400 mt-1">Langsung, Umum, Bebas, Rahasia</p>
            </div>
        </div>
    </div>

    {{-- Alert Hak Pilih --}}
    @if(auth()->user()->role === 'admin')
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-amber-900 text-xs flex items-center gap-3">
            <svg class="w-6 h-6 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-bold">Mode Admin Panitia Penyelenggara</p>
                <p class="mt-0.5">Akun Admin tidak memiliki hak suara. Anda dapat memantau perolehan suara secara real-time di Admin Panel Filament.</p>
            </div>
        </div>
    @else
        <div class="bg-blue-50/80 border border-blue-200/60 rounded-2xl p-4 text-blue-900 text-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">1x</span>
                <div>
                    <p class="font-bold">Pilihlah dengan Bijak!</p>
                    <p class="text-blue-700/90 mt-0.5">Setiap akun pengguna hanya memiliki 1 kali hak suara yang sah & tidak dapat diubah.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Grid 2 Paslon OSIS --}}
    <form method="POST" action="{{ route('siswa.voting.vote', $session) }}" id="vote-form">
        @csrf
        <input type="hidden" name="candidate_id" id="selected-candidate-id" required>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($session->candidates->sortBy('candidate_number') as $candidate)
                @php
                    $number = $candidate->candidate_number ?? $loop->iteration;
                    $badgeBg = $number == 1 ? 'from-amber-500 to-orange-600' : 'from-indigo-600 to-blue-700';
                    $accentBorder = $number == 1 ? 'hover:border-amber-400' : 'hover:border-indigo-400';
                @endphp
                <div class="bg-white rounded-3xl border-2 border-slate-200 {{ $accentBorder }} shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between group">

                    {{-- Card Header & Photo --}}
                    <div>
                        <div class="relative bg-slate-900 h-64 sm:h-72 overflow-hidden flex items-center justify-center">
                            @if($candidate->photoUrl())
                                <img src="{{ $candidate->photoUrl() }}"
                                     alt="{{ $candidate->name }}"
                                     class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900 flex flex-col items-center justify-center text-slate-400 p-6 text-center">
                                    <svg class="w-16 h-16 mb-2 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <p class="text-xs">Foto Resmi Paslon 0{{ $number }}</p>
                                </div>
                            @endif

                            {{-- Badge Nomor Urut --}}
                            <div class="absolute top-4 left-4">
                                <div class="bg-gradient-to-r {{ $badgeBg }} text-white px-4 py-1.5 rounded-full font-black text-sm tracking-wider shadow-lg border border-white/20 flex items-center gap-1.5">
                                    <span>PASLON</span>
                                    <span class="text-lg">0{{ $number }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Identity & Motto --}}
                        <div class="p-6">
                            <div class="text-center pb-4 border-b border-slate-100">
                                <h3 class="text-lg md:text-xl font-black text-slate-900 leading-tight">
                                    {{ $candidate->name }}
                                </h3>
                                @if($candidate->vice_name)
                                    <p class="text-sm font-semibold text-blue-700 mt-1">
                                        &amp; {{ $candidate->vice_name }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 font-medium">Calon Ketua &amp; Wakil Ketua OSIS</p>
                                @else
                                    <p class="text-xs text-slate-500 font-medium mt-0.5">Calon Ketua OSIS</p>
                                @endif

                                @if($candidate->motto)
                                    <div class="mt-3 inline-block bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 italic">
                                        "{{ $candidate->motto }}"
                                    </div>
                                @endif
                            </div>

                            {{-- Visi & Misi --}}
                            <div class="mt-4 space-y-4 text-xs">
                                @if($candidate->vision)
                                    <div>
                                        <p class="font-bold text-slate-900 uppercase tracking-wider text-[11px] flex items-center gap-1.5 text-blue-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Visi Paslon
                                        </p>
                                        <p class="text-slate-600 mt-1 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                                            {{ $candidate->vision }}
                                        </p>
                                    </div>
                                @endif

                                @if($candidate->mission)
                                    <div>
                                        <p class="font-bold text-slate-900 uppercase tracking-wider text-[11px] flex items-center gap-1.5 text-blue-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Misi Paslon
                                        </p>
                                        <div class="text-slate-600 mt-1 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line">
                                            {{ $candidate->mission }}
                                        </div>
                                    </div>
                                @endif

                                @if($candidate->programs)
                                    <div>
                                        <p class="font-bold text-slate-900 uppercase tracking-wider text-[11px] flex items-center gap-1.5 text-amber-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            Program Kerja Unggulan
                                        </p>
                                        <div class="text-slate-600 mt-1 leading-relaxed bg-amber-50/50 p-3 rounded-xl border border-amber-100 whitespace-pre-line">
                                            {{ $candidate->programs }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Footer Action Button --}}
                    <div class="p-6 pt-0 mt-4">
                        @if(auth()->user()->role === 'admin')
                            <button type="button" disabled
                                    class="w-full bg-slate-100 text-slate-400 font-bold py-3.5 rounded-2xl text-xs cursor-not-allowed border border-slate-200">
                                Paslon Nomor 0{{ $number }}
                            </button>
                        @else
                            <button type="button"
                                    onclick="openBallotModal({{ $candidate->id }}, '{{ addslashes($candidate->name) }}', '{{ addslashes($candidate->vice_name ?? '') }}', {{ $number }}, '{{ $candidate->photoUrl() ?? '' }}')"
                                    class="w-full bg-gradient-to-r {{ $badgeBg }} text-white font-extrabold py-4 rounded-2xl text-sm shadow-lg hover:shadow-xl active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                COBLOS PASLON 0{{ $number }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </form>
</div>

{{-- Dialog Bilik Suara Digital --}}
<dialog id="ballot-modal" class="rounded-3xl shadow-2xl p-0 backdrop:bg-slate-900/80 backdrop:backdrop-blur-sm w-full max-w-md border-0">
    <div class="p-6 md:p-8 bg-white rounded-3xl text-center">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-blue-50 shadow-inner">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h3 class="font-extrabold text-slate-900 text-lg mb-1">Bilik Suara Digital</h3>
        <p class="text-xs text-slate-500 mb-4">Konfirmasi Pilihan Suara Anda:</p>

        {{-- Preview Candidate --}}
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-5 text-center">
            <span class="inline-block bg-blue-600 text-white font-extrabold text-xs px-3 py-1 rounded-full mb-2" id="modal-paslon-number">
                PASLON 01
            </span>
            <h4 class="font-black text-slate-900 text-base leading-tight" id="modal-paslon-name">
                —
            </h4>
            <p class="text-xs text-blue-700 font-semibold mt-0.5" id="modal-paslon-vice">
                —
            </p>
        </div>

        <p class="text-[11px] text-slate-400 mb-6 leading-relaxed">
            ⚠️ Pilihan Anda bersifat <span class="font-bold text-slate-700">RAHASIA</span> dan <span class="font-bold text-slate-700">FINAL</span>.<br>Tidak dapat diubah atau dibatalkan setelah dikirim.
        </p>

        <div class="grid grid-cols-2 gap-3">
            <button type="button" onclick="document.getElementById('ballot-modal').close()"
                class="py-3.5 rounded-2xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 active:bg-slate-100 transition-colors">
                Batal / Cek Lagi
            </button>
            <button type="button" onclick="submitBallotForm()"
                class="py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-extrabold shadow-lg shadow-emerald-600/30 transition-all">
                SAH! KIRIM SUARA
            </button>
        </div>
    </div>
</dialog>

<script>
function openBallotModal(candidateId, name, viceName, number, photoUrl) {
    document.getElementById('selected-candidate-id').value = candidateId;
    document.getElementById('modal-paslon-number').textContent = 'PASLON 0' + number;
    document.getElementById('modal-paslon-name').textContent = name;
    document.getElementById('modal-paslon-vice').textContent = viceName ? '& ' + viceName : '';

    document.getElementById('ballot-modal').showModal();
}

function submitBallotForm() {
    document.getElementById('ballot-modal').close();
    document.getElementById('vote-form').submit();
}
</script>
@endsection
