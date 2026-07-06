@extends('layouts.siswa')

@section('title', isset($isCheckOut) && $isCheckOut ? 'Absen Pulang' : 'Presensi')
@section('page-title', isset($isCheckOut) && $isCheckOut ? 'Absen Pulang' : 'Presensi Selfie')

@section('content')

@if($isClosed ?? false)
{{-- Presensi sudah ditutup --}}
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h2 class="text-lg font-bold text-gray-800 mb-2">Presensi Sudah Ditutup</h2>
    <p class="text-sm text-gray-500 mb-6">Absen masuk sudah tidak bisa dilakukan (lewat pukul {{ $checkInClose ?? '08:00' }})</p>
    <a href="{{ route('siswa.dashboard') }}"
        class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium">
        Kembali ke Beranda
    </a>
</div>

@elseif($notYetOpen ?? false)
{{-- Presensi belum dibuka --}}
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h2 class="text-lg font-bold text-gray-800 mb-2">Presensi Belum Dibuka</h2>
    <p class="text-sm text-gray-500 mb-6">Absen masuk baru bisa dilakukan mulai pukul <strong>{{ $checkInOpen ?? '06:00' }}</strong></p>
    <a href="{{ route('siswa.dashboard') }}"
        class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium">
        Kembali ke Beranda
    </a>
</div>

@elseif(($checkOutTooEarly ?? false))
{{-- Absen pulang belum dibuka --}}
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
    </div>
    <h2 class="text-lg font-bold text-gray-800 mb-2">Absen Pulang Belum Dibuka</h2>
    <p class="text-sm text-gray-500 mb-6">Absen pulang baru bisa dilakukan mulai pukul <strong>{{ $checkOutOpen ?? '13:00' }}</strong></p>
    <a href="{{ route('siswa.dashboard') }}"
        class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium mb-3">
        Kembali ke Beranda
    </a>
    <a href="{{ route('siswa.early-checkout.create') }}"
        class="flex items-center gap-2 px-5 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        Ajukan Izin Pulang Lebih Awal
    </a>
</div>

@else
{{-- Halaman Selfie --}}
<div class="max-w-sm mx-auto space-y-4">

    @if(isset($hasEarlyApproval) && $hasEarlyApproval)
    {{-- Banner izin pulang awal disetujui --}}
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3 flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-emerald-800">Izin Pulang Awal Disetujui</p>
            <p class="text-xs text-emerald-600">Kamu boleh absen pulang sekarang</p>
        </div>
    </div>
    @endif

    @if(isset($isCheckOut) && $isCheckOut)
    {{-- Banner check-in info --}}
    <div class="bg-green-50 border border-green-200 rounded-2xl p-3 flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-green-800">Sudah absen masuk</p>
            <p class="text-xs text-green-600">
                {{ ucfirst($attendance->status) }} pukul {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}
                · Absen pulang belum dilakukan
            </p>
        </div>
    </div>
    @endif

    {{-- Status Waktu --}}
    <div class="bg-blue-50 rounded-2xl p-3 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p id="clock" class="text-lg font-bold text-blue-800">--:--</p>
            @if(isset($isCheckOut) && $isCheckOut)
                <p class="text-xs text-blue-600">Foto selfie untuk konfirmasi absen pulang</p>
            @else
                <p class="text-xs text-blue-600">Hadir sebelum {{ $checkInLate ?? '07:30' }} · Terlambat {{ $checkInLate ?? '07:30' }}–{{ $checkInClose ?? '08:00' }}</p>
            @endif
        </div>
    </div>

    {{-- Info Lokasi Aktif --}}
    <div class="bg-green-50 rounded-2xl p-3 flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-green-800">{{ $location['name'] }}</p>
            <p class="text-xs text-green-600">Radius presensi: {{ $location['radius'] }} meter dari titik lokasi</p>
        </div>
    </div>

    {{-- Kamera --}}
    <div class="relative bg-black rounded-2xl overflow-hidden" style="aspect-ratio: 3/4;">
        <video id="camera" autoplay playsinline muted
            class="w-full h-full object-cover scale-x-[-1]"></video>

        {{-- Overlay frame wajah --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-48 h-60 rounded-full border-4 border-white/60 border-dashed"></div>
        </div>

        {{-- GPS Status --}}
        <div id="gps-status"
            class="absolute top-3 left-3 bg-black/50 text-white text-xs px-2 py-1 rounded-full flex items-center gap-1">
            <div class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></div>
            Mencari GPS...
        </div>

        {{-- Canvas tersembunyi untuk capture --}}
        <canvas id="canvas" class="hidden"></canvas>
    </div>

    {{-- Tombol Ambil Foto --}}
    <button id="btn-capture" disabled
        onclick="captureAndSend()"
        class="w-full py-4 rounded-2xl text-white font-semibold text-base
            bg-gray-400 cursor-not-allowed transition-all
            disabled:opacity-70">
        <span id="btn-text">Menunggu GPS...</span>
    </button>

    {{-- Indikator proses (menggantikan tombol supaya tidak bisa di-spam) --}}
    <div id="processing-box" class="hidden w-full py-4 rounded-2xl bg-blue-50 border border-blue-200 items-center justify-center gap-2">
        <div class="w-4 h-4 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
        <span class="text-blue-700 text-sm font-medium">Memproses absensi...</span>
    </div>

    {{-- Error box --}}
    <div id="error-box" class="hidden bg-red-50 border border-red-200 rounded-xl p-3">
        <p id="error-msg" class="text-red-700 text-sm text-center font-medium"></p>
    </div>

    {{-- Success box --}}
    <div id="success-box" class="hidden bg-green-50 border border-green-200 rounded-xl p-4 text-center">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p id="success-msg" class="text-green-800 font-semibold text-sm"></p>
        <a href="{{ isset($isCheckOut) && $isCheckOut ? route('siswa.kesiswaan') : route('siswa.dashboard') }}"
            class="inline-block mt-3 px-5 py-2 bg-green-600 text-white text-sm rounded-xl font-medium">
            {{ isset($isCheckOut) && $isCheckOut ? 'Ke Kesiswaan' : 'Ke Beranda' }}
        </a>
    </div>

    <p class="text-center text-xs text-gray-400 pb-2">
        Pastikan wajah terlihat jelas dan kamu berada dalam radius {{ $location['radius'] }}m dari
        <span class="font-medium">{{ $location['name'] }}</span>
    </p>
</div>

<script>
let gpsData = null;

// ── Jam real-time ──────────────────────────────────────────────────────────
function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent =
        now.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
}
updateClock();
setInterval(updateClock, 1000);

// ── Inisialisasi Kamera Depan ──────────────────────────────────────────────
async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 960 } }
        });
        document.getElementById('camera').srcObject = stream;
    } catch (err) {
        showError('Tidak dapat mengakses kamera. Pastikan izin kamera diaktifkan.');
    }
}
startCamera();

