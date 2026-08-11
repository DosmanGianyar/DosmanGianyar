<x-filament-panels::page>
<div x-data="{ showGridPreviewModal: false }">
<style>
.ar-filter-bar {
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
.ar-filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    min-width: 160px;
}
.ar-filter-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 0.07em;
}
.ar-select {
    background: #0d1628;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 0.5rem;
    color: rgba(255,255,255,0.9);
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    font-size: 0.875rem;
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.25em 1.25em;
    transition: border-color 0.15s;
}
.ar-select:focus { border-color: rgba(245,158,11,0.5); }
.ar-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.ar-stat-card {
    background: #0f1d33;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    flex: 1;
    min-width: 140px;
}
.ar-stat-label {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.4);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.35rem;
}
.ar-stat-value {
    font-size: 1.4rem;
    font-weight: 800;
    color: rgba(255,255,255,0.9);
    line-height: 1.2;
}
.ar-stat-value.amber  { color: rgb(245,158,11); }
.ar-stat-value.green  { color: rgb(74,222,128); }
.ar-stat-value.yellow { color: rgb(250,204,21); }
.ar-stat-value.red    { color: rgb(248,113,113); }
.ar-table-wrap {
    background: #0f1d33;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 1rem;
    overflow: auto;
    box-shadow: 0 4px 24px rgba(0,0,0,0.3);
    min-height: 380px;
}
.ar-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
    min-width: 680px;
}
.ar-table thead tr {
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.ar-table th {
    padding: 0.7rem 1rem;
    text-align: left;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.35);
    white-space: nowrap;
}
.ar-table th.c, .ar-table td.c { text-align: center; }
.ar-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.12s;
}
.ar-table tbody tr:last-child { border-bottom: none; }
.ar-table tbody tr:hover { background: rgba(255,255,255,0.025); }
.ar-table td {
    padding: 0.6rem 1rem;
    color: rgba(255,255,255,0.75);
    white-space: nowrap;
}
.ar-table td.name  { font-weight: 600; color: rgba(255,255,255,0.92); max-width: 200px; overflow: hidden; text-overflow: ellipsis; }
.ar-table td.nis   { color: rgba(255,255,255,0.35); font-size: 0.72rem; font-family: monospace; }
.ar-table td.kelas { color: rgba(255,255,255,0.5); font-size: 0.78rem; }
.ar-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.8rem;
    height: 1.5rem;
    padding: 0 0.45rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 700;
}
.ar-badge.hadir      { background: rgba(34,197,94,0.15);  color: rgb(74,222,128); }
.ar-badge.terlambat  { background: rgba(234,179,8,0.15);  color: rgb(250,204,21); }
.ar-badge.izin       { background: rgba(59,130,246,0.15); color: rgb(96,165,250); }
.ar-badge.sakit      { background: rgba(168,85,247,0.15); color: rgb(192,132,252); }
.ar-badge.alpa       { background: rgba(239,68,68,0.18);  color: rgb(248,113,113); }
.ar-badge.dispensasi { background: rgba(20,184,166,0.15); color: rgb(45,212,191); }
.ar-badge.zero       { opacity: 0.2; }
.ar-pct { font-weight: 700; font-size: 0.8rem; }
.ar-pct.high  { color: rgb(74,222,128); }
.ar-pct.mid   { color: rgb(250,204,21); }
.ar-pct.low   { color: rgb(248,113,113); }
.ar-class-sep td {
    background: rgba(245,158,11,0.05);
    border-top: 1px solid rgba(245,158,11,0.18) !important;
    color: rgb(245,158,11);
    font-weight: 700;
    font-size: 0.7rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 0.45rem 1rem;
}
.ar-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: rgba(255,255,255,0.25);
}
.ar-legend {
    display: flex;
    gap: 0.875rem;
    flex-wrap: wrap;
    margin-top: 0.75rem;
    opacity: 0.55;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.7);
    align-items: center;
}
.ar-legend span { display: flex; align-items: center; gap: 0.35rem; }

