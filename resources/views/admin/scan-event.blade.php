<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner QR — {{ $scanEvent->title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #camera-container {
            position: relative;
            width: 100%;
            max-width: 480px;
            aspect-ratio: 1/1;
            overflow: hidden;
            border-radius: 1.25rem;
            background: #000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        #camera-video { width: 100%; height: 100%; object-fit: cover; }
        #scan-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
        #scan-frame {
            width: 68%;
            height: 68%;
            border: 3px solid #38bdf8;
            border-radius: 1.25rem;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.55), 0 0 20px rgba(56, 189, 248, 0.4);
        }
        #scan-line {
            position: absolute;
            width: 65%;
            height: 3px;
            background: linear-gradient(to right, transparent, #38bdf8, transparent);
            box-shadow: 0 0 10px #38bdf8;
            animation: scan 1.8s ease-in-out infinite;
        }
        @keyframes scan { 0%,100% { top: 18%; } 50% { top: 80%; } }

        /* Result Card Pop animation */
        .result-card-anim {
            animation: cardPop 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        @keyframes cardPop {
            0% { opacity: 0; transform: scale(0.88) translateY(12px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Progress bar animation */
        @keyframes timerProgress {
            from { width: 100%; }
            to { width: 0%; }
        }
        .timer-bar {
            animation: timerProgress 3.2s linear forwards;
        }
    </style>
</head>
<body class="bg-gray-950 min-h-screen text-white font-sans">

{{-- Top Bar --}}
<div class="flex items-center gap-3 px-4 py-3 bg-gray-900 border-b border-gray-800">
    <a href="{{ url()->previous() }}" class="text-gray-400 hover:text-white transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div class="flex-1 min-w-0">
        <p class="text-xs text-gray-400">Absensi QR Kegiatan</p>
        <h1 class="font-bold text-sm truncate">{{ $scanEvent->title }}</h1>
    </div>
    <div class="text-right shrink-0">
        <p class="text-xs text-gray-400">{{ $scanEvent->date->translatedFormat('d M Y') }}</p>
        @if($scanEvent->location)
        <p class="text-xs text-emerald-400">{{ $scanEvent->location }}</p>
        @endif
    </div>
</div>

<div class="max-w-xl mx-auto px-4 py-5 space-y-5">

    {{-- Camera Viewport --}}
    <div class="flex justify-center">
        <div id="camera-container">
            <video id="camera-video" autoplay muted playsinline></video>
            <canvas id="camera-canvas" class="hidden"></canvas>
            <div id="scan-overlay">
                <div id="scan-frame"></div>
                <div id="scan-line"></div>
            </div>
        </div>
    </div>

    {{-- Camera Controls --}}
    <div class="flex gap-3">
        <button id="btn-start" onclick="startCamera()"
            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition shadow-lg flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Mulai Kamera
        </button>
        <button id="btn-stop" onclick="stopCamera()"
            class="flex-1 hidden bg-gray-800 hover:bg-gray-700 text-white text-sm font-semibold py-2.5 rounded-xl transition border border-gray-700">
            Hentikan Kamera
        </button>
        <button id="btn-flip" onclick="flipCamera()"
            class="px-4 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl border border-gray-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
    </div>

    {{-- Manual Input Fallback --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-3.5 flex gap-2">
        <input type="text" id="manual-code-input" placeholder="Ketik NISN / NIS / Token Kartu Siswa..."
            onkeydown="if(event.key==='Enter') submitManualCode()"
            class="flex-1 px-3.5 py-2.5 bg-gray-950 border border-gray-800 rounded-xl text-xs text-white placeholder-gray-500 focus:outline-none focus:border-blue-500">
        <button type="button" onclick="submitManualCode()" class="px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white font-bold text-xs rounded-xl border border-gray-700 transition shrink-0">
            Cari / Absen
        </button>
    </div>

    {{-- Stats --}}
    <div class="bg-gray-900 rounded-2xl p-4 flex items-center justify-between border border-gray-800">
        <span class="text-gray-400 text-sm font-medium">Total Peserta Hadir</span>
        <span id="total-count" class="text-2xl font-black text-emerald-400">{{ $attendances->count() }}</span>
    </div>

    {{-- Attendance List --}}
    <div>
        <h2 class="text-sm font-semibold text-gray-300 mb-3">Daftar Peserta Hadir</h2>
        <div id="attendance-list" class="space-y-2">
            @forelse($attendances as $a)
            <div class="attendance-row bg-gray-900 border border-gray-800/80 rounded-2xl p-3.5 flex items-center gap-3.5 shadow-sm" data-id="{{ $a->id }}">
                <div class="w-11 h-11 rounded-full bg-gray-800 overflow-hidden ring-2 ring-gray-700 shrink-0 flex items-center justify-center">
                    @if($a->student?->photo_url)
                    <img src="{{ $a->student->photo_url }}" class="w-full h-full object-cover" alt="">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                        </svg>
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm text-white truncate">{{ $a->student?->name ?? '—' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        NIS: {{ $a->student?->nis ?? '—' }} · Kelas {{ $a->student?->schoolClass?->name ?? '—' }}
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <span class="inline-block px-2.5 py-1 bg-emerald-950/80 text-emerald-400 border border-emerald-800/60 rounded-full text-[11px] font-bold">
                        ⏱️ {{ $a->scanned_at->format('H:i:s') }}
                    </span>
                    <div class="mt-1">
                        <button onclick="deleteAttendance(this)"
                            class="text-xs text-red-400 hover:text-red-300 transition font-medium">Hapus</button>
                    </div>
                </div>
            </div>
            @empty
            <div id="empty-state" class="text-center py-10 text-gray-500 text-sm bg-gray-900/50 border border-gray-800/60 rounded-2xl">
                Belum ada siswa yang diabsen.<br>Mulai kamera dan arahkan ke QR/Barcode Kartu Pelajar.
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Scan Result Popup Modal Overlay (Hasil Scan Mobile-Style Card) --}}
<div id="result-overlay" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div id="result-card-body" class="bg-gray-900 border border-gray-800 w-full max-w-sm rounded-3xl overflow-hidden shadow-2xl result-card-anim text-center relative">
        
        {{-- Header Status Banner --}}
        <div id="result-header-bg" class="py-4 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black text-sm tracking-wide uppercase flex items-center justify-center gap-2 shadow-sm">
            <span id="result-header-icon" class="text-base">✓</span>
            <span id="result-header-text">ABSENSI BERHASIL!</span>
        </div>

        <div class="p-6 space-y-4">
            {{-- Student Avatar --}}
            <div class="relative w-20 h-20 mx-auto">
                <div id="result-photo-wrap" class="w-20 h-20 rounded-full bg-gray-800 overflow-hidden ring-4 ring-emerald-500/40 shadow-xl flex items-center justify-center mx-auto">
                    <img id="result-photo-img" src="" class="w-full h-full object-cover hidden" alt="">
                    <div id="result-photo-icon" class="text-gray-400">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Student Details --}}
            <div class="space-y-1">
                <h3 id="result-student-name" class="text-lg font-extrabold text-white leading-tight">Nama Siswa</h3>
                <p id="result-student-info" class="text-xs font-medium text-gray-400">NIS · Kelas</p>
            </div>

            {{-- Badges --}}
            <div class="flex items-center justify-center gap-2 flex-wrap text-xs font-bold pt-1">
                <span id="result-badge-class" class="px-3 py-1 bg-blue-950/80 text-blue-400 border border-blue-800/60 rounded-full">
                    🏫 —
                </span>
                <span id="result-badge-time" class="px-3 py-1 bg-gray-800 text-gray-300 border border-gray-700 rounded-full">
                    ⏱️ —
                </span>
            </div>

            {{-- Action & Progress Timer --}}
            <div class="pt-3">
                <button type="button" onclick="closeResultModal()" class="w-full py-2.5 bg-gray-800 hover:bg-gray-700 text-white font-bold text-xs rounded-xl border border-gray-700 transition">
                    Lanjut Scan
                </button>
                <div class="w-full h-1 bg-gray-800 rounded-full overflow-hidden mt-3">
                    <div id="result-timer-bar" class="h-full bg-emerald-500"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const SCAN_URL   = "{{ route('admin.scan-events.scan', $scanEvent) }}";
const LIST_URL   = "{{ route('admin.scan-events.list', $scanEvent) }}";
const DEL_BASE   = "{{ url('admin/scan-events/' . $scanEvent->id . '/attendances') }}";
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

let stream = null;
let animFrame = null;
let facingMode = 'environment';
let lastScanned = '';
let scanCooldown = false;

// jsQR loaded dynamically
const script = document.createElement('script');
script.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
document.head.appendChild(script);

function playScanSound(type = 'success') {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);

        if (type === 'success') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
            osc.start();
            osc.stop(ctx.currentTime + 0.2);
        } else if (type === 'duplicate') {
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(440, ctx.currentTime);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
        } else {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(300, ctx.currentTime);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        }
    } catch(e) {}
}

