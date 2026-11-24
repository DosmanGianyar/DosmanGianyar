@extends('layouts.piket')
@section('title', 'Riwayat Shift & Log Piket')
@section('page-title', 'Riwayat Shift Guru Piket & Log Aktivitas')

@section('content')
<div class="space-y-5">

    <form method="GET" action="{{ route('piket.logs.index') }}" class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Filter Tanggal</label>
            <input type="date" name="date" value="{{ $date }}" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
        </div>
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">Tampilkan</button>
        @if($date)
            <a href="{{ route('piket.logs.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50">Semua Tanggal</a>
        @endif
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Riwayat Shift Guru Piket --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-3.5">
            <h2 class="text-lg font-extrabold text-slate-900">Riwayat Shift Guru Bertugas</h2>
            <div class="space-y-2.5">
                @forelse($shifts as $shift)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-base font-bold text-slate-900">{{ $shift->teacher?->name ?? '—' }}</p>
                            <p class="text-sm text-slate-500 mt-0.5">
                                Mulai: {{ $shift->started_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                                @if($shift->ended_at)
                                    • Selesai: {{ $shift->ended_at->locale('id')->translatedFormat('d M Y, H:i') }}
                                @endif
                            </p>
                            @if($shift->notes)
                                <p class="text-sm text-slate-600 mt-0.5">Catatan: {{ $shift->notes }}</p>
                            @endif
                        </div>
                        @if(!$shift->ended_at)
                            <span class="px-2.5 py-1 rounded bg-emerald-100 text-emerald-700 text-xs font-bold shrink-0">Aktif</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-6">Belum ada riwayat shift piket.</p>
                @endforelse
            </div>
            <div>{{ $shifts->links() }}</div>
        </div>

        {{-- Log Aktivitas Verifikasi Piket --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-3.5">
            <h2 class="text-lg font-extrabold text-slate-900">Log Aktivitas Meja Piket</h2>
            <div class="space-y-2.5">
                @forelse($activityLogs as $log)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-base font-bold text-slate-900">
                                {{ $log->changes['piket_teacher_name'] ?? $log->changes['teacher_name'] ?? 'Guru Piket' }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $log->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <p class="text-sm text-slate-600 mt-0.5">
                            Aksi: <strong>{{ str_replace('_', ' ', $log->action) }}</strong>
                            @if($log->ip_address) • IP {{ $log->ip_address }} @endif
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-6">Belum ada log aktivitas piket.</p>
                @endforelse
            </div>
            <div>{{ $activityLogs->links() }}</div>
        </div>
    </div>
</div>
@endsection
