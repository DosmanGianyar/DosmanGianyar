<x-filament-panels::page>
    <style>
        .imp-wrap {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        .imp-banner {
            background: linear-gradient(135deg, #1d4ed8 0%, #3730a3 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

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
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .imp-table-container {
            background-color: #0f1d33;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            overflow: visible;
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

        .imp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            text-align: left;
        }

        .imp-table th {
            background-color: #1e293b;
            color: #cbd5e1;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            white-space: nowrap;
        }

        .imp-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            vertical-align: top;
            position: relative;
        }

        .imp-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }

        .imp-input {
            width: 100%;
            background-color: #1e293b !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 0.5rem !important;
            padding: 0.45rem 0.65rem !important;
            font-size: 0.8rem !important;
            outline: none !important;
        }

        .imp-input:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
        }

        .imp-input-name {
            min-width: 240px !important;
        }

        .imp-input-contact {
            min-width: 140px !important;
        }

        .imp-raw-hint {
            font-size: 0.7rem;
            color: #94a3b8;
            font-style: italic;
        }

        .imp-select-trigger {
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            padding: 0.45rem 0.65rem;
            font-size: 0.75rem;
            color: #ffffff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .imp-dropdown-menu {
            position: absolute;
            z-index: 9999;
            top: 100%;
            left: 0;
            right: 0;
            min-width: 260px;
            margin-top: 0.25rem;
            background-color: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            padding: 0.5rem;
            max-height: 250px;
            overflow-y: auto;
        }

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

        [x-cloak] { display: none !important; }
    </style>

    <div class="imp-wrap">

        {{-- Top Banner --}}
        <div class="imp-banner">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">
                    🏆
                </div>
                <div>
                    <h2 class="font-bold text-base text-white">Import Master Data Ekstrakurikuler</h2>
                    <p class="text-xs text-blue-100">Unggah file CSV baru atau muat file default `public/ekstra.csv` untuk pencocokan otomatis Guru Pembina dan Siswa Pengurus.</p>
                </div>
            </div>

            <x-filament::button wire:click="loadDefaultFile" color="warning" icon="heroicon-o-arrow-path">
                Muat Default `public/ekstra.csv`
            </x-filament::button>
        </div>

        {{-- Upload Card --}}
        @if(! $isParsed)
        <div class="imp-card">
            <div class="imp-card-title">
                <span>📁 Unggah File CSV Baru</span>
            </div>

            <form wire:submit.prevent="startParsing" class="space-y-4">
                {{ $this->form }}

                <div class="flex justify-end pt-2">
                    <x-filament::button type="submit" icon="heroicon-o-magnifying-glass" color="primary">
                        Pratinjau & Sesuai CSV
                    </x-filament::button>
                </div>
            </form>
        </div>
        @endif

        {{-- Preview Table Section --}}
        @if($isParsed)
        @php
            $allItems       = $this->getAllSessionItems();
            $paginatedItems = $this->getPaginatedItems();
            $teachers       = $this->teachersCollection;
            $students       = $this->studentsCollection;
            $totalPages     = $this->getTotalPages();
        @endphp

        <div class="imp-table-container">
            <div class="imp-table-header">
                <div>
                    <span class="font-bold text-base text-white flex items-center gap-2">
                        📊 Pratinjau & Matching Ekstrakurikuler (Total: {{ count($allItems) }} Ekstra)
                    </span>
                    <p class="text-xs text-slate-400 mt-0.5">Ketik pada dropdown pencarian untuk memilih Pembina, Ketua, atau Wakil Ketua. Klik simpan setelah data sesuai.</p>
                </div>

                <div class="flex items-center gap-3">
                    <x-filament::button wire:click="cancelPreview" color="gray" icon="heroicon-o-x-mark">
                        Batal / Reset
                    </x-filament::button>
                    <x-filament::button wire:click="saveToDatabase" color="success" size="lg" icon="heroicon-o-check-circle">
                        💾 Simpan All Data Ekstrakurikuler
                    </x-filament::button>
                </div>
            </div>

            {{-- Pagination Bar --}}
            @if($totalPages > 1)
            <div class="imp-pagination">
                <div class="text-xs text-slate-300">
                    Menampilkan halaman <strong class="text-amber-400">{{ $currentPage }}</strong> dari <strong class="text-white">{{ $totalPages }}</strong> (Total: {{ count($allItems) }} Data)
                </div>
                <div class="flex items-center gap-2">
                    <x-filament::button wire:click="previousPage" color="gray" size="xs" :disabled="$currentPage <= 1">
                        ◀ Sebelumnya
                    </x-filament::button>
                    <x-filament::button wire:click="nextPage" color="gray" size="xs" :disabled="$currentPage >= $totalPages">
                        Selanjutnya ▶
                    </x-filament::button>
                </div>
            </div>
            @endif

            <div class="overflow-x-auto" style="overflow-y: visible;">
                <table class="imp-table">
                    <thead>
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th class="min-w-[240px]">Nama Ekstra</th>
                            <th class="min-w-[320px]">Guru Pembina (Multi-Select)</th>
                            <th class="min-w-[320px]">Pengurus Ekstra (Ketua & Wakil)</th>
                            <th class="min-w-[140px]">Contact Person</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedItems as $index => $item)
                        @php
                            $rowNumber = (($currentPage - 1) * $perPage) + $index + 1;
                        @endphp
                        <tr wire:key="extra-row-{{ $item['temp_id'] }}">
                            <td class="text-center font-bold text-slate-500 pt-3">{{ $rowNumber }}</td>

                            {{-- Nama Ekstra --}}
                            <td>
                                <input type="text"
                                    value="{{ $item['name'] }}"
                                    @change="$wire.updateItemRow('{{ $item['temp_id'] }}', 'name', $event.target.value)"
                                    required class="imp-input imp-input-name font-bold">
                            </td>

                            {{-- Guru Pembina (Multi-Select) --}}
                            <td>
                                <div class="space-y-1.5 min-w-[280px]">
                                    @if(!empty($item['pembinas_raw']))
                                        <div class="imp-raw-hint mb-1">CSV: {{ implode(', ', $item['pembinas_raw']) }}</div>
                                    @endif

                                    {{-- Selected Teacher Badges --}}
                                    <div class="flex flex-wrap gap-1 mb-1">
                                        @php
                                            $selectedTeacherIds = $item['teacher_ids'] ?? [];
                                        @endphp
                                        @foreach($selectedTeacherIds as $tId)
                                            @php $tObj = $teachers->firstWhere('id', $tId); @endphp
                                            @if($tObj)
                                                <span class="inline-flex items-center gap-1.5 bg-amber-500/20 text-amber-300 text-[11px] font-semibold px-2 py-0.5 rounded-md border border-amber-500/30">
                                                    <span>{{ $tObj->name }}</span>
                                                    <button type="button" wire:click="updateItemRow('{{ $item['temp_id'] }}', 'remove_teacher_id', {{ $tId }})" class="hover:text-red-400 font-bold ml-0.5 text-xs">&times;</button>
                                                </span>
                                            @endif
                                        @endforeach
                                        @if(empty($selectedTeacherIds))
                                            <span class="text-xs text-slate-500 italic">Belum ada pembina terpilih</span>
                                        @endif
                                    </div>

                                    {{-- Search & Select Pembina Dropdown --}}
                                    <div x-data="{ open: false, search: '' }" style="position: relative;">
                                        <div @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())" class="imp-select-trigger">
                                            <span>➕ Tambah Guru Pembina...</span>
                                            <span class="text-slate-400 text-[10px]">🔍 ▼</span>
                                        </div>

                                        <div x-show="open" @click.away="open = false" x-cloak class="imp-dropdown-menu">
                                            <input type="text" x-model="search" x-ref="searchInput" placeholder="🔍 Ketik nama guru..." class="imp-input text-xs mb-2" @click.stop>
                                            <div class="space-y-0.5 max-h-40 overflow-y-auto">
                                                @foreach($teachers as $t)
                                                    <div x-show="search === '' || '{{ strtolower(addslashes($t->name)) }}'.includes(search.toLowerCase())"
                                                        @click="open = false; $wire.updateItemRow('{{ $item['temp_id'] }}', 'add_teacher_id', '{{ $t->id }}')"
                                                        class="px-2 py-1.5 hover:bg-amber-600/30 text-slate-200 hover:text-white text-xs cursor-pointer rounded flex items-center justify-between">
                                                        <span>{{ $t->name }}</span>
                                                        @if(in_array($t->id, $selectedTeacherIds))
                                                            <span class="text-amber-400 font-bold text-[10px]">✓ Terpilih</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Pengurus Ekstra (Ketua & Wakil Ketua) --}}
                            <td>
                                {{-- Ketua Searchable Select --}}
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-emerald-400">👑 Ketua:</span>
                                        @if(!empty($item['ketua_raw']))
                                            <span class="imp-raw-hint">CSV: {{ $item['ketua_raw'] }}</span>
                                        @endif
                                    </div>

                                    @php
                                        $ketuaObj = !empty($item['ketua_id']) ? $students->firstWhere('id', $item['ketua_id']) : null;
                                    @endphp
                                    <div x-data="{ open: false, search: '' }" style="position: relative;">
                                        <div @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())" class="imp-select-trigger">
                                            <span class="{{ $ketuaObj ? 'font-bold text-emerald-300' : 'text-slate-400' }}">
                                                {{ $ketuaObj ? $ketuaObj->name : '-- Pilih Ketua Ekstra --' }}
                                            </span>
                                            <span class="text-slate-400 text-[10px]">🔍 ▼</span>
                                        </div>

                                        <div x-show="open" @click.away="open = false" x-cloak class="imp-dropdown-menu border-emerald-500/40">
                                            <input type="text" x-model="search" x-ref="searchInput" placeholder="🔍 Ketik nama siswa..." class="imp-input text-xs mb-2" @click.stop>
                                            <div class="space-y-0.5 max-h-40 overflow-y-auto">
                                                <div @click="open = false; $wire.updateItemRow('{{ $item['temp_id'] }}', 'ketua_id', '')" class="px-2 py-1 hover:bg-slate-700 text-slate-400 text-xs cursor-pointer rounded italic">
                                                    -- Kosongkan --
                                                </div>
                                                @foreach($students as $s)
                                                    <div x-show="search === '' || '{{ strtolower(addslashes($s->name)) }}'.includes(search.toLowerCase())"
                                                        @click="open = false; $wire.updateItemRow('{{ $item['temp_id'] }}', 'ketua_id', '{{ $s->id }}')"
                                                        class="px-2 py-1.5 hover:bg-emerald-600/30 text-slate-200 hover:text-white text-xs cursor-pointer rounded flex items-center justify-between">
                                                        <span>{{ $s->name }}</span>
                                                        @if(($item['ketua_id'] ?? null) == $s->id)
                                                            <span class="text-emerald-400 font-bold text-[10px]">✓</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Wakil Ketua Searchable Select --}}
                                <div class="space-y-1 mt-3">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-sky-400">🎖️ Wakil Ketua:</span>
                                        @if(!empty($item['wakil_raw']))
                                            <span class="imp-raw-hint">CSV: {{ $item['wakil_raw'] }}</span>
                                        @endif
                                    </div>

                                    @php
                                        $wakilObj = !empty($item['wakil_ketua_id']) ? $students->firstWhere('id', $item['wakil_ketua_id']) : null;
                                    @endphp
                                    <div x-data="{ open: false, search: '' }" style="position: relative;">
                                        <div @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())" class="imp-select-trigger">
                                            <span class="{{ $wakilObj ? 'font-bold text-sky-300' : 'text-slate-400' }}">
                                                {{ $wakilObj ? $wakilObj->name : '-- Pilih Wakil Ketua --' }}
                                            </span>
                                            <span class="text-slate-400 text-[10px]">🔍 ▼</span>
                                        </div>

                                        <div x-show="open" @click.away="open = false" x-cloak class="imp-dropdown-menu border-sky-500/40">
                                            <input type="text" x-model="search" x-ref="searchInput" placeholder="🔍 Ketik nama siswa..." class="imp-input text-xs mb-2" @click.stop>
                                            <div class="space-y-0.5 max-h-40 overflow-y-auto">
                                                <div @click="open = false; $wire.updateItemRow('{{ $item['temp_id'] }}', 'wakil_ketua_id', '')" class="px-2 py-1 hover:bg-slate-700 text-slate-400 text-xs cursor-pointer rounded italic">
                                                    -- Kosongkan --
                                                </div>
                                                @foreach($students as $s)
                                                    <div x-show="search === '' || '{{ strtolower(addslashes($s->name)) }}'.includes(search.toLowerCase())"
                                                        @click="open = false; $wire.updateItemRow('{{ $item['temp_id'] }}', 'wakil_ketua_id', '{{ $s->id }}')"
                                                        class="px-2 py-1.5 hover:bg-sky-600/30 text-slate-200 hover:text-white text-xs cursor-pointer rounded flex items-center justify-between">
                                                        <span>{{ $s->name }}</span>
                                                        @if(($item['wakil_ketua_id'] ?? null) == $s->id)
                                                            <span class="text-sky-400 font-bold text-[10px]">✓</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact Person --}}
                            <td>
                                <input type="text"
                                    value="{{ $item['contact_person'] }}"
                                    @change="$wire.updateItemRow('{{ $item['temp_id'] }}', 'contact_person', $event.target.value)"
                                    placeholder="No HP" class="imp-input imp-input-contact font-mono">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Bottom Pagination & Action Bar --}}
            <div class="p-4 bg-slate-900 border-t border-white/10 flex items-center justify-between flex-wrap gap-4">
                <div class="text-xs text-slate-400">
                    Menampilkan {{ count($paginatedItems) }} dari {{ count($allItems) }} Ekstrakurikuler
                </div>

                <div class="flex items-center gap-3">
                    <x-filament::button wire:click="cancelPreview" color="gray" icon="heroicon-o-x-mark">
                        Batal / Reset
                    </x-filament::button>
                    <x-filament::button wire:click="saveToDatabase" color="success" size="lg" icon="heroicon-o-check-circle">
                        💾 Simpan All Data Ekstrakurikuler
                    </x-filament::button>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