let resultTimer = null;

function showResultModal(type, title, student, scannedAt) {
    playScanSound(type);

    const overlay     = document.getElementById('result-overlay');
    const headerBg    = document.getElementById('result-header-bg');
    const headerIcon  = document.getElementById('result-header-icon');
    const headerText  = document.getElementById('result-header-text');
    const nameEl      = document.getElementById('result-student-name');
    const infoEl      = document.getElementById('result-student-info');
    const badgeClass  = document.getElementById('result-badge-class');
    const badgeTime   = document.getElementById('result-badge-time');
    const photoImg    = document.getElementById('result-photo-img');
    const photoIcon   = document.getElementById('result-photo-icon');
    const photoWrap   = document.getElementById('result-photo-wrap');
    const timerBar    = document.getElementById('result-timer-bar');

    if (type === 'success') {
        headerBg.className   = 'py-3.5 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black text-sm uppercase flex items-center justify-center gap-2 tracking-wide';
        headerIcon.textContent = '✓';
        headerText.textContent = 'ABSENSI BERHASIL!';
        photoWrap.className  = 'w-20 h-20 rounded-full bg-gray-800 overflow-hidden ring-4 ring-emerald-500/40 shadow-xl flex items-center justify-center mx-auto';
        timerBar.className   = 'h-full bg-emerald-500 timer-bar';
    } else if (type === 'duplicate') {
        headerBg.className   = 'py-3.5 px-6 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-black text-sm uppercase flex items-center justify-center gap-2 tracking-wide';
        headerIcon.textContent = '⚠';
        headerText.textContent = 'SUDAH DISCANNED';
        photoWrap.className  = 'w-20 h-20 rounded-full bg-gray-800 overflow-hidden ring-4 ring-amber-500/40 shadow-xl flex items-center justify-center mx-auto';
        timerBar.className   = 'h-full bg-amber-500 timer-bar';
    } else {
        headerBg.className   = 'py-3.5 px-6 bg-gradient-to-r from-red-600 to-rose-600 text-white font-black text-sm uppercase flex items-center justify-center gap-2 tracking-wide';
        headerIcon.textContent = '✕';
        headerText.textContent = 'TIDAK DITEMUKAN';
        photoWrap.className  = 'w-20 h-20 rounded-full bg-gray-800 overflow-hidden ring-4 ring-red-500/40 shadow-xl flex items-center justify-center mx-auto';
        timerBar.className   = 'h-full bg-red-500 timer-bar';
    }

    if (student) {
        nameEl.textContent = student.name || 'Siswa';
        infoEl.textContent = `NIS: ${student.nis || '—'} · Kelas: ${student.class || '—'}`;
        badgeClass.textContent = `🏫 Kelas ${student.class || '—'}`;
        if (student.photo_url) {
            photoImg.src = student.photo_url;
            photoImg.classList.remove('hidden');
            photoIcon.classList.add('hidden');
        } else {
            photoImg.classList.add('hidden');
            photoIcon.classList.remove('hidden');
        }
    } else {
        nameEl.textContent = title || 'Tidak Ditemukan';
        infoEl.textContent = 'Kode barcode/QR tidak terdaftar.';
        badgeClass.textContent = '🏫 —';
        photoImg.classList.add('hidden');
        photoIcon.classList.remove('hidden');
    }

    badgeTime.textContent = `⏱️ ${scannedAt || new Date().toLocaleTimeString('id-ID')}`;

    overlay.classList.remove('hidden');

    // Restart timer progress bar animation
    timerBar.style.animation = 'none';
    timerBar.offsetHeight; /* trigger reflow */
    timerBar.style.animation = null;

    clearTimeout(resultTimer);
    resultTimer = setTimeout(() => {
        closeResultModal();
    }, 3200);
}