/* Student Detail Modal Custom Styles */
.ar-modal-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    z-index: 999999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 1rem !important;
    background: rgba(0, 0, 0, 0.8) !important;
    backdrop-filter: blur(4px) !important;
}
.ar-modal-card {
    background: #0f1d33 !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-radius: 1rem !important;
    max-width: 42rem !important;
    width: 100% !important;
    max-height: 90vh !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
}
.ar-modal-header {
    padding: 1.25rem !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    display: flex !important;
    align-items: flex-start !important;
    justify-content: space-between !important;
    background: #0d1628 !important;
}
.ar-modal-title {
    font-size: 1.125rem !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 0.5rem !important;
    margin: 0 !important;
    line-height: 1.4 !important;
}
.ar-modal-icon {
    width: 20px !important;
    height: 20px !important;
    min-width: 20px !important;
    min-height: 20px !important;
    max-width: 20px !important;
    max-height: 20px !important;
    flex-shrink: 0 !important;
    display: inline-block !important;
    color: #f59e0b !important;
}
.ar-modal-close-icon {
    width: 24px !important;
    height: 24px !important;
    min-width: 24px !important;
    min-height: 24px !important;
    max-width: 24px !important;
    max-height: 24px !important;
    flex-shrink: 0 !important;
    display: inline-block !important;
}
.ar-modal-subtitle {
    font-size: 0.75rem !important;
    color: rgba(255, 255, 255, 0.5) !important;
    margin-top: 0.25rem !important;
    margin-bottom: 0 !important;
}
.ar-modal-summary-grid {
    padding: 1rem !important;
    background: #0b1220 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
    display: grid !important;
    grid-template-columns: repeat(6, 1fr) !important;
    gap: 0.5rem !important;
    text-align: center !important;
}
.ar-modal-summary-card {
    background: rgba(255, 255, 255, 0.05) !important;
    padding: 0.5rem !important;
    border-radius: 0.75rem !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
}
.ar-modal-tabs {
    padding: 0.75rem 1.25rem 0 1.25rem !important;
    display: flex !important;
    flex-direction: row !important;
    gap: 0.5rem !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
}
.ar-modal-tab-btn {
    padding-bottom: 0.5rem !important;
    font-size: 0.75rem !important;
    background: none !important;
    border-top: none !important;
    border-left: none !important;
    border-right: none !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
}
.ar-modal-body {
    padding: 1.25rem !important;
    overflow-y: auto !important;
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 0.5rem !important;
}
.ar-modal-log-item {
    padding: 0.75rem !important;
    border-radius: 0.75rem !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    background: rgba(255, 255, 255, 0.04) !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 0.75rem !important;
    font-size: 0.75rem !important;
}
.ar-modal-footer {
    padding: 0.75rem 1.25rem !important;
    border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
    background: #0d1628 !important;
    text-align: right !important;
}
</style>

@php
    $report      = $this->getReportData();
    $rows        = $report['rows'];
    $workingDays = $report['working_days'];
    $total       = $report['total'];
    $classes     = $this->getClasses();
    $years       = $this->getYears();
    $monthName   = $this->getMonthName();
    $showClass   = ! $this->classId;
    $months = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
    ];
    $avgPct = $total > 0 ? round(collect($rows)->avg('pct'), 1) : 0;
    $avgClass = $avgPct >= 90 ? 'green' : ($avgPct >= 75 ? 'yellow' : 'red');
@endphp

