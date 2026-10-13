<div class="space-y-6 text-slate-800">
    {{-- Info Bar / Badges --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl text-xs">
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Target Audience --}}
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-bold
                @if($announcement->target === 'siswa') bg-blue-100 text-blue-800 border border-blue-200
                @elseif($announcement->target === 'guru') bg-amber-100 text-amber-800 border border-amber-200
                @else bg-emerald-100 text-emerald-800 border border-emerald-200 @endif">
                👥 Target:
                @if($announcement->target === 'siswa') Khusus Siswa
                @elseif($announcement->target === 'guru') Khusus Guru
                @else Semua Pengguna @endif
            </span>

            {{-- Status Aktif --}}
            @if($announcement->is_active)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Nonaktif
                </span>
            @endif

            {{-- Pop-Up Status --}}
            @if($announcement->show_as_modal)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                    ✨ Pop-Up Modal Aktif (15 Detik)
                </span>
            @endif

            {{-- Pinned --}}
            @if($announcement->is_pinned)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                    📌 Pinned
                </span>
            @endif
        </div>

        <div>
            @if(Route::has('siswa.humas.announcements.show'))
                <a href="{{ route('siswa.humas.announcements.show', $announcement) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-semibold transition-colors hover:underline">
                    <span>Lihat Halaman Siswa</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Frame Preview: Simulasi Pop-Up Modal (Tampilan yang dilihat Siswa/Guru saat login) --}}
    <div class="rounded-3xl border border-slate-300/80 bg-slate-900/5 p-3 sm:p-5 shadow-inner">
        <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-2 flex items-center justify-between">
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                Simulasi Pop-Up Modal di Aplikasi / Web
            </span>
            <span class="text-[10px] font-normal normal-case text-slate-400">Tinggi adaptif, auto-close 15 detik</span>
        </div>

        {{-- Card Modal --}}
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200/90 flex flex-col mx-auto max-w-xl">
            {{-- Modal Header --}}
            <div class="relative bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white p-4 sm:p-5 shrink-0 overflow-hidden">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-500/20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-10 -left-10 w-28 h-28 bg-indigo-500/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/20 shadow-inner">
                            <span class="text-xl">📢</span>
                        </div>
                        <div class="min-w-0">
                            <span class="inline-block text-[10px] font-extrabold tracking-widest uppercase text-blue-200/90">Pengumuman Sekolah</span>
                            <h2 class="text-sm font-bold text-white truncate">SMAN 1 Gianyar</h2>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <div class="bg-amber-400 text-amber-950 text-xs font-black px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow-md border border-amber-300/80">
                            <span>⏳</span>
                            <span class="font-mono">15</span>s
                        </div>
                        <div class="w-8 h-8 rounded-full bg-white/10 text-white/80 flex items-center justify-center text-sm font-bold">
                            ✕
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-white/10 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-amber-400 to-amber-300 w-3/4"></div>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-4 sm:p-6 space-y-4 max-h-[50vh] overflow-y-auto bg-gradient-to-b from-slate-50 via-gray-50 to-blue-50/30">
                {{-- Gambar / Poster --}}
                @if($announcement->image)
                    <div class="rounded-2xl overflow-hidden shadow-md border border-gray-200 bg-slate-950 flex justify-center items-center relative group">
                        <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}"
                             class="max-h-72 w-full object-contain"
                             onerror="this.onerror=null; this.src='{{ asset('img/logo_sekolah.png') }}';">
                    </div>
                @else
                    <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-4 text-center">
                        <span class="text-2xl">🖼️</span>
                        <p class="text-xs text-slate-400 mt-1">Tidak ada gambar poster yang diunggah untuk pengumuman ini.</p>
                    </div>
                @endif

                {{-- Metadata Pengumuman --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2 flex-wrap text-[11px]">
                        @if($announcement->is_pinned)
                            <span class="bg-amber-100 text-amber-900 font-bold px-2 py-0.5 rounded-full border border-amber-200">
                                📌 Pinned
                            </span>
                        @endif
                        <span class="bg-blue-50 text-blue-700 font-semibold px-2 py-0.5 rounded-full border border-blue-100">
                            🗓️ {{ $announcement->published_at?->isoFormat('D MMMM Y, HH:mm') ?? now()->isoFormat('D MMMM Y, HH:mm') }} WITA
                        </span>
                        @if($announcement->author)
                            <span class="text-slate-500 font-medium">
                                • Oleh: {{ $announcement->author->name }}
                            </span>
                        @endif
                    </div>

                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900 leading-snug">
                        {{ $announcement->title }}
                    </h3>
                </div>

                {{-- Deskripsi / Isi Pengumuman --}}
                <div class="text-xs sm:text-sm text-slate-700 whitespace-pre-line leading-relaxed bg-white/95 p-4 rounded-2xl border border-slate-200 shadow-xs">
                    {!! nl2br(e($announcement->body)) !!}
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-4 py-3 sm:px-6 sm:py-4 bg-white border-t border-slate-100 flex items-center justify-between gap-3 shrink-0">
                <span class="text-xs font-bold text-blue-600 flex items-center gap-1">
                    <span>Daftar Pengumuman</span> →
                </span>
                <button type="button" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-xs font-bold shadow-md opacity-90 cursor-default">
                    Mengerti & Tutup
                </button>
            </div>
        </div>
    </div>
</div>