function closeResultModal() {
    clearTimeout(resultTimer);
    document.getElementById('result-overlay').classList.add('hidden');
}

async function startCamera() {
    try {
        if (stream) stopCamera();
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode, width: { ideal: 1280 }, height: { ideal: 720 } } });
        const video = document.getElementById('camera-video');
        video.srcObject = stream;
        await video.play();
        document.getElementById('btn-start').classList.add('hidden');
        document.getElementById('btn-stop').classList.remove('hidden');
        tick();
    } catch(e) {
        showResultModal('error', 'Tidak dapat mengakses kamera: ' + e.message, null, null);
    }
}

function stopCamera() {
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
    document.getElementById('btn-start').classList.remove('hidden');
    document.getElementById('btn-stop').classList.add('hidden');
}

function flipCamera() {
    facingMode = facingMode === 'environment' ? 'user' : 'environment';
    if (stream) startCamera();
}

function tick() {
    const video  = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    if (video.readyState === video.HAVE_ENOUGH_DATA && typeof jsQR !== 'undefined') {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imgData.data, imgData.width, imgData.height, { inversionAttempts: 'dontInvert' });
        if (code && code.data && !scanCooldown && code.data !== lastScanned) {
            lastScanned = code.data;
            scanCooldown = true;
            setTimeout(() => { scanCooldown = false; lastScanned = ''; }, 3200);
            sendScan(code.data);
        }
    }
    animFrame = requestAnimationFrame(tick);
}

