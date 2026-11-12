<x-filament-panels::page>
    <style>
        /* ═══════════════════════════════════════════════════════════════════════════
           TABLER.IO STYLING — JADWAL MENGAJAR GURU (TIMETABLE GRID)
           ═══════════════════════════════════════════════════════════════════════════ */
        .tg-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            width: 100%;
        }

        /* ─── Control Bar (Tabler Card) ─────────────────────────────────── */
        .tg-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-top: 3px solid #206bc4 !important; /* Tabler Top Accent Line */
            border-radius: 0.75rem !important;
            padding: 1.25rem 1.5rem !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05) !important;
        }

        .tg-controls-row {
            display: flex;
            flex-direction: row;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .tg-filter-group {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 1rem;
            flex: 1;
        }

        .tg-field {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .tg-field-teacher {
            min-width: 320px;
            flex: 1;
            max-width: 540px;
        }

        .tg-field-year {
            min-width: 180px;
        }

        .tg-label {
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.06em !important;
            margin: 0 !important;
        }

        .tg-select {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding: 0.55rem 2.25rem 0.55rem 0.85rem !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            outline: none !important;
            width: 100% !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.75rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.15em 1.15em !important;
            transition: border-color 0.15s, box-shadow 0.15s !important;
        }

        .tg-select:focus {
            border-color: #206bc4 !important;
            box-shadow: 0 0 0 3px rgba(32, 107, 196, 0.15) !important;
            background-color: #ffffff !important;
        }

        /* ─── Buttons (Tabler Button Styles) ────────────────────────────── */
        .tg-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .tg-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.45rem !important;
            padding: 0.55rem 1.15rem !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            border-radius: 0.5rem !important;
            cursor: pointer !important;
            text-decoration: none !important;
            transition: all 0.15s ease !important;
            white-space: nowrap !important;
            line-height: 1.25 !important;
        }

        .tg-btn svg {
            width: 1.15rem !important;
            height: 1.15rem !important;
            max-width: 1.15rem !important;
            max-height: 1.15rem !important;
            flex-shrink: 0 !important;
            display: inline-block !important;
        }

        .tg-btn-primary {
            background-color: #206bc4 !important;
            border: 1px solid #206bc4 !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(32, 107, 196, 0.25) !important;
        }

        .tg-btn-primary:hover {
            background-color: #1a569d !important;
            border-color: #1a569d !important;
            color: #ffffff !important;
        }

        .tg-btn-secondary {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #334155 !important;
        }

        .tg-btn-secondary:hover {
            background-color: #f1f5f9 !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }

        /* ─── Stats Badges (Tabler Badges) ───────────────────────────────── */
        .tg-badges-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            padding-top: 0.85rem;
            margin-top: 0.85rem;
            border-top: 1px solid #f1f5f9;
        }

        .tg-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.35rem !important;
            padding: 0.35rem 0.75rem !important;
            border-radius: 9999px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            line-height: 1 !important;
        }

        .tg-badge-dot {
            width: 0.45rem !important;
            height: 0.45rem !important;
            border-radius: 9999px !important;
            display: inline-block !important;
        }

        .tg-badge-blue {
            background: #ebf3fe !important;
            color: #1e40af !important;
            border: 1px solid #bfdbfe !important;
        }
        .tg-badge-blue .tg-badge-dot { background: #2563eb !important; }

        .tg-badge-purple {
            background: #f5f3ff !important;
            color: #6b21a8 !important;
            border: 1px solid #ddd6fe !important;
        }
        .tg-badge-purple .tg-badge-dot { background: #9333ea !important; }

        .tg-badge-amber {
            background: #fffbeb !important;
            color: #92400e !important;
            border: 1px solid #fde68a !important;
        }
        .tg-badge-amber .tg-badge-dot { background: #f59e0b !important; }

        .tg-badge-gray {
            background: #f8fafc !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        }

        /* ─── Timetable Sheet Container ─────────────────────────────────── */
        .tg-sheet-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05) !important;
            overflow-x: auto !important;
        }

        .tg-sheet {
            min-width: 960px;
            max-width: 1180px;
            margin: 0 auto;
            background: #ffffff;
            color: #000000;
        }

        /* ─── Timetable Header ───────────────────────────────────────────── */
        .tg-sheet-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.85rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #000000;
        }

        .tg-header-logo-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 140px;
        }

        .tg-header-logo {
            height: 56px;
            width: auto;
            object-fit: contain;
        }

        .tg-header-title-group {
            text-align: center;
            flex: 1;
            padding: 0 1rem;
        }

        .tg-title-sub {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #111827;
            margin-bottom: 2px;
        }

        .tg-title-main {
            font-size: 1.75rem;
            font-weight: 900;
            color: #000000;
            line-height: 1.15;
            text-transform: uppercase;
            letter-spacing: -0.01em;
            margin: 0;
        }

        .tg-title-school {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #000000;
            margin-top: 3px;
        }

        .tg-header-brand {
            min-width: 140px;
            text-align: right;
            font-size: 0.65rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.05em;
        }

        /* ─── Timetable Grid Table ───────────────────────────────────────── */
        .tg-grid-table {
            width: 100%;
            border-collapse: collapse !important;
            border: 2px solid #000000 !important;
            table-layout: fixed !important;
            color: #000000 !important;
            font-family: inherit !important;
        }

        .tg-grid-table th,
        .tg-grid-table td {
            border: 1px solid #000000 !important;
            box-sizing: border-box !important;
        }

        /* Table Day Headers */
        .tg-th-time {
            width: 85px !important;
            background: #e2e8f0 !important;
            color: #000000 !important;
            font-size: 0.75rem !important;
            font-weight: 800 !important;
            text-align: center !important;
            padding: 0.6rem 0.25rem !important;
        }

        .tg-th-day {
            padding: 0.65rem 0.5rem !important;
            font-size: 1.05rem !important;
            font-weight: 900 !important;
            text-align: center !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            color: #000000 !important;
        }

        /* Table Period (Jam Ke) Cell */
        .tg-td-period {
            text-align: center !important;
            padding: 0.35rem 0.15rem !important;
            user-select: none !important;
        }

        .tg-period-num {
            font-size: 1.15rem !important;
            font-weight: 900 !important;
            line-height: 1.1 !important;
            color: #000000 !important;
        }

        .tg-period-time {
            font-size: 0.62rem !important;
            font-weight: 700 !important;
            line-height: 1 !important;
            margin-top: 2px !important;
            color: #1f2937 !important;
            letter-spacing: -0.02em !important;
        }

        /* Table Slot Cell */
        .tg-td-slot {
            background-color: #ffffff !important;
            vertical-align: top !important;
            position: relative !important;
            padding: 0.4rem 0.5rem !important;
            transition: background 0.12s ease !important;
        }

        .tg-td-slot:hover {
            background-color: #fefce8 !important;
        }

        .tg-slot-mapel {
            position: absolute !important;
            top: 4px !important;
            left: 6px !important;
            font-size: 0.68rem !important;
            font-weight: 800 !important;
            color: #000000 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.02em !important;
            line-height: 1 !important;
        }

        .tg-slot-class-wrap {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 100% !important;
            padding-top: 0.5rem !important;
        }

        .tg-slot-class {
            font-size: 1.15rem !important;
            font-weight: 900 !important;
            color: #000000 !important;
            text-align: center !important;
            letter-spacing: -0.01em !important;
        }

        .tg-td-empty {
            background-color: #ffffff !important;
            height: 44px !important;
            padding: 0 !important;
        }

        /* ─── Timetable Footer ───────────────────────────────────────────── */
        .tg-sheet-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: #000000;
            padding: 0 0.25rem;
        }

        /* ─── Print Optimization (A4 Landscape) ─────────────────────────── */
        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm 8mm;
            }

            body, html {
                background: #ffffff !important;
                color: #000000 !important;
            }

            body * {
                visibility: hidden !important;
            }

            .tg-sheet, .tg-sheet * {
                visibility: visible !important;
            }

            .tg-sheet {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }

            .no-print,
            .fi-topbar,
            .fi-sidebar,
            .fi-header,
            .tg-card {
                display: none !important;
            }

            .tg-grid-table,
            .tg-grid-table th,
            .tg-grid-table td {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .tg-grid-table {
                border: 2px solid #000000 !important;
            }

            .tg-grid-table th,
            .tg-grid-table td {
                border: 1px solid #000000 !important;
            }
        }
    </style>

    <div class="tg-container">
        {{-- Control Bar (Tabler Card) --}}
        <div class="no-print tg-card">
            <div class="tg-controls-row">
                {{-- Left: Filter Controls --}}
                <div class="tg-filter-group">
                    {{-- Guru Selector --}}
                    <div class="tg-field tg-field-teacher">
                        <label class="tg-label">Pilih Nama Guru:</label>
                        <select wire:model.live="selectedTeacherId" class="tg-select">
                            @forelse($this->teachers as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->name }} ({{ $t->teacher_code ?: '—' }}) — [{{ $t->slot_count }} Jam]
                                </option>
                            @empty
                                <option value="">Tidak ada guru yang memiliki jadwal</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Tahun Ajaran Selector --}}
                    <div class="tg-field tg-field-year">
                        <label class="tg-label">Tahun Ajaran:</label>
                        <select wire:model.live="academicYear" class="tg-select">
                            @foreach($this->academicYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Right: Action Buttons --}}
                <div class="tg-actions">
                    <button type="button" onclick="window.print()" class="tg-btn tg-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        <span>Cetak Jadwal (PDF / Print)</span>
                    </button>

                    <a href="{{ \App\Filament\Resources\ScheduleResource::getUrl() }}" class="tg-btn tg-btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="3" y1="15" x2="21" y2="15"></line>
                            <line x1="9" y1="3" x2="9" y2="21"></line>
                            <line x1="15" y1="3" x2="15" y2="21"></line>
                        </svg>
                        <span>Tabel Semua Jadwal</span>
                    </a>
                </div>
            </div>

            {{-- Summary Stat Badges (Tabler Badges) --}}
            @if($this->selectedTeacher)
                <div class="tg-badges-row">
                    <span class="tg-badge tg-badge-blue">
                        <span class="tg-badge-dot"></span>
                        <span>Beban: {{ $this->stats['total_hours'] }} Jam / Minggu</span>
                    </span>
                    <span class="tg-badge tg-badge-purple">
                        <span class="tg-badge-dot"></span>
                        <span>{{ $this->stats['total_classes'] }} Kelas: {{ implode(', ', $this->stats['classes']) ?: '—' }}</span>
                    </span>
                    <span class="tg-badge tg-badge-amber">
                        <span class="tg-badge-dot"></span>
                        <span>Mapel: {{ implode(', ', $this->stats['subjects']) ?: '—' }}</span>
                    </span>
                    <span class="tg-badge tg-badge-gray">
                        Kode: {{ $this->selectedTeacher->teacher_code ?: '—' }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Printable Timetable Sheet Card --}}
        <div class="tg-sheet-card">
            <div class="tg-sheet">
                
                {{-- Header (matching aSc Timetables & SMAN 1 Gianyar layout) --}}
                <div class="tg-sheet-header">
                    {{-- Left: Logo SMAN 1 Gianyar + Bali script --}}
                    <div class="tg-header-logo-group">
                        <img
                            src="{{ asset('images/logo-dosman.png') }}"
                            alt="Logo SMAN 1 Gianyar"
                            class="tg-header-logo"
                            onerror="this.src='{{ asset('img/logo_sekolah.png') }}'"
                        />
                        <div style="line-height: 1.15;">
                            <div style="font-size: 10px; font-family: serif; color: #4b5563;">ᬲ᭄ᬫᬦ᭄᭑ᬕ᭄ᬬᬜᬃ</div>
                            <div style="font-size: 11px; font-weight: 900; text-transform: uppercase; color: #000000; letter-spacing: -0.01em;">SMAN 1 GIANYAR</div>
                        </div>
                    </div>

                    {{-- Center: Timetable Title & Teacher Name --}}
                    <div class="tg-header-title-group">
                        <div class="tg-title-sub">
                            JADWAL PELAJARAN SEMESTER {{ strtoupper(str_contains($academicYear, 'Genap') ? 'GENAP' : 'GANJIL') }} {{ preg_replace('/[^0-9\/]/', '', $academicYear) }}
                        </div>
                        <h1 class="tg-title-main">
                            Guru {{ $this->selectedTeacher?->name ?? '—' }}
                        </h1>
                        <div class="tg-title-school">
                            SMA NEGERI 1 GIANYAR
                        </div>
                    </div>

                    {{-- Right: Placeholder balance --}}
                    <div class="tg-header-brand">
                        SIMAK DOSMAN
                    </div>
                </div>

                {{-- Timetable Main Table --}}
                <table class="tg-grid-table">
                    <thead>
                        <tr>
                            {{-- Top-left cell --}}
                            <th class="tg-th-time">
                                JAM / HARI
                            </th>

                            {{-- Days Columns --}}
                            @foreach(\App\Filament\Pages\TeacherSchedulePage::DAYS as $d => $dayInfo)
                                <th class="tg-th-day" style="background-color: {{ $dayInfo['bg'] }};">
                                    {{ $dayInfo['name'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grid = $this->gridData;
                        @endphp

                        @foreach(\App\Filament\Pages\TeacherSchedulePage::TIME_SLOTS as $p => $timeStr)
                            @php
                                $isEven = ($p % 2 === 0);
                                $periodBg = $isEven ? '#F8BBD0' : '#FFF59D'; // Alternating Pink & Yellow
                            @endphp
                            <tr>
                                {{-- Period (Jam Ke) Cell --}}
                                <td class="tg-td-period" style="background-color: {{ $periodBg }};">
                                    <div class="tg-period-num">{{ $p }}</div>
                                    <div class="tg-period-time">{{ $timeStr }}</div>
                                </td>

                                {{-- Day Cells --}}
                                @foreach(\App\Filament\Pages\TeacherSchedulePage::DAYS as $d => $dayInfo)
                                    @php
                                        $cell = $grid[$p][$d] ?? ['type' => 'empty', 'span' => 1];
                                    @endphp

                                    @if($cell['type'] === 'slot')
                                        <td
                                            rowspan="{{ $cell['span'] }}"
                                            class="tg-td-slot"
                                            style="height: {{ $cell['span'] * 46 }}px;"
                                        >
                                            {{-- Mapel Code (Top-Left) --}}
                                            <div class="tg-slot-mapel">
                                                {{ $cell['subject_code'] }}
                                            </div>

                                            {{-- Class Name (Centered Big) --}}
                                            <div class="tg-slot-class-wrap">
                                                <span class="tg-slot-class">
                                                    {{ $cell['class_name'] }}
                                                </span>
                                            </div>

                                            {{-- Room (Bottom-Right if available) --}}
                                            @if(!empty($cell['room']))
                                                <div style="position: absolute; bottom: 3px; right: 5px; font-size: 8px; font-weight: 600; color: #64748b; font-style: italic;">
                                                    {{ $cell['room'] }}
                                                </div>
                                            @endif
                                        </td>
                                    @elseif($cell['type'] === 'empty')
                                        <td class="tg-td-empty"></td>
                                    @endif
                                    {{-- If type === 'skip', do nothing (rowspan already covered) --}}
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Table Footer (under the table, matching aSc style) --}}
                <div class="tg-sheet-footer">
                    <div style="text-transform: uppercase;">
                        {{ $this->selectedTeacher?->name }} • {{ $this->selectedTeacher?->teacher_code ?: '' }} • {{ $this->stats['total_hours'] }} JAM
                    </div>
                    <div style="letter-spacing: 0.05em; color: #4b5563;">
                        aSc Timetables
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-filament-panels::page>
