@extends('layouts.piket')
@section('title', 'Riwayat Shift & Log Piket')
@section('page-title', 'Riwayat Shift Guru Piket & Log Aktivitas')

@section('content')
<div class="space-y-5">

    <form method="GET" action="{{ route('piket.logs.index') }}" class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Filter Tanggal</label>
            <input type="date" name="date" value="{{ $date }}" class="rounded-xl border border-slate-300 px-3 py-2 text-xs">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Tampilkan</button>
        @if($date)
            <a href="{{ route('piket.logs.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600">Semua Tanggal</a>
        @endif
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Riwayat Shift Guru Piket --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
            <h2 class="text-sm font-extrabold text-slate-900">Riwayat Shift Guru Bertugas</h2>
            <div class="space-y-2">
                @forelse($shifts as $shift)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-slate-900">{{ $shift->teacher?->name ?? '—' }}</p>
                            <p class="text-[11px] text-slate-500">
                                Mulai: {{ $shift->started_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                                @if($shift->ended_at)
                                    • Selesai: {{ $shift->ended_at->locale('id')->translatedFormat('d M Y, H:i') }}
                                @endif
                            </p>
                            @if($shift->notes)
                                <p class="text-[11px] text-slate-600 mt-0.5">Catatan: {{ $shift->notes }}</p>
                            @endif
                        </div>
                        @if(!$shift->ended_at)
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10px] font-bold shrink-0">Aktif</span>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada riwayat shift piket.</p>
                @endforelse
            </div>
            <div>{{ $shifts->links() }}</div>
        </div>

        {{-- Log Aktivitas Verifikasi Piket --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
            <h2 class="text-sm font-extrabold text-slate-900">Log Aktivitas Meja Piket</h2>
            <div class="space-y-2">
                @forelse($activityLogs as $log)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-900">
                                {{ $log->changes['piket_teacher_name'] ?? $log->changes['teacher_name'] ?? 'Guru Piket' }}
                            </span>
                            <span class="text-[10.5px] text-slate-400">{{ $log->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <p class="text-[11px] text-slate-600 mt-0.5">
                            Aksi: <strong>{{ str_replace('_', ' ', $log->action) }}</strong>
                            @if($log->ip_address) • IP {{ $log->ip_address }} @endif
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada log aktivitas piket.</p>
                @endforelse
            </div>
            <div>{{ $activityLogs->links() }}</div>
        </div>
    </div>
</div>
@endsection
