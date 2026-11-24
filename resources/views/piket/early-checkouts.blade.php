@extends('layouts.piket')
@section('title', 'Verifikasi Izin Pulang Awal')
@section('page-title', 'Verifikasi Izin Pulang Lebih Awal')

@section('content')
<div class="space-y-4">

    <form method="GET" action="{{ route('piket.early-checkouts.index') }}"
          class="bg-white rounded-2xl border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Cari Siswa / NISN</label>
            <input type="text" name="q" value="{{ $search }}" placeholder="Nama siswa atau NISN..."
                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Status</label>
            <select name="status" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu ({{ $pendingCount }})</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Kelas</label>
            <select name="class_id" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                <option value="">Semua Kelas</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ (string) $classId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">Filter</button>
            <a href="{{ route('piket.early-checkouts.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50">Reset</a>
        </div>
    </form>

    <div class="space-y-3.5">
        @forelse($requests as $req)
            <div x-data="{ showReject: false }" class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-2 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-extrabold text-slate-900">{{ $req->student?->name ?? 'Siswa' }}</h3>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">{{ $req->student?->schoolClass?->name ?? '—' }}</span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $req->statusBadgeClass() }}">{{ $req->statusLabel() }}</span>
                            <span class="px-2.5 py-1 rounded bg-blue-50 text-blue-700 text-xs font-semibold">{{ $req->typeLabel() }}</span>
                        </div>
                        <p class="text-sm text-slate-500">
                            Tanggal: <strong>{{ $req->date?->locale('id')->translatedFormat('d M Y') }}</strong> • Jam pulang dimohon: <strong>{{ $req->requestedTimeFormatted() }}</strong>
                        </p>
                        <p class="text-base text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-3.5">
                            <strong>Alasan:</strong> {{ $req->reason }}
                        </p>
                        @if($req->reviewer)
                            <p class="text-sm text-slate-500">
                                Diverifikasi oleh: <strong>{{ $req->reviewer->name }}</strong>
                                @if($req->reviewer_note) • Catatan: {{ $req->reviewer_note }} @endif
                            </p>
                        @endif
                    </div>

                    @if($req->isPending())
                        <div class="flex flex-wrap gap-2.5 shrink-0">
                            <form method="POST" action="{{ route('piket.early-checkouts.approve', $req) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
                                    ✓ Setujui
                                </button>
                            </form>
                            <button type="button" @click="showReject = !showReject"
                                class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-sm font-bold">
                                ✕ Tolak
                            </button>
                        </div>
                    @endif
                </div>

                @if($req->isPending())
                    <form x-show="showReject" x-cloak method="POST" action="{{ route('piket.early-checkouts.reject', $req) }}"
                          class="mt-3.5 pt-3.5 border-t border-slate-200 flex flex-col sm:flex-row gap-2.5">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="reviewer_note" required placeholder="Alasan penolakan pulang awal..."
                               class="flex-1 rounded-xl border border-rose-300 px-3.5 py-2.5 text-sm">
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold shrink-0">
                            Konfirmasi Tolak
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-base text-slate-500">
                Tidak ada pengajuan izin pulang awal.
            </div>
        @endforelse
    </div>

    <div>{{ $requests->links() }}</div>
</div>
@endsection
