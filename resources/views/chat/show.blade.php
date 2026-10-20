@extends($layout)

@section('title', $room->name)
@section('page-title', 'Konsultasi: ' . ($room->student?->name ?? 'Chat'))

@section('content')
<div class="max-w-4xl mx-auto flex flex-col h-[calc(100vh-8.5rem)] sm:h-[calc(100vh-7.5rem)] bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">

    {{-- ─── 1. Header Chat ──────────────────────────────────────────────── --}}
    <div class="bg-slate-900 text-white px-4 py-3 shrink-0 flex items-center justify-between gap-3 shadow-md">
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
                <div class="flex items-center gap-1.5 text-[11px] text-slate-300 truncate">
                    <span>Anggota:</span>
                    @foreach($participants as $p)
                        @php
                            $roleIcon = match($p->role) {
                                'guru'     => '👨‍🏫 Wali',
                                'orangtua' => '👨‍👩‍👧 Ortu',
                                'siswa'    => '🎒 Siswa',
                                default    => '👤',
                            };
                        @endphp
                        <span class="font-medium text-slate-200">
                            {{ $p->user?->name ? Str::words($p->user->name, 1, '') : 'User' }} ({{ $roleIcon }}){{ !$loop->last ? ' ·' : '' }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Info Badge --}}
        <div class="hidden sm:flex items-center gap-1.5 bg-emerald-500/20 border border-emerald-400/30 px-3 py-1 rounded-full text-emerald-300 text-xs font-semibold shrink-0">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Segitiga Terbuka</span>
        </div>
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
            <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} message-item" data-message-id="{{ $msg->id }}">
                <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl px-3.5 py-2.5 shadow-2xs relative group
                            {{ $isMe ? 'bg-[#d9fdd3] text-slate-800 rounded-tr-xs border border-[#bbf7d0]' : 'bg-white text-slate-800 rounded-tl-xs border border-gray-200/80' }}">

                    {{-- Nama Pengirim (Jika Lawan Bicara) --}}
                    @if(!$isMe)
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
                    @if($msg->message)
                        <p class="text-xs sm:text-sm whitespace-pre-wrap break-words leading-relaxed select-text">{{ $msg->message }}</p>
                    @endif

                    {{-- Lampiran (Jika Ada) --}}
                    @if($msg->attachment_path)
                        @php
                            $attUrl = Storage::url($msg->attachment_path);
                        @endphp
                        <div class="mt-2 pt-1 border-t border-black/5">
                            @if($msg->attachment_type === 'image')
                                <a href="{{ $attUrl }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200">
                                    <img src="{{ $attUrl }}" alt="Lampiran" class="max-h-60 w-auto rounded-lg object-contain">
                                </a>
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

                    {{-- Waktu & Centang Status --}}
                    <div class="flex items-center justify-end gap-1 mt-1 text-[10px] text-gray-500 select-none">
                        <span>{{ $msg->created_at->format('H:i') }}</span>

                        @if($isMe)
                            <span class="read-status-icon" data-msg-id="{{ $msg->id }}">
                                @if($readStatus === 'sent')
                                    {{-- Centang 1 Abu-abu (Terkirim ke Server) --}}
                                    <svg class="w-3.5 h-3.5 text-gray-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Terkirim">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    {{-- Centang 2 Biru (Sudah Dibaca Lawan Bicara) --}}
                                    <span class="inline-flex items-center text-[#53bdeb]" title="Sudah dibaca">
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

    {{-- ─── 3. Form Input Pesan ────────────────────────────────────────── --}}
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roomId = {{ $room->id }};
        const pollUrl = "{{ route(auth()->user()->role . '.chat.messages', $room->id) }}";
        const sendUrl = "{{ route(auth()->user()->role . '.chat.send', $room->id) }}";
        const currentUserId = {{ auth()->id() }};

        const container = document.getElementById('chatMessagesContainer');
        const form = document.getElementById('chatSendForm');
        const inputMessage = document.getElementById('chatInputMessage');
        const attachmentInput = document.getElementById('attachmentInput');
        const attachmentPreview = document.getElementById('attachmentPreview');
        const attachmentFileName = document.getElementById('attachmentFileName');
        const btnRemoveAttachment = document.getElementById('btnRemoveAttachment');
        const btnSend = document.getElementById('btnSendMessage');

        // Scroll to bottom pada inisialisasi
        function scrollToBottom(smooth = false) {
            container.scrollTo({
                top: container.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }
        scrollToBottom();

        // Auto resize textarea
        inputMessage.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Enter untuk kirim (Shift+Enter untuk baris baru)
        inputMessage.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });

        // Attachment file handling
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

        // Ambil ID pesan terakhir yang ada di DOM
        function getLastMessageId() {
            const items = document.querySelectorAll('.message-item');
            if (items.length === 0) return 0;
            const last = items[items.length - 1];
            return parseInt(last.getAttribute('data-message-id')) || 0;
        }

        // Template HTML Centang 2 Biru
        const doubleBlueTickSvg = `
            <span class="inline-flex items-center text-[#53bdeb]" title="Sudah dibaca">
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 15" fill="none">
                    <path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z" fill="currentColor"/>
                </svg>
            </span>
        `;

        // Render bubble pesan baru
        function appendMessage(msg) {
            const emptyEl = document.getElementById('emptyChatMessage');
            if (emptyEl) emptyEl.remove();

            // Hindari duplikasi jika pesan sudah ada
            if (document.querySelector(`.message-item[data-message-id="${msg.id}"]`)) {
                return;
            }

            const isMe = msg.is_me;
            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = `flex ${isMe ? 'justify-end' : 'justify-start'} message-item`;
            bubbleDiv.setAttribute('data-message-id', msg.id);

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
                            <a href="${msg.attachment_url}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200">
                                <img src="${msg.attachment_url}" alt="Lampiran" class="max-h-60 w-auto rounded-lg object-contain">
                            </a>
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

            let statusTickHtml = '';
            if (isMe) {
                if (msg.read_status === 'sent') {
                    statusTickHtml = `
                        <span class="read-status-icon" data-msg-id="${msg.id}">
                            <svg class="w-3.5 h-3.5 text-gray-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Terkirim">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    `;
                } else {
                    statusTickHtml = `
                        <span class="read-status-icon" data-msg-id="${msg.id}">
                            ${doubleBlueTickSvg}
                        </span>
                    `;
                }
            }

            bubbleDiv.innerHTML = `
                <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl px-3.5 py-2.5 shadow-2xs relative group
                            ${isMe ? 'bg-[#d9fdd3] text-slate-800 rounded-tr-xs border border-[#bbf7d0]' : 'bg-white text-slate-800 rounded-tl-xs border border-gray-200/80'}">
                    ${senderHeaderHtml}
                    ${msg.message ? `<p class="text-xs sm:text-sm whitespace-pre-wrap break-words leading-relaxed select-text">${escapeHtml(msg.message)}</p>` : ''}
                    ${attachmentHtml}
                    <div class="flex items-center justify-end gap-1 mt-1 text-[10px] text-gray-500 select-none">
                        <span>${msg.time}</span>
                        ${statusTickHtml}
                    </div>
                </div>
            `;

            container.appendChild(bubbleDiv);
            scrollToBottom(true);
        }

        // Helper escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Kirim Pesan via AJAX
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const messageText = inputMessage.value.trim();
            const hasAttachment = attachmentInput.files && attachmentInput.files.length > 0;

            if (!messageText && !hasAttachment) return;

            btnSend.disabled = true;

            const formData = new FormData(form);

            fetch(sendUrl, {
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

                    // 1. Tambahkan pesan baru jika ada
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => appendMessage(msg));
                    }

                    // 2. Update status centang baca untuk pesan yang dikirim oleh saya
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
                }
            } catch (err) {
                console.error('Polling error:', err);
            } finally {
                isPolling = false;
            }
        }

        // Interval polling
        const pollInterval = setInterval(pollMessages, 3000);

        // Hentikan interval jika halaman ditutup
        window.addEventListener('beforeunload', () => clearInterval(pollInterval));
    });
</script>
@endpush
@endsection
