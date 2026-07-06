@extends('layouts.siswa')

@section('title', isset($isCheckOut) && $isCheckOut ? 'Absen Pulang' : 'Presensi')
@section('page-title', isset($isCheckOut) && $isCheckOut ? 'Absen Pulang' : 'Presensi')

@section('content')

{{-- ── STATE: Presensi ditutup ─────────────────────────────────────────── --}}
@if($isClosed ?? false)
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h2 class="text-lg font-bold text-gray-800 mb-2">Presensi Sudah Ditutup</h2>
    <p class="text-sm text-gray-500 mb-6">Absen masuk sudah tidak bisa dilakukan (lewat pukul {{ $checkInClose ?? '08:00' }})</p>
    <a href="{{ route('siswa.dashboard') }}" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium">
        Kembali ke Beranda
    </a>
</div>

{{-- ── STATE: Belum dibuka ──────────────────────────────────────────────── --}}
@elseif($notYetOpen ?? false)
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h2 class="text-lg font-bold text-gray-800 mb-2">Presensi Belum Dibuka</h2>
    <p class="text-sm text-gray-500 mb-6">Absen masuk baru bisa dilakukan mulai pukul <strong>{{ $checkInOpen ?? '06:00' }}</strong></p>
    <a href="{{ route('siswa.dashboard') }}" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium">
        Kembali ke Beranda
    </a>
</div>

{{-- ── STATE: Absen pulang belum dibuka ────────────────────────────────── --}}
@elseif($checkOutTooEarly ?? false)
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
    </div>
    <h2 class="text-lg font-bold text-gray-800 mb-2">Absen Pulang Belum Dibuka</h2>
    <p class="text-sm text-gray-500 mb-6">Absen pulang baru bisa dilakukan mulai pukul <strong>{{ $checkOutOpen ?? '13:00' }}</strong></p>
    <a href="{{ route('siswa.dashboard') }}" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium mb-3">
        Kembali ke Beranda
    </a>
    <a href="{{ route('siswa.early-checkout.create') }}"
        class="flex items-center gap-2 px-5 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
        Ajukan Izin Pulang Lebih Awal
    </a>
</div>

{{-- ── STATE: Normal (Peta + Kamera) ──────────────────────────────────── --}}
@else

