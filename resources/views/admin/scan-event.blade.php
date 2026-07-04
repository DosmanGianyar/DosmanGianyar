<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner QR — {{ $scanEvent->title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #camera-container { position: relative; width: 100%; max-width: 480px; aspect-ratio: 1/1; overflow: hidden; border-radius: 1rem; background: #000; }
        #camera-video { width: 100%; height: 100%; object-fit: cover; }
        #scan-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
        #scan-frame { width: 65%; height: 65%; border: 3px solid #34d399; border-radius: 12px; box-shadow: 0 0 0 9999px rgba(0,0,0,0.45); }
        #scan-line { position: absolute; width: 63%; height: 2px; background: linear-gradient(to right, transparent, #34d399, transparent); animation: scan 1.8s ease-in-out infinite; }
        @keyframes scan { 0%,100% { top: 18%; } 50% { top: 82%; } }
        .toast { transition: opacity 0.3s, transform 0.3s; }
        .toast.hide { opacity: 0; transform: translateY(-8px); }
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

    {{-- Toast Notification --}}
    <div id="toast" class="hidden toast rounded-xl p-4 text-sm font-medium shadow-lg"></div>

    {{-- Camera --}}
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
            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 rounded-xl transition">
            Mulai Kamera
        </button>
        <button id="btn-stop" onclick="stopCamera()"
            class="flex-1 hidden bg-gray-700 hover:bg-gray-600 text-white text-sm font-semibold py-2.5 rounded-xl transition">
            Hentikan
        </button>
        <button id="btn-flip" onclick="flipCamera()"
            class="px-4 bg-gray-800 hover:bg-gray-700 text-white text-sm font-semibold rounded-xl transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
    </div>

    {{-- Stats --}}
    <div class="bg-gray-900 rounded-xl p-4 flex items-center justify-between">
        <span class="text-gray-400 text-sm">Total Peserta Hadir</span>
        <span id="total-count" class="text-2xl font-bold text-emerald-400">{{ $attendances->count() }}</span>
    </div>

    {{-- Attendance List --}}
    <div>
        <h2 class="text-sm font-semibold text-gray-300 mb-3">Daftar Peserta</h2>
        <div id="attendance-list" class="space-y-2">
            @forelse($attendances as $a)
            <div class="attendance-row bg-gray-900 rounded-xl p-3 flex items-center gap-3" data-id="{{ $a->id }}">
                <div class="w-10 h-10 rounded-full bg-gray-700 overflow-hidden shrink-0">
                    @if($a->student?->photo_url)
                    <img src="{{ $a->student->photo_url }}" class="w-full h-full object-cover" alt="">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                        </svg>
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm truncate">{{ $a->student?->name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $a->student?->nis ?? '—' }} · {{ $a->student?->schoolClass?->name ?? '—' }}
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs text-gray-400">{{ $a->scanned_at->format('H:i:s') }}</p>
                    <button onclick="deleteAttendance(this)"
                        class="text-xs text-red-400 hover:text-red-300 mt-0.5 transition">Hapus</button>
                </div>
            </div>
            @empty
            <div id="empty-state" class="text-center py-8 text-gray-600 text-sm">
                Belum ada siswa yang diabsen.<br>Mulai kamera dan arahkan ke QR kartu pelajar.
            </div>
            @endforelse
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
        showToast('Tidak dapat mengakses kamera: ' + e.message, 'error');
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
            setTimeout(() => { scanCooldown = false; lastScanned = ''; }, 3000);
            sendScan(code.data);
        }
    }
    animFrame = requestAnimationFrame(tick);
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
            showToast('✓ ' + data.message, 'success');
            prependStudent(data.attendance_id, data.student, data.scanned_at);
            document.getElementById('total-count').textContent = data.total;
            removeEmptyState();
        } else if (data.status === 'duplicate') {
            showToast('⚠ ' + data.message, 'warning');
        } else {
            showToast('✗ ' + (data.message ?? 'Siswa tidak ditemukan'), 'error');
        }
    } catch (e) {
        showToast('Gagal menghubungi server.', 'error');
    }
}

function prependStudent(attendanceId, student, scannedAt) {
    const row = document.createElement('div');
    row.className = 'attendance-row bg-gray-900 rounded-xl p-3 flex items-center gap-3';
    row.dataset.id = attendanceId;
    row.innerHTML = `
        <div class="w-10 h-10 rounded-full bg-gray-700 overflow-hidden shrink-0">
            ${student.photo_url
                ? `<img src="${student.photo_url}" class="w-full h-full object-cover" alt="">`
                : `<div class="w-full h-full flex items-center justify-center text-gray-400">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                    </svg>
                   </div>`}
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm truncate">${student.name}</p>
            <p class="text-xs text-gray-400">${student.nis} · ${student.class}</p>
        </div>
        <div class="text-right shrink-0">
            <p class="text-xs text-gray-400">${scannedAt}</p>
            <button onclick="deleteAttendance(this)"
                class="text-xs text-red-400 hover:text-red-300 mt-0.5 transition">Hapus</button>
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
            showToast('Absen dihapus.', 'info');
            if (!document.querySelector('.attendance-row')) {
                document.getElementById('attendance-list').innerHTML =
                    `<div id="empty-state" class="text-center py-8 text-gray-600 text-sm">Belum ada siswa yang diabsen.</div>`;
            }
        } else {
            showToast('Gagal menghapus.', 'error');
        }
    } catch(e) {
        showToast('Gagal menghubungi server.', 'error');
    }
}

function removeEmptyState() {
    document.getElementById('empty-state')?.remove();
}

let toastTimer;
function showToast(msg, type) {
    const el = document.getElementById('toast');
    const colors = { success: 'bg-emerald-900 border border-emerald-600 text-emerald-200',
                     warning: 'bg-yellow-900 border border-yellow-600 text-yellow-200',
                     error:   'bg-red-900 border border-red-600 text-red-200',
                     info:    'bg-gray-800 border border-gray-600 text-gray-200' };
    el.className = `toast rounded-xl p-4 text-sm font-medium shadow-lg ${colors[type] ?? colors.info}`;
    el.textContent = msg;
    el.classList.remove('hidden', 'hide');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        el.classList.add('hide');
        setTimeout(() => el.classList.add('hidden'), 300);
    }, 3000);
}
</script>
</body>
</html>
