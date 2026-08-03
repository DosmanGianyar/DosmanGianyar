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
            overflow: hidden;
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

        .imp-input, .imp-select {
            width: 100%;
            background-color: #1e293b !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 0.5rem !important;
            padding: 0.45rem 0.65rem !important;
            font-size: 0.8rem !important;
            outline: none !important;
        }

        .imp-input-name {
            min-width: 280px !important;
        }

        .imp-input:focus, .imp-select:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
        }

        .imp-select-multi {
            min-width: 280px !important;
            height: 120px !important;
        }

        .imp-select-student {
            width: 100% !important;
        }

        .imp-input-contact {
            min-width: 140px !important;
        }

        .imp-raw-hint {
            font-size: 0.7rem;
            color: #94a3b8;
            font-style: italic;
        }
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
                    <p class="text-xs text-slate-400 mt-0.5">Ditemukan {{ count($previewItems) }} Ekstrakurikuler. Pembina & Pengurus yang cocok di DB telah **otomatis terpilih** di bawah.</p>
                </div>
                <x-filament::button wire:click="saveAll" color="success" size="lg" icon="heroicon-o-check-circle">
                    💾 Simpan All Data Ekstrakurikuler
                </x-filament::button>
            </div>

            <div class="overflow-x-auto">
                <table class="imp-table">
                    <thead>
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th class="min-w-[260px]">Nama Ekstra</th>
                            <th class="min-w-[280px]">Guru Pembina</th>
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

                            {{-- Guru Pembina (Multi Select Auto-Selected) --}}
                            <td>
                                @if(!empty($item['pembinas_raw']))
                                    <div class="imp-raw-hint mb-1">CSV: {{ implode(', ', $item['pembinas_raw']) }}</div>
                                @endif
                                <select multiple wire:model.defer="previewItems.{{ $index }}.teacher_ids" class="imp-select imp-select-multi">
                                    @foreach($teachersList as $tId => $tName)
                                        <option value="{{ $tId }}" {{ in_array($tId, $item['teacher_ids'] ?? []) ? 'selected' : '' }}>
                                            {{ $tName }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-[10px] text-slate-400 mt-1">
                                    @if(count($item['teacher_ids'] ?? []) > 0)
                                        <span class="text-emerald-400 font-semibold">✓ {{ count($item['teacher_ids']) }} Pembina Otomatis Terpilih</span>
                                    @else
                                        <span>Tahan Ctrl/Cmd untuk memilih lebih dari 1 Pembina</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Pengurus Ekstra (Ketua & Wakil dalam 1 Kolom Atas Bawah) --}}
                            <td>
                                {{-- Ketua --}}
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-emerald-400">👑 Ketua:</span>
                                        @if(!empty($item['ketua_raw']))
                                            <span class="imp-raw-hint">CSV: {{ $item['ketua_raw'] }}</span>
                                        @endif
                                    </div>
                                    <select wire:model.defer="previewItems.{{ $index }}.ketua_id" class="imp-select imp-select-student">
                                        <option value="">-- Pilih Ketua Ekstra --</option>
                                        @foreach($studentsList as $sId => $sName)
                                            <option value="{{ $sId }}" {{ ($item['ketua_id'] ?? null) == $sId ? 'selected' : '' }}>
                                                {{ $sName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Wakil Ketua --}}
                                <div class="space-y-1 mt-3">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-sky-400">🎖️ Wakil Ketua:</span>
                                        @if(!empty($item['wakil_raw']))
                                            <span class="imp-raw-hint">CSV: {{ $item['wakil_raw'] }}</span>
                                        @endif
                                    </div>
                                    <select wire:model.defer="previewItems.{{ $index }}.wakil_ketua_id" class="imp-select imp-select-student">
                                        <option value="">-- Pilih Wakil Ketua --</option>
                                        @foreach($studentsList as $sId => $sName)
                                            <option value="{{ $sId }}" {{ ($item['wakil_ketua_id'] ?? null) == $sId ? 'selected' : '' }}>
                                                {{ $sName }}
                                            </option>
                                        @endforeach
                                    </select>
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