function submitManualCode() {
    const input = document.getElementById('manual-code-input');
    const val = input.value.trim();
    if (val) {
        sendScan(val);
        input.value = '';
    }
}

async function sendScan(identifier) {
    try {
        const res  = await fetch(SCAN_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ identifier }),
        });
        const data = await res.json();
        if (data.status === 'success') {
            showResultModal('success', data.message, data.student, data.scanned_at);
            prependStudent(data.attendance_id, data.student, data.scanned_at);
            document.getElementById('total-count').textContent = data.total;
            removeEmptyState();
        } else if (data.status === 'duplicate') {
            showResultModal('duplicate', data.message, data.student, data.scanned_at);
        } else {
            showResultModal('error', data.message ?? 'Siswa tidak ditemukan', null, null);
        }
    } catch (e) {
        showResultModal('error', 'Gagal terhubung ke server', null, null);
    }
}

function prependStudent(attendanceId, student, scannedAt) {
    const row = document.createElement('div');
    row.className = 'attendance-row bg-gray-900 border border-gray-800/80 rounded-2xl p-3.5 flex items-center gap-3.5 shadow-sm';
    row.dataset.id = attendanceId;
    row.innerHTML = `
        <div class="w-11 h-11 rounded-full bg-gray-800 overflow-hidden ring-2 ring-gray-700 shrink-0 flex items-center justify-center">
            ${student.photo_url
                ? `<img src="${student.photo_url}" class="w-full h-full object-cover" alt="">`
                : `<div class="w-full h-full flex items-center justify-center text-gray-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                    </svg>
                   </div>`}
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-sm text-white truncate">${student.name}</p>
            <p class="text-xs text-gray-400 mt-0.5">NIS: ${student.nis} · Kelas ${student.class}</p>
        </div>
        <div class="text-right shrink-0">
            <span class="inline-block px-2.5 py-1 bg-emerald-950/80 text-emerald-400 border border-emerald-800/60 rounded-full text-[11px] font-bold">
                ⏱️ ${scannedAt}
            </span>
            <div class="mt-1">
                <button onclick="deleteAttendance(this)"
                    class="text-xs text-red-400 hover:text-red-300 transition font-medium">Hapus</button>
            </div>
        </div>`;
    const list = document.getElementById('attendance-list');
    list.prepend(row);
}

async function deleteAttendance(btn) {
    if (!confirm('Hapus absen siswa ini?')) return;
    const row = btn.closest('.attendance-row');
    const rowId = row.dataset.id;

    try {
        const res = await fetch(`${DEL_BASE}/${rowId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            row.remove();
            document.getElementById('total-count').textContent = data.total;
            if (!document.querySelector('.attendance-row')) {
                document.getElementById('attendance-list').innerHTML =
                    `<div id="empty-state" class="text-center py-10 text-gray-500 text-sm bg-gray-900/50 border border-gray-800/60 rounded-2xl">Belum ada siswa yang diabsen.</div>`;
            }
        } else {
            showResultModal('error', 'Gagal menghapus.', null, null);
        }
    } catch(e) {
        showResultModal('error', 'Gagal terhubung ke server.', null, null);
    }
}

function removeEmptyState() {
    document.getElementById('empty-state')?.remove();
}
</script>
</body>
</html>
