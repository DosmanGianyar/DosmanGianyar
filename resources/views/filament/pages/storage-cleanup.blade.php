@php
    $attStats    = $this->getAttendanceStats();
    $permitStats = $this->getPermitStats();
@endphp

<x-filament-panels::page>
    <style>
        /* Force strictly bounded SVG dimensions for Blade Icons */
        svg {
            display: inline-block !important;
            vertical-align: middle !important;
        }
        .sc-stat-icon svg {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            min-height: 28px !important;
            max-width: 28px !important;
            max-height: 28px !important;
        }
        .fi-section-header-icon svg,
        .fi-section-header svg {
            width: 20px !important;
            height: 20px !important;
            min-width: 20px !important;
            min-height: 20px !important;
            max-width: 20px !important;
            max-height: 20px !important;
        }
        .fi-btn svg,
        button svg {
            width: 16px !important;
            height: 16px !important;
            min-width: 16px !important;
            min-height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
        }
    </style>

    <div class="space-y-6">

        {{-- Petunjuk Keamanan / Info --}}
        <x-filament::section icon="heroicon-o-information-circle" icon-color="warning">
            <x-slot name="heading">
                💡 Petunjuk Pembersihan Disk Server
            </x-slot>

            <div class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                Fitur ini digunakan untuk menghapus <strong>file foto fisik & file lampiran surat</strong> di folder storage server guna membebaskan ruang disk server.
                <ul class="list-disc list-inside mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                    <li><strong class="text-emerald-600 dark:text-emerald-400">Keamanan Database 100%:</strong> Histori kehadiran, jam presensi, status Hadir/Izin/Sakit/Alpa, dan rekap siswa <strong>TETAP UTUH & TIDAK HILANG</strong>.</li>
                    <li>Sangat direkomendasikan membersihkan foto presensi bulan-bulan lalu yang sudah tidak dipergunakan lagi.</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- Kartu Ringkasan Statistik Storage --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Stat Foto Presensi --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Storage Foto Presensi</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $attStats['size'] }}</div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total <strong>{{ number_format($attStats['files']) }}</strong> file foto ({{ number_format($attStats['records']) }} data presensi)
                    </p>
                </div>
                <div class="sc-stat-icon w-12 h-12 rounded-xl bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <x-heroicon-o-camera />
                </div>
            </div>

            {{-- Stat Surat Izin --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Storage Surat Izin / Sakit / Dispensasi</span>
                    <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $permitStats['size'] }}</div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total <strong>{{ number_format($permitStats['files']) }}</strong> file surat ({{ number_format($permitStats['records']) }} pengajuan)
                    </p>
                </div>
                <div class="sc-stat-icon w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <x-heroicon-o-document-text />
                </div>
            </div>

        </div>

        {{-- SECTION 1: Pembersihan Foto Selfie Presensi --}}
        <x-filament::section icon="heroicon-o-photo" icon-color="danger">
            <x-slot name="heading">
                1. Pembersihan Foto Selfie Presensi (Masuk & Pulang)
            </x-slot>
            <x-slot name="description">
                Menghapus file foto fisik dari disk server. Data jam & status presensi di DB tetap tersimpan utuh.
            </x-slot>

            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Dari Tanggal (Presensi)</label>
                        <input
                            type="date"
                            wire:model="attendance_start_date"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:border-amber-500 focus:ring-amber-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Sampai Tanggal (Presensi)</label>
                        <input
                            type="date"
                            wire:model="attendance_end_date"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:border-amber-500 focus:ring-amber-500"
                        />
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        *Kosongkan tanggal jika ingin menghapus <strong>SELURUH foto presensi</strong>.
                    </p>
                    <x-filament::button
                        color="danger"
                        icon="heroicon-o-trash"
                        x-data
                        x-on:click="
                            if (confirm('APAKAH ANDA YAKIN?\nFile foto selfie presensi pada rentang tanggal terpilih akan dihapus permanen dari disk server.\n\nData histori & status presensi di DB tetap tersimpan.')) {
                                $wire.deleteAttendancePhotos()
                            }
                        "
                    >
                        Hapus Foto Presensi
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- SECTION 2: Pembersihan File Surat Lampiran --}}
        <x-filament::section icon="heroicon-o-document-duplicate" icon-color="primary">
            <x-slot name="heading">
                2. Pembersihan File Surat Lampiran (Izin, Sakit, Dispensasi)
            </x-slot>
            <x-slot name="description">
                Menghapus file lampiran surat dari disk server. Record histori & alasan izin di DB tetap tersimpan utuh.
            </x-slot>

            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Dari Tanggal (Pengajuan Izin)</label>
                        <input
                            type="date"
                            wire:model="permit_start_date"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:border-amber-500 focus:ring-amber-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Sampai Tanggal (Pengajuan Izin)</label>
                        <input
                            type="date"
                            wire:model="permit_end_date"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:border-amber-500 focus:ring-amber-500"
                        />
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        *Kosongkan tanggal jika ingin menghapus <strong>SELURUH file surat lampiran</strong>.
                    </p>
                    <x-filament::button
                        color="primary"
                        icon="heroicon-o-trash"
                        x-data
                        x-on:click="
                            if (confirm('APAKAH ANDA YAKIN?\nFile surat lampiran (Izin, Sakit, Dispensasi) pada rentang tanggal terpilih akan dihapus permanen dari disk server.\n\nData histori & alasan izin di DB tetap tersimpan.')) {
                                $wire.deletePermitFiles()
                            }
                        "
                    >
                        Hapus File Surat Lampiran
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