{{-- Filter bar --}}
<div class="ar-filter-bar">
    <div class="ar-filter-group">
        <span class="ar-filter-label">Kelas</span>
        <select class="ar-select" wire:model.live="classId" style="min-width:200px">
            <option value="">— Semua Kelas —</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="ar-filter-group">
        <span class="ar-filter-label">Bulan</span>
        <select class="ar-select" wire:model.live="month">
            @foreach ($months as $num => $name)
                <option value="{{ $num }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div class="ar-filter-group">
        <span class="ar-filter-label">Tahun</span>
        <select class="ar-select" wire:model.live="year">
            @foreach ($years as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>
    </div>

    @php
        $dlParams = http_build_query(array_filter([
            'class_id' => $this->classId,
            'month'    => $this->month,
            'year'     => $this->year,
        ]));
        $targetClassId = $this->classId ?: ($classes->first()?->id);
        $gridMonthStr  = sprintf('%04d-%02d', $this->year, $this->month);
    @endphp
    <div style="margin-left:auto;display:flex;flex-wrap:wrap;gap:0.625rem;align-items:flex-end">
        {{-- Preview Cetak Laporan Grid (PDF) --}}
        <button type="button"
            @click="showGridPreviewModal = true"
            style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35);border-radius:0.5rem;color:rgb(52,211,153);font-size:0.8rem;font-weight:700;cursor:pointer;transition:background 0.15s"
            onmouseover="this.style.background='rgba(16,185,129,0.25)'" onmouseout="this.style.background='rgba(16,185,129,0.15)'">
            <svg style="width:1rem;height:1rem;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            👁️ Preview Rekap Grid (PDF)
        </button>

        @if ($targetClassId)
        <a href="{{ route('admin.attendance-report.grid-pdf', ['month' => $gridMonthStr, 'class_id' => $targetClassId]) }}"
            target="_blank"
            style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.35);border-radius:0.5rem;color:rgb(96,165,250);font-size:0.8rem;font-weight:700;text-decoration:none;transition:background 0.15s"
            onmouseover="this.style.background='rgba(59,130,246,0.25)'" onmouseout="this.style.background='rgba(59,130,246,0.15)'">
            <svg style="width:1rem;height:1rem;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Cetak Rekap Grid (PDF)
        </a>
        @endif

        <a href="{{ route('admin.attendance-report.excel') . '?' . $dlParams }}"
           target="_blank"
           style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.3);border-radius:0.5rem;color:rgb(74,222,128);font-size:0.8rem;font-weight:600;text-decoration:none;transition:background 0.15s"
           onmouseover="this.style.background='rgba(34,197,94,0.2)'" onmouseout="this.style.background='rgba(34,197,94,0.12)'">
            <svg style="width:1rem;height:1rem;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Summary Excel
        </a>
        <a href="{{ route('admin.attendance-report.pdf') . '?' . $dlParams }}"
           target="_blank"
           style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);border-radius:0.5rem;color:rgb(248,113,113);font-size:0.8rem;font-weight:600;text-decoration:none;transition:background 0.15s"
           onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.12)'">
            <svg style="width:1rem;height:1rem;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Summary PDF
        </a>
    </div>
</div>

{{-- Tab Navigation --}}
<div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;border-bottom:1px solid rgba(255,255,255,0.08);padding-bottom:0.75rem">
    <button type="button" wire:click="$set('activeTab', 'grid')"
        style="padding:0.5rem 1rem;font-size:0.8rem;font-weight:700;border-radius:0.5rem;cursor:pointer;transition:all 0.15s;border:none;{{ $activeTab === 'grid' ? 'background:rgb(245,158,11);color:#000;' : 'background:#0f1d33;color:rgba(255,255,255,0.7);' }}">
        📅 Rekap Grid Tanggal (1 s/d 31)
    </button>
    <button type="button" wire:click="$set('activeTab', 'summary')"
        style="padding:0.5rem 1rem;font-size:0.8rem;font-weight:700;border-radius:0.5rem;cursor:pointer;transition:all 0.15s;border:none;{{ $activeTab === 'summary' ? 'background:rgb(245,158,11);color:#000;' : 'background:#0f1d33;color:rgba(255,255,255,0.7);' }}">
        📊 Ringkasan Total Bulanan
    </button>
</div>

