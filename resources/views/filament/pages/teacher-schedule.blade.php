<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Control Bar (Hidden on Print) --}}
        <div class="no-print bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                {{-- Left: Filter Controls --}}
                <div class="flex flex-wrap items-center gap-3 flex-1">
                    {{-- Guru Selector --}}
                    <div class="min-w-[280px] max-w-md flex-1">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Pilih Nama Guru:
                        </label>
                        <select
                            wire:model.live="selectedTeacherId"
                            class="w-full text-sm font-semibold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3"
                        >
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
                    <div class="min-w-[180px]">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Tahun Ajaran:
                        </label>
                        <select
                            wire:model.live="academicYear"
                            class="w-full text-sm font-semibold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3"
                        >
                            @foreach($this->academicYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Right: Actions --}}
                <div class="flex items-center gap-2 pt-2 lg:pt-0">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak Jadwal (PDF / Print)
                    </button>

                    <a
                        href="{{ \App\Filament\Resources\ScheduleResource::getUrl() }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-lg shadow-sm transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Tabel Semua Jadwal
                    </a>
                </div>
            </div>

            {{-- Stat Pills --}}
            @if($this->selectedTeacher)
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Beban: {{ $this->stats['total_hours'] }} Jam / Minggu
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        {{ $this->stats['total_classes'] }} Kelas: {{ implode(', ', $this->stats['classes']) ?: '—' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Mapel: {{ implode(', ', $this->stats['subjects']) ?: '—' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 font-mono">
                        Kode: {{ $this->selectedTeacher->teacher_code ?: '—' }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Printable Timetable Sheet --}}
        <div class="timetable-sheet-container bg-white rounded-xl shadow-md border border-gray-300 p-6 overflow-x-auto text-black">
            <div class="timetable-sheet min-w-[900px] max-w-[1200px] mx-auto bg-white p-4">
                
                {{-- Header (matching aSc Timetables & SMAN 1 Gianyar layout) --}}
                <div class="timetable-header mb-3 flex items-center justify-between pb-2 border-b-2 border-black">
                    {{-- Left: Logo SMAN 1 Gianyar + Bali script --}}
                    <div class="header-left flex items-center gap-3">
                        <img
                            src="{{ asset('images/logo-dosman.png') }}"
                            alt="Logo SMAN 1 Gianyar"
                            class="h-16 w-auto object-contain"
                            onerror="this.src='{{ asset('img/logo_sekolah.png') }}'"
                        />
                        <div class="text-left leading-tight">
                            <div class="text-[10px] tracking-wider font-serif text-gray-700">ᬲ᭄ᬫᬦ᭄᭑ᬕ᭄ᬬᬜᬃ</div>
                            <div class="text-xs font-black tracking-tight uppercase text-black">SMAN 1 GIANYAR</div>
                        </div>
                    </div>

                    {{-- Center: Timetable Title & Teacher Name --}}
                    <div class="header-center text-center flex-1 px-4">
                        <div class="text-xs font-bold uppercase tracking-wider text-black">
                            JADWAL PELAJARAN SEMESTER {{ strtoupper(str_contains($academicYear, 'Genap') ? 'GENAP' : 'GANJIL') }} {{ preg_replace('/[^0-9\/]/', '', $academicYear) }}
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-black text-black tracking-tight my-0.5 uppercase">
                            Guru {{ $this->selectedTeacher?->name ?? '—' }}
                        </h1>
                        <div class="text-[11px] font-bold tracking-widest text-black uppercase">
                            SMA NEGERI 1 GIANYAR
                        </div>
                    </div>

                    {{-- Right: Placeholder balance --}}
                    <div class="header-right w-24 text-right">
                        <div class="text-[9px] text-gray-500 font-mono">SIMAK DOSMAN</div>
                    </div>
                </div>

                {{-- Timetable Main Table --}}
                <div class="timetable-table-wrapper">
                    <table class="timetable-grid w-full border-collapse border-2 border-black text-black">
                        <thead>
                            <tr>
                                {{-- Top-left cell --}}
                                <th class="w-[90px] border border-black bg-[#E8F5E9] py-2 px-1 text-center font-bold text-xs uppercase">
                                    JAM / HARI
                                </th>

                                {{-- Days Columns --}}
                                @foreach(\App\Filament\Pages\TeacherSchedulePage::DAYS as $d => $dayInfo)
                                    <th
                                        class="border border-black py-2.5 px-2 text-center font-black text-base lg:text-lg uppercase tracking-wide"
                                        style="background-color: {{ $dayInfo['bg'] }}; color: #000;"
                                    >
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
                                    <td
                                        class="border border-black text-center p-1 font-bold select-none"
                                        style="background-color: {{ $periodBg }}; color: #000;"
                                    >
                                        <div class="text-base lg:text-lg font-black leading-tight">{{ $p }}</div>
                                        <div class="text-[9px] lg:text-[10px] font-semibold text-gray-800 leading-none mt-0.5 tracking-tighter">
                                            {{ $timeStr }}
                                        </div>
                                    </td>

                                    {{-- Day Cells --}}
                                    @foreach(\App\Filament\Pages\TeacherSchedulePage::DAYS as $d => $dayInfo)
                                        @php
                                            $cell = $grid[$p][$d] ?? ['type' => 'empty', 'span' => 1];
                                        @endphp

                                        @if($cell['type'] === 'slot')
                                            <td
                                                rowspan="{{ $cell['span'] }}"
                                                class="border border-black bg-white p-2 align-top text-center relative hover:bg-amber-50 transition-colors"
                                                style="height: {{ $cell['span'] * 48 }}px;"
                                            >
                                                {{-- Mapel Code (Top-Left) --}}
                                                <div class="absolute top-1 left-1.5 text-[10px] font-bold text-black uppercase tracking-tight">
                                                    {{ $cell['subject_code'] }}
                                                </div>

                                                {{-- Class Name (Centered Big) --}}
                                                <div class="flex items-center justify-center h-full min-h-[38px] pt-2">
                                                    <span class="text-base lg:text-xl font-black text-black tracking-tight">
                                                        {{ $cell['class_name'] }}
                                                    </span>
                                                </div>

                                                {{-- Room (Bottom-Right if available) --}}
                                                @if(!empty($cell['room']))
                                                    <div class="absolute bottom-1 right-1.5 text-[9px] text-gray-500 font-semibold italic">
                                                        {{ $cell['room'] }}
                                                    </div>
                                                @endif
                                            </td>
                                        @elseif($cell['type'] === 'empty')
                                            <td class="border border-black bg-white h-12 p-0"></td>
                                        @endif
                                        {{-- If type === 'skip', do nothing (rowspan already covered) --}}
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Table Footer (under the table, matching aSc style) --}}
                <div class="timetable-footer mt-2 flex items-center justify-between text-[11px] font-bold text-black px-1">
                    <div class="footer-left uppercase">
                        {{ $this->selectedTeacher?->name }} • {{ $this->selectedTeacher?->teacher_code ?: '' }} • {{ $this->stats['total_hours'] }} JAM
                    </div>
                    <div class="footer-right text-gray-700 tracking-wider">
                        aSc Timetables
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Print & Custom CSS Styling --}}
    <style>
        @media print {
            /* Force landscape orientation on print */
            @page {
                size: A4 landscape;
                margin: 6mm 8mm;
            }

            /* Hide everything outside timetable sheet */
            body * {
                visibility: hidden;
            }

            .timetable-sheet, .timetable-sheet * {
                visibility: visible;
            }

            .timetable-sheet {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .no-print, .fi-topbar, .fi-sidebar, .fi-header {
                display: none !important;
            }

            /* Force browser to print exact background colors */
            .timetable-grid, th, td, div {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .timetable-grid {
                border: 2px solid #000 !important;
            }

            .timetable-grid th, .timetable-grid td {
                border: 1px solid #000 !important;
            }
        }
    </style>
</x-filament-panels::page>
