@extends('layouts.piket')
@section('title', 'Pantau Izin Keluar Kelas')
@section('page-title', 'Pantau Izin Keluar Kelas (Exit Pass)')

@section('content')
<div class="space-y-5">

    {{-- Siswa Sedang Berada Di Luar Kelas --}}
    <div class="bg-white rounded-2xl border border-emerald-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">Sedang Keluar Kelas Saat Ini ({{ $activePasses->count() }})</h2>
                <p class="text-xs text-slate-500">Tandai siswa kembali jika mereka lapor di meja piket atau sudah masuk kembali ke kelas.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($activePasses as $pass)
                <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ $pass->student?->name ?? 'Siswa' }}</p>
                        <p class="text-[11px] text-slate-600">
                            {{ $pass->student?->schoolClass?->name ?? '—' }} • {{ $pass->reason_label }}
                        </p>
                        <p class="text-[10.5px] text-emerald-700 font-semibold">
                            Keluar pukul {{ $pass->out_time?->format('H:i') }} ({{ $pass->out_time?->diffForHumans() }})
                        </p>
                    </div>
                    <form method="POST" action="{{ route('piket.exit-passes.return', $pass) }}" class="shrink-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold">
                            ✓ Kembali
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-full text-center py-4 text-xs text-slate-400">
                    Tidak ada siswa yang sedang berada di luar kelas saat ini.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Filter & Riwayat --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
        <form method="GET" action="{{ route('piket.exit-passes.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" class="rounded-xl border border-slate-300 px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status</label>
                <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="out" {{ $status === 'out' ? 'selected' : '' }}>Masih Keluar</option>
                    <option value="returned" {{ $status === 'returned' ? 'selected' : '' }}>Sudah Kembali</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[11px]">
                        <th class="py-2 pr-3">Siswa</th>
                        <th class="py-2 px-3">Kelas</th>
                        <th class="py-2 px-3">Keperluan</th>
                        <th class="py-2 px-3">Keluar</th>
                        <th class="py-2 px-3">Kembali</th>
                        <th class="py-2 pl-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($passes as $pass)
                        <tr>
                            <td class="py-2.5 pr-3 font-bold text-slate-900">{{ $pass->student?->name ?? '—' }}</td>
                            <td class="py-2.5 px-3">{{ $pass->student?->schoolClass?->name ?? '—' }}</td>
                            <td class="py-2.5 px-3">{{ $pass->reason_label }}</td>
                            <td class="py-2.5 px-3">{{ $pass->out_time?->format('H:i') }}</td>
                            <td class="py-2.5 px-3">{{ $pass->in_time ? $pass->in_time->format('H:i') . " ({$pass->duration_minutes} mnt)" : '—' }}</td>
                            <td class="py-2.5 pl-3">
                                @if($pass->status === 'out')
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Di Luar</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">Kembali</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Belum ada data izin keluar pada tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $passes->links() }}</div>
    </div>
</div>
@endsection
