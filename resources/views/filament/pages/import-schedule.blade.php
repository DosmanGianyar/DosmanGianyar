<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ── Card Panduan Import ────────────────────────────────────────── --}}
        <div class="p-5 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl text-white shadow-lg">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-white/10 rounded-xl backdrop-blur-md shrink-0">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Import & Parsing PDF Jadwal Pelajaran (aSc Timetables)</h3>
                    <p class="text-xs text-blue-100 mt-1 leading-relaxed">
                        Unggah file PDF jadwal hasil cetak **aSc Timetables** atau file Excel export per tingkat kelas (Kelas 10, 11, atau 12). Sistem akan mengekstrak jam, hari, mapel, dan melakukan pencocokan nama guru secara otomatis.
                    </p>
                </div>
            </div>
        </div>

        @if (! $isParsed)
            {{-- ── Form Langkah 1: Upload File & Parameter ──────────────────── --}}
            <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-6">
                <h4 class="text-base font-bold text-gray-800 dark:text-gray-200 border-b pb-3">Langkah 1: Unggah File & Pilih Tingkat Kelas</h4>

                <form wire:submit.prevent="startParsing" class="space-y-6">
                    {{ $this->form }}

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                            Ekstrak & Pratinjau Jadwal
                        </button>
                    </div>
                </form>
            </div>
        @else
            {{-- ── Langkah 2: Ringkasan & Pratinjau Pencocokan ───────────── --}}
            <div class="space-y-6">

                {{-- Summary Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div class="text-xs font-semibold text-gray-500">Tingkat Kelas</div>
                        <div class="text-xl font-extrabold text-blue-600 mt-1">Kelas {{ $selectedGrade }}</div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div class="text-xs font-semibold text-gray-500">Tahun Ajaran</div>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $academicYear }}</div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div class="text-xs font-semibold text-gray-500">Total Slot Jadwal</div>
                        <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ count($parsedItems) }} Slot</div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div class="text-xs font-semibold text-gray-500">Status Opsi</div>
                        <div class="text-xs font-bold text-amber-600 mt-1">
                            {{ $replaceExisting ? 'Hapus & Timpa Jadwal Lama' : 'Tambahkan Ke Jadwal Ada' }}
                        </div>
                    </div>
                </div>

                {{-- Table Pratinjau --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">
                            Pratinjau & Verifikasi Pencocokan Guru ({{ count($parsedItems) }} Data)
                        </h4>
                        <div class="flex gap-2">
                            <button wire:click="cancelPreview" type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-lg transition-colors">
                                Ulangi / Batal
                            </button>
                            <button wire:click="saveToDatabase" type="button" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow transition-colors">
                                💾 SIMPAN KE DATABASE
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold uppercase">
                                <tr>
                                    <th class="px-4 py-3">Kelas</th>
                                    <th class="px-4 py-3">Hari & Jam</th>
                                    <th class="px-4 py-3">Mata Pelajaran</th>
                                    <th class="px-4 py-3">Nama Guru di PDF</th>
                                    <th class="px-4 py-3">Hasil Match Database</th>
                                    <th class="px-4 py-3">Aksi Guru</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @php
                                    $teachersList = \App\Models\User::where('role', 'guru')->orderBy('name')->get();
                                    $classesList  = \App\Models\SchoolClass::orderBy('name')->get();
                                    $subjectsList = \App\Models\Subject::orderBy('name')->get();
                                    $daysName     = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                @endphp

                                @foreach ($parsedItems as $idx => $item)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                        {{-- Kelas --}}
                                        <td class="px-4 py-3 font-extrabold text-blue-600">
                                            <select wire:model="parsedItems.{{ $idx }}.class_id" class="text-xs py-1 px-2 rounded border border-gray-300 dark:bg-gray-800 dark:text-white">
                                                <option value="">— Pilih Kelas —</option>
                                                @foreach ($classesList as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        {{-- Hari & Jam --}}
                                        <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $daysName[$item['day']] ?? 'Hari ' . $item['day'] }}</span>
                                            <br>
                                            <span class="text-[11px] text-gray-500">Jam {{ $item['period'] }} ({{ $item['start_time'] }} - {{ $item['end_time'] }})</span>
                                        </td>

                                        {{-- Mapel --}}
                                        <td class="px-4 py-3">
                                            <select wire:model="parsedItems.{{ $idx }}.subject_id" class="text-xs py-1 px-2 rounded border border-gray-300 dark:bg-gray-800 dark:text-white">
                                                <option value="">— Pilih Mapel —</option>
                                                @foreach ($subjectsList as $s)
                                                    <option value="{{ $s->id }}">{{ $s->code ? "[{$s->code}] " : '' }}{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        {{-- Teks Raw PDF --}}
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $item['teacher_raw'] ?: '—' }}
                                            @if(!empty($item['room']))
                                                <span class="inline-block px-1.5 py-0.5 ml-1 text-[10px] bg-purple-100 text-purple-700 font-bold rounded">
                                                    {{ $item['room'] }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Match Status --}}
                                        <td class="px-4 py-3">
                                            @if ($item['teacher_id'])
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                    ✓ {{ $item['teacher_name'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded-full bg-amber-100 text-amber-800">
                                                    ⚠ Belum Ada di DB
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Aksi Guru --}}
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <select wire:model="parsedItems.{{ $idx }}.teacher_id" class="text-xs py-1 px-2 rounded border border-gray-300 dark:bg-gray-800 dark:text-white">
                                                    <option value="">— Pilih Guru —</option>
                                                    @foreach ($teachersList as $t)
                                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                                    @endforeach
                                                </select>

                                                @if (! $item['teacher_id'] && ! empty($item['teacher_raw']))
                                                    <button type="button"
                                                        wire:click="createTeacherInline('{{ $item['temp_id'] }}', '{{ addslashes($item['teacher_raw']) }}')"
                                                        class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] rounded transition-colors whitespace-nowrap"
                                                        title="Buat akun guru baru secara instan">
                                                        + Buat Guru Baru
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <button wire:click="cancelPreview" type="button" class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-xl transition-colors">
                            Batal
                        </button>
                        <button wire:click="saveToDatabase" type="button" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-lg transition-colors">
                            💾 SIMPAN JADWAL KE DATABASE
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
