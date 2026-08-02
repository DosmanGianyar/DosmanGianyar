{{-- ─── E-Kartu Pelajar Digital (Universal iOS & Android Responsive Card) ─── --}}
<div x-data="{ flipped: false }" class="select-none mb-4 w-full">
    <div class="flex items-center justify-between mb-2 px-1 max-w-[440px] mx-auto">
        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.657 1.343 3 3 3s3-1.343 3-3"/>
            </svg>
            Kartu Pelajar Digital
        </h3>
        <span class="text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2 py-0.5 shadow-xs">
            Resmi & Read-Only
        </span>
    </div>

    {{-- Container Kartu (Fixed Aspect Ratio 85.6/54 KTP Standard, Max Width 440px) --}}
    <div class="relative w-full max-w-[440px] mx-auto cursor-pointer"
         style="aspect-ratio: 85.6 / 54; -webkit-perspective: 1200px; perspective: 1200px;"
         @click="flipped = !flipped">

        {{-- Flip Inner Container --}}
        <div class="w-full h-full relative"
             style="-webkit-transform-style: preserve-3d; transform-style: preserve-3d; -webkit-transition: -webkit-transform 0.6s ease, transform 0.6s ease; transition: transform 0.6s ease;"
             :style="{ transform: flipped ? 'rotateY(180deg)' : 'rotateY(0deg)', '-webkit-transform': flipped ? 'rotateY(180deg)' : 'rotateY(0deg)' }">

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- SISI DEPAN (FRONT CARD) --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div class="absolute inset-0 rounded-2xl overflow-hidden shadow-xl flex flex-col bg-[#f8f7f4]"
                 style="-webkit-backface-visibility: hidden; backface-visibility: hidden; -webkit-transform: rotateY(0deg) translateZ(1px); transform: rotateY(0deg) translateZ(1px); font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">

                {{-- Header Biru SMAN 1 Gianyar --}}
                <div class="shrink-0 bg-linear-to-r from-[#0a3880] via-[#1565c0] to-[#1976d2] px-[3%] py-[2.5%] flex items-center gap-[2.5%] border-b border-blue-900/30">
                    <div class="w-[12%] aspect-square rounded-full bg-white shrink-0 relative shadow-md flex items-center justify-center">
                        <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo" class="w-[120%] h-[120%] object-contain absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 drop-shadow-sm">
                    </div>
                    <div class="flex-1 min-w-0 leading-tight">
                        <p class="font-extrabold text-white uppercase tracking-wider text-[clamp(8px,2.6vw,14px)] leading-none" style="font-family: 'Oswald', system-ui, sans-serif;">
                            SMA Negeri 1 Gianyar
                        </p>
                        <p class="text-blue-100/80 text-[clamp(4px,1.3vw,7.5px)] mt-0.5 truncate">
                            Jl. Ratna No.1, Gianyar, Bali 80511 · Telp. (0361) 943443
                        </p>
                    </div>
                    <div class="shrink-0 border border-white/40 rounded-md px-1.5 py-0.5 text-center bg-white/10">
                        <p class="font-black text-white text-[clamp(4.5px,1.4vw,8px)] tracking-widest uppercase leading-tight">
                            KARTU<br>PELAJAR
                        </p>
                    </div>
                </div>

                {{-- Accent Strip Emas --}}
                <div class="shrink-0 h-[1.2%] bg-linear-to-r from-[#b45309] via-[#fbbf24] to-[#b45309]"></div>

                {{-- Body Utama Kartu --}}
                <div class="flex-1 flex min-h-0 overflow-hidden relative p-[3%]">
                    {{-- Watermark Logo Transparan --}}
                    <div class="absolute right-[2%] top-1/2 -translate-y-1/2 w-[34%] aspect-square opacity-5 pointer-events-none">
                        <img src="{{ asset('img/logo_sekolah.png') }}" class="w-full h-full object-contain">
                    </div>

                    {{-- Foto Siswa 3x4 --}}
                    <div class="shrink-0 mr-[3%] flex items-start">
                        <div class="w-[16.5vw] max-w-[72px] aspect-[3/4] border-[1.5px] border-[#1565c0] bg-[#dce8f8] rounded-md overflow-hidden shadow-sm">
                            @if($siswa->photo)
                                <img src="{{ $siswa->photo_url }}" class="w-full h-full object-cover object-top">
                            @else
                                <div class="w-full h-full bg-[#dce8f8] flex items-end justify-center pb-1">
                                    <svg viewBox="0 0 24 30" fill="none" class="w-[75%] text-[#6fa3d8]">
                                        <ellipse cx="12" cy="9" rx="6" ry="6.5" fill="currentColor"/>
                                        <path d="M0 29c0-6.627 5.373-12 12-12s12 5.373 12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Data Siswa Detail --}}
                    <div class="flex-1 min-w-0 flex flex-col relative z-10 justify-between">
                        <div>
                            <p class="font-black text-[#0a3880] text-[clamp(6px,1.9vw,10.5px)] tracking-widest text-center uppercase underline underline-offset-1 mb-1">
                                KARTU PELAJAR
                            </p>

                            @php
                            $rows = [
                                ['label' => 'Nama',      'value' => $siswa->name, 'bold' => true],
                                ['label' => 'NIS/NISN',  'value' => ($siswa->nis ?? '—') . ' / ' . ($siswa->nisn ?? '—')],
                                ['label' => 'Kelas',     'value' => $siswa->schoolClass?->name ?? '—'],
                                ['label' => 'Angkatan',  'value' => $siswa->angkatan ?? '—', 'highlight' => true],
                                ['label' => 'Tgl Lahir', 'value' => $siswa->birth_date?->isoFormat('D MMMM Y') ?? '—'],
                                ['label' => 'Gender',    'value' => match($siswa->gender ?? '') { 'L' => 'Laki-laki', 'P' => 'Perempuan', default => '—' }],
                            ];
                            @endphp

                            <div class="space-y-[1.2%]">
                                @foreach($rows as $r)
                                <div class="flex items-center text-[clamp(4.5px,1.45vw,8.5px)] leading-tight">
                                    <span class="w-[26%] text-gray-500 shrink-0 font-medium">{{ $r['label'] }}</span>
                                    <span class="text-gray-400 shrink-0 mr-1">:</span>
                                    <span class="truncate font-semibold {{ !empty($r['highlight']) ? 'text-amber-800 font-extrabold' : (!empty($r['bold']) ? 'text-gray-900 font-bold' : 'text-gray-700') }}">
                                        {{ $r['value'] }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Footer Tanda Tangan & Masa Berlaku --}}
                        <div class="flex justify-between items-end pt-1">
                            <p class="text-[clamp(3.5px,1.15vw,6.5px)] text-gray-400 italic leading-tight">
                                Berlaku selama menjadi<br>siswa SMAN 1 Gianyar
                            </p>
                            <div class="text-center shrink-0">
                                <p class="text-[clamp(3.5px,1.15vw,6.5px)] text-gray-600">
                                    Gianyar, {{ now()->isoFormat('D MMMM Y') }}
                                </p>
                                <p class="text-[clamp(3.5px,1.15vw,6.5px)] text-gray-600 font-medium">
                                    Kepala Sekolah,
                                </p>
                                <div class="mt-2 border-t border-gray-700 pt-0.5 inline-block min-w-[50px]">
                                    <p class="text-[clamp(3px,1vw,5.5px)] text-gray-400">
                                        NIP. ———————————
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Strip Bawah Biru --}}
                <div class="shrink-0 h-[3.5%] bg-linear-to-r from-[#0a3880] via-[#1565c0] to-[#1976d2]"></div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- SISI BELAKANG (BACK CARD) --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div class="absolute inset-0 rounded-2xl overflow-hidden shadow-xl flex flex-col bg-white"
                 style="-webkit-backface-visibility: hidden; backface-visibility: hidden; -webkit-transform: rotateY(180deg) translateZ(1px); transform: rotateY(180deg) translateZ(1px); font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">

                {{-- Strip Atas Biru --}}
                <div class="shrink-0 h-[6%] bg-linear-to-r from-[#0a3880] via-[#1565c0] to-[#1976d2] px-[3%] flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-white shrink-0 overflow-hidden">
                        <img src="{{ asset('img/logo_sekolah.png') }}" class="w-full h-full object-contain">
                    </div>
                    <p class="font-extrabold text-white text-[clamp(5px,1.6vw,9px)] uppercase tracking-wider">
                        SMA NEGERI 1 GIANYAR
                    </p>
                    <p class="text-blue-100/70 text-[clamp(3.5px,1.2vw,7px)] ml-auto">
                        NPSN 50102079
                    </p>
                </div>

                {{-- Body Tengah Belakang (QR Code & Detail) --}}
                <div class="flex-1 flex flex-col items-center justify-center p-[4%] text-center overflow-hidden">
                    @if(isset($qrSvg))
                    <div class="w-[32%] aspect-square border border-gray-200 rounded-lg p-1 shadow-sm bg-white">
                        <img src="{{ $qrSvg }}" alt="QR Code" class="w-full h-full object-contain block">
                    </div>
                    @endif

                    <p class="text-[clamp(4.5px,1.4vw,8px)] text-gray-400 mt-1 tracking-wide">
                        Scan untuk verifikasi identitas resmi siswa
                    </p>

                    <div class="w-1/2 h-px bg-linear-to-r from-transparent via-gray-200 to-transparent my-1.5"></div>

                    <p class="font-bold text-gray-900 text-[clamp(6.5px,2vw,12px)] truncate max-w-[90%]">
                        {{ $siswa->name }}
                    </p>
                    <p class="text-gray-500 text-[clamp(4.5px,1.4vw,8.5px)] mt-0.5">
                        NIS: {{ $siswa->nis ?? '—' }} @if($siswa->nisn) · NISN: {{ $siswa->nisn }} @endif
                    </p>
                    <p class="text-gray-400 text-[clamp(4px,1.3vw,7.5px)] mt-0.5">
                        {{ $siswa->schoolClass?->name ?? '—' }} @if($siswa->angkatan) · <strong class="text-amber-800 font-bold">{{ $siswa->angkatan }}</strong> @endif
                    </p>
                </div>

                {{-- Strip Bawah Emas --}}
                <div class="shrink-0 h-[4%] bg-linear-to-r from-[#b45309] via-[#fbbf24] to-[#b45309] flex items-center justify-center">
                    <p class="font-extrabold text-white text-[clamp(4px,1.3vw,7.5px)] tracking-widest uppercase">
                        SISWA {{ $siswa->angkatan ? '· ' . strtoupper($siswa->angkatan) : '' }}
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- Hint Petunjuk Ketuk --}}
    <p class="text-center text-[11px] text-gray-400 mt-2 font-medium">
        <span x-show="!flipped" class="flex items-center justify-center gap-1">
            Ketuk kartu untuk melihat QR Code
            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </span>
        <span x-show="flipped" class="flex items-center justify-center gap-1">
            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Ketuk kartu untuk kembali ke depan
        </span>
    </p>
</div>