@if ($activeTab === 'grid')
    @php $gridData = $this->getGridReportData(); @endphp
    @if (!empty($gridData) && count($gridData['students']) > 0)
    <div style="background:#0f1d33;border:1px solid rgba(255,255,255,0.07);border-radius:1rem;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.3);margin-bottom:1.5rem">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem">
            <div>
                <h3 style="font-size:0.95rem;font-weight:700;color:rgba(255,255,255,0.9)">
                    Rekapitulasi Absensi Grid Tanggal 1 s/d {{ $gridData['daysInMonth'] }} — Kelas {{ $gridData['className'] }}
                </h3>
                <p style="font-size:0.75rem;color:rgba(255,255,255,0.4)">Periode {{ $monthName }} &middot; Wali Kelas: {{ $gridData['homeroomName'] }}</p>
            </div>
            <div style="display:flex;gap:0.4rem;align-items:center;font-size:0.72rem;color:rgba(255,255,255,0.6);flex-wrap:wrap">
                <span style="padding:0.2rem 0.5rem;border-radius:0.25rem;background:#064e3b;color:#a7f3d0;border:1px solid #047857;font-weight:700">H = Hadir</span>
                <span style="padding:0.2rem 0.5rem;border-radius:0.25rem;background:#713f12;color:#fef08a;border:1px solid #a16207;font-weight:700">T = Terlambat</span>
                <span style="padding:0.2rem 0.5rem;border-radius:0.25rem;background:#581c87;color:#f3e8ff;border:1px solid #7e22ce;font-weight:700">Lp = Lupa Absen</span>
                <span style="padding:0.2rem 0.5rem;border-radius:0.25rem;background:#4c1d95;color:#ddd6fe;border:1px solid #6d28d9;font-weight:700">S = Sakit</span>
                <span style="padding:0.2rem 0.5rem;border-radius:0.25rem;background:#1e3a8a;color:#bfdbfe;border:1px solid #1d4ed8;font-weight:700">I = Izin</span>
                <span style="padding:0.2rem 0.5rem;border-radius:0.25rem;background:#134e4a;color:#99f6e4;border:1px solid #0f766e;font-weight:700">D = Dispen</span>
                <span style="padding:0.2rem 0.5rem;border-radius:0.25rem;background:#7f1d1d;color:#fecaca;border:1px solid #b91c1c;font-weight:700">A = Alpa</span>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table class="ar-table" style="min-width:1100px">
                <thead>
                    <tr>
                        <th style="width:2.2rem">#</th>
                        <th style="min-width:180px">Nama Siswa</th>
                        <th style="width:70px">NIS</th>
                        @for ($d = 1; $d <= $gridData['daysInMonth']; $d++)
                            <th class="c" style="width:26px;padding:0.4rem 0.2rem;font-size:0.65rem;border-left:1px solid rgba(255,255,255,0.05)">{{ $d }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gridData['students'] as $idx => $st)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04)">
                        <td style="color:rgba(255,255,255,0.25);font-size:0.72rem;text-align:right">{{ $idx + 1 }}</td>
                        <td style="font-weight:600;color:rgba(255,255,255,0.9)">
                            <button type="button" wire:click="openStudentDetail({{ $st->id }})" class="hover:underline text-amber-300 font-semibold text-left cursor-pointer">
                                {{ $st->name }}
                            </button>
                        </td>
                        <td style="color:rgba(255,255,255,0.4);font-size:0.72rem;font-family:monospace">{{ $st->nis ?? '—' }}</td>
                        @for ($d = 1; $d <= $gridData['daysInMonth']; $d++)
                            @php
                                $cell = $gridData['grid'][$st->id][$d] ?? ['status' => null, 'via_lupa_absen' => false];
                                $status = strtolower($cell['status'] ?? '');
                                $isLupa  = $cell['via_lupa_absen'];
                            @endphp
                            <td class="c" style="padding:0.35rem 0.1rem;border-left:1px solid rgba(255,255,255,0.04);text-align:center">
                                @if ($isLupa)
                                    <span class="ar-badge" style="background:#581c87;color:#f3e8ff;border:1px solid #7e22ce;font-size:0.65rem;padding:0.1rem 0.25rem" title="Hadir via Lupa Absen">Lp</span>
                                @elseif ($status === 'hadir')
                                    <span class="ar-badge hadir" style="font-size:0.65rem;padding:0.1rem 0.3rem">H</span>
                                @elseif ($status === 'terlambat')
                                    <span class="ar-badge terlambat" style="font-size:0.65rem;padding:0.1rem 0.3rem">T</span>
                                @elseif ($status === 'sakit')
                                    <span class="ar-badge sakit" style="font-size:0.65rem;padding:0.1rem 0.3rem">S</span>
                                @elseif ($status === 'izin')
                                    <span class="ar-badge izin" style="font-size:0.65rem;padding:0.1rem 0.3rem">I</span>
                                @elseif ($status === 'dispensasi')
                                    <span class="ar-badge dispensasi" style="font-size:0.65rem;padding:0.1rem 0.3rem">D</span>
                                @elseif ($status === 'alpa')
                                    <span class="ar-badge alpa" style="font-size:0.65rem;padding:0.1rem 0.3rem">A</span>
                                @else
                                    <span style="color:rgba(255,255,255,0.15)">·</span>
                                @endif
                            </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@elseif ($activeTab === 'summary')

