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
            min-width: 260px !important;
        }

        .imp-input-contact {
            min-width: 140px !important;
        }

        .imp-raw-hint {
            font-size: 0.7rem;
            color: #94a3b8;
            font-style: italic;
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

        {{-- Preview Table Section --}}
        @if($isParsed && count($previewItems) > 0)
        <div class="imp-table-container">
            <div class="imp-table-header">
                <div>
                    <span class="imp-table-title">
                        <span>📊 Hasil Pencocokan Data Ekstrakurikuler</span>
                    </span>
                    <p class="text-xs text-slate-400 mt-0.5">Ditemukan {{ count($previewItems) }} Ekstrakurikuler. Gunakan fitur pencarian nama di tiap kolom untuk mengubah Pembina / Pengurus.</p>
                </div>
                <x-filament::button wire:click="saveAll" color="success" size="lg" icon="heroicon-o-check-circle">
                    💾 Simpan All Data Ekstrakurikuler
                </x-filament::button>
            </div>

            <div class="overflow-visible">
                <table class="imp-table">
                    <thead>
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th class="min-w-[240px]">Nama Ekstra</th>
                            <th class="min-w-[300px]">Guru Pembina (Multi-Select)</th>
                            <th class="min-w-[300px]">Pengurus Ekstra (Ketua & Wakil)</th>
                            <th class="min-w-[140px]">Contact Person (Admin)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewItems as $index => $item)
                        <tr>
                            <td class="text-center font-bold text-slate-500 pt-3">{{ $index + 1 }}</td>

                            {{-- Nama Ekstra --}}
                            <td>
                                <input type="text" wire:model.defer="previewItems.{{ $index }}.name" required class="imp-input imp-input-name font-bold">
                            </td>

                            {{-- Guru Pembina Picker (Badges + Interactive Search) --}}
                            <td>
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    selectedIds: $wire.entangle('previewItems.{{ $index }}.teacher_ids').defer,
                                    teachers: @js($teachersList),
                                    get selectedPembinas() {
                                        return (this.selectedIds || []).map(id => ({ id: parseInt(id), name: this.teachers[id] || id }));
                                    },
                                    get filteredTeachers() {
                                        if (!this.search) return this.teachers;
                                        const s = this.search.toLowerCase();
                                        const res = {};
                                        for (const [id, name] of Object.entries(this.teachers)) {
                                            if (name.toLowerCase().includes(s)) res[id] = name;
                                        }
                                        return res;
                                    },
                                    addTeacher(id) {
                                        id = parseInt(id);
                                        if (!this.selectedIds) this.selectedIds = [];
                                        if (!this.selectedIds.includes(id)) {
                                            this.selectedIds.push(id);
                                        }
                                        this.search = '';
                                        this.open = false;
                                    },
                                    removeTeacher(id) {
                                        this.selectedIds = (this.selectedIds || []).filter(i => parseInt(i) !== parseInt(id));
                                    }
                                }" class="space-y-1.5 min-w-[280px]">

                                    @if(!empty($item['pembinas_raw']))
                                        <div class="imp-raw-hint mb-1">CSV: {{ implode(', ', $item['pembinas_raw']) }}</div>
                                    @endif

                                    {{-- Selected Teacher Badges --}}
                                    <div class="flex flex-wrap gap-1 mb-1">
                                        <template x-for="p in selectedPembinas" :key="p.id">
                                            <span class="inline-flex items-center gap-1 bg-amber-500/20 text-amber-300 text-[11px] font-semibold px-2 py-0.5 rounded-md border border-amber-500/30 shadow-xs">
                                                <span x-text="p.name"></span>
                                                <button type="button" @click="removeTeacher(p.id)" class="hover:text-red-400 font-bold ml-0.5 text-xs">&times;</button>
                                            </span>
                                        </template>
                                    </div>

                                    {{-- Search Input & Dropdown --}}
                                    <div class="relative" @click.outside="open = false">
                                        <input type="text" x-model="search" @focus="open = true" @input="open = true"
                                            placeholder="🔍 Cari & Tambah Guru Pembina..."
                                            class="imp-input text-xs">

                                        <div x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-slate-800 border border-slate-700 rounded-lg shadow-2xl p-1 divide-y divide-slate-700/50">
                                            <template x-for="(name, id) in filteredTeachers" :key="id">
                                                <div @click="addTeacher(id)" class="px-2.5 py-1.5 hover:bg-amber-600/30 text-slate-200 hover:text-white text-xs cursor-pointer rounded flex items-center justify-between">
                                                    <span x-text="name"></span>
                                                    <span x-show="(selectedIds || []).includes(parseInt(id))" class="text-amber-400 font-bold text-[10px]">✓ Terpilih</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Pengurus Ekstra (Ketua & Wakil dengan Search Box) --}}
                            <td>
                                {{-- Ketua Searchable Picker --}}
                                <div class="space-y-1" x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: $wire.entangle('previewItems.{{ $index }}.ketua_id').defer,
                                    students: @js($studentsList),
                                    get selectedName() {
                                        return this.selectedId ? (this.students[this.selectedId] || '') : '';
                                    },
                                    get filteredStudents() {
                                        if (!this.search) return this.students;
                                        const s = this.search.toLowerCase();
                                        const res = {};
                                        for (const [id, name] of Object.entries(this.students)) {
                                            if (name.toLowerCase().includes(s)) res[id] = name;
                                        }
                                        return res;
                                    },
                                    selectStudent(id) {
                                        this.selectedId = id ? parseInt(id) : null;
                                        this.open = false;
                                        this.search = '';
                                    }
                                }">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-emerald-400">👑 Ketua:</span>
                                        @if(!empty($item['ketua_raw']))
                                            <span class="imp-raw-hint">CSV: {{ $item['ketua_raw'] }}</span>
                                        @endif
                                    </div>

                                    <div class="relative" @click.outside="open = false">
                                        <div @click="open = !open" class="imp-input flex items-center justify-between cursor-pointer text-xs">
                                            <span x-text="selectedName || '-- Pilih Ketua Ekstra --'" :class="selectedId ? 'font-bold text-white' : 'text-slate-400'"></span>
                                            <span class="text-slate-400 text-[10px]">🔍 ▼</span>
                                        </div>

                                        <div x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-slate-800 border border-slate-700 rounded-lg shadow-2xl p-2 w-72">
                                            <input type="text" x-model="search" placeholder="Cari nama siswa..." class="imp-input text-xs mb-2">

                                            <div class="max-h-44 overflow-y-auto space-y-0.5">
                                                <div @click="selectStudent(null)" class="px-2 py-1 hover:bg-slate-700 text-slate-400 text-xs cursor-pointer rounded italic">
                                                    -- Kosongkan --
                                                </div>
                                                <template x-for="(name, id) in filteredStudents" :key="id">
                                                    <div @click="selectStudent(id)" class="px-2 py-1.5 hover:bg-emerald-600/30 text-slate-200 hover:text-white text-xs cursor-pointer rounded flex items-center justify-between">
                                                        <span x-text="name"></span>
                                                        <span x-show="parseInt(selectedId) === parseInt(id)" class="text-emerald-400 font-bold text-[10px]">✓</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Wakil Ketua Searchable Picker --}}
                                <div class="space-y-1 mt-3" x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: $wire.entangle('previewItems.{{ $index }}.wakil_ketua_id').defer,
                                    students: @js($studentsList),
                                    get selectedName() {
                                        return this.selectedId ? (this.students[this.selectedId] || '') : '';
                                    },
                                    get filteredStudents() {
                                        if (!this.search) return this.students;
                                        const s = this.search.toLowerCase();
                                        const res = {};
                                        for (const [id, name] of Object.entries(this.students)) {
                                            if (name.toLowerCase().includes(s)) res[id] = name;
                                        }
                                        return res;
                                    },
                                    selectStudent(id) {
                                        this.selectedId = id ? parseInt(id) : null;
                                        this.open = false;
                                        this.search = '';
                                    }
                                }">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-sky-400">🎖️ Wakil Ketua:</span>
                                        @if(!empty($item['wakil_raw']))
                                            <span class="imp-raw-hint">CSV: {{ $item['wakil_raw'] }}</span>
                                        @endif
                                    </div>

                                    <div class="relative" @click.outside="open = false">
                                        <div @click="open = !open" class="imp-input flex items-center justify-between cursor-pointer text-xs">
                                            <span x-text="selectedName || '-- Pilih Wakil Ketua --'" :class="selectedId ? 'font-bold text-white' : 'text-slate-400'"></span>
                                            <span class="text-slate-400 text-[10px]">🔍 ▼</span>
                                        </div>

                                        <div x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-slate-800 border border-slate-700 rounded-lg shadow-2xl p-2 w-72">
                                            <input type="text" x-model="search" placeholder="Cari nama siswa..." class="imp-input text-xs mb-2">

                                            <div class="max-h-44 overflow-y-auto space-y-0.5">
                                                <div @click="selectStudent(null)" class="px-2 py-1 hover:bg-slate-700 text-slate-400 text-xs cursor-pointer rounded italic">
                                                    -- Kosongkan --
                                                </div>
                                                <template x-for="(name, id) in filteredStudents" :key="id">
                                                    <div @click="selectStudent(id)" class="px-2 py-1.5 hover:bg-sky-600/30 text-slate-200 hover:text-white text-xs cursor-pointer rounded flex items-center justify-between">
                                                        <span x-text="name"></span>
                                                        <span x-show="parseInt(selectedId) === parseInt(id)" class="text-sky-400 font-bold text-[10px]">✓</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact Person --}}
                            <td>
                                <input type="text" wire:model.defer="previewItems.{{ $index }}.contact_person" placeholder="No HP" class="imp-input imp-input-contact font-mono">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-slate-900 border-t border-white/10 flex justify-end">
                <x-filament::button wire:click="saveAll" color="success" size="lg" icon="heroicon-o-check-circle">
                    💾 Simpan All Data Ekstrakurikuler
                </x-filament::button>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
