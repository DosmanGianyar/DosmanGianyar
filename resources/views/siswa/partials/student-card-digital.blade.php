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
        <span class="text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2.5 py-0.5 shadow-2xs">
            Resmi & Read-Only
        </span>
    </div>

    {{-- Container Kartu (KTP Aspect Ratio 85.6/54, Max Width 440px) --}}
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
                <div style="background: linear-gradient(135deg, #0a3880 0%, #1565c0 60%, #1976d2 100%); padding: 2.5% 3.5%; display: flex; align-items: center; gap: 3%; border-bottom: 1px solid rgba(10,56,128,0.3); flex-shrink: 0;">
                    
                    {{-- Circular Logo Badge --}}
                    <div style="width: 11%; aspect-ratio: 1; border-radius: 50%; background: #ffffff; flex-shrink: 0; overflow: hidden; display: flex; align-items: center; justify-content: center; padding: 2.5%; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">
                        <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                    </div>

                    <div style="flex: 1; min-width: 0; line-height: 1.15;">
                        <p style="font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.04em; font-size: clamp(10px, 3.2vw, 15.5px); margin: 0; font-family: 'Oswald', system-ui, sans-serif; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            SMA Negeri 1 Gianyar
                        </p>
                        <p style="color: rgba(255,255,255,0.9); font-size: clamp(5.5px, 1.7vw, 8.5px); margin-top: 1px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            Jl. Ratna No.1, Gianyar, Bali 80511 · Telp. (0361) 943443
                        </p>
                    </div>

                    {{-- Badge KARTU PELAJAR --}}
                    <div style="flex-shrink: 0; border: 1px solid rgba(255,255,255,0.4); border-radius: 5px; padding: 1.5% 2.5%; text-align: center; background: rgba(255,255,255,0.18);">
                        <p style="font-weight: 900; color: #ffffff; font-size: clamp(5.5px, 1.7vw, 9px); letter-spacing: 0.1em; text-transform: uppercase; line-height: 1.2; margin: 0;">
                            KARTU<br>PELAJAR
                        </p>
                    </div>
                </div>

                {{-- Accent Strip Emas --}}
                <div style="flex-shrink: 0; height: 1.2%; background: linear-gradient(90deg, #b45309 0%, #fbbf24 50%, #b45309 100%);"></div>

                {{-- Body Utama Kartu --}}
                <div style="flex: 1; display: flex; min-height: 0; overflow: hidden; position: relative; padding: 2.5% 3.5% 2% 3.5%;">
                    
                    {{-- Watermark Logo Transparan --}}
                    <div style="position: absolute; right: 2%; top: 50%; transform: translateY(-50%); width: 32%; aspect-ratio: 1; opacity: 0.04; pointer-events: none; overflow: hidden;">
                        <img src="{{ asset('img/logo_sekolah.png') }}" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                    </div>

                    {{-- Foto Siswa 3x4 --}}
                    <div style="flex-shrink: 0; margin-right: 3.5%; display: flex; align-items: flex-start;">
                        <div style="width: 16.5vw; max-width: 72px; aspect-ratio: 3/4; border: 1.5px solid #1565c0; background: #dce8f8; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(21,101,192,0.2);">
                            @if($siswa->photo)
                                <img src="{{ $siswa->photo_url }}" style="width: 100%; height: 100%; object-fit: cover; object-position: top; display: block;">
                            @else
                                <div style="width: 100%; height: 100%; background: #dce8f8; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 4px;">
                                    <svg viewBox="0 0 24 30" fill="none" style="width: 75%; color: #6fa3d8;">
                                        <ellipse cx="12" cy="9" rx="6" ry="6.5" fill="currentColor"/>
                                        <path d="M0 29c0-6.627 5.373-12 12-12s12 5.373 12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Data Siswa Detail --}}
                    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; position: relative; z-index: 10;">
                        <div>
                            <p style="font-weight: 900; color: #0a3880; font-size: clamp(7.5px, 2.3vw, 12px); letter-spacing: 0.12em; text-align: center; text-transform: uppercase; text-decoration: underline; margin: 0 0 2px 0;">
                                KARTU PELAJAR
                            </p>

                            @php
                            $verifyUrl = url('/verifikasi/kartu-pelajar/' . ($siswa->nis ?? $siswa->id));
                            $qrKepsekSvg = (new \chillerlan\QRCode\QRCode(new \chillerlan\QRCode\QROptions(['outputType' => 'svg', 'scale' => 2])))->render($verifyUrl);

                            $rows = [
                                ['label' => 'Nama',      'value' => strtoupper($siswa->name), 'bold' => true, 'isName' => true],
                                ['label' => 'NIS/NISN',  'value' => ($siswa->nis ?? '—') . ' / ' . ($siswa->nisn ?? '—')],
                                ['label' => 'Kelas',     'value' => $siswa->schoolClass?->name ?? '—'],
                                ['label' => 'Angkatan',  'value' => $siswa->angkatan ?? '—', 'highlight' => true],
                                ['label' => 'Tgl Lahir', 'value' => $siswa->birth_date?->isoFormat('D MMMM Y') ?? '—'],
                                ['label' => 'Gender',    'value' => match($siswa->gender ?? '') { 'L' => 'Laki-laki', 'P' => 'Perempuan', default => '—' }],
                            ];
                            @endphp

                            <div style="display: flex; flex-direction: column; gap: 1.5px;">
                                @foreach($rows as $r)
                                <div style="display: flex; align-items: center; font-size: clamp(6px, 1.85vw, 10px); line-height: 1.3;">
                                    <span style="width: 26%; color: #4b5563; flex-shrink: 0; font-weight: 600;">{{ $r['label'] }}</span>
                                    <span style="color: #6b7280; flex-shrink: 0; margin-right: 4px;">:</span>
                                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; {{ !empty($r['isName']) ? 'color: #0a3880; font-weight: 900; font-size: clamp(6.5px, 2.05vw, 11px); letter-spacing: 0.02em;' : (!empty($r['highlight']) ? 'color: #92400e; font-weight: 800;' : (!empty($r['bold']) ? 'color: #111827; font-weight: 800;' : 'color: #1f2937; font-weight: 700;')) }}">
                                        {{ $r['value'] }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Footer Tanda Tangan & Masa Berlaku --}}
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-top: 1px;">
                            <p style="font-size: clamp(4.5px, 1.4vw, 7.5px); color: #6b7280; font-style: italic; line-height: 1.25; margin: 0; font-weight: 500;">
                                Berlaku selama menjadi<br>siswa SMAN 1 Gianyar
                            </p>
                            <div style="text-align: center; flex-shrink: 0;">
                                <p style="font-size: clamp(4.5px, 1.4vw, 7.5px); color: #374151; font-weight: 500; margin: 0;">
                                    Gianyar, 13 Juli 2026
                                </p>
                                <p style="font-size: clamp(4.5px, 1.4vw, 7.5px); color: #374151; font-weight: 600; margin: 0 0 1px 0;">
                                    Kepala Sekolah,
                                </p>

                                {{-- Barcode / QR Code Verifikasi Keabsahan Kepsek --}}
                                <div style="width: 6.5vw; max-width: 28px; aspect-ratio: 1; margin: 1px auto 2px auto; padding: 1px; background: #ffffff; border: 0.8px solid #cbd5e1; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.06);" title="Scan untuk verifikasi keabsahan kartu pelajar">
                                    <img src="{{ $qrKepsekSvg }}" alt="QR Verifikasi Resmi" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                                </div>

                                <p style="font-size: clamp(4.5px, 1.45vw, 8px); color: #111827; font-weight: 800; text-decoration: underline; margin: 0; white-space: nowrap;">
                                    I Wayan Sudra Astra, S.Pd., M.Pd.
                                </p>
                                <p style="font-size: clamp(4px, 1.25vw, 7px); color: #4b5563; font-weight: 600; margin: 0.5px 0 0 0; white-space: nowrap;">
                                    NIP. 19710415 199703 1 007
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Strip Bawah Biru --}}
                <div style="flex-shrink: 0; height: 3.5%; background: linear-gradient(90deg, #0a3880 0%, #1565c0 50%, #1976d2 100%);"></div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- SISI BELAKANG (BACK CARD) --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div class="absolute inset-0 rounded-2xl overflow-hidden shadow-xl flex flex-col bg-white"
                 style="-webkit-backface-visibility: hidden; backface-visibility: hidden; -webkit-transform: rotateY(180deg) translateZ(1px); transform: rotateY(180deg) translateZ(1px); font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">

                {{-- Strip Atas Biru --}}
                <div style="height: 6%; background: linear-gradient(90deg, #0a3880 0%, #1565c0 60%, #1976d2 100%); padding: 0 3.5%; display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #ffffff; flex-shrink: 0; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('img/logo_sekolah.png') }}" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                    </div>
                    <p style="font-weight: 800; color: #ffffff; font-size: clamp(5px, 1.6vw, 9px); text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                        SMA NEGERI 1 GIANYAR
                    </p>
                    <p style="color: rgba(255,255,255,0.7); font-size: clamp(3.5px, 1.2vw, 7px); margin-left: auto; margin-top: 0; margin-bottom: 0;">
                        NPSN 50102079
                    </p>
                </div>

                {{-- Body Tengah Belakang (QR Code & Detail) --}}
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4%; text-align: center; overflow: hidden;">
                    @if(isset($qrSvg))
                    <div style="width: 32%; aspect-ratio: 1; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #ffffff;">
                        <img src="{{ $qrSvg }}" alt="QR Code" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                    </div>
                    @endif

                    <p style="font-size: clamp(4.5px, 1.4vw, 8px); color: #9ca3af; margin-top: 4px; letter-spacing: 0.02em;">
                        Scan untuk verifikasi identitas resmi siswa
                    </p>

                    <div style="width: 50%; height: 1px; background: linear-gradient(90deg, transparent 0%, #e5e7eb 50%, transparent 100%); margin: 6px 0;"></div>

                    <p style="font-weight: 700; color: #111827; font-size: clamp(6.5px, 2vw, 12px); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90%; margin: 0;">
                        {{ $siswa->name }}
                    </p>
                    <p style="color: #6b7280; font-size: clamp(4.5px, 1.4vw, 8.5px); margin-top: 2px; margin-bottom: 0;">
                        NIS: {{ $siswa->nis ?? '—' }} @if($siswa->nisn) · NISN: {{ $siswa->nisn }} @endif
                    </p>
                    <p style="color: #9ca3af; font-size: clamp(4px, 1.3vw, 7.5px); margin-top: 2px; margin-bottom: 0;">
                        {{ $siswa->schoolClass?->name ?? '—' }} @if($siswa->angkatan) · <strong style="color: #92400e; font-weight: 700;">{{ $siswa->angkatan }}</strong> @endif
                    </p>
                </div>

                {{-- Strip Bawah Emas --}}
                <div style="height: 4%; background: linear-gradient(90deg, #b45309 0%, #fbbf24 50%, #b45309 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <p style="font-weight: 800; color: #ffffff; font-size: clamp(4px, 1.3vw, 7.5px); letter-spacing: 0.1em; text-transform: uppercase; margin: 0;">
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
