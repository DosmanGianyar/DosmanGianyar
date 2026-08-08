<x-filament-panels::page>
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
    @endphp
    <div style="margin-left:auto;display:flex;gap:0.625rem;align-items:flex-end">
        <a href="{{ route('admin.attendance-report.excel') . '?' . $dlParams }}"
           target="_blank"
           style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.3);border-radius:0.5rem;color:rgb(74,222,128);font-size:0.8rem;font-weight:600;text-decoration:none;transition:background 0.15s"
           onmouseover="this.style.background='rgba(34,197,94,0.2)'" onmouseout="this.style.background='rgba(34,197,94,0.12)'">
            <svg style="width:1rem;height:1rem;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Excel
        </a>
        <a href="{{ route('admin.attendance-report.pdf') . '?' . $dlParams }}"
           target="_blank"
           style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);border-radius:0.5rem;color:rgb(248,113,113);font-size:0.8rem;font-weight:600;text-decoration:none;transition:background 0.15s"
           onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.12)'">
            <svg style="width:1rem;height:1rem;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            PDF
        </a>
    </div>
</div>

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

{{-- Student Detail Modal --}}
@if($showDetailModal && $studentDetailData)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs" x-data="{ filter: 'all' }">
    <div class="bg-[#0f1d33] border border-white/10 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
        {{-- Header --}}
        <div class="p-5 border-b border-white/10 flex items-start justify-between bg-[#0d1628]">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $studentDetailData['student']['name'] }}
                </h3>
                <p class="text-xs text-white/50 mt-1">
                    NIS: {{ $studentDetailData['student']['nis'] }} • Kelas: {{ $studentDetailData['student']['class_name'] }} • Periode {{ $studentDetailData['month_name'] }}
                </p>
            </div>
            <button type="button" wire:click="closeStudentDetail" class="text-white/60 hover:text-white transition-colors p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Summary Cards --}}
        <div class="p-4 bg-[#0b1220] border-b border-white/5 grid grid-cols-6 gap-2 text-center">
            <div class="bg-white/5 p-2 rounded-xl border border-white/5">
                <p class="text-[10px] font-bold text-green-400 uppercase">Hadir</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ $studentDetailData['counts']['hadir'] }}</p>
            </div>
            <div class="bg-white/5 p-2 rounded-xl border border-white/5">
                <p class="text-[10px] font-bold text-yellow-400 uppercase">Terlambat</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ $studentDetailData['counts']['terlambat'] }}</p>
            </div>
            <div class="bg-white/5 p-2 rounded-xl border border-white/5">
                <p class="text-[10px] font-bold text-blue-400 uppercase">Izin</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ $studentDetailData['counts']['izin'] }}</p>
            </div>
            <div class="bg-white/5 p-2 rounded-xl border border-white/5">
                <p class="text-[10px] font-bold text-purple-400 uppercase">Sakit</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ $studentDetailData['counts']['sakit'] }}</p>
            </div>
            <div class="bg-white/5 p-2 rounded-xl border border-white/5">
                <p class="text-[10px] font-bold text-teal-400 uppercase">Dispensasi</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ $studentDetailData['counts']['dispensasi'] }}</p>
            </div>
            <div class="bg-white/5 p-2 rounded-xl border border-white/5">
                <p class="text-[10px] font-bold text-red-400 uppercase">Alpa</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ $studentDetailData['counts']['alpa'] }}</p>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="px-5 pt-3 flex gap-2 border-b border-white/5">
            <button type="button" @click="filter = 'all'" :class="filter === 'all' ? 'border-amber-400 text-amber-300 font-bold' : 'border-transparent text-white/50'" class="pb-2 text-xs border-b-2 transition-colors">
                Semua Hari ({{ count($studentDetailData['logs']) }})
            </button>
            <button type="button" @click="filter = 'alpa'" :class="filter === 'alpa' ? 'border-red-400 text-red-300 font-bold' : 'border-transparent text-white/50'" class="pb-2 text-xs border-b-2 transition-colors">
                🔴 Alpa ({{ $studentDetailData['counts']['alpa'] }})
            </button>
            <button type="button" @click="filter = 'izin_sakit'" :class="filter === 'izin_sakit' ? 'border-blue-400 text-blue-300 font-bold' : 'border-transparent text-white/50'" class="pb-2 text-xs border-b-2 transition-colors">
                🔵 Izin / Sakit / Disp ({{ $studentDetailData['counts']['izin'] + $studentDetailData['counts']['sakit'] + $studentDetailData['counts']['dispensasi'] }})
            </button>
            <button type="button" @click="filter = 'terlambat'" :class="filter === 'terlambat' ? 'border-yellow-400 text-yellow-300 font-bold' : 'border-transparent text-white/50'" class="pb-2 text-xs border-b-2 transition-colors">
                🟡 Terlambat ({{ $studentDetailData['counts']['terlambat'] }})
            </button>
        </div>

        {{-- Timeline Log Table --}}
        <div class="p-5 overflow-y-auto flex-1 space-y-2">
            @forelse($studentDetailData['logs'] as $log)
                <div x-show="filter === 'all' || (filter === 'alpa' && '{{ $log['status'] }}' === 'alpa') || (filter === 'izin_sakit' && ['izin','sakit','dispensasi'].includes('{{ $log['status'] }}')) || (filter === 'terlambat' && '{{ $log['status'] }}' === 'terlambat')"
                    class="p-3 rounded-xl border border-white/5 bg-white/5 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0
                            {{ match($log['status']) {
                                'hadir' => 'bg-green-400',
                                'terlambat' => 'bg-yellow-400',
                                'izin' => 'bg-blue-400',
                                'sakit' => 'bg-purple-400',
                                'dispensasi' => 'bg-teal-400',
                                'alpa' => 'bg-red-500',
                                default => 'bg-gray-500'
                            } }}"></span>
                        <div>
                            <p class="font-bold text-white">{{ $log['date_formatted'] }}</p>
                            @if($log['reason'])
                                <p class="text-[11px] text-white/60 mt-0.5">{{ $log['reason'] }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-right shrink-0">
                        @if($log['via_lupa_absen'])
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                Lupa Absen
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                {{ match($log['status']) {
                                    'hadir' => 'bg-green-500/20 text-green-300',
                                    'terlambat' => 'bg-yellow-500/20 text-yellow-300',
                                    'izin' => 'bg-blue-500/20 text-blue-300',
                                    'sakit' => 'bg-purple-500/20 text-purple-300',
                                    'dispensasi' => 'bg-teal-500/20 text-teal-300',
                                    'alpa' => 'bg-red-500/20 text-red-300',
                                    default => 'bg-gray-500/20 text-gray-300'
                                } }}">
                                {{ ucfirst($log['status']) }}
                            </span>
                        @endif
                        <span class="font-mono text-[11px] text-white/50">
                            {{ $log['check_in'] ?? '—' }} / {{ $log['check_out'] ?? '—' }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-center text-xs text-white/40 py-6">Tidak ada catatan presensi untuk filter ini.</p>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="p-3 border-t border-white/10 bg-[#0d1628] text-right">
            <button type="button" wire:click="closeStudentDetail" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-semibold transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endif

<x-filament-actions::modals />
</x-filament-panels::page>
