@extends('layouts.siswa')
@section('title', 'Prestasi Saya')
@section('page-title', 'Prestasi Saya')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-yellow-50 rounded-2xl p-3 text-center border border-yellow-200 shadow-xs">
            <p class="text-2xl font-extrabold text-yellow-600">{{ $stats['pending'] }}</p>
            <p class="text-xs font-bold text-yellow-800 mt-0.5">Menunggu</p>
        </div>
        <div class="bg-green-50 rounded-2xl p-3 text-center border border-green-200 shadow-xs">
            <p class="text-2xl font-extrabold text-green-600">{{ $stats['approved'] }}</p>
            <p class="text-xs font-bold text-green-800 mt-0.5">Disetujui</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-3 text-center border border-red-200 shadow-xs">
            <p class="text-2xl font-extrabold text-red-600">{{ $stats['rejected'] }}</p>
    </div>

    {{-- Banner Pengumuman TU Prestasi Siswa --}}
    <div class="bg-indigo-50/90 border border-indigo-200/90 rounded-2xl p-3.5 flex items-start gap-3 shadow-xs">
        <div class="w-8 h-8 bg-indigo-600 text-white rounded-xl flex items-center justify-center text-sm font-bold shrink-0 shadow-xs">🏛️</div>
        <div class="text-xs text-indigo-950 space-y-0.5">
            <p class="font-extrabold text-indigo-900">Pengumuman Kurasi Prestasi</p>
            <p class="leading-relaxed text-indigo-800">Jika Anda ingin mengajukan prestasi kurasi lebih lengkap atau membutuhkan informasi tambahan, silakan dapat menghubungi <strong>TU Bagian Prestasi Siswa</strong>.</p>
        </div>
    </div>

    {{-- 5 Syarat Kurasi Guide Card --}}
    <div x-data="{ open: true }" class="rounded-2xl p-4 shadow-lg border border-blue-400/30"
         style="background: linear-gradient(135deg, #0f2460 0%, #1a3a8f 50%, #1e3fad 100%) !important; color: #ffffff !important;">
        <button @click="open = !open" class="w-full flex items-center justify-between text-left focus:outline-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-base shrink-0"
                     style="background: rgba(250, 204, 21, 0.2); color: #facc15;">
                    💡
                </div>
                <div>
                    <h4 class="font-extrabold text-sm text-white">5 Syarat Lomba Bisa Dikurasi</h4>
                    <p class="text-xs font-medium" style="color: #bfdbfe !important;">Panduan resmi Pusprestnas / BPTI Kemendikdasmen</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-blue-200 transform transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-collapse class="mt-3.5 space-y-2.5 pt-3 border-t border-white/15 text-xs">
            <!-- P1 -->
            <div class="rounded-xl p-3 space-y-1" style="background: rgba(0, 0, 0, 0.25) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important;">
                <p class="font-extrabold" style="color: #facc15 !important;">1. Penyelenggara Resmi & Kredibel (P1)</p>
                <p style="color: #4ade80 !important;">✅ <strong style="color: #86efac !important;">BISA:</strong> Kementerian/Lembaga (Kemendikbud/BRIN/KONI), PTN/PTS Terakreditasi, atau Organisasi Resmi.</p>
                <p style="color: #fca5a5 !important;">❌ <strong style="color: #f87171 !important;">TIDAK:</strong> Lomba komersial berbayar tanpa akreditasi, EO abal-abal, atau event tidak terdaftar.</p>
            </div>
            <!-- P2 -->
            <div class="rounded-xl p-3 space-y-1" style="background: rgba(0, 0, 0, 0.25) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important;">
                <p class="font-extrabold" style="color: #facc15 !important;">2. Tahapan Seleksi Berjenjang (P2)</p>
                <p style="color: #4ade80 !important;">✅ <strong style="color: #86efac !important;">BISA:</strong> Memiliki seleksi berjenjang terstruktur (Sekolah ➔ Kab/Kota ➔ Prov ➔ Nasional).</p>
                <p style="color: #fca5a5 !important;">❌ <strong style="color: #f87171 !important;">TIDAK:</strong> Lomba instan online tanpa tahap seleksi resmi dan tanpa juri tersertifikasi.</p>
            </div>
            <!-- P3 -->
            <div class="rounded-xl p-3 space-y-1" style="background: rgba(0, 0, 0, 0.25) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important;">
                <p class="font-extrabold" style="color: #facc15 !important;">3. Konsistensi Pelaksanaan Rutin (P3)</p>
                <p style="color: #4ade80 !important;">✅ <strong style="color: #86efac !important;">BISA:</strong> Perlombaan rutin berkala setiap tahun (minimal 2-3 kali berturut-turut).</p>
                <p style="color: #fca5a5 !important;">❌ <strong style="color: #f87171 !important;">TIDAK:</strong> Event sekali jalan (one-time event) yang tidak punya rekam jejak tahunan.</p>
            </div>
            <!-- P4 -->
            <div class="rounded-xl p-3 space-y-1" style="background: rgba(0, 0, 0, 0.25) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important;">
                <p class="font-extrabold" style="color: #facc15 !important;">4. Sarana & Standar Infrastruktur (P4)</p>
                <p style="color: #4ade80 !important;">✅ <strong style="color: #86efac !important;">BISA:</strong> Menggunakan arena/lab/platform resmi yang memenuhi regulasi teknis & keselamatan.</p>
                <p style="color: #fca5a5 !important;">❌ <strong style="color: #f87171 !important;">TIDAK:</strong> Perlombaan informal tanpa standar keselamatan dan regulasi bidang terkait.</p>
            </div>
            <!-- P5 -->
            <div class="rounded-xl p-3 space-y-1" style="background: rgba(0, 0, 0, 0.25) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important;">
                <p class="font-extrabold" style="color: #facc15 !important;">5. Keabsahan Sertifikat & SK Juara (P5)</p>
                <p style="color: #4ade80 !important;">✅ <strong style="color: #86efac !important;">BISA:</strong> Sertifikat asli TTD pejabat/QR Code verifikasi + Surat Keputusan (SK) Juara resmi.</p>
                <p style="color: #fca5a5 !important;">❌ <strong style="color: #f87171 !important;">TIDAK:</strong> Sertifikat peserta biasa, tanpa SK Pemenang resmi, atau dokumen fiktif/editan.</p>
            </div>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="flex gap-2">
        <a href="{{ route('siswa.achievements.create') }}"
            class="flex-1 text-white text-sm font-bold py-3 rounded-xl text-center shadow-md transition hover:opacity-90"
            style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;">
            + Laporkan Prestasi
        </a>
        <a href="{{ route('siswa.achievements.report') }}"
            class="px-4 bg-gray-100 text-gray-800 text-sm font-bold py-3 rounded-xl text-center border border-gray-200 hover:bg-gray-200 transition">
            Laporan Sekolah
        </a>
    </div>

    {{-- List --}}
    @forelse($achievements as $achievement)
    <a href="{{ route('siswa.achievements.show', $achievement) }}"
        class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-200 hover:border-blue-300 transition-colors">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900 text-sm leading-tight truncate">{{ $achievement->title }}</p>
                <p class="text-xs text-gray-600 font-medium mt-1">
                    {{ $achievement->category?->name ?? '—' }} ·
                    {{ $achievement->achievement_date->translatedFormat('d M Y') }}
                </p>
                @if($achievement->rank)
                    <p class="text-xs text-blue-700 font-bold mt-1">{{ $achievement->rank }}</p>
                @endif
            </div>
            <div class="flex flex-col items-end gap-1.5 shrink-0">
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $achievement->statusBadgeClass() }}">
                    {{ $achievement->statusLabel() }}
                </span>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $achievement->levelBadgeClass() }}">
                    {{ $achievement->levelLabel() }}
                </span>
            </div>
        </div>
    </a>
    @empty
    <div class="bg-white rounded-2xl p-8 text-center shadow-sm border border-gray-200">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
        </div>
        <p class="text-gray-900 font-bold">Belum ada prestasi dilaporkan</p>
        <p class="text-gray-500 text-xs mt-1">Raih prestasi dan laporkan di sini</p>
        <a href="{{ route('siswa.achievements.create') }}"
            class="inline-block mt-4 px-5 py-2.5 text-white text-sm rounded-xl font-bold shadow-md"
            style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;">
            Laporkan Sekarang
        </a>
    </div>
    @endforelse

</div>
@endsection