{{-- Summary cards --}}
<div class="ar-stats">
    <div class="ar-stat-card">
        <div class="ar-stat-label">Periode</div>
        <div class="ar-stat-value amber">{{ $monthName }}</div>
    </div>
    <div class="ar-stat-card">
        <div class="ar-stat-label">Total Siswa</div>
        <div class="ar-stat-value">{{ $total }}</div>
    </div>
    <div class="ar-stat-card">
        <div class="ar-stat-label">Hari Efektif</div>
        <div class="ar-stat-value">{{ $workingDays }} <span style="font-size:0.9rem;font-weight:500;opacity:0.5">hari</span></div>
    </div>
    @if ($total > 0)
    <div class="ar-stat-card">
        <div class="ar-stat-label">Rata-rata Kehadiran</div>
        <div class="ar-stat-value {{ $avgClass }}">{{ $avgPct }}%</div>
    </div>
    @endif
</div>

{{-- Report table --}}
<div class="ar-table-wrap">
    @if (empty($rows))
        <div class="ar-empty">
            <svg style="width:3rem;height:3rem;margin:0 auto 1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p style="font-size:0.9rem">Belum ada data presensi untuk periode ini.</p>
        </div>
    @else
    <table class="ar-table">
        <thead>
            <tr>
                <th style="width:2.5rem">#</th>
                <th>Nama Siswa</th>
                <th>NIS</th>
                @if ($showClass) <th>Kelas</th> @endif
                <th class="c" title="Hadir Tepat Waktu">H</th>
                <th class="c" title="Terlambat">T</th>
                <th class="c" title="Izin">I</th>
                <th class="c" title="Sakit">S</th>
                <th class="c" title="Alpa">A</th>
                <th class="c" title="Dispensasi">D</th>
                <th class="c">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @php $prevClass = null; $no = 1; @endphp
            @foreach ($rows as $row)
                @if ($showClass && $row['class'] !== $prevClass)
                    <tr class="ar-class-sep">
                        <td colspan="{{ $showClass ? 11 : 10 }}">Kelas {{ $row['class'] }}</td>
                    </tr>
                    @php $prevClass = $row['class']; @endphp
                @endif
                <tr>
                    <td style="color:rgba(255,255,255,0.2);font-size:0.72rem;text-align:right">{{ $no++ }}</td>
                    <td class="name">
                        <button type="button" wire:click="openStudentDetail({{ $row['id'] }})"
                            class="hover:underline text-amber-300 font-semibold cursor-pointer text-left transition-colors">
                            {{ $row['name'] }}
                        </button>
                    </td>
                    <td class="nis">{{ $row['nis'] }}</td>
                    @if ($showClass) <td class="kelas">{{ $row['class'] }}</td> @endif
                    <td class="c"><span class="ar-badge hadir {{ $row['hadir'] === 0 ? 'zero' : '' }}">{{ $row['hadir'] }}</span></td>
                    <td class="c"><span class="ar-badge terlambat {{ $row['terlambat'] === 0 ? 'zero' : '' }}">{{ $row['terlambat'] }}</span></td>
                    <td class="c"><span class="ar-badge izin {{ $row['izin'] === 0 ? 'zero' : '' }}">{{ $row['izin'] }}</span></td>
                    <td class="c"><span class="ar-badge sakit {{ $row['sakit'] === 0 ? 'zero' : '' }}">{{ $row['sakit'] }}</span></td>
                    <td class="c"><span class="ar-badge alpa {{ $row['alpa'] === 0 ? 'zero' : '' }}">{{ $row['alpa'] }}</span></td>
                    <td class="c"><span class="ar-badge dispensasi {{ $row['dispensasi'] === 0 ? 'zero' : '' }}">{{ $row['dispensasi'] }}</span></td>
                    <td class="c">
                        <span class="ar-pct {{ $row['pct'] >= 90 ? 'high' : ($row['pct'] >= 75 ? 'mid' : 'low') }}">
                            {{ $row['pct'] }}%
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Legend --}}
<div class="ar-legend">
    <span><span class="ar-badge hadir">H</span> Hadir</span>
    <span><span class="ar-badge terlambat">T</span> Terlambat</span>
    <span><span class="ar-badge izin">I</span> Izin</span>
    <span><span class="ar-badge sakit">S</span> Sakit</span>
    <span><span class="ar-badge alpa">A</span> Alpa</span>
    <span><span class="ar-badge dispensasi">D</span> Dispensasi</span>
    <span style="margin-left:auto;opacity:0.6">*Klik nama siswa untuk melihat rincian tanggal alpa/izin/sakit</span>
