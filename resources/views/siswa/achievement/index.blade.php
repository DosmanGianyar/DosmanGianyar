@extends('layouts.siswa')
@section('title', 'Prestasi Saya')
@section('page-title', 'Prestasi Saya')

@section('content')
<div class="max-w-lg mx-auto space-y-4">

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-yellow-50 rounded-2xl p-3 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
            <p class="text-xs text-yellow-700 mt-0.5">Menunggu</p>
        </div>
        <div class="bg-green-50 rounded-2xl p-3 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
            <p class="text-xs text-green-700 mt-0.5">Disetujui</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-3 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
            <p class="text-xs text-red-700 mt-0.5">Ditolak</p>
        </div>
    </div>

    {{-- 5 Syarat Kurasi Guide Card --}}
    <div x-data="{ open: true }" class="bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 text-white rounded-2xl p-4 shadow-sm border border-indigo-700/50">
        <button @click="open = !open" class="w-full flex items-center justify-between text-left focus:outline-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-yellow-400/20 text-yellow-400 flex items-center justify-center font-bold text-base shrink-0">
                    💡
                </div>
                <div>
                    <h4 class="font-bold text-sm text-white">5 Syarat Lomba Bisa Dikurasi</h4>
                    <p class="text-xs text-indigo-200">Panduan resmi Pusprestnas / BPTI Kemendikdasmen</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-indigo-200 transform transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-collapse class="mt-3.5 space-y-2.5 pt-3 border-t border-indigo-700/50 text-xs">
            <!-- P1 -->
            <div class="bg-white/5 rounded-xl p-3 border border-white/10 space-y-1">
                <p class="font-bold text-yellow-300">1. Penyelenggara Resmi & Kredibel (P1)</p>
                <p class="text-emerald-300">✅ <strong class="text-emerald-200">BISA:</strong> Kementerian/Lembaga (Kemendikbud/BRIN/KONI), PTN/PTS Terakreditasi, atau Organisasi Resmi.</p>
                <p class="text-red-300">❌ <strong class="text-red-200">TIDAK:</strong> Lomba komersial berbayar tanpa akreditasi, EO abal-abal, atau event tidak terdaftar.</p>
            </div>
            <!-- P2 -->
            <div class="bg-white/5 rounded-xl p-3 border border-white/10 space-y-1">
                <p class="font-bold text-yellow-300">2. Tahapan Seleksi Berjenjang (P2)</p>
                <p class="text-emerald-300">✅ <strong class="text-emerald-200">BISA:</strong> Memiliki seleksi berjenjang terstruktur (Sekolah ➔ Kab/Kota ➔ Prov ➔ Nasional).</p>
                <p class="text-red-300">❌ <strong class="text-red-200">TIDAK:</strong> Lomba instan online tanpa tahap seleksi resmi dan tanpa juri tersertifikasi.</p>
            </div>
            <!-- P3 -->
            <div class="bg-white/5 rounded-xl p-3 border border-white/10 space-y-1">
                <p class="font-bold text-yellow-300">3. Konsistensi Pelaksanaan Rutin (P3)</p>
                <p class="text-emerald-300">✅ <strong class="text-emerald-200">BISA:</strong> Perlombaan rutin berkala setiap tahun (minimal 2-3 kali berturut-turut).</p>
                <p class="text-red-300">❌ <strong class="text-red-200">TIDAK:</strong> Event sekali jalan (one-time event) yang tidak punya rekam jejak tahunan.</p>
            </div>
            <!-- P4 -->
            <div class="bg-white/5 rounded-xl p-3 border border-white/10 space-y-1">
                <p class="font-bold text-yellow-300">4. Sarana & Standar Infrastruktur (P4)</p>
                <p class="text-emerald-300">✅ <strong class="text-emerald-200">BISA:</strong> Menggunakan arena/lab/platform resmi yang memenuhi regulasi teknis & keselamatan.</p>
                <p class="text-red-300">❌ <strong class="text-red-200">TIDAK:</strong> Perlombaan informal tanpa standar keselamatan dan regulasi bidang terkait.</p>
            </div>
            <!-- P5 -->
            <div class="bg-white/5 rounded-xl p-3 border border-white/10 space-y-1">
                <p class="font-bold text-yellow-300">5. Keabsahan Sertifikat & SK Juara (P5)</p>
                <p class="text-emerald-300">✅ <strong class="text-emerald-200">BISA:</strong> Sertifikat asli TTD pejabat/QR Code verifikasi + Surat Keputusan (SK) Juara resmi.</p>
                <p class="text-red-300">❌ <strong class="text-red-200">TIDAK:</strong> Sertifikat peserta biasa, tanpa SK Pemenang resmi, atau dokumen fiktif/editan.</p>
            </div>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="flex gap-2">
        <a href="{{ route('siswa.achievements.create') }}"
            class="flex-1 bg-blue-600 text-white text-sm font-semibold py-3 rounded-xl text-center">
            + Laporkan Prestasi
        </a>
        <a href="{{ route('siswa.achievements.report') }}"
            class="px-4 bg-gray-100 text-gray-700 text-sm font-semibold py-3 rounded-xl text-center">
            Laporan Sekolah
        </a>
    </div>

    {{-- List --}}
    @forelse($achievements as $achievement)
    <a href="{{ route('siswa.achievements.show', $achievement) }}"
        class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:border-blue-200 transition-colors">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ $achievement->title }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $achievement->category?->name ?? '—' }} ·
                    {{ $achievement->achievement_date->translatedFormat('d M Y') }}
                </p>
                @if($achievement->rank)
                    <p class="text-xs text-blue-600 font-medium mt-1">{{ $achievement->rank }}</p>
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
    <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
        </div>
        <p class="text-gray-700 font-semibold">Belum ada prestasi dilaporkan</p>
        <p class="text-gray-400 text-xs mt-1">Raih prestasi dan laporkan di sini</p>
        <a href="{{ route('siswa.achievements.create') }}"
            class="inline-block mt-4 px-5 py-2 bg-blue-600 text-white text-sm rounded-xl font-medium">
            Laporkan Sekarang
        </a>
    </div>
    @endforelse

</div>
@endsection