{{-- ════════════════════════════════════════════════════════════════════════
     FASE 1 — Cek Lokasi (Peta)
════════════════════════════════════════════════════════════════════════ --}}
<div id="phase-map">

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    {{-- Peta --}}
    <div id="map" class="-mx-4" style="height: 50vh; min-height: 260px;"></div>

    {{-- Panel bawah --}}
    <div class="space-y-2.5 mt-3">

        {{-- Info lokasi sekolah --}}
        <div class="bg-blue-50 rounded-2xl p-3 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-blue-800 truncate">{{ $location['name'] }}</p>
                <p class="text-xs text-blue-600">Radius presensi: <strong>{{ $location['radius'] }} meter</strong></p>
            </div>
        </div>

        {{-- Status GPS --}}
        <div id="status-card" class="bg-gray-50 border border-gray-200 rounded-2xl p-3 flex items-center gap-3">
            <div id="status-icon" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center shrink-0">
                <div class="w-4 h-4 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></div>
            </div>
            <div class="flex-1 min-w-0">
                <p id="status-title" class="text-sm font-semibold text-gray-700">Mencari lokasi kamu...</p>
                <p id="status-desc" class="text-xs text-gray-500">Pastikan GPS aktif di browser</p>
            </div>
        </div>

        {{-- Tombol Lanjut --}}
        <button onclick="goToCamera()"
            class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm transition-colors">
            @if(isset($isCheckOut) && $isCheckOut)
                Lanjut ke Absen Pulang
            @else
                Lanjut ke Presensi
            @endif
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     FASE 2 — Ambil Foto Selfie (tersembunyi, muncul setelah klik Lanjut)
════════════════════════════════════════════════════════════════════════ --}}
<div id="phase-camera" class="hidden max-w-sm mx-auto space-y-4">

    {{-- Banner izin pulang awal --}}
    @if(isset($hasEarlyApproval) && $hasEarlyApproval)
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

    {{-- Banner sudah check-in --}}
    @if(isset($isCheckOut) && $isCheckOut)
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

    {{-- Jam & info waktu --}}
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
                <p class="text-xs text-blue-600">Hadir sebelum {{ $checkInLate ?? '07:30' }} · Terlambat s/d {{ $checkInClose ?? '08:00' }}</p>
            @endif
        </div>
    </div>

    {{-- Kamera --}}
    <div class="relative bg-black rounded-2xl overflow-hidden" style="aspect-ratio: 3/4;">
        <video id="camera" autoplay playsinline muted
            class="w-full h-full object-cover scale-x-[-1]"></video>

        {{-- Oval wajah --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-48 h-60 rounded-full border-4 border-white/60 border-dashed"></div>
        </div>

        {{-- GPS badge --}}
        <div id="gps-badge"
            class="absolute top-3 left-3 bg-black/50 text-white text-xs px-2 py-1 rounded-full flex items-center gap-1">
            <div class="w-2 h-2 rounded-full bg-green-400"></div>
            <span id="gps-badge-text">GPS siap</span>
        </div>

        <canvas id="canvas" class="hidden"></canvas>
    </div>

    {{-- Tombol ambil foto --}}
    @if(isset($isCheckOut) && $isCheckOut)
    <button id="btn-capture" onclick="captureAndSend()"
        class="w-full py-4 rounded-2xl text-white font-semibold text-base transition-all bg-emerald-600 hover:bg-emerald-700">
        <span id="btn-text">Ambil Foto & Absen Pulang</span>
    </button>
    @else
    <button id="btn-capture" onclick="captureAndSend()"
        class="w-full py-4 rounded-2xl text-white font-semibold text-base transition-all bg-blue-600 hover:bg-blue-700">
        <span id="btn-text">Ambil Foto & Presensi</span>
    </button>
    @endif

    {{-- Indikator proses (menggantikan tombol supaya tidak bisa di-spam) --}}
    <div id="processing-box" class="hidden w-full py-4 rounded-2xl bg-blue-50 border border-blue-200 items-center justify-center gap-2">
        <div class="w-4 h-4 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
        <span class="text-blue-700 text-sm font-medium">Memproses absensi...</span>
    </div>

    {{-- Error --}}
    <div id="error-box" class="hidden bg-red-50 border border-red-200 rounded-xl p-3">
        <p id="error-msg" class="text-red-700 text-sm text-center font-medium"></p>
    </div>

    {{-- Sukses --}}
    <div id="success-box" class="hidden bg-green-50 border border-green-200 rounded-xl p-4 text-center">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p id="success-msg" class="text-green-800 font-semibold text-sm"></p>
        <a href="{{ route('siswa.dashboard') }}"
            class="inline-block mt-3 px-5 py-2 bg-green-600 text-white text-sm rounded-xl font-medium">
            Ke Beranda
        </a>
    </div>

    {{-- Tombol kembali ke peta --}}
    <button onclick="backToMap()"
        class="w-full py-2.5 rounded-2xl border border-gray-200 text-gray-500 text-sm font-medium hover:bg-gray-50 transition-colors">
        ← Kembali ke Peta
    </button>

</div>
{{-- @endif state normal --}}
@endif

{{-- ── Leaflet JS (hanya dimuat jika fase peta) ─────────────────────────── --}}
@if(!($isClosed ?? false) && !($notYetOpen ?? false) && !($checkOutTooEarly ?? false))
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endif

<script>
// ─────────────────────────────────────────────────────────────────────────────
// DATA DARI SERVER
// ─────────────────────────────────────────────────────────────────────────────
const SCHOOL_LAT  = {{ $location['lat'] ?? 0 }};
const SCHOOL_LNG  = {{ $location['lng'] ?? 0 }};
const RADIUS      = {{ $location['radius'] ?? 50 }};
const SCHOOL_NAME = @json($location['name'] ?? '');
const IS_CHECKOUT = {{ isset($isCheckOut) && $isCheckOut ? 'true' : 'false' }};

// GPS dari fase peta — dipakai kembali di fase kamera
let gpsData = null;

// ─────────────────────────────────────────────────────────────────────────────
// FASE 1 — PETA
// ─────────────────────────────────────────────────────────────────────────────
let mapInstance  = null;
let userMarker = null;
let firstFix   = true;

function initMap() {
    if (!document.getElementById('map')) return;

    mapInstance = L.map('map', { zoomControl: true, attributionControl: true })
                   .setView([SCHOOL_LAT, SCHOOL_LNG], 17);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
    }).addTo(mapInstance);

    setTimeout(() => mapInstance.invalidateSize(), 150);

    // Lingkaran radius
    L.circle([SCHOOL_LAT, SCHOOL_LNG], {
        radius: RADIUS, color: '#2563eb', weight: 2,
        fillColor: '#3b82f6', fillOpacity: 0.12, dashArray: '6 4',
    }).addTo(mapInstance);

    // Marker sekolah
    const schoolIcon = L.divIcon({
        className: '',
        iconSize: [36, 36], iconAnchor: [18, 18],
        html: `<div style="width:36px;height:36px;background:#2563eb;border-radius:50%;
               border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);
               display:flex;align-items:center;justify-content:center;">
            <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5
                       m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>`,
    });
    L.marker([SCHOOL_LAT, SCHOOL_LNG], { icon: schoolIcon })
     .addTo(mapInstance)
     .bindTooltip(SCHOOL_NAME, { permanent: true, direction: 'top', offset: [0, -22], className: 'school-tooltip' });
}

