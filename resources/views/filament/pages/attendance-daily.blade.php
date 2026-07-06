<x-filament-panels::page>
<style>
.ad-filter-bar {
    background: #0f1d33;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 0.75rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.ad-filter-group { display: flex; flex-direction: column; gap: 0.35rem; min-width: 160px; }
.ad-filter-label { font-size: 0.7rem; font-weight: 700; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.07em; }
.ad-select, .ad-input {
    background: #0d1628;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 0.5rem;
    color: rgba(255,255,255,0.9);
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    outline: none;
}
.ad-select {
    padding-right: 2rem;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.25em 1.25em;
}
.ad-select:focus, .ad-input:focus { border-color: rgba(245,158,11,0.5); }
.ad-stats { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.ad-stat { background: #0f1d33; border: 1px solid rgba(255,255,255,0.07); border-radius: 0.75rem; padding: 0.875rem 1.25rem; flex: 1; min-width: 110px; }
.ad-stat-label { font-size: 0.68rem; color: rgba(255,255,255,0.4); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem; }
.ad-stat-value { font-size: 1.5rem; font-weight: 800; color: rgba(255,255,255,0.9); line-height: 1.2; }
.ad-stat-value.hadir      { color: rgb(74,222,128); }
.ad-stat-value.terlambat  { color: rgb(250,204,21); }
.ad-stat-value.izin       { color: rgb(96,165,250); }
.ad-stat-value.sakit      { color: rgb(192,132,252); }
.ad-stat-value.alpa       { color: rgb(248,113,113); }
.ad-stat-value.dispensasi { color: rgb(45,212,191); }
.ad-table-wrap { background: #0f1d33; border: 1px solid rgba(255,255,255,0.07); border-radius: 1rem; overflow: auto; box-shadow: 0 4px 24px rgba(0,0,0,0.3); }
.ad-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; min-width: 500px; }
.ad-table thead tr { border-bottom: 1px solid rgba(255,255,255,0.08); }
.ad-table th { padding: 0.7rem 1rem; text-align: left; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: rgba(255,255,255,0.35); white-space: nowrap; }
.ad-table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.12s; }
.ad-table tbody tr:last-child { border-bottom: none; }
.ad-table tbody tr:hover { background: rgba(255,255,255,0.025); }
.ad-table td { padding: 0.65rem 1rem; color: rgba(255,255,255,0.75); }
.ad-table td.name { font-weight: 600; color: rgba(255,255,255,0.92); }
.ad-table td.nis  { color: rgba(255,255,255,0.35); font-size: 0.72rem; font-family: monospace; }
.ad-table td.time { color: rgba(255,255,255,0.4); font-size: 0.78rem; font-family: monospace; }
.ad-badge {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0.25rem 0.75rem; border-radius: 99px;
    font-size: 0.78rem; font-weight: 700; letter-spacing: 0.01em;
}
.ad-badge.hadir      { background: rgba(34,197,94,0.15);  color: rgb(74,222,128); }
.ad-badge.terlambat  { background: rgba(234,179,8,0.15);  color: rgb(250,204,21); }
.ad-badge.izin       { background: rgba(59,130,246,0.15); color: rgb(96,165,250); }
.ad-badge.sakit      { background: rgba(168,85,247,0.15); color: rgb(192,132,252); }
.ad-badge.alpa       { background: rgba(239,68,68,0.18);  color: rgb(248,113,113); }
.ad-badge.dispensasi { background: rgba(20,184,166,0.15); color: rgb(45,212,191); }
.ad-empty { text-align: center; padding: 4rem 2rem; color: rgba(255,255,255,0.25); }
.ad-legend {
    display: flex; gap: 0.875rem; flex-wrap: wrap;
    margin-top: 0.75rem; font-size: 0.75rem;
    color: rgba(255,255,255,0.5); align-items: center;
}
.ad-legend span { display: flex; align-items: center; gap: 0.35rem; }
.ad-tabs { display: flex; gap: 0.35rem; background: #0d1628; border: 1px solid rgba(255,255,255,0.07); border-radius: 0.75rem; padding: 0.3rem; margin-bottom: 1rem; width: fit-content; }
.ad-tab { padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; color: rgba(255,255,255,0.45); cursor: pointer; border: none; background: transparent; }
.ad-tab.active { background: rgba(245,158,11,0.15); color: rgb(251,191,36); }
.ad-photo-btn { width: 2.25rem; height: 2.25rem; border-radius: 0.6rem; overflow: hidden; border: 1px solid rgba(255,255,255,0.12); padding: 0; cursor: pointer; background: rgba(255,255,255,0.03); }
.ad-photo-btn img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ad-photo-empty { width: 2.25rem; height: 2.25rem; border-radius: 0.6rem; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.2); font-size: 0.7rem; background: rgba(255,255,255,0.03); }
.ad-del-btn { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.35rem 0.65rem; border-radius: 0.5rem; font-size: 0.72rem; font-weight: 600; color: rgb(248,113,113); background: rgba(239,68,68,0.12); border: none; cursor: pointer; }
.ad-del-btn:hover { background: rgba(239,68,68,0.22); }
.ad-photo-modal { position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.75); padding: 1rem; }
.ad-photo-modal img { width: 100%; max-height: 70vh; object-fit: contain; border-radius: 1rem; background: #000; }
</style>

@php
    $rows    = $this->getRows();
    $summary = $this->getSummary($rows);
    $classes = $this->getClasses();
    $total   = count($rows);
    $hadir   = $summary['hadir'] + $summary['terlambat'] + $summary['dispensasi'];
    $dateLabel = \Carbon\Carbon::parse($this->date)->isoFormat('dddd, D MMMM Y');
    $statusLabel = [
        'hadir'      => 'Hadir',
        'terlambat'  => 'Terlambat',
        'izin'       => 'Izin',
        'sakit'      => 'Sakit',
        'alpa'       => 'Alpa',
        'dispensasi' => 'Dispensasi',
    ];
@endphp

{{-- Filter bar --}}
<div class="ad-filter-bar">
    <div class="ad-filter-group" style="min-width:220px">
        <span class="ad-filter-label">Kelas</span>
        <select class="ad-select" wire:model.live="classId">
            <option value="">— Pilih Kelas —</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="ad-filter-group">
        <span class="ad-filter-label">Tanggal</span>
        <input type="date" class="ad-input" wire:model.live="date" style="min-width:160px">
    </div>

    <div style="margin-left:auto;display:flex;align-items:flex-end">
        <span style="font-size:0.78rem;color:rgba(255,255,255,0.3);line-height:1.4">
            {{ $dateLabel }}
        </span>
    </div>
</div>

@if (! $this->classId)
{{-- Prompt to select class --}}
<div class="ad-table-wrap">
    <div class="ad-empty">
        <svg style="width:3rem;height:3rem;margin:0 auto 1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <p style="font-size:0.9rem">Pilih kelas untuk melihat data absensi.</p>
    </div>
</div>
@else
{{-- Summary cards --}}
<div class="ad-stats">
    <div class="ad-stat">
        <div class="ad-stat-label">Total Siswa</div>
        <div class="ad-stat-value">{{ $total }}</div>
    </div>
    <div class="ad-stat">
        <div class="ad-stat-label">Hadir</div>
        <div class="ad-stat-value hadir">{{ $hadir }}</div>
    </div>
    <div class="ad-stat">
        <div class="ad-stat-label">Terlambat</div>
        <div class="ad-stat-value terlambat">{{ $summary['terlambat'] }}</div>
    </div>
    <div class="ad-stat">
        <div class="ad-stat-label">Izin</div>
        <div class="ad-stat-value izin">{{ $summary['izin'] }}</div>
    </div>
    <div class="ad-stat">
        <div class="ad-stat-label">Sakit</div>
        <div class="ad-stat-value sakit">{{ $summary['sakit'] }}</div>
    </div>
    <div class="ad-stat">
        <div class="ad-stat-label">Alpa</div>
        <div class="ad-stat-value alpa">{{ $summary['alpa'] }}</div>
    </div>
    <div class="ad-stat">
        <div class="ad-stat-label">Dispensasi</div>
        <div class="ad-stat-value dispensasi">{{ $summary['dispensasi'] }}</div>
    </div>
</div>

{{-- Tab Absen Datang / Absen Pulang --}}
<div x-data="{ tab: 'masuk' }">
    <div class="ad-tabs">
        <button type="button" class="ad-tab" :class="tab === 'masuk' ? 'active' : ''" @click="tab = 'masuk'">Absen Datang</button>
        <button type="button" class="ad-tab" :class="tab === 'pulang' ? 'active' : ''" @click="tab = 'pulang'">Absen Pulang</button>
    </div>

    {{-- Table --}}
    <div class="ad-table-wrap" x-data="{ photoUrl: null, photoName: '' }">
    @if (empty($rows))
    <div class="ad-empty">
        <svg style="width:3rem;height:3rem;margin:0 auto 1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p style="font-size:0.9rem">Tidak ada siswa di kelas ini.</p>
    </div>
    @else
    <table class="ad-table">
        <thead>
            <tr>
                <th style="width:2.5rem">#</th>
                <th>Nama Siswa</th>
                <th>NIS</th>
                <th>Status</th>
                <th><span x-text="tab === 'masuk' ? 'Jam Masuk' : 'Jam Pulang'"></span></th>
                <th>Foto</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
            <tr>
                <td style="color:rgba(255,255,255,0.2);font-size:0.72rem;text-align:right">{{ $i + 1 }}</td>
                <td class="name">{{ $row['name'] }}</td>
                <td class="nis">{{ $row['nis'] }}</td>
                <td><span class="ad-badge {{ $row['status'] }}">{{ $statusLabel[$row['status']] ?? ucfirst($row['status']) }}</span></td>
                <td class="time">
                    <span x-show="tab === 'masuk'">{{ $row['check_in'] ?? '—' }}</span>
                    <span x-show="tab === 'pulang'" style="display:none">{{ $row['check_out'] ?? '—' }}</span>
                </td>
                <td>
                    <template x-if="tab === 'masuk'">
                        @if($row['photo_in_url'])
                        <button type="button" class="ad-photo-btn"
                            @click="photoUrl = '{{ $row['photo_in_url'] }}'; photoName = '{{ addslashes($row['name']) }} — Absen Datang'">
                            <img src="{{ $row['photo_in_url'] }}">
                        </button>
                        @else
                        <div class="ad-photo-empty">—</div>
                        @endif
                    </template>
                    <template x-if="tab === 'pulang'">
                        @if($row['photo_out_url'])
                        <button type="button" class="ad-photo-btn"
                            @click="photoUrl = '{{ $row['photo_out_url'] }}'; photoName = '{{ addslashes($row['name']) }} — Absen Pulang'">
                            <img src="{{ $row['photo_out_url'] }}">
                        </button>
                        @else
                        <div class="ad-photo-empty">—</div>
                        @endif
                    </template>
                </td>
                <td>
                    {{-- TESTING ONLY — hapus tombol ini setelah tahap uji coba selesai --}}
                    @if($row['attendance_id'])
                    <button type="button" class="ad-del-btn"
                        wire:click="deleteAttendance({{ $row['attendance_id'] }})"
                        wire:confirm="Hapus data absensi {{ addslashes($row['name']) }} tanggal {{ $this->date }}? (mode testing)">
                        <svg style="width:0.85rem;height:0.85rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Photo popup modal --}}
    <div x-show="photoUrl" x-cloak
        @click.self="photoUrl = null"
        @keydown.escape.window="photoUrl = null"
        class="ad-photo-modal">
        <div style="max-width:24rem;width:100%" @click.stop>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem">
                <p style="color:#fff;font-size:0.85rem;font-weight:600" x-text="photoName"></p>
                <button type="button" @click="photoUrl = null" style="color:rgba(255,255,255,0.7);background:none;border:none;cursor:pointer">
                    <svg style="width:1.5rem;height:1.5rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <img :src="photoUrl">
        </div>
    </div>
    </div>
</div>

{{-- Legend --}}
<div class="ad-legend">
    <span><span class="ad-badge hadir">Hadir</span></span>
    <span><span class="ad-badge terlambat">Terlambat</span></span>
    <span><span class="ad-badge izin">Izin</span></span>
    <span><span class="ad-badge sakit">Sakit</span></span>
    <span><span class="ad-badge alpa">Alpa</span></span>
    <span><span class="ad-badge dispensasi">Dispensasi</span></span>
    <span style="margin-left:auto;opacity:0.5;font-size:0.72rem">Alpa = belum scan / ajuan belum disetujui</span>
</div>
@endif

<x-filament-actions::modals />
</x-filament-panels::page>