</div>
@endif

{{-- Student Detail Modal --}}
@if($showDetailModal && $studentDetailData)
<div class="ar-modal-overlay" x-data="{ filter: 'all' }">
    <div class="ar-modal-card">
        {{-- Header --}}
        <div class="ar-modal-header">
            <div>
                <h3 class="ar-modal-title">
                    <svg class="ar-modal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ $studentDetailData['student']['name'] }}</span>
                </h3>
                <p class="ar-modal-subtitle">
                    NIS: {{ $studentDetailData['student']['nis'] }} &bull; Kelas: {{ $studentDetailData['student']['class_name'] }} &bull; Periode {{ $studentDetailData['month_name'] }}
                </p>
            </div>
            <button type="button" wire:click="closeStudentDetail" style="color:rgba(255,255,255,0.6);background:none;border:none;cursor:pointer;padding:0.25rem" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">
                <svg class="ar-modal-close-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Summary Cards --}}
        <div class="ar-modal-summary-grid">
            <div class="ar-modal-summary-card">
                <p style="font-size:0.65rem;font-weight:700;color:rgb(74,222,128);text-transform:uppercase;margin:0">Hadir</p>
                <p style="font-size:1rem;font-weight:800;color:#fff;margin:0.125rem 0 0 0">{{ $studentDetailData['counts']['hadir'] }}</p>
            </div>
            <div class="ar-modal-summary-card">
                <p style="font-size:0.65rem;font-weight:700;color:rgb(250,204,21);text-transform:uppercase;margin:0">Terlambat</p>
                <p style="font-size:1rem;font-weight:800;color:#fff;margin:0.125rem 0 0 0">{{ $studentDetailData['counts']['terlambat'] }}</p>
            </div>
            <div class="ar-modal-summary-card">
                <p style="font-size:0.65rem;font-weight:700;color:rgb(96,165,250);text-transform:uppercase;margin:0">Izin</p>
                <p style="font-size:1rem;font-weight:800;color:#fff;margin:0.125rem 0 0 0">{{ $studentDetailData['counts']['izin'] }}</p>
            </div>
            <div class="ar-modal-summary-card">
                <p style="font-size:0.65rem;font-weight:700;color:rgb(192,132,252);text-transform:uppercase;margin:0">Sakit</p>
                <p style="font-size:1rem;font-weight:800;color:#fff;margin:0.125rem 0 0 0">{{ $studentDetailData['counts']['sakit'] }}</p>
            </div>
            <div class="ar-modal-summary-card">
                <p style="font-size:0.65rem;font-weight:700;color:rgb(45,212,191);text-transform:uppercase;margin:0">Dispensasi</p>
                <p style="font-size:1rem;font-weight:800;color:#fff;margin:0.125rem 0 0 0">{{ $studentDetailData['counts']['dispensasi'] }}</p>
            </div>
            <div class="ar-modal-summary-card">
                <p style="font-size:0.65rem;font-weight:700;color:rgb(248,113,113);text-transform:uppercase;margin:0">Alpa</p>
                <p style="font-size:1rem;font-weight:800;color:#fff;margin:0.125rem 0 0 0">{{ $studentDetailData['counts']['alpa'] }}</p>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="ar-modal-tabs">
            <button type="button" class="ar-modal-tab-btn" @click="filter = 'all'" :style="filter === 'all' ? 'border-bottom:2px solid rgb(245,158,11);color:rgb(252,211,77);font-weight:700' : 'border-bottom:2px solid transparent;color:rgba(255,255,255,0.5)'">
                Semua Hari ({{ count($studentDetailData['logs']) }})
            </button>
            <button type="button" class="ar-modal-tab-btn" @click="filter = 'alpa'" :style="filter === 'alpa' ? 'border-bottom:2px solid rgb(248,113,113);color:rgb(252,165,165);font-weight:700' : 'border-bottom:2px solid transparent;color:rgba(255,255,255,0.5)'">
                🔴 Alpa ({{ $studentDetailData['counts']['alpa'] }})
            </button>
            <button type="button" class="ar-modal-tab-btn" @click="filter = 'izin_sakit'" :style="filter === 'izin_sakit' ? 'border-bottom:2px solid rgb(96,165,250);color:rgb(147,197,253);font-weight:700' : 'border-bottom:2px solid transparent;color:rgba(255,255,255,0.5)'">
                🔵 Izin / Sakit / Disp ({{ $studentDetailData['counts']['izin'] + $studentDetailData['counts']['sakit'] + $studentDetailData['counts']['dispensasi'] }})
            </button>
            <button type="button" class="ar-modal-tab-btn" @click="filter = 'terlambat'" :style="filter === 'terlambat' ? 'border-bottom:2px solid rgb(250,204,21);color:rgb(253,224,71);font-weight:700' : 'border-bottom:2px solid transparent;color:rgba(255,255,255,0.5)'">
                🟡 Terlambat ({{ $studentDetailData['counts']['terlambat'] }})
            </button>
        </div>

        {{-- Timeline Log List --}}
        <div class="ar-modal-body">
            @forelse($studentDetailData['logs'] as $log)
                <div x-show="filter === 'all' || (filter === 'alpa' && '{{ $log['status'] }}' === 'alpa') || (filter === 'izin_sakit' && ['izin','sakit','dispensasi'].includes('{{ $log['status'] }}')) || (filter === 'terlambat' && '{{ $log['status'] }}' === 'terlambat')"
                    class="ar-modal-log-item">
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        @php
                            $dotBg = match($log['status']) {
                                'hadir' => '#4ade80',
                                'terlambat' => '#facc15',
                                'izin' => '#60a5fa',
                                'sakit' => '#c084fc',
                                'dispensasi' => '#2dd4bf',
                                'alpa' => '#ef4444',
                                default => '#9ca3af'
                            };
                        @endphp
                        <span style="width:0.625rem;height:0.625rem;border-radius:50%;flex-shrink:0;background:{{ $dotBg }}"></span>
                        <div>
                            <p style="font-weight:700;color:#fff;margin:0">{{ $log['date_formatted'] }}</p>
                            @if($log['reason'])
                                <p style="font-size:0.7rem;color:rgba(255,255,255,0.6);margin:0.125rem 0 0 0">{{ $log['reason'] }}</p>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.5rem;text-align:right;flex-shrink:0">
                        @if($log['via_lupa_absen'])
                            <span style="padding:0.125rem 0.5rem;border-radius:9999px;font-size:0.65rem;font-weight:700;background:rgba(245,158,11,0.2);color:rgb(253,224,71);border:1px solid rgba(245,158,11,0.3)">
                                Lupa Absen
                            </span>
                        @else
                            <span style="padding:0.125rem 0.5rem;border-radius:9999px;font-size:0.65rem;font-weight:700;background:rgba(255,255,255,0.1);color:#fff">
                                {{ ucfirst($log['status']) }}
                            </span>
                        @endif
                        <span style="font-family:monospace;font-size:0.7rem;color:rgba(255,255,255,0.5)">
                            {{ $log['check_in'] ?? '—' }} / {{ $log['check_out'] ?? '—' }}
                        </span>
                    </div>
                </div>
            @empty
                <p style="text-align:center;font-size:0.75rem;color:rgba(255,255,255,0.4);padding:1.5rem 0">Tidak ada catatan presensi untuk filter ini.</p>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="ar-modal-footer">
            <button type="button" wire:click="closeStudentDetail" style="padding:0.5rem 1rem;background:rgba(255,255,255,0.1);color:#fff;border:none;border-radius:0.75rem;font-size:0.75rem;font-weight:600;cursor:pointer" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                Tutup
            </button>
        </div>
    </div>