// Helper status card
function setStatus(type, title, desc, iconHtml) {
    const c = {
        inside:  { card: 'bg-green-50 border-green-200', icon: 'bg-green-100', title: 'text-green-800', desc: 'text-green-600'  },
        outside: { card: 'bg-red-50 border-red-200',     icon: 'bg-red-100',   title: 'text-red-800',   desc: 'text-red-600'    },
        warning: { card: 'bg-amber-50 border-amber-200', icon: 'bg-amber-100', title: 'text-amber-800', desc: 'text-amber-700'  },
        loading: { card: 'bg-gray-50 border-gray-200',   icon: 'bg-gray-100',  title: 'text-gray-700',  desc: 'text-gray-500'   },
    }[type];

    document.getElementById('status-card').className  = `${c.card} border rounded-2xl p-3 flex items-center gap-3`;
    document.getElementById('status-icon').className  = `w-10 h-10 ${c.icon} rounded-full flex items-center justify-center shrink-0`;
    document.getElementById('status-icon').innerHTML  = iconHtml;
    document.getElementById('status-title').className = `text-sm font-semibold ${c.title}`;
    document.getElementById('status-title').textContent = title;
    document.getElementById('status-desc').className  = `text-xs ${c.desc}`;
    document.getElementById('status-desc').textContent = desc;
}

