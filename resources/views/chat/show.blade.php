@extends($layout)

@section('title', $room->name)
@section('page-title', 'Konsultasi: ' . ($room->student?->name ?? 'Chat'))

@section('content')
<div class="max-w-4xl mx-auto flex flex-col h-[calc(100vh-8.5rem)] sm:h-[calc(100vh-7.5rem)] bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden relative">

    {{-- ─── 1. Header Chat ──────────────────────────────────────────────── --}}
    <div class="bg-slate-900 text-white px-4 py-3 shrink-0 flex items-center justify-between gap-3 shadow-md z-10">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ $backUrl }}"
               class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors shrink-0"
               title="Kembali">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>

            {{-- Avatar Siswa --}}
            <div class="relative shrink-0">
                @if($room->student?->photo)
                    <img src="{{ $room->student->photo_url }}" class="w-10 h-10 rounded-xl object-cover ring-2 ring-emerald-500/50">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                        {{ $room->student?->initials ?? 'S' }}
                    </div>
                @endif
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-slate-900 absolute -bottom-0.5 -right-0.5"></span>
            </div>

            <div class="min-w-0">
                <h2 class="font-bold text-sm sm:text-base text-white truncate flex items-center gap-2">
                    <span>{{ $room->student?->name ?? $room->name }}</span>
                    @if($room->schoolClass)
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">
                            {{ $room->schoolClass->name }}
                        </span>
                    @endif
                </h2>

                {{-- Status Terakhir Dilihat Partisipan --}}
                <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-300 truncate">
                    @foreach($participantLastSeen as $ls)
                        @if($ls['user_id'] !== auth()->id())
                            <span class="truncate">
                                <strong class="text-white">{{ $ls['role'] }}:</strong>
                                <span class="{{ str_contains($ls['status_text'], 'sekarang') ? 'text-emerald-400 font-bold' : 'text-slate-300' }}">{{ $ls['status_text'] }}</span>{{ !$loop->last ? ' ·' : '' }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Header Actions: Search & Audio Toggle --}}
        <div class="flex items-center gap-2 shrink-0">
            {{-- Tombol Toggle Pencarian Pesan --}}
            <button type="button" id="btnToggleSearch"
                    class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors"
                    title="Cari Pesan dalam Obrolan">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>

            {{-- Tombol Toggle Suara Notifikasi --}}
            <button type="button" id="btnToggleSound"
                    class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors"
                    title="Suara Notifikasi Aktif">
                <span id="soundIcon" class="text-xs">🔔</span>
            </button>
        </div>
    </div>

    {{-- Bar Pencarian Pesan (Tersembunyi by default) --}}
    <div id="searchBarContainer" class="hidden bg-slate-800 px-4 py-2 border-b border-slate-700 flex items-center gap-2 z-10 transition-all">
        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" id="inputSearchChat" placeholder="Cari pesan di obrolan ini..."
               class="flex-1 bg-transparent border-0 text-xs sm:text-sm text-white placeholder-slate-400 focus:ring-0 focus:outline-hidden py-1">
        <span id="searchResultCount" class="text-[11px] text-slate-400"></span>
        <button type="button" id="btnCloseSearch" class="text-slate-400 hover:text-white text-xs px-2 py-1">
            ✕
        </button>
    </div>

    {{-- ─── 2. Area Pesan (Thread) ─────────────────────────────────────── --}}
    <div id="chatMessagesContainer"
         class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#efeae2] dark:bg-slate-900"
         style="background-image: radial-gradient(rgba(0,0,0,0.04) 1px, transparent 1px); background-size: 16px 16px;">

        {{-- Disclaimer Banner --}}
        <div class="max-w-md mx-auto my-2 p-2.5 rounded-xl bg-amber-50/90 border border-amber-200 text-center shadow-2xs">
            <p class="text-[11px] font-semibold text-amber-800 leading-tight">
                🔒 Ruang obrolan ini hanya dapat dilihat oleh <strong>Wali Kelas</strong>, <strong>Orang Tua</strong>, dan <strong>Siswa</strong> terkait. Gunakan bahasa yang santun dan kondusif.
            </p>
        </div>

        {{-- List Pesan --}}
        @php
            $lastDate = null;
        @endphp

        @forelse($messages as $msg)
            @php
                $msgDate = $msg->created_at->format('d M Y');
                $isMe = $msg->sender_id === auth()->id();
                $readStatus = $msg->read_status; // 'sent', 'read', 'read_all'
                $isDeleted = (bool) $msg->is_deleted;
                $canDelete = $msg->canBeDeletedBy(auth()->user());
            @endphp

            {{-- Tanggal Divider --}}
            @if($msgDate !== $lastDate)
                <div class="flex justify-center my-3">
                    <span class="px-3 py-1 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xs border border-gray-200/60 dark:border-slate-700 rounded-full text-[10px] font-bold text-gray-500 dark:text-gray-400 shadow-2xs uppercase tracking-wider">
                        {{ $msg->created_at->isToday() ? 'Hari Ini' : ($msg->created_at->isYesterday() ? 'Kemarin' : $msg->created_at->isoFormat('D MMMM Y')) }}
                    </span>
                </div>
                @php $lastDate = $msgDate; @endphp
            @endif

            {{-- Message Bubble --}}
            <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} message-item"
                 data-message-id="{{ $msg->id }}"
                 data-is-me="{{ $isMe ? '1' : '0' }}"
                 data-is-deleted="{{ $isDeleted ? '1' : '0' }}">
                <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl px-3.5 py-2.5 shadow-2xs relative group
                            {{ $isMe ? 'bg-[#d9fdd3] text-slate-800 rounded-tr-xs border border-[#bbf7d0]' : 'bg-white text-slate-800 rounded-tl-xs border border-gray-200/80' }}">

                    {{-- Header Nama Pengirim (Jika Lawan Bicara) --}}
                    @if(!$isMe && !$isDeleted)
                        @php
                            $senderRole = $msg->sender?->role;
                            $roleBadge = match($senderRole) {
                                'guru'     => ['color' => 'text-blue-700', 'label' => 'Wali Kelas', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                'orangtua' => ['color' => 'text-amber-700', 'label' => 'Orang Tua', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                'siswa'    => ['color' => 'text-emerald-700', 'label' => 'Siswa', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                default    => ['color' => 'text-gray-700', 'label' => 'User', 'bg' => 'bg-gray-50 text-gray-700 border-gray-200'],
                            };
                        @endphp
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="text-xs font-bold {{ $roleBadge['color'] }}">
                                {{ $msg->sender?->name ?? 'Pengguna' }}
                            </span>
                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-md border {{ $roleBadge['bg'] }}">
                                {{ $roleBadge['label'] }}
                            </span>
                        </div>
                    @endif

                    {{-- Isi Pesan --}}
                    @if($isDeleted)
                        <p class="text-xs italic text-gray-400 flex items-center gap-1 select-none">
                            <span>🚫</span> <span>Pesan ini telah ditarik</span>
                        </p>
                    @else
                        @if($msg->message)
                            <p class="text-xs sm:text-sm whitespace-pre-wrap break-words leading-relaxed select-text msg-content">{{ $msg->message }}</p>
                        @endif

                        {{-- Lampiran (Jika Ada) --}}
                        @if($msg->attachment_path)
                            @php
                                $attUrl = Storage::url($msg->attachment_path);
                            @endphp
                            <div class="mt-2 pt-1 border-t border-black/5">
                                @if($msg->attachment_type === 'image')
                                    <div class="block rounded-lg overflow-hidden border border-gray-200 cursor-pointer hover:opacity-95 transition-opacity"
                                         onclick="openImagePreview('{{ $attUrl }}', '{{ addslashes($msg->attachment_name ?: 'Foto Lampiran') }}')">
                                        <img src="{{ $attUrl }}" alt="Lampiran" class="max-h-60 w-auto rounded-lg object-contain">
                                    </div>
                                @else
                                    <a href="{{ $attUrl }}" target="_blank" download
                                       class="inline-flex items-center gap-2 p-2 rounded-xl bg-black/5 hover:bg-black/10 text-xs font-semibold text-slate-700 transition-colors">
                                        <span>📎</span>
                                        <span class="truncate max-w-[200px]">{{ $msg->attachment_name ?: 'Download Berkas' }}</span>
                                        <span>⬇️</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- Waktu, Tombol Tarik Pesan, dan Centang Status --}}
                    <div class="flex items-center justify-end gap-1.5 mt-1 text-[10px] text-gray-500 select-none">
                        {{-- Tombol Tarik Pesan (Khusus pesan pengirim sendiri yang belum dihapus) --}}
                        @if($isMe && $canDelete && !$isDeleted)
                            <button type="button"
                                    class="btn-delete-msg opacity-0 group-hover:opacity-100 hover:text-rose-600 transition-opacity p-0.5"
                                    onclick="confirmDeleteMessage({{ $msg->id }})"
                                    title="Tarik Pesan">
                                <svg class="w-3 h-3 text-gray-400 hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        @endif

                        <span>{{ $msg->created_at->format('H:i') }}</span>

                        {{-- Status Centang (Khusus Pengirim) --}}
                        @if($isMe && !$isDeleted)
                            <span class="read-status-icon cursor-pointer hover:scale-110 transition-transform"
                                  data-msg-id="{{ $msg->id }}"
                                  onclick="showReadDetails({{ $msg->id }})"
                                  title="Klik untuk melihat siapa yang sudah membaca">
                                @if($readStatus === 'sent')
                                    {{-- Centang 1 Abu-abu (Terkirim ke Server) --}}
                                    <svg class="w-3.5 h-3.5 text-gray-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    {{-- Centang 2 Biru (Sudah Dibaca Lawan Bicara) --}}
                                    <span class="inline-flex items-center text-[#53bdeb]">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 16 15" fill="none">
                                            <path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z" fill="currentColor"/>
                                        </svg>
                                    </span>
                                @endif
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        @empty
            <div id="emptyChatMessage" class="py-12 text-center text-gray-400 space-y-2">
                <div class="text-4xl">👋</div>
                <p class="text-sm font-bold text-gray-600">Belum ada percakapan</p>
                <p class="text-xs text-gray-400">Kirim pesan pertama Anda untuk memulai komunikasi dengan Wali Kelas & Orang Tua.</p>
            </div>
        @endforelse

    </div>

    {{-- ─── 3. Template Pesan Cepat (Quick Suggestions) ────────────────── --}}
    <div class="px-3 py-1.5 bg-slate-100/80 border-t border-gray-200/80 flex items-center gap-1.5 overflow-x-auto no-scrollbar shrink-0 select-none">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider shrink-0 flex items-center gap-1">
            <span>⚡</span> Cepat:
        </span>
        <button type="button" class="quick-chip text-[11px] px-2.5 py-1 rounded-full bg-white hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 border border-gray-200 hover:border-emerald-300 font-medium whitespace-nowrap shadow-2xs transition-all"
                data-text="Izin menginformasikan putra/putri kami sakit hari ini dan tidak dapat hadir ke sekolah.">
            🩺 Izin Sakit
        </button>
        <button type="button" class="quick-chip text-[11px] px-2.5 py-1 rounded-full bg-white hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 border border-gray-200 hover:border-emerald-300 font-medium whitespace-nowrap shadow-2xs transition-all"
                data-text="Selamat pagi/siang Bapak/Ibu Wali Kelas, mohon informasi terkait perkembangan belajar siswa di kelas.">
            📖 Info Belajar
        </button>
        <button type="button" class="quick-chip text-[11px] px-2.5 py-1 rounded-full bg-white hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 border border-gray-200 hover:border-emerald-300 font-medium whitespace-nowrap shadow-2xs transition-all"
                data-text="Izin bertanya terkait jadwal kegiatan dan tugas sekolah yang perlu dipersiapkan.">
            📋 Jadwal & Tugas
        </button>
        <button type="button" class="quick-chip text-[11px] px-2.5 py-1 rounded-full bg-white hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 border border-gray-200 hover:border-emerald-300 font-medium whitespace-nowrap shadow-2xs transition-all"
                data-text="Terima kasih banyak atas perhatian dan bimbingannya, Bapak/Ibu Wali Kelas. 🙏">
            🙏 Terima Kasih
        </button>
    </div>

    {{-- ─── 4. Form Input Pesan ────────────────────────────────────────── --}}
    <div class="p-3 bg-slate-50 border-t border-gray-200 shrink-0">
        {{-- Preview Attachment yang dipilih --}}
        <div id="attachmentPreview" class="hidden mb-2 p-2 bg-white rounded-xl border border-emerald-200 flex items-center justify-between gap-2 shadow-2xs">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-lg">📎</span>
                <span id="attachmentFileName" class="text-xs font-bold text-gray-700 truncate max-w-[240px]">nama_file.jpg</span>
            </div>
            <button type="button" id="btnRemoveAttachment" class="w-6 h-6 rounded-full bg-gray-100 hover:bg-rose-100 text-gray-500 hover:text-rose-600 flex items-center justify-center text-xs font-bold transition-colors">
                ✕
            </button>
        </div>

        <form id="chatSendForm" action="{{ route(auth()->user()->role . '.chat.send', $room->id) }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-2">
            @csrf

            {{-- Tombol Lampiran --}}
            <label class="w-10 h-10 rounded-2xl bg-white hover:bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-500 hover:text-emerald-600 cursor-pointer transition-colors shrink-0 shadow-2xs"
                   title="Lampirkan foto atau dokumen">
                <input type="file" id="attachmentInput" name="attachment" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
            </label>

            {{-- Input Teks Pesan --}}
            <div class="flex-1 relative">
                <textarea id="chatInputMessage" name="message" rows="1"
                          placeholder="Ketik pesan..."
                          class="w-full bg-white border border-gray-200 rounded-2xl px-4 py-2.5 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none max-h-24 shadow-2xs transition-all leading-normal"></textarea>
            </div>

            {{-- Tombol Kirim --}}
            <button type="submit" id="btnSendMessage"
                    class="w-10 h-10 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center transition-all shrink-0 shadow-md hover:scale-105 active:scale-95 disabled:opacity-50">
                <svg class="w-5 h-5 -rotate-45 relative left-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </form>
    </div>

</div>

{{-- ─── 5. Modal Rincian "Dibaca Oleh" ──────────────────────────────── --}}
<div id="readDetailsModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-5 shadow-2xl space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                <span class="text-[#53bdeb] text-base">✓✓</span>
                <span>Rincian Pembaca Pesan</span>
            </h3>
            <button type="button" onclick="closeReadDetailsModal()" class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center text-xs font-bold transition-colors">
                ✕
            </button>
        </div>

        <div id="readDetailsList" class="space-y-2.5 max-h-60 overflow-y-auto">
            {{-- Content di-inject lewat JavaScript --}}
        </div>

        <button type="button" onclick="closeReadDetailsModal()"
                class="w-full py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors">
            Tutup
        </button>
    </div>
</div>

{{-- ─── 6. Modal Lightbox Pratinjau Foto ─────────────────────────────── --}}
<div id="chatImageLightbox" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex flex-col items-center justify-center p-4" onclick="closeImagePreview()">
    <div class="relative max-w-3xl max-h-[90vh] flex flex-col items-center" onclick="event.stopPropagation()">
        <img id="lightboxImg" src="" alt="Pratinjau" class="max-h-[80vh] w-auto rounded-2xl shadow-2xl object-contain border border-white/20">
        <div class="flex items-center justify-between w-full mt-3 text-white px-2">
            <span id="lightboxCaption" class="text-xs truncate max-w-xs font-medium"></span>
            <div class="flex items-center gap-3">
                <a id="lightboxDownloadBtn" href="" download class="text-xs px-3 py-1.5 rounded-lg bg-white/20 hover:bg-white/30 text-white font-semibold transition-colors flex items-center gap-1.5">
                    <span>⬇️ Unduh Foto</span>
                </a>
                <button type="button" onclick="closeImagePreview()" class="text-xs px-3 py-1.5 rounded-lg bg-rose-500/80 hover:bg-rose-500 text-white font-semibold transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Global variable helpers
    let soundEnabled = true;
    const currentUserId = {{ auth()->id() }};
    const pollUrl = "{{ route(auth()->user()->role . '.chat.messages', $room->id) }}";
    const deleteRouteBase = "{{ url(auth()->user()->role . '/chat/messages') }}";
    const readsRouteBase = "{{ url(auth()->user()->role . '/chat/messages') }}";

    // 1. Audio Synthesizer (Web Audio API pop / ting - Tanpa perlu file eksternal)
    function playNotificationSound() {
        if (!soundEnabled) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime); // nada A5
            osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.12); // naik ke E6

            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch (e) {
            console.log('Audio not allowed yet:', e);
        }
    }

    // Toggle Sound Button
    const btnToggleSound = document.getElementById('btnToggleSound');
    const soundIcon = document.getElementById('soundIcon');
    if (btnToggleSound && soundIcon) {
        btnToggleSound.addEventListener('click', function () {
            soundEnabled = !soundEnabled;
            soundIcon.textContent = soundEnabled ? '🔔' : '🔕';
            btnToggleSound.title = soundEnabled ? 'Suara Notifikasi Aktif' : 'Suara Notifikasi Dibisukan';
        });
    }

    // 2. Image Lightbox
    function openImagePreview(url, caption) {
        document.getElementById('lightboxImg').src = url;
        document.getElementById('lightboxCaption').textContent = caption;
        document.getElementById('lightboxDownloadBtn').href = url;
        document.getElementById('chatImageLightbox').classList.remove('hidden');
    }
    function closeImagePreview() {
        document.getElementById('chatImageLightbox').classList.add('hidden');
    }

    // 3. Modal Read Details
    async function showReadDetails(messageId) {
        const modal = document.getElementById('readDetailsModal');
        const listEl = document.getElementById('readDetailsList');
        listEl.innerHTML = '<p class="text-xs text-gray-400 py-4 text-center">Memuat rincian...</p>';
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`${readsRouteBase}/${messageId}/reads`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.read_details && data.read_details.length > 0) {
                let html = '';
                data.read_details.forEach(rd => {
                    html += `
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                            <div>
                                <p class="text-xs font-bold text-gray-900">${rd.user_name}</p>
                                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded-md border border-emerald-200">${rd.role}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-gray-700">${rd.read_at}</span>
                                <p class="text-[9px] text-gray-400">${rd.read_date}</p>
                            </div>
                        </div>
                    `;
                });
                listEl.innerHTML = html;
            } else {
                listEl.innerHTML = `
                    <div class="text-center py-6 space-y-1">
                        <span class="text-2xl">⏳</span>
                        <p class="text-xs font-bold text-gray-700">Belum Ada yang Membaca</p>
                        <p class="text-[11px] text-gray-400">Pesan ini belum dibuka oleh Wali Kelas maupun Orang Tua.</p>
                    </div>
                `;
            }
        } catch (e) {
            listEl.innerHTML = '<p class="text-xs text-rose-500 py-3 text-center">Gagal memuat rincian.</p>';
        }
    }
    function closeReadDetailsModal() {
        document.getElementById('readDetailsModal').classList.add('hidden');
    }

    // 4. Tarik Pesan (Delete for Everyone)
    async function confirmDeleteMessage(messageId) {
        if (!confirm('Apakah Anda yakin ingin menarik pesan ini? Pesan akan ditarik untuk semua anggota ruang obrolan.')) {
            return;
        }

        try {
            const res = await fetch(`${deleteRouteBase}/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                markMessageAsDeletedInDom(messageId);
            } else if (data.error) {
                alert(data.error);
            }
        } catch (e) {
            console.error('Gagal menarik pesan:', e);
        }
    }

    function markMessageAsDeletedInDom(messageId) {
        const item = document.querySelector(`.message-item[data-message-id="${messageId}"]`);
        if (!item) return;

        item.setAttribute('data-is-deleted', '1');
        const bubble = item.querySelector('.shadow-2xs');
        if (bubble) {
            bubble.innerHTML = `
                <p class="text-xs italic text-gray-400 flex items-center gap-1 select-none py-1">
                    <span>🚫</span> <span>Pesan ini telah ditarik</span>
                </p>
            `;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('chatMessagesContainer');
        const form = document.getElementById('chatSendForm');
        const inputMessage = document.getElementById('chatInputMessage');
        const attachmentInput = document.getElementById('attachmentInput');
        const attachmentPreview = document.getElementById('attachmentPreview');
        const attachmentFileName = document.getElementById('attachmentFileName');
        const btnRemoveAttachment = document.getElementById('btnRemoveAttachment');
        const btnSend = document.getElementById('btnSendMessage');

        // Scroll to bottom
        function scrollToBottom(smooth = false) {
            container.scrollTo({
                top: container.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }
        scrollToBottom();

        // Auto-grow textarea
        inputMessage.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Enter to send
        inputMessage.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });

        // Quick Suggestion Chips
        document.querySelectorAll('.quick-chip').forEach(chip => {
            chip.addEventListener('click', function () {
                const text = this.getAttribute('data-text');
                inputMessage.value = text;
                inputMessage.style.height = 'auto';
                inputMessage.style.height = (inputMessage.scrollHeight) + 'px';
                inputMessage.focus();
            });
        });

        // File Attachment handling
        attachmentInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                attachmentFileName.textContent = this.files[0].name;
                attachmentPreview.classList.remove('hidden');
            }
        });

        btnRemoveAttachment.addEventListener('click', function () {
            attachmentInput.value = '';
            attachmentPreview.classList.add('hidden');
        });

        // Pencarian Pesan Lokal
        const btnToggleSearch = document.getElementById('btnToggleSearch');
        const searchBarContainer = document.getElementById('searchBarContainer');
        const inputSearchChat = document.getElementById('inputSearchChat');
        const btnCloseSearch = document.getElementById('btnCloseSearch');
        const searchResultCount = document.getElementById('searchResultCount');

        btnToggleSearch.addEventListener('click', function () {
            searchBarContainer.classList.toggle('hidden');
            if (!searchBarContainer.classList.contains('hidden')) {
                inputSearchChat.focus();
            } else {
                clearSearchHighlight();
            }
        });

        btnCloseSearch.addEventListener('click', function () {
            searchBarContainer.classList.add('hidden');
            clearSearchHighlight();
        });

        function clearSearchHighlight() {
            inputSearchChat.value = '';
            searchResultCount.textContent = '';
            document.querySelectorAll('.message-item').forEach(el => {
                el.style.opacity = '1';
                el.classList.remove('ring-2', 'ring-emerald-400');
            });
        }

        inputSearchChat.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            if (!query) {
                clearSearchHighlight();
                return;
            }

            let matches = 0;
            let firstMatchEl = null;

            document.querySelectorAll('.message-item').forEach(el => {
                const text = el.textContent.toLowerCase();
                if (text.includes(query)) {
                    matches++;
                    el.style.opacity = '1';
                    el.classList.add('ring-2', 'ring-emerald-400', 'rounded-2xl');
                    if (!firstMatchEl) firstMatchEl = el;
                } else {
                    el.style.opacity = '0.35';
                    el.classList.remove('ring-2', 'ring-emerald-400');
                }
            });

            searchResultCount.textContent = `${matches} ditemukan`;

            if (firstMatchEl) {
                firstMatchEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        // Template HTML Centang 2 Biru
        const doubleBlueTickSvg = `
            <span class="inline-flex items-center text-[#53bdeb]" title="Sudah dibaca (Klik untuk rincian)">
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 15" fill="none">
                    <path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.063-.51z" fill="currentColor"/>
                </svg>
            </span>
        `;

        function getLastMessageId() {
            const items = document.querySelectorAll('.message-item');
            if (items.length === 0) return 0;
            const last = items[items.length - 1];
            return parseInt(last.getAttribute('data-message-id')) || 0;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Render bubble pesan baru
        function appendMessage(msg) {
            const emptyEl = document.getElementById('emptyChatMessage');
            if (emptyEl) emptyEl.remove();

            if (document.querySelector(`.message-item[data-message-id="${msg.id}"]`)) {
                return;
            }

            const isMe = msg.is_me;
            const isDeleted = msg.is_deleted;
            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = `flex ${isMe ? 'justify-end' : 'justify-start'} message-item`;
            bubbleDiv.setAttribute('data-message-id', msg.id);
            bubbleDiv.setAttribute('data-is-me', isMe ? '1' : '0');
            bubbleDiv.setAttribute('data-is-deleted', isDeleted ? '1' : '0');

            if (isDeleted) {
                bubbleDiv.innerHTML = `
                    <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl px-3.5 py-2 shadow-2xs relative
                                ${isMe ? 'bg-[#d9fdd3] text-slate-800 rounded-tr-xs border border-[#bbf7d0]' : 'bg-white text-slate-800 rounded-tl-xs border border-gray-200/80'}">
                        <p class="text-xs italic text-gray-400 flex items-center gap-1 select-none">
                            <span>🚫</span> <span>Pesan ini telah ditarik</span>
                        </p>
                    </div>
                `;
                container.appendChild(bubbleDiv);
                scrollToBottom(true);
                return;
            }

            let senderHeaderHtml = '';
            if (!isMe) {
                let badgeColor = 'text-gray-700';
                let badgeBg = 'bg-gray-50 text-gray-700 border-gray-200';
                if (msg.sender_role === 'Wali Kelas') {
                    badgeColor = 'text-blue-700';
                    badgeBg = 'bg-blue-50 text-blue-700 border-blue-200';
                } else if (msg.sender_role === 'Orang Tua') {
                    badgeColor = 'text-amber-700';
                    badgeBg = 'bg-amber-50 text-amber-700 border-amber-200';
                } else if (msg.sender_role === 'Siswa') {
                    badgeColor = 'text-emerald-700';
                    badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                }

                senderHeaderHtml = `
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-xs font-bold ${badgeColor}">${escapeHtml(msg.sender_name)}</span>
                        <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-md border ${badgeBg}">${msg.sender_role}</span>
                    </div>
                `;
            }

            let attachmentHtml = '';
            if (msg.attachment_url) {
                if (msg.attachment_type === 'image') {
                    attachmentHtml = `
                        <div class="mt-2 pt-1 border-t border-black/5">
                            <div class="block rounded-lg overflow-hidden border border-gray-200 cursor-pointer hover:opacity-95 transition-opacity"
                                 onclick="openImagePreview('${msg.attachment_url}', '${escapeHtml(msg.attachment_name || 'Foto Lampiran')}')">
                                <img src="${msg.attachment_url}" alt="Lampiran" class="max-h-60 w-auto rounded-lg object-contain">
                            </div>
                        </div>
                    `;
                } else {
                    attachmentHtml = `
                        <div class="mt-2 pt-1 border-t border-black/5">
                            <a href="${msg.attachment_url}" target="_blank" download class="inline-flex items-center gap-2 p-2 rounded-xl bg-black/5 hover:bg-black/10 text-xs font-semibold text-slate-700 transition-colors">
                                <span>📎</span>
                                <span class="truncate max-w-[200px]">${escapeHtml(msg.attachment_name || 'Download Berkas')}</span>
                                <span>⬇️</span>
                            </a>
                        </div>
                    `;
                }
            }

            let deleteBtnHtml = '';
            if (isMe && msg.can_delete) {
                deleteBtnHtml = `
                    <button type="button" class="btn-delete-msg opacity-0 group-hover:opacity-100 hover:text-rose-600 transition-opacity p-0.5"
                            onclick="confirmDeleteMessage(${msg.id})" title="Tarik Pesan">
                        <svg class="w-3 h-3 text-gray-400 hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                `;
            }

            let statusTickHtml = '';
            if (isMe) {
                if (msg.read_status === 'sent') {
                    statusTickHtml = `
                        <span class="read-status-icon cursor-pointer hover:scale-110 transition-transform" data-msg-id="${msg.id}" onclick="showReadDetails(${msg.id})" title="Terkirim (Klik untuk rincian)">
                            <svg class="w-3.5 h-3.5 text-gray-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    `;
                } else {
                    statusTickHtml = `
                        <span class="read-status-icon cursor-pointer hover:scale-110 transition-transform" data-msg-id="${msg.id}" onclick="showReadDetails(${msg.id})" title="Sudah dibaca (Klik untuk rincian)">
                            ${doubleBlueTickSvg}
                        </span>
                    `;
                }
            }

            bubbleDiv.innerHTML = `
                <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl px-3.5 py-2.5 shadow-2xs relative group
                            ${isMe ? 'bg-[#d9fdd3] text-slate-800 rounded-tr-xs border border-[#bbf7d0]' : 'bg-white text-slate-800 rounded-tl-xs border border-gray-200/80'}">
                    ${senderHeaderHtml}
                    ${msg.message ? `<p class="text-xs sm:text-sm whitespace-pre-wrap break-words leading-relaxed select-text msg-content">${escapeHtml(msg.message)}</p>` : ''}
                    ${attachmentHtml}
                    <div class="flex items-center justify-end gap-1.5 mt-1 text-[10px] text-gray-500 select-none">
                        ${deleteBtnHtml}
                        <span>${msg.time}</span>
                        ${statusTickHtml}
                    </div>
                </div>
            `;

            container.appendChild(bubbleDiv);
            scrollToBottom(true);

            // Putar audio lembut jika pesan dari orang lain
            if (!isMe) {
                playNotificationSound();
            }
        }

        // Form Submit
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const messageText = inputMessage.value.trim();
            const hasAttachment = attachmentInput.files && attachmentInput.files.length > 0;

            if (!messageText && !hasAttachment) return;

            btnSend.disabled = true;

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSend.disabled = false;
                if (data.success && data.message) {
                    appendMessage(data.message);
                    inputMessage.value = '';
                    inputMessage.style.height = 'auto';
                    attachmentInput.value = '';
                    attachmentPreview.classList.add('hidden');
                    inputMessage.focus();
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(err => {
                btnSend.disabled = false;
                console.error(err);
            });
        });

        // Polling Real-time (tiap 3 detik)
        let isPolling = false;
        async function pollMessages() {
            if (isPolling) return;
            isPolling = true;

            const afterId = getLastMessageId();

            try {
                const res = await fetch(`${pollUrl}?after_id=${afterId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                if (res.ok) {
                    const data = await res.json();

                    // 1. Tambahkan pesan baru
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => appendMessage(msg));
                    }

                    // 2. Update status centang
                    if (data.read_statuses) {
                        for (const [msgId, status] of Object.entries(data.read_statuses)) {
                            if (status === 'read' || status === 'read_all') {
                                const statusEl = document.querySelector(`.read-status-icon[data-msg-id="${msgId}"]`);
                                if (statusEl && !statusEl.innerHTML.includes('text-[#53bdeb]')) {
                                    statusEl.innerHTML = doubleBlueTickSvg;
                                }
                            }
                        }
                    }

                    // 3. Sinkronkan pesan-pesan yang baru ditarik lawan bicara
                    if (data.deleted_ids && data.deleted_ids.length > 0) {
                        data.deleted_ids.forEach(delId => {
                            const item = document.querySelector(`.message-item[data-message-id="${delId}"]`);
                            if (item && item.getAttribute('data-is-deleted') !== '1') {
                                markMessageAsDeletedInDom(delId);
                            }
                        });
                    }
                }
            } catch (err) {
                console.error('Polling error:', err);
            } finally {
                isPolling = false;
            }
        }

        const pollInterval = setInterval(pollMessages, 3000);
        window.addEventListener('beforeunload', () => clearInterval(pollInterval));
    });
</script>
@endpush
@endsection