</div>
@endif

{{-- Modal Web Preview Rekap Grid PDF Admin --}}
<div x-show="showGridPreviewModal" x-cloak
    @keydown.escape.window="showGridPreviewModal = false"
    style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgba(0,0,0,0.8);backdrop-filter:blur(4px)">
    <div style="background:#0f1d33;border-radius:1rem;width:100%;max-width:72rem;height:92vh;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.1);overflow:hidden" @click.stop>
        {{-- Header Modal --}}
        <div style="padding:0.875rem 1.25rem;background:#090d16;color:#fff;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;border-bottom:1px solid rgba(255,255,255,0.1)">
            <div style="display:flex;align-items:center;gap:0.75rem">
                <span style="padding:0.375rem;background:rgba(16,185,129,0.2);color:rgb(52,211,153);border-radius:0.5rem">
                    <svg class="w-5 h-5 shrink-0" style="width:20px !important;height:20px !important;min-width:20px !important;min-height:20px !important;max-width:20px !important;max-height:20px !important;flex-shrink:0 !important;display:inline-block !important" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <div>
                    <h3 style="font-size:0.875rem;font-weight:700;color:#fff;margin:0">Pratinjau Dokumen Cetak Rekapitulasi Absensi Siswa Bulanan (Grid 1-31)</h3>
                    <p style="font-size:0.7rem;color:rgba(255,255,255,0.4);margin:0">SMA Negeri 1 Gianyar &middot; Dokumen Resmi PDF Admin</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem">
                @php
                    $targetClassId = $this->classId ?: ($classes->first()?->id);
                    $gridMonthStr = sprintf('%04d-%02d', $this->year, $this->month);
                @endphp
                @if ($targetClassId)
                <a href="{{ route('admin.attendance-report.grid-pdf', ['month' => $gridMonthStr, 'class_id' => $targetClassId]) }}"
                    target="_blank"
                    style="padding:0.5rem 1rem;background:#059669;color:#fff;font-size:0.75rem;font-weight:700;border-radius:0.75rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.375rem">
                    <svg class="w-4 h-4 shrink-0" style="width:16px !important;height:16px !important;min-width:16px !important;min-height:16px !important;max-width:16px !important;max-height:16px !important;flex-shrink:0 !important;display:inline-block !important" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Cetak Rekap Grid (PDF)
                </a>
                @endif
                <button type="button" @click="showGridPreviewModal = false"
                    style="padding:0.5rem 0.875rem;background:rgba(255,255,255,0.1);color:#fff;border:none;border-radius:0.75rem;font-size:0.75rem;font-weight:600;cursor:pointer">
                    Tutup
                </button>
            </div>
        </div>

        {{-- Iframe Preview --}}
        <div style="flex:1;background:#090d16;padding:0.5rem;overflow:hidden">
            @if ($targetClassId)
            <iframe src="{{ route('admin.attendance-report.grid-preview', ['month' => $gridMonthStr, 'class_id' => $targetClassId]) }}"
                style="width:100%;height:100%;background:#fff;border-radius:0.75rem;border:1px solid rgba(255,255,255,0.1);box-shadow:0 4px 6px -1px rgba(0,0,0,0.1)"></iframe>
            @else
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:rgba(255,255,255,0.4);font-size:0.875rem">
                Pilih kelas terlebih dahulu untuk meilihat pratinjau.
            </div>
            @endif
        </div>
    </div>
</div>

<x-filament-actions::modals />
</x-filament-panels::page>