// ── Ambil GPS ─────────────────────────────────────────────────────────────
function startGPS() {
    if (!window.isSecureContext) {
        showError('Presensi membutuhkan koneksi HTTPS. Hubungi admin sekolah.');
        return;
    }
    if (!navigator.geolocation) {
        showError('Browser tidak mendukung GPS.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            gpsData = {
                latitude  : pos.coords.latitude,
                longitude : pos.coords.longitude,
                accuracy  : pos.coords.accuracy,
            };

            const statusEl = document.getElementById('gps-status');
            statusEl.innerHTML = `<div class="w-2 h-2 rounded-full bg-green-400"></div> GPS aktif (±${Math.round(pos.coords.accuracy)}m)`;

            const btn = document.getElementById('btn-capture');
            btn.disabled = false;
            btn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            @if(isset($isCheckOut) && $isCheckOut)
            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
            document.getElementById('btn-text').textContent = 'Ambil Foto & Absen Pulang';
            @else
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            document.getElementById('btn-text').textContent = 'Ambil Foto & Presensi';
            @endif
        },
        (err) => {
            showError('GPS tidak bisa diakses. Aktifkan lokasi di browser.');
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}
startGPS();

// ── Capture & Kirim ───────────────────────────────────────────────────────
async function captureAndSend() {
    if (!gpsData) { showError('GPS belum siap.'); return; }

    const btn = document.getElementById('btn-capture');
    const processingBox = document.getElementById('processing-box');
    if (btn.classList.contains('hidden')) return; // sudah diproses, cegah spam klik

    const video  = document.getElementById('camera');
    const canvas = document.getElementById('canvas');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0);

    const photoBase64 = canvas.toDataURL('image/jpeg', 0.7);

    // Sembunyikan tombol & tampilkan indikator proses — mencegah spam tap
    btn.classList.add('hidden');
    processingBox.classList.remove('hidden');
    processingBox.classList.add('flex');

    try {
        const resp = await fetch('{{ isset($isCheckOut) && $isCheckOut ? route("siswa.attendance.checkout") : route("siswa.attendance.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type' : 'application/json',
                'X-CSRF-TOKEN'  : '{{ csrf_token() }}',
                'Accept'        : 'application/json',
            },
            body: JSON.stringify({
                photo    : photoBase64,
                latitude : gpsData.latitude,
                longitude: gpsData.longitude,
                accuracy : gpsData.accuracy,
            }),
        });

        const data = await resp.json();

        processingBox.classList.add('hidden');
        processingBox.classList.remove('flex');

        if (data.success) {
            document.getElementById('success-box').classList.remove('hidden');
            document.getElementById('success-msg').textContent = data.message;
            setTimeout(() => {
                window.location.href = '{{ isset($isCheckOut) && $isCheckOut ? route("siswa.dashboard") : route("siswa.dashboard") }}';
            }, 1500);
        } else {
            showError(data.message);
            btn.classList.remove('hidden');
            btn.disabled = false;
            document.getElementById('btn-text').textContent = 'Coba Lagi';
        }
    } catch (e) {
        processingBox.classList.add('hidden');
        processingBox.classList.remove('flex');
        showError('Terjadi kesalahan koneksi. Coba lagi.');
        btn.classList.remove('hidden');
        btn.disabled = false;
        document.getElementById('btn-text').textContent = 'Coba Lagi';
    }
}

function showError(msg) {
    const box = document.getElementById('error-box');
    box.classList.remove('hidden');
    document.getElementById('error-msg').textContent = msg;
    setTimeout(() => box.classList.add('hidden'), 5000);
}
</script>
@endif
@endsection
