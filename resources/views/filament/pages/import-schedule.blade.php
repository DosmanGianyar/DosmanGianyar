<x-filament-panels::page>
    <style>
        /* Modern & Ultra-Clean Custom Styling for Import Schedule Page */
        .imp-wrap {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        /* Banner Header */
        .imp-banner {
            background: linear-gradient(135deg, #1d4ed8 0%, #3730a3 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .imp-banner-icon {
            width: 42px;
            height: 42px;
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            backdrop-filter: blur(4px);
        }
        .imp-banner-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 1;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .imp-banner-title {
            font-size: 1.125rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }
        .imp-banner-desc {
            font-size: 0.825rem;
            color: #e0e7ff;
            line-height: 1.5;
        }

        /* Download Button */
        .imp-btn-download {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff !important;
            border-radius: 0.5rem;
            font-size: 0.775rem;
            font-weight: 700;
            text-decoration: none !important;
            transition: all 0.2s ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.3);
            white-space: nowrap;
            cursor: pointer;
        }
        .imp-btn-download:hover {
            background-color: rgba(255, 255, 255, 0.35);
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transform: translateY(-1px);
        }

        /* SVG Icon Fixes */
        .imp-svg-banner {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            stroke: #ffffff;
            fill: none;
        }
        .imp-svg-btn {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            stroke: #ffffff;
            fill: none;
        }

        /* Section Cards */
        .imp-card {
            background-color: #0f1d33;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .imp-card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .imp-card-actions {
            padding-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
        }

        /* Stat Grid */
        .imp-grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .imp-stat-card {
            background-color: #0f1d33;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.875rem;
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .imp-stat-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .imp-stat-value {
            font-size: 1.35rem;
            font-weight: 900;
            color: #ffffff;
        }

        /* Preview Table */
        .imp-table-container {
            background-color: #0f1d33;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .imp-table-header {
            padding: 1rem 1.25rem;
            background-color: rgba(30, 41, 59, 0.9);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .imp-table-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .imp-flex-gap {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .imp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.775rem;
            text-align: left;
        }
        .imp-table th {
            background-color: #1e293b;
            color: #cbd5e1;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.875rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .imp-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            vertical-align: middle;
        }
        .imp-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }

        /* Badges */
        .imp-badge-matched {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            background-color: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        .imp-badge-unmatched {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            background-color: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .imp-badge-room {
            display: inline-block;
            padding: 0.15rem 0.4rem;
            border-radius: 0.35rem;
            font-size: 0.65rem;
            font-weight: 700;
            background-color: rgba(168, 85, 247, 0.2);
            color: #c084fc;
            border: 1px solid rgba(168, 85, 247, 0.3);
            margin-left: 0.25rem;
        }

        /* Select Input Styling inside table */
        .imp-select {
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-radius: 0.5rem;
            padding: 0.35rem 0.6rem;
            font-size: 0.75rem;
            outline: none;
            width: 100%;
        }
        .imp-select:focus {
            border-color: #3b82f6;
        }

        /* Pagination Bar */
        .imp-pagination {
            padding: 0.75rem 1.25rem;
            background-color: rgba(30, 41, 59, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
    </style>

    <div class="imp-wrap">

        {{-- ── Banner Panduan Header ──────────────────────────────────────── --}}
        <div class="imp-banner">
            <div class="imp-banner-icon">
                <svg class="imp-svg-banner" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="imp-banner-content">
                <div>
                    <div class="imp-banner-title">Import & Parsing CSV / Excel Jadwal Pelajaran</div>
                    <div class="imp-banner-desc">
                        Unggah file CSV / Excel master jadwal pelajaran. Sistem mendukung <strong>Format Tabel Standar</strong> (Kolom: <code>Kelas, Hari, Jam Ke, Mata Pelajaran, Nama Guru</code>) maupun format matriks horizontal timetable.
                    </div>
                </div>
                <div>
                    <a href="/templates/template_jadwal_pelajaran.csv" download class="imp-btn-download">
                        <svg class="imp-svg-btn" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Model Template CSV / Excel
                    </a>
                </div>
            </div>
        </div>

        @if (! $isParsed)
            {{-- ── Form Langkah 1: Upload File & Parameter ──────────────────── --}}
            <div class="imp-card">
                <div class="imp-card-title">
                    <span>Langkah 1: Unggah File Master CSV / Excel Jadwal Pelajaran</span>
                </div>

                <form wire:submit.prevent="startParsing" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    {{ $this->form }}

                    <div class="imp-card-actions">
                        <x-filament::button type="submit" icon="heroicon-o-arrow-path" size="lg" color="primary">
                            Ekstrak & Pratinjau Jadwal
                        </x-filament::button>
                    </div>
                </form>
            </div>
        @else
            @php
                $allItems      = $this->getAllSessionItems();
                $filteredItems = $this->getFilteredItems();
                $paginated     = $this->getPaginatedItems();
                $totalPages    = $this->getTotalPages();
                $totalCount    = count($allItems);
                $filteredCount = count($filteredItems);
                
                $classesList  = \App\Models\SchoolClass::orderBy('name')->get();
                $teachersList = \App\Models\User::where('role', 'guru')->orderBy('name')->get();
                $subjectsList = \App\Models\Subject::orderBy('name')->get();

                $hasUnmatched = false;
                foreach ($allItems as $p) {
                    if (empty($p['teacher_id']) && !empty($p['teacher_raw'])) {
                        $hasUnmatched = true;
                        break;
                    }
                }
            @endphp

            {{-- ── Langkah 2: Ringkasan Stat & Pratinjau Tabel Match ───────── --}}
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                {{-- Stat Grid Cards --}}
                <div class="imp-grid-stats">
                    <div class="imp-stat-card">
                        <span class="imp-stat-label">Tingkat Kelas</span>
                        <span class="imp-stat-value" style="color: #60a5fa;">{{ $selectedGrade }}</span>
                    </div>
                    <div class="imp-stat-card">
                        <span class="imp-stat-label">Tahun Ajaran</span>
                        <span class="imp-stat-value" style="font-size: 1.1rem; margin-top: 0.2rem;">{{ $academicYear }}</span>
                    </div>
                    <div class="imp-stat-card">
                        <span class="imp-stat-label">Total Slot Jadwal</span>
                        <span class="imp-stat-value" style="color: #4ade80;">{{ $totalCount }} Slot</span>
                    </div>
                    <div class="imp-stat-card">
                        <span class="imp-stat-label">Status Opsi Timpa</span>
                        <span class="imp-stat-value" style="font-size: 0.95rem; color: #fbbf24; margin-top: 0.25rem;">
                            {{ $replaceExisting ? 'Hapus & Timpa Jadwal Lama' : 'Tambahkan Ke Jadwal Ada' }}
                        </span>
                    </div>
                </div>

                {{-- Table Pratinjau --}}
                <div class="imp-table-container">
                    <div class="imp-table-header">
                        <div class="imp-table-title">
                            Pratinjau & Verifikasi Jadwal ({{ $totalCount }} Data Total)
                        </div>
                        <div class="imp-flex-gap">
                            @if ($hasUnmatched)
                                <x-filament::button wire:click="createAllUnmatchedTeachers" color="warning" size="sm" icon="heroicon-o-user-plus">
                                    ⚡ Buat Otomatis Akun Guru yang Belum Ada
                                </x-filament::button>
                            @endif
                            <x-filament::button wire:click="cancelPreview" color="gray" size="sm">
                                Batal / Upload Ulang
                            </x-filament::button>
                            <x-filament::button wire:click="saveToDatabase" color="success" size="sm" icon="heroicon-o-check-circle">
                                SIMPAN JADWAL KE DATABASE
                            </x-filament::button>
                        </div>
                    </div>

                    {{-- Filter & Pagination Header Bar --}}
                    <div class="imp-pagination">
                        <div class="imp-flex-gap">
                            <label style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">Filter Kelas:</label>
                            <select wire:model.live="filterClass" class="imp-select" style="width: auto;">
                                <option value="ALL">Semua Kelas ({{ $totalCount }})</option>
                                @foreach ($classesList as $c)
                                    <option value="{{ $c->name }}">{{ $c->name }}</option>
                                @endforeach
                            </select>

                            <label style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; margin-left: 0.5rem;">Status Guru:</label>
                            <select wire:model.live="filterStatus" class="imp-select" style="width: auto;">
                                <option value="ALL">Semua Status</option>
                                <option value="unmatched">Belum Ada di DB (Perlu Dibuat/Dicocokkan)</option>
                                <option value="matched">Sudah Match DB</option>
                            </select>
                        </div>

                        <div class="imp-flex-gap">
                            <span style="font-size: 0.75rem; color: #cbd5e1;">
                                Menampilkan <strong>{{ count($paginated) }}</strong> dari <strong>{{ $filteredCount }}</strong> data (Halaman {{ $currentPage }} / {{ $totalPages }})
                            </span>
                            <x-filament::button wire:click="previousPage" color="gray" size="xs" :disabled="$currentPage <= 1">
                                ◄ Sblm
                            </x-filament::button>
                            <x-filament::button wire:click="nextPage" color="gray" size="xs" :disabled="$currentPage >= $totalPages">
                                Lanjut ►
                            </x-filament::button>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="imp-table">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Kelas</th>
                                    <th style="width: 200px;">Hari & Jam</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Nama/Kode Guru di File</th>
                                    <th>Hasil Match DB</th>
                                    <th style="width: 320px;">Aksi / Pilih Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($paginated as $item)
                                    <tr>
                                        {{-- Kelas --}}
                                        <td>
                                            <select wire:change="updateItemRow('{{ $item['temp_id'] }}', 'class_id', $event.target.value)" class="imp-select font-bold text-blue-400">
                                                <option value="">— Pilih Kelas —</option>
                                                @foreach ($classesList as $c)
                                                    <option value="{{ $c->id }}" @selected($item['class_id'] == $c->id)>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        {{-- Hari & Jam --}}
                                        <td>
                                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                                <select wire:change="updateItemRow('{{ $item['temp_id'] }}', 'day', $event.target.value)" class="imp-select font-bold text-emerald-400">
                                                    <option value="1" @selected($item['day'] == 1)>Senin</option>
                                                    <option value="2" @selected($item['day'] == 2)>Selasa</option>
                                                    <option value="3" @selected($item['day'] == 3)>Rabu</option>
                                                    <option value="4" @selected($item['day'] == 4)>Kamis</option>
                                                    <option value="5" @selected($item['day'] == 5)>Jumat</option>
                                                    <option value="6" @selected($item['day'] == 6)>Sabtu</option>
                                                </select>

                                                <select wire:change="updateItemRow('{{ $item['temp_id'] }}', 'period', $event.target.value)" class="imp-select font-medium text-slate-300">
                                                    <option value="0" @selected($item['period'] == 0)>Jam 0 (07:10 - 07:55)</option>
                                                    <option value="1" @selected($item['period'] == 1)>Jam 1 (07:30 - 08:15)</option>
                                                    <option value="2" @selected($item['period'] == 2)>Jam 2 (08:15 - 09:00)</option>
                                                    <option value="3" @selected($item['period'] == 3)>Jam 3 (09:00 - 09:45)</option>
                                                    <option value="4" @selected($item['period'] == 4)>Jam 4 (10:00 - 10:45)</option>
                                                    <option value="5" @selected($item['period'] == 5)>Jam 5 (10:45 - 11:30)</option>
                                                    <option value="6" @selected($item['period'] == 6)>Jam 6 (11:30 - 12:15)</option>
                                                    <option value="7" @selected($item['period'] == 7)>Jam 7 (12:30 - 13:15)</option>
                                                    <option value="8" @selected($item['period'] == 8)>Jam 8 (13:15 - 14:00)</option>
                                                    <option value="9" @selected($item['period'] == 9)>Jam 9 (16:00 - 16:45)</option>
                                                    <option value="10" @selected($item['period'] == 10)>Jam 10 (17:00 - 17:45)</option>
                                                    <option value="11" @selected($item['period'] == 11)>Jam 11 (17:45 - 18:30)</option>
                                                </select>
                                            </div>
                                        </td>

                                        {{-- Mapel --}}
                                        <td>
                                            <select wire:change="updateItemRow('{{ $item['temp_id'] }}', 'subject_id', $event.target.value)" class="imp-select font-bold text-amber-300">
                                                <option value="">— Belum Ada di DB (-) —</option>
                                                @foreach ($subjectsList as $s)
                                                    <option value="{{ $s->id }}" @selected($item['subject_id'] == $s->id)>{{ $s->code ? "[{$s->code}] " : '' }}{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        {{-- Teks Raw CSV --}}
                                        <td>
                                            <strong style="color: #f8fafc;">{{ $item['teacher_raw'] ?: '—' }}</strong>
                                            @if(!empty($item['room']))
                                                <span class="imp-badge-room">{{ $item['room'] }}</span>
                                            @endif
                                        </td>

                                        {{-- Match Status --}}
                                        <td>
                                            @if ($item['teacher_id'])
                                                <span class="imp-badge-matched">
                                                    ✓ {{ $item['teacher_name'] }}
                                                </span>
                                            @else
                                                <span class="imp-badge-unmatched">
                                                    ⚠ Belum Ada di DB
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Aksi Guru --}}
                                        <td style="position: relative;">
                                            @php
                                                $rawText = $item['teacher_raw'] ?? '';
                                                $sortedTeachers = $teachersList->sortByDesc(function ($t) use ($rawText) {
                                                    if (empty($rawText)) return 0;
                                                    $cleanRaw = strtolower(preg_replace('/[^a-zA-Z]/', '', preg_replace('/,.*$/', '', $rawText)));
                                                    $cleanDb  = strtolower(preg_replace('/[^a-zA-Z]/', '', preg_replace('/,.*$/', '', $t->name)));
                                                    if ($cleanRaw === $cleanDb) return 1000;
                                                    
                                                    similar_text($cleanRaw, $cleanDb, $percent);
                                                    
                                                    $words = array_filter(explode(' ', strtolower(preg_replace('/[^a-zA-Z\s]/', '', $rawText))));
                                                    $wordBonus = 0;
                                                    foreach ($words as $w) {
                                                        if (strlen($w) >= 3 && str_contains(strtolower($t->name), $w)) {
                                                            $wordBonus += 25;
                                                        }
                                                    }
                                                    return $percent + $wordBonus;
                                                });
                                            @endphp

                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <div x-data="{
                                                    open: false,
                                                    search: '',
                                                    selectedId: '{{ $item['teacher_id'] }}',
                                                    selectedName: '{{ addslashes($item['teacher_name'] ?? '') }}'
                                                }" style="position: relative; flex: 1;">
                                                    <div @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                                                         class="imp-select" 
                                                         style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; background-color: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 0.5rem; padding: 0.35rem 0.6rem; font-size: 0.75rem; color: #ffffff;">
                                                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="selectedId ? selectedName : '— Pilih Guru —'"></span>
                                                        <span style="font-size: 0.65rem; color: #94a3b8;">▼</span>
                                                    </div>

                                                    <div x-show="open" 
                                                         @click.away="open = false" 
                                                         x-transition
                                                         style="position: absolute; z-index: 9999; top: 100%; left: 0; right: 0; min-width: 260px; margin-top: 0.25rem; background-color: #0f172a; border: 1px solid rgba(255,255,255,0.2); border-radius: 0.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.5); padding: 0.5rem; max-height: 250px; overflow-y: auto;">
                                                        <input type="text" 
                                                               x-model="search" 
                                                               x-ref="searchInput"
                                                               placeholder="🔍 Ketik nama / panggilan guru..." 
                                                               style="width: 100%; margin-bottom: 0.5rem; padding: 0.35rem 0.5rem; font-size: 0.75rem; background-color: #1e293b; border: 1px solid #3b82f6; border-radius: 0.375rem; color: #ffffff; outline: none;"
                                                               @click.stop />

                                                        <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; margin-bottom: 0.25rem; padding: 0 0.25rem;">
                                                            Urut Relevansi Kemiripan Nama:
                                                        </div>

                                                        <div @click="
                                                            selectedId = '';
                                                            selectedName = '— Pilih Guru —';
                                                            open = false;
                                                            $wire.updateItemRow('{{ $item['temp_id'] }}', 'teacher_id', '');
                                                        " style="padding: 0.35rem 0.5rem; font-size: 0.75rem; color: #94a3b8; cursor: pointer; border-radius: 0.25rem; margin-bottom: 0.25rem;" onmouseover="this.style.backgroundColor='#1e293b'" onmouseout="this.style.backgroundColor='transparent'">
                                                            — Pilih Guru —
                                                        </div>

                                                        @foreach ($sortedTeachers as $t)
                                                            <div x-show="search === '' || '{{ strtolower(addslashes($t->name)) }}'.includes(search.toLowerCase())"
                                                                 @click="
                                                                    selectedId = '{{ $t->id }}';
                                                                    selectedName = '{{ addslashes($t->name) }}';
                                                                    open = false;
                                                                    $wire.updateItemRow('{{ $item['temp_id'] }}', 'teacher_id', '{{ $t->id }}');
                                                                 "
                                                                 style="padding: 0.35rem 0.5rem; font-size: 0.75rem; color: #ffffff; cursor: pointer; border-radius: 0.25rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.15rem; @if($item['teacher_id'] == $t->id) background-color: #1e3a8a; font-weight: bold; @endif"
                                                                 onmouseover="this.style.backgroundColor='#2563eb'" 
                                                                 onmouseout="this.style.backgroundColor='{{ $item['teacher_id'] == $t->id ? '#1e3a8a' : 'transparent' }}'">
                                                                <span>{{ $t->name }}</span>
                                                                @if($item['teacher_id'] == $t->id)
                                                                    <span style="color: #4ade80; font-size: 0.65rem;">✓ Selected</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                @if (! $item['teacher_id'] && ! empty($item['teacher_raw']))
                                                    <x-filament::button
                                                        type="button"
                                                        color="info"
                                                        size="xs"
                                                        wire:click="createTeacherInline('{{ $item['temp_id'] }}', '{{ addslashes($item['teacher_raw']) }}')">
                                                        + Buat Guru
                                                    </x-filament::button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                            Tidak ada data jadwal yang sesuai dengan filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Bottom Footer Bar --}}
                    <div style="padding: 1rem 1.25rem; background-color: rgba(30, 41, 59, 0.9); border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="font-size: 0.75rem; color: #cbd5e1;">
                            Halaman {{ $currentPage }} dari {{ $totalPages }} (Total {{ $filteredCount }} data terfilter)
                        </div>
                        <div class="imp-flex-gap">
                            <x-filament::button wire:click="previousPage" color="gray" size="sm" :disabled="$currentPage <= 1">
                                ◄ Halaman Sebelumnya
                            </x-filament::button>
                            <x-filament::button wire:click="nextPage" color="gray" size="sm" :disabled="$currentPage >= $totalPages">
                                Halaman Selanjutnya ►
                            </x-filament::button>
                            <x-filament::button wire:click="cancelPreview" color="gray" size="sm">
                                Batal / Upload Ulang
                            </x-filament::button>
                            <x-filament::button wire:click="saveToDatabase" color="success" size="sm" icon="heroicon-o-check-circle">
                                SIMPAN JADWAL KE DATABASE
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
