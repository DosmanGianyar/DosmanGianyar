<x-filament-panels::page>
<style>
.sc-toolbar {
    background: #0f1d33;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: .75rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
    margin-bottom: 1.25rem;
}
.sc-filter-group { display:flex; flex-direction:column; gap:.3rem; min-width:180px; }
.sc-label { font-size:.7rem; font-weight:700; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.07em; }
.sc-input, .sc-select {
    background: #0d1628;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: .5rem;
    color: rgba(255,255,255,.9);
    padding: .5rem .75rem;
    font-size: .875rem;
    outline: none;
}
.sc-select {
    appearance: none; -webkit-appearance: none;
    padding-right: 2rem;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right .5rem center;
    cursor: pointer;
}
.sc-input:focus, .sc-select:focus { border-color: rgba(251,191,36,.6); }

/* table */
.sc-table { width:100%; border-collapse:collapse; }
.sc-table th {
    background: #0f1d33;
    color: rgba(255,255,255,.5);
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    padding: .75rem 1rem;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.sc-table td {
    padding: .7rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,.04);
    font-size: .875rem;
    color: rgba(255,255,255,.85);
    vertical-align: middle;
}
.sc-table tr:hover td { background: rgba(255,255,255,.03); }
.sc-table tr:last-child td { border-bottom: none; }
.sc-avatar {
    width: 32px; height: 32px; border-radius: 8px;
    object-fit: cover; object-position: top;
    border: 1px solid rgba(255,255,255,.1);
    flex-shrink: 0;
}
.sc-avatar-placeholder {
    width: 32px; height: 32px; border-radius: 8px;
    background: linear-gradient(135deg, #1d4ed8, #4f46e5);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .7rem; font-weight: 700; color: white;
    border: 1px solid rgba(255,255,255,.1);
    flex-shrink: 0;
}
.sc-btn-dl {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .35rem .9rem;
    border-radius: .5rem;
    font-size: .78rem; font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #1d4ed8, #4f46e5);
    border: none; cursor: pointer;
    text-decoration: none;
    transition: opacity .15s;
    white-space: nowrap;
}
.sc-btn-dl:hover { opacity: .85; }
.sc-btn-dl svg { width: 14px; height: 14px; flex-shrink:0; }
.sc-empty {
    text-align: center; padding: 3rem 1rem;
    color: rgba(255,255,255,.3); font-size: .875rem;
}
.sc-badge {
    display: inline-block;
    padding: .2rem .55rem;
    border-radius: 4px;
    font-size: .7rem; font-weight: 600;
}
.sc-count {
    font-size: .78rem; color: rgba(255,255,255,.4);
    margin-left: auto; white-space: nowrap;
    align-self: center;
}
</style>

{{-- Toolbar filter --}}
<div class="sc-toolbar">
    <div class="sc-filter-group" style="flex:1;min-width:200px;">
        <label class="sc-label">Cari nama / NIS / NISN</label>
        <input class="sc-input" type="text"
               wire:model.live.debounce.300ms="search"
               placeholder="Ketik nama atau nomor siswa..."
               value="{{ $this->search }}">
    </div>
    <div class="sc-filter-group">
        <label class="sc-label">Filter Kelas</label>
        <select class="sc-select" wire:model.live="classId">
            <option value="">Semua Kelas</option>
            @foreach($this->getClasses() as $cls)
                <option value="{{ $cls->id }}">{{ $cls->name }}</option>
            @endforeach
        </select>
    </div>
    @php $count = $this->getStudents()->count(); @endphp
    <span class="sc-count">{{ $count }} siswa</span>
</div>

{{-- Table --}}
<div style="background:#0f1d33;border:1px solid rgba(255,255,255,.07);border-radius:.75rem;overflow:hidden;">
    @php $students = $this->getStudents(); @endphp

    @if($students->isEmpty())
        <div class="sc-empty">
            <svg style="width:40px;height:40px;margin:0 auto 12px;display:block;color:rgba(255,255,255,.2);"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
            </svg>
            Tidak ada siswa ditemukan.
        </div>
    @else
        <table class="sc-table">
            <thead>
                <tr>
                    <th style="width:44px;"></th>
                    <th>Nama Siswa</th>
                    <th>NIS</th>
                    <th>NISN</th>
                    <th>Kelas</th>
                    <th style="text-align:right;">Download</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                <tr>
                    <td>
                        @if($s->photo)
                            <img src="{{ $s->photo_url }}" alt="{{ $s->name }}" class="sc-avatar">
                        @else
                            <span class="sc-avatar-placeholder">{{ $s->initials }}</span>
                        @endif
                    </td>
                    <td style="font-weight:600;">{{ $s->name }}</td>
                    <td style="color:rgba(255,255,255,.6);font-size:.8rem;">{{ $s->nis ?? '—' }}</td>
                    <td style="color:rgba(255,255,255,.6);font-size:.8rem;">{{ $s->nisn ?? '—' }}</td>
                    <td>
                        @if($s->schoolClass)
                            <span class="sc-badge" style="background:rgba(29,78,216,.3);color:#93c5fd;">
                                {{ $s->schoolClass->name }}
                            </span>
                        @else
                            <span style="color:rgba(255,255,255,.3);">—</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('admin.student-card.download', $s->id) }}"
                           class="sc-btn-dl" target="_blank" title="Download PDF Kartu Pelajar">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            PDF Kartu
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<p style="font-size:.72rem;color:rgba(255,255,255,.3);margin-top:.75rem;text-align:center;">
    PDF berisi 2 halaman: depan kartu (info siswa) + belakang kartu (QR code). Ukuran: 85,6 mm × 54 mm (standar KTP).
</p>
</x-filament-panels::page>
