<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Section 1: Upload Form & File Default Button --}}
        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Import Master Data Ekstrakurikuler</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Unggah file CSV baru atau muat file default `public/ekstra.csv` untuk melakukan pencocokan otomatis Guru Pembina dan Siswa Pengurus.</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-filament::button wire:click="loadDefaultFile" color="gray" icon="heroicon-o-arrow-path">
                        Muat `public/ekstra.csv`
                    </x-filament::button>
                </div>
            </div>

            <form wire:submit.prevent="startParsing" class="space-y-4">
                {{ $this->form }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">
                        Pratinjau & Sesuai CSV
                    </x-filament::button>
                </div>
            </form>
        </div>

        {{-- Section 2: Interactive Preview & Dropdown Matching Table --}}
        @if($isParsed && count($previewItems) > 0)
        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Hasil Pencocokan Data Ekstrakurikuler</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ditemukan {{ count($previewItems) }} ekstrakurikuler. Periksa dan sesuaikan pilihan dropdown di bawah jika belum cocok.</p>
                </div>
                <x-filament::button wire:click="saveAll" color="success" size="lg" icon="heroicon-o-check-circle">
                    Simpan All Data Ekstrakurikuler
                </x-filament::button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-3 w-10 text-center">No</th>
                            <th class="p-3 w-56">Nama Ekstra</th>
                            <th class="p-3 w-72">Guru Pembina</th>
                            <th class="p-3 w-64">Ketua Ekstra (Siswa 1)</th>
                            <th class="p-3 w-64">Wakil Ketua (Siswa 2)</th>
                            <th class="p-3 w-48">Contact Person (Admin)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($previewItems as $index => $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                            <td class="p-3 text-center font-bold text-gray-400">{{ $index + 1 }}</td>

                            {{-- Nama Ekstra --}}
                            <td class="p-3 align-top">
                                <input type="text" wire:model.defer="previewItems.{{ $index }}.name" required
                                    class="w-full text-xs font-bold text-gray-900 dark:text-white bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 focus:ring-primary-500">
                            </td>

                            {{-- Pembina (Guru) --}}
                            <td class="p-3 align-top space-y-1.5">
                                @if(!empty($item['pembinas_raw']))
                                    <div class="text-[10px] text-gray-400 italic mb-1">
                                        CSV: {{ implode(', ', $item['pembinas_raw']) }}
                                    </div>
                                @endif
                                <select multiple wire:model.defer="previewItems.{{ $index }}.teacher_ids"
                                    class="w-full text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-primary-500 h-24">
                                    @foreach($teachersList as $tId => $tName)
                                        <option value="{{ $tId }}">{{ $tName }}</option>
                                    @endforeach
                                </select>
                                <span class="text-[10px] text-gray-400">Tahan Ctrl/Cmd untuk memilih lebih dari 1 Pembina</span>
                            </td>

                            {{-- Ketua (Siswa 1) --}}
                            <td class="p-3 align-top">
                                @if($item['ketua_raw'])
                                    <div class="text-[10px] text-gray-400 italic mb-1">CSV: {{ $item['ketua_raw'] }}</div>
                                @endif
                                <select wire:model.defer="previewItems.{{ $index }}.ketua_id"
                                    class="w-full text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 focus:ring-emerald-500 font-medium">
                                    <option value="">-- Pilih Ketua Ekstra --</option>
                                    @foreach($studentsList as $sId => $sName)
                                        <option value="{{ $sId }}">{{ $sName }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Wakil Ketua (Siswa 2) --}}
                            <td class="p-3 align-top">
                                @if($item['wakil_raw'])
                                    <div class="text-[10px] text-gray-400 italic mb-1">CSV: {{ $item['wakil_raw'] }}</div>
                                @endif
                                <select wire:model.defer="previewItems.{{ $index }}.wakil_ketua_id"
                                    class="w-full text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 focus:ring-sky-500 font-medium">
                                    <option value="">-- Pilih Wakil Ketua --</option>
                                    @foreach($studentsList as $sId => $sName)
                                        <option value="{{ $sId }}">{{ $sName }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Contact Person --}}
                            <td class="p-3 align-top">
                                <input type="text" wire:model.defer="previewItems.{{ $index }}.contact_person" placeholder="No HP"
                                    class="w-full text-xs font-mono text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5">
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end pt-2">
                <x-filament::button wire:click="saveAll" color="success" size="lg" icon="heroicon-o-check-circle">
                    Simpan All Data Ekstrakurikuler
                </x-filament::button>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
