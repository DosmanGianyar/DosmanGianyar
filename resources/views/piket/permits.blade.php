@extends('layouts.piket')
@section('title', 'Verifikasi Izin Tidak Sekolah')
@section('page-title', 'Verifikasi Izin Tidak Masuk Sekolah')

@section('content')
<div class="space-y-4">

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('piket.permits.index') }}"
          class="bg-white rounded-2xl border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
        <div class="lg:col-span-2">
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Cari Siswa / NISN</label>
            <input type="text" name="q" value="{{ $search }}" placeholder="Ketik nama siswa atau NISN..."
                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status</label>
            <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu ({{ $pendingCount }})</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Jenis & Kelas</label>
            <div class="flex gap-2">
                <select name="type" class="w-1/2 rounded-xl border border-slate-300 px-2 py-2 text-xs">
                    <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="izin" {{ $type === 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ $type === 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="dispensasi" {{ $type === 'dispensasi' ? 'selected' : '' }}>Dispensasi</option>
                </select>
                <select name="class_id" class="w-1/2 rounded-xl border border-slate-300 px-2 py-2 text-xs">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string) $classId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">
                Filter
            </button>
            <a href="{{ route('piket.permits.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                Reset
            </a>
        </div>
    </form>

    {{-- Daftar Pengajuan --}}
    <div class="space-y-3">
        @forelse($permits as $permit)
            <div x-data="{ showReject: false }" class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1.5 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-extrabold text-slate-900">{{ $permit->student?->name ?? 'Siswa' }}</h3>
                            <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-bold">
                                {{ $permit->student?->schoolClass?->name ?? '—' }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $permit->typeBadgeClass() }}">
                                {{ $permit->typeLabel() }}
                            </span>
                            @if($permit->isApproved())
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold">Disetujui</span>
                            @elseif($permit->isRejected())
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[11px] font-bold">Ditolak</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">Menunggu</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">
                            Tanggal izin: <strong class="text-slate-700">{{ $permit->start_date->locale('id')->translatedFormat('d M Y') }}</strong>
                            @if($permit->end_date->ne($permit->start_date))
                                s.d. <strong class="text-slate-700">{{ $permit->end_date->locale('id')->translatedFormat('d M Y') }}</strong>
                            @endif
                            • Diajukan {{ $permit->created_at?->diffForHumans() }}
                        </p>
                        <p class="text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                            <strong>Keterangan:</strong> {{ $permit->reason }}
                        </p>
                        @if($permit->file)
                            <p class="text-xs">
                                <a href="{{ asset('storage/' . $permit->file) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-blue-600 hover:underline font-semibold">
                                    📎 Lihat Surat / Bukti Lampiran ↗
                                </a>
                            </p>
                        @endif
                        @if($permit->approvedBy)
                            <p class="text-[11px] text-slate-500">
                                Diproses oleh: <strong>{{ $permit->approvedBy->name }}</strong>
                                @if($permit->rejection_note)
                                    • Alasan tolak: <span class="text-rose-600">{{ $permit->rejection_note }}</span>
                                @endif
                            </p>
                        @endif
                    </div>

                    @if($permit->isPending())
                        <div class="flex flex-wrap gap-2 shrink-0">
                            <form method="POST" action="{{ route('piket.permits.approve', $permit) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-2xs">
                                    ✓ Setujui ({{ $teacher->name }})
                                </button>
                            </form>
                            <button type="button" @click="showReject = !showReject"
                                class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-bold">
                                ✕ Tolak
                            </button>
                        </div>
                    @endif
                </div>

                @if($permit->isPending())
                    <form x-show="showReject" x-cloak method="POST" action="{{ route('piket.permits.reject', $permit) }}"
                          class="mt-3 pt-3 border-t border-slate-200 flex flex-col sm:flex-row gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="rejection_note" required placeholder="Tuliskan alasan penolakan izin..."
                               class="flex-1 rounded-xl border border-rose-300 px-3 py-2 text-xs">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shrink-0">
                            Konfirmasi Tolak
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-xs text-slate-500">
                Tidak ada data pengajuan izin yang sesuai filter.
            </div>
        @endforelse
    </div>

    <div>{{ $permits->links() }}</div>
</div>
@endsection
