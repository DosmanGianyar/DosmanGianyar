<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keabsahan Kartu Pelajar — {{ strtoupper($siswa->name) }}</title>
    <meta name="robots" content="noindex,nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cinzel:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        .font-serif-header { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="py-6 px-4 min-h-screen flex items-center justify-center">

    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden my-auto">
        
        {{-- Header Banner Status --}}
        <div class="bg-emerald-600 text-white px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-emerald-500/80 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider">Kartu Pelajar Terverifikasi Resmi</span>
            </div>
            <span class="text-[11px] font-semibold bg-emerald-700/60 px-2.5 py-1 rounded-full border border-emerald-400/40">
                Sistem SIMS Live
            </span>
        </div>

        <div class="p-6 md:p-8">
            {{-- Kop Surat Resmi Sekolah --}}
            <div class="flex items-center gap-4 border-b-2 border-slate-800 pb-4 mb-1">
                <img src="{{ asset('img/logo_sekolah.png') }}" alt="Logo SMAN 1 Gianyar" class="w-16 h-16 object-contain shrink-0">
                <div class="text-center flex-1">
                    <h3 class="text-xs font-bold text-slate-600 tracking-wider uppercase">Pemerintah Provinsi Bali</h3>
                    <h2 class="text-xs font-bold text-slate-700 uppercase">Dinas Pendidikan Kepemudaan dan Olahraga</h2>
                    <h1 class="text-base md:text-lg font-black text-blue-950 uppercase tracking-tight font-serif-header">SMA Negeri 1 Gianyar</h1>
                    <p class="text-[11px] text-slate-500 font-medium">Jl. Ratna No.1, Gianyar, Bali 80511 · Telp. (0361) 943443 · NPSN: 50102079</p>
                </div>
            </div>
            <div class="h-0.5 bg-slate-800 mb-6"></div>

            {{-- Judul Surat Keterangan --}}
            <div class="text-center mb-6">
                <h4 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide underline decoration-slate-400 underline-offset-4">
                    SURAT KETERANGAN VERIFIKASI KEABSAHAN KARTU PELAJAR
                </h4>
                <p class="text-[11px] font-mono text-slate-500 mt-1">
                    No. Reg: SIMS/VERIFY/2026/07/{{ $siswa->nis ?? $siswa->id }}
                </p>
            </div>

            {{-- Teks Pernyataan Resmi --}}
            <div class="text-xs text-slate-700 leading-relaxed mb-6 space-y-2">
                <p>
                    Kepala SMA Negeri 1 Gianyar menerangkan dan mengonfirmasi secara resmi bahwa Kartu Pelajar Digital berikut adalah <strong>BENAR, SAH, DAN TERDAFTAR SECARA RESMI</strong> pada basis data Sistem Informasi Manajemen Sekolah (SIMS) SMA Negeri 1 Gianyar:
                </p>
            </div>

            {{-- Box Detail Siswa --}}
            <div class="bg-slate-50/80 rounded-xl border border-slate-200 p-4 md:p-5 mb-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                    
                    {{-- Foto Siswa --}}
                    <div class="w-24 h-32 rounded-lg border-2 border-blue-600 overflow-hidden bg-slate-200 shrink-0 shadow-sm">
                        @if($siswa->photo)
                            <img src="{{ $siswa->photo_url }}" alt="{{ $siswa->name }}" class="w-full h-full object-cover object-top">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-blue-50 text-blue-400 font-bold text-xl">
                                {{ strtoupper(substr($siswa->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    {{-- Tabel Identitas --}}
                    <div class="flex-1 w-full space-y-2 text-xs">
                        <div class="grid grid-cols-3 py-1 border-b border-slate-200/60">
                            <span class="text-slate-500 font-medium">Nama Lengkap</span>
                            <span class="col-span-2 font-extrabold text-slate-900 uppercase">{{ strtoupper($siswa->name) }}</span>
                        </div>
                        <div class="grid grid-cols-3 py-1 border-b border-slate-200/60">
                            <span class="text-slate-500 font-medium">NIS / NISN</span>
                            <span class="col-span-2 font-bold text-slate-800">{{ $siswa->nis ?? '—' }} / {{ $siswa->nisn ?? '—' }}</span>
                        </div>
                        <div class="grid grid-cols-3 py-1 border-b border-slate-200/60">
                            <span class="text-slate-500 font-medium">Kelas / Angkatan</span>
                            <span class="col-span-2 font-bold text-slate-800">{{ $siswa->schoolClass?->name ?? '—' }} <span class="text-amber-700 font-extrabold">({{ $siswa->angkatan }})</span></span>
                        </div>
                        <div class="grid grid-cols-3 py-1 border-b border-slate-200/60">
                            <span class="text-slate-500 font-medium">Jenis Kelamin</span>
                            <span class="col-span-2 font-semibold text-slate-800">{{ match($siswa->gender ?? '') { 'L' => 'Laki-laki', 'P' => 'Perempuan', default => '—' } }}</span>
                        </div>
                        <div class="grid grid-cols-3 py-1 border-b border-slate-200/60">
                            <span class="text-slate-500 font-medium">Tanggal Lahir</span>
                            <span class="col-span-2 font-semibold text-slate-800">{{ $siswa->birth_date?->isoFormat('D MMMM Y') ?? '—' }}</span>
                        </div>
                        <div class="grid grid-cols-3 py-1">
                            <span class="text-slate-500 font-medium">Status Siswa</span>
                            <span class="col-span-2 inline-flex items-center gap-1 font-bold text-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                Siswa Aktif Terdaftar (T.A 2026/2027)
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Tanda Tangan & Stempel Resmi --}}
            <div class="flex flex-col sm:flex-row justify-between items-center sm:items-end gap-6 pt-2">
                <div class="text-center sm:text-left text-[11px] text-slate-400 italic">
                    <p>Dokumen ini diterbitkan secara otomatis oleh SIMS</p>
                    <p>Tanggal Terbit Kartu: <strong>13 Juli 2026</strong></p>
                </div>

                <div class="text-center shrink-0">
                    <p class="text-xs text-slate-600 font-medium">Gianyar, 13 Juli 2026</p>
                    <p class="text-xs text-slate-700 font-semibold mb-2">Kepala Sekolah,</p>
                    
                    {{-- Stempel Digital Badge --}}
                    <div class="my-1 py-1 px-3 bg-blue-50 border border-blue-200 rounded-lg inline-block text-[10px] text-blue-900 font-bold uppercase tracking-wider shadow-2xs">
                        ✓ Digital Verified Signature
                    </div>

                    <p class="text-xs font-extrabold text-slate-900 underline decoration-slate-400 mt-2">
                        I Wayan Sudra Astra, S.Pd., M.Pd.
                    </p>
                    <p class="text-[11px] font-semibold text-slate-600">
                        NIP. 19710415 199703 1 007
                    </p>
                </div>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="mt-8 pt-4 border-t border-slate-200 flex items-center justify-between text-xs">
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1">
                    ← Kembali ke Aplikasi SIMS
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg transition-colors flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Bukti Verifikasi
                </button>
            </div>

        </div>

    </div>

</body>
</html>
