@extends(auth()->user()->isGuru() ? 'layouts.guru' : 'layouts.siswa')

@section('title', 'Hasil ' . $session->title)
@section('page-title', 'Hasil E-Voting Real-Time')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Banner Hasil --}}
    <div class="bg-gradient-to-r from-emerald-800 via-teal-900 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-xl border border-emerald-500/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold uppercase tracking-wider mb-2">
                    Hasil Pemilihan Resmi
                </span>
                <h2 class="text-xl md:text-2xl font-black text-white leading-tight">
                    {{ $session->title }}
                </h2>
                <p class="text-xs text-emerald-200/80 mt-2">
                    Status: <span class="font-bold text-white uppercase">{{ $session->status === 'closed' ? 'Telah Ditutup (Final)' : 'Voting Masih Berlangsung (Live Count)' }}</span>
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 text-center shrink-0 border border-white/10">
                <p class="text-xs text-emerald-200 font-medium">Total Suara Masuk</p>
                <p class="text-2xl md:text-3xl font-black text-amber-300 mt-1">{{ number_format($totalVotes) }}</p>
                <p class="text-[10px] text-emerald-100 mt-0.5">Suara Sah Terverifikasi</p>
            </div>
        </div>
    </div>

    {{-- Status Suara Pemilih Ini --}}
    @if(isset($hasVoted) && $hasVoted && isset($userVote))
        @php
            $myCandidate = $userVote->candidate;
            $paslonNo = $myCandidate->candidate_number ?? 1;
        @endphp
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-emerald-900 text-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow">
                ✓
            </div>
            <div>
                <p class="font-bold text-sm">Hak Suara Anda Telah Dicatat!</p>
                <p class="text-emerald-700 mt-0.5">
                    Terima kasih telah memberikan suara pada <span class="font-bold">PASLON 0{{ $paslonNo }}: {{ $myCandidate->name }} {{ $myCandidate->vice_name ? '& '.$myCandidate->vice_name : '' }}</span>.
                </p>
            </div>
        </div>
    @endif

    {{-- Breakdown Perolehan Suara 2 Paslon --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-6 md:p-8">
        <h3 class="font-extrabold text-slate-900 text-lg mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Perolehan Suara Paslon
        </h3>

        <div class="space-y-6">
            @foreach($session->candidates->sortBy('candidate_number') as $candidate)
                @php
                    $count = $candidate->votes_count ?? $candidate->votes()->count();
                    $percent = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0;
                    $number = $candidate->candidate_number ?? $loop->iteration;
                    $barBg = $number == 1 ? 'from-amber-500 to-orange-600' : 'from-indigo-600 to-blue-700';
                    $badgeBg = $number == 1 ? 'bg-amber-600' : 'bg-indigo-600';
                @endphp
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="{{ $badgeBg }} text-white text-xs font-extrabold px-3 py-1 rounded-full shrink-0">
                                PASLON 0{{ $number }}
                            </span>
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-sm md:text-base leading-tight">
                                    {{ $candidate->name }}
                                </h4>
                                @if($candidate->vice_name)
                                    <p class="text-xs text-blue-700 font-semibold mt-0.5">
                                        &amp; {{ $candidate->vice_name }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-xl md:text-2xl font-black text-slate-900">{{ $percent }}%</span>
                            <p class="text-xs text-slate-500 font-medium">{{ number_format($count) }} suara</p>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full h-4 bg-slate-200 rounded-full overflow-hidden p-0.5 shadow-inner">
                        <div class="h-full bg-gradient-to-r {{ $barBg }} rounded-full transition-all duration-1000"
                             style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Navigation --}}
    <div class="text-center pt-2">
        <a href="{{ auth()->user()->isGuru() ? route('guru.dashboard') : route('siswa.kesiswaan') }}"
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
            ← Kembali ke Dashboard
        </a>
    </div>

</div>
@endsection
