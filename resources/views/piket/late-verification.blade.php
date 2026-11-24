@extends('layouts.piket')
@section('title', 'Piket Gerbang & Keterlambatan')
@section('page-title', 'Piket Gerbang — Verifikasi Keterlambatan & Pembinaan')

@section('content')
<div class="space-y-4">

    <div class="grid grid-cols-3 gap-3">
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 text-center">
            <p class="text-xl font-black text-amber-700">{{ $pendingCount }}</p>
            <p class="text-[11px] font-bold text-amber-800">Menunggu Verifikasi</p>
        </div>
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 text-center">
            <p class="text-xl font-black text-emerald-700">{{ $verifiedCount }}</p>
            <p class="text-[11px] font-bold text-emerald-800">Terverifikasi</p>
        </div>
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-3.5 text-center">
            <p class="text-xl font-black text-rose-700">{{ $rejectedCount }}</p>
            <p class="text-[11px] font-bold text-rose-800">Dikembalikan</p>
        </div>
    </div>

    <form method="GET" action="{{ route('piket.late-verification.index') }}"
          class="bg-white rounded-2xl border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tanggal</label>
            <input type="date" name="date" value="{{ $date }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Cari Siswa / NISN</label>
            <input type="text" name="q" value="{{ $search }}" placeholder="Nama atau NISN..."
                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status</label>
            <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                <option value="verified" {{ $status === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Dikembalikan</option>
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Kelas</label>
            <select name="class_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                <option value="">Semua Kelas</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ (string) $classId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Filter</button>
            <a href="{{ route('piket.late-verification.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600">Reset</a>
        </div>
    </form>

    <div class="space-y-3">
        @forelse($logs as $log)
            @php $catName = method_exists($log, 'displayCategoryName') ? $log->displayCategoryName() : ($log->category?->name ?? 'Pembinaan'); @endphp
            <div x-data="{ showReject: false }" class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1.5 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-extrabold text-slate-900">{{ $log->student?->name ?? 'Siswa' }}</h3>
                            <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-bold">{{ $log->student?->schoolClass?->name ?? '—' }}</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">{{ $catName }}</span>
                            @if($log->status === 'verified')
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold">Terverifikasi</span>
                            @elseif($log->status === 'rejected')
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[11px] font-bold">Dikembalikan</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-yellow-100 text-yellow-800 text-[11px] font-bold">Menunggu</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">Waktu ajuan: {{ $log->created_at?->format('H:i') }} WITA</p>
                        @if($log->description || $log->note)
                            <p class="text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                                {{ $log->description ?? $log->note }}
                            </p>
                        @endif
                        @if($log->photo)
                            <p class="text-xs">
                                <a href="{{ asset('storage/' . $log->photo) }}" target="_blank" class="text-blue-600 hover:underline font-semibold">
                                    📷 Lihat Foto Pembinaan ↗
                                </a>
                            </p>
                        @endif
                        @if($log->verifier)
                            <p class="text-[11px] text-slate-500">
                                Verifikator: <strong>{{ $log->verifier->name }}</strong>
                                @if($log->rejection_note) • Catatan: <span class="text-rose-600">{{ $log->rejection_note }}</span> @endif
                            </p>
                        @endif
                    </div>

                    @if($log->status !== 'verified')
                        <div class="flex flex-wrap gap-2 shrink-0">
                            <form method="POST" action="{{ route('piket.late-verification.verify', $log) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                                    ✓ Verifikasi Masuk
                                </button>
                            </form>
                            <button type="button" @click="showReject = !showReject"
                                class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-bold">
                                ↩ Kembalikan
                            </button>
                        </div>
                    @endif
                </div>

                @if($log->status !== 'verified')
                    <form x-show="showReject" x-cloak method="POST" action="{{ route('piket.late-verification.reject', $log) }}"
                          class="mt-3 pt-3 border-t border-slate-200 flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="text" name="rejection_note" required placeholder="Alasan pengembalian agar diperbaiki siswa..."
                               class="flex-1 rounded-xl border border-rose-300 px-3 py-2 text-xs">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shrink-0">
                            Kembalikan Ajuan
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-xs text-slate-500">
                Tidak ada pengajuan pembinaan gerbang pada filter yang dipilih.
            </div>
        @endforelse
    </div>

    <div>{{ $logs->links() }}</div>
</div>
@endsection