function haversine(lat1, lng1, lat2, lng2) {
    const R = 6371000, dLat = (lat2-lat1)*Math.PI/180, dLng = (lng2-lng1)*Math.PI/180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// GPS watch — berjalan sejak fase 1, gpsData dipakai fase 2
function startGPSWatch() {
    if (!navigator.geolocation) {
        setStatus('warning', 'GPS tidak didukung', 'Browser ini tidak mendukung geolokasi.',
            `<svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>`);
        return;
    }

    navigator.geolocation.watchPosition(
        (pos) => {
            const lat  = pos.coords.latitude;
            const lng  = pos.coords.longitude;
            const acc  = Math.round(pos.coords.accuracy);
            const dist = Math.round(haversine(SCHOOL_LAT, SCHOOL_LNG, lat, lng));
            const inside = dist <= RADIUS;

            // Simpan untuk fase kamera
            gpsData = { latitude: lat, longitude: lng, accuracy: pos.coords.accuracy };

            // Update GPS badge di fase kamera jika sudah aktif
            const badge = document.getElementById('gps-badge-text');
            if (badge) badge.textContent = `GPS ±${acc}m`;

            // Update marker di peta
            if (mapInstance) {
                if (!userMarker) {
                    const userIcon = L.divIcon({
                        className : '',
                        iconSize  : [16, 16],
                        iconAnchor: [8, 8],
                        html: `<div style="
                            width:16px;height:16px;border-radius:50%;
                            background:#ef4444;border:3px solid #fff;
                            box-shadow:0 1px 5px rgba(0,0,0,0.4);">
                        </div>`,
                    });
                    userMarker = L.marker([lat, lng], { icon: userIcon })
                        .addTo(mapInstance)
                        .bindTooltip('Lokasi kamu', { direction: 'top', offset: [0, -10] });

                    if (firstFix) {
                        firstFix = false;
                        mapInstance.fitBounds([[SCHOOL_LAT, SCHOOL_LNG],[lat, lng]], { padding: [60,60], maxZoom: 18 });
                    }
                } else {
                    userMarker.setLatLng([lat, lng]);
                }
            }

            // Update status card (hanya jika fase peta aktif)
            if (!document.getElementById('phase-map').classList.contains('hidden')) {
                if (inside) {
                    setStatus('inside', '✓ Kamu berada di area presensi',
                        `Jarak: ${dist}m · Akurasi: ±${acc}m`,
                        `<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>`);
                } else {
                    setStatus('outside', `Di luar area presensi (${dist}m)`,
                        `Harus dalam radius ${RADIUS}m · Akurasi: ±${acc}m`,
                        `<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>`);
                }
            }
        },
        (err) => {
            const msgs = { 1: 'Izin lokasi ditolak.', 2: 'Sinyal GPS lemah.', 3: 'Waktu tunggu habis.' };
            setStatus('warning', 'GPS tidak bisa diakses', msgs[err.code] || 'Tidak dapat membaca GPS.',
                `<svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>`);
        },
        { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 }
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// TRANSISI ANTAR FASE
// ─────────────────────────────────────────────────────────────────────────────
function goToCamera() {
    document.getElementById('phase-map').classList.add('hidden');
    document.getElementById('phase-camera').classList.remove('hidden');
    startCamera();
    updateClock();
    setInterval(updateClock, 1000);
}

function backToMap() {
    // Hentikan stream kamera
    const video = document.getElementById('camera');
    if (video && video.srcObject) {
        video.srcObject.getTracks().forEach(t => t.stop());
        video.srcObject = null;
    }
    document.getElementById('phase-camera').classList.add('hidden');
    document.getElementById('phase-map').classList.remove('hidden');
    setTimeout(() => mapInstance && mapInstance.invalidateSize(), 100);
}

// ─────────────────────────────────────────────────────────────────────────────
// FASE 2 — KAMERA
// ─────────────────────────────────────────────────────────────────────────────
function updateClock() {
    const el = document.getElementById('clock');
    if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}

async function startCamera() {
    const btn     = document.getElementById('btn-capture');
    const btnText = document.getElementById('btn-text');

    // Nonaktifkan tombol sampai kamera berhasil dibuka
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    btnText.textContent = 'Membuka kamera...';

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        const video  = document.getElementById('camera');
        video.srcObject = stream;

        // Aktifkan tombol saat video sudah siap dirender
        video.onloadedmetadata = () => {
            video.play().catch(() => {});
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            btnText.textContent = IS_CHECKOUT ? 'Ambil Foto & Absen Pulang' : 'Ambil Foto & Presensi';
        };
    } catch (err) {
        const messages = {
            NotAllowedError    : 'Izin kamera ditolak. Klik ikon kamera di address bar browser lalu pilih "Izinkan", kemudian muat ulang halaman.',
            NotFoundError      : 'Kamera tidak ditemukan. Pastikan kamera laptop terpasang dan tidak dinonaktifkan.',
            NotReadableError   : 'Kamera sedang digunakan aplikasi lain (Zoom, Teams, dll). Tutup aplikasi tersebut lalu muat ulang halaman.',
            OverconstrainedError: 'Kamera tidak mendukung resolusi yang diminta. Coba di browser lain.',
            SecurityError      : 'Akses kamera memerlukan HTTPS. Coba akses via localhost bukan 127.0.0.1.',
            AbortError         : 'Akses kamera dibatalkan. Muat ulang halaman dan coba lagi.',
        };

        const errMsg = messages[err.name]
            ?? `Tidak dapat membuka kamera (${err.name}). Pastikan tidak ada aplikasi lain yang menggunakan kamera.`;

        btnText.textContent = 'Kamera tidak tersedia';

        const errorBox = document.getElementById('error-box');
        errorBox.classList.remove('hidden');
        document.getElementById('error-msg').textContent = errMsg;
    }
}

async function captureAndSend() {
    if (!gpsData) { showError('GPS belum siap. Tunggu sebentar.'); return; }

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

    const url = IS_CHECKOUT
        ? '{{ route("siswa.attendance.checkout") }}'
        : '{{ route("siswa.attendance.store") }}';

    try {
        const resp = await fetch(url, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body   : JSON.stringify({ photo: photoBase64, latitude: gpsData.latitude, longitude: gpsData.longitude, accuracy: gpsData.accuracy }),
        });
        const data = await resp.json();

        processingBox.classList.add('hidden');
        processingBox.classList.remove('flex');

        if (data.success) {
            document.getElementById('success-box').classList.remove('hidden');
            document.getElementById('success-msg').textContent = data.message;
            setTimeout(() => window.location.href = '{{ route("siswa.dashboard") }}', 1800);
        } else {
            showError(data.message);
            btn.classList.remove('hidden');
            btn.disabled = false;
            document.getElementById('btn-text').textContent = IS_CHECKOUT ? 'Ambil Foto & Absen Pulang' : 'Ambil Foto & Presensi';
        }
    } catch {
        processingBox.classList.add('hidden');
        processingBox.classList.remove('flex');
        showError('Terjadi kesalahan koneksi. Coba lagi.');
        btn.classList.remove('hidden');
        btn.disabled = false;
        document.getElementById('btn-text').textContent = IS_CHECKOUT ? 'Ambil Foto & Absen Pulang' : 'Ambil Foto & Presensi';
    }
}

function showError(msg, permanent = false) {
    const box = document.getElementById('error-box');
    if (!box) return;
    box.classList.remove('hidden');
    document.getElementById('error-msg').textContent = msg;
    if (!permanent) {
        setTimeout(() => box.classList.add('hidden'), 5000);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initMap();
    startGPSWatch();
});
</script>

<style>
.school-tooltip {
    background: #1e3a8a; color: #fff; border: none;
    border-radius: 8px; padding: 3px 9px; font-size: 11px;
    font-weight: 600; white-space: nowrap; box-shadow: 0 1px 5px rgba(0,0,0,.3);
}
.school-tooltip::before { display: none; }
</style>

@endsection
