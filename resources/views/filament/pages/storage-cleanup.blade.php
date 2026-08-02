@php
    $attStats    = $this->getAttendanceStats();
    $permitStats = $this->getPermitStats();
@endphp

<x-filament-panels::page>
    <style>
        .sc-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .sc-card {
            background-color: #111827;
            border-color: #1f2937;
        }
        .sc-alert {
            background-color: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            color: #92400e;
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
        }
        .dark .sc-alert {
            background-color: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.4);
            color: #fef3c7;
        }

        /* Enforce strict SVG dimensions so icons never blow up */
        .sc-alert svg,
        .sc-alert img {
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            min-height: 24px !important;
            max-width: 24px !important;
            max-height: 24px !important;
        }
        .sc-icon-lg svg,
        .sc-icon-lg img {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            min-height: 32px !important;
            max-width: 32px !important;
            max-height: 32px !important;
        }
        .sc-icon-md svg,
        .sc-icon-md img {
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            min-height: 24px !important;
            max-width: 24px !important;
            max-height: 24px !important;
        }
        .sc-icon-sm svg,
        .sc-icon-sm img {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            min-height: 18px !important;
            max-width: 18px !important;
            max-height: 18px !important;
        }

        .sc-input {
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            color: #111827;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            outline: none;
        }
        .dark .sc-input {
            border-color: #374151;
            background-color: #1f2937;
            color: #ffffff;
        }
        .sc-input:focus {
            border-color: #eab308;
            box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.2);
        }
        .sc-btn-danger {
            background-color: #dc2626;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }
        .sc-btn-danger:hover {
            background-color: #b91c1c;
        }
        .sc-btn-purple {
            background-color: #9333ea;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }
        .sc-btn-purple:hover {
            background-color: #7e22ce;
        }
    </style>

    <div class="space-y-6">

        {{-- Alert Info Keamanan --}}
        <div class="sc-alert">
            <div class="sc-icon-md text-amber-500 shrink-0 mt-0.5" style="width: 24px; height: 24px;">
                <x-heroicon-o-information-circle style="width: 24px; height: 24px;" />
            </div>
            <div class="text-sm leading-relaxed">
                <strong class="font-bold block text-base mb-1">💡 Petunjuk Pembersihan Disk Server:</strong>
                Fitur ini menghapus <strong>file foto fisik & file lampiran surat</strong> di folder storage server untuk membebaskan ruang disk.
                <ul class="list-disc list-inside mt-1 space-y-1 text-xs opacity-90">
                    <li><strong>Keamanan Database 100%:</strong> Histori kehadiran, jam presensi, status Hadir/Izin/Sakit/Alpa, dan rekap siswa <strong>TETAP UTUH & TIDAK HILANG</strong>.</li>
                    <li>Sangat direkomendasikan membersihkan foto presensi bulan-bulan lalu yang sudah tidak digunakan lagi.</li>
                </ul>
            </div>
        </div>

        {{-- Kartu Ringkasan Statistik Storage --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="sc-card flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Storage Foto Presensi</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $attStats['size'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Total <strong>{{ number_format($attStats['files']) }}</strong> file foto ({{ number_format($attStats['records']) }} data presensi)
                    </p>
                </div>
                <div class="sc-icon-lg p-3 bg-red-500/10 text-red-600 dark:text-red-400 rounded-xl" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-o-camera style="width: 32px; height: 32px;" />
                </div>
            </div>

            <div class="sc-card flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Storage Surat Izin / Sakit / Dispensasi</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $permitStats['size'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Total <strong>{{ number_format($permitStats['files']) }}</strong> file surat ({{ number_format($permitStats['records']) }} pengajuan)
                    </p>
                </div>
                <div class="sc-icon-lg p-3 bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-xl" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-o-document-text style="width: 32px; height: 32px;" />
                </div>
            </div>
        </div>

        {{-- SECTION 1: Pembersihan Foto Selfie Presensi --}}
        <div class="sc-card space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-200 dark:border-gray-800 pb-4">
                <div class="sc-icon-md p-2 rounded-lg bg-red-500/10 text-red-600 dark:text-red-400" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-o-photo style="width: 24px; height: 24px;" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">1. Pembersihan Foto Selfie Presensi (Masuk & Pulang)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Menghapus file foto fisik dari disk server. Data jam & status presensi di DB tetap utuh.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal (Presensi)</label>
                    <input type="date" wire:model="attendance_start_date" class="sc-input" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal (Presensi)</label>
                    <input type="date" wire:model="attendance_end_date" class="sc-input" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    *Kosongkan tanggal jika ingin menghapus <strong>SELURUH foto presensi</strong>.
                </p>
                <button
                    type="button"
                    x-data
                    x-on:click="
                        if (confirm('APAKAH ANDA YAKIN?\nFile foto selfie presensi pada rentang tanggal terpilih akan dihapus permanen dari disk server.\n\nData histori & status presensi di DB tetap tersimpan.')) {
                            $wire.deleteAttendancePhotos()
                        }
                    "
                    class="sc-btn-danger"
                >
                    <div class="sc-icon-sm" style="width: 18px; height: 18px; display: flex; align-items: center;">
                        <x-heroicon-o-trash style="width: 18px; height: 18px;" />
                    </div>
                    Hapus Foto Presensi
                </button>
            </div>
        </div>

        {{-- SECTION 2: Pembersihan File Surat Lampiran (Izin / Sakit / Dispensasi) --}}
        <div class="sc-card space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-200 dark:border-gray-800 pb-4">
                <div class="sc-icon-md p-2 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-o-document-duplicate style="width: 24px; height: 24px;" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">2. Pembersihan File Surat Lampiran (Izin, Sakit, Dispensasi)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Menghapus file lampiran surat dari disk server. Record histori & alasan izin di DB tetap utuh.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal (Pengajuan Izin)</label>
                    <input type="date" wire:model="permit_start_date" class="sc-input" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal (Pengajuan Izin)</label>
                    <input type="date" wire:model="permit_end_date" class="sc-input" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    *Kosongkan tanggal jika ingin menghapus <strong>SELURUH file surat lampiran</strong>.
                </p>
                <button
                    type="button"
                    x-data
                    x-on:click="
                        if (confirm('APAKAH ANDA YAKIN?\nFile surat lampiran (Izin, Sakit, Dispensasi) pada rentang tanggal terpilih akan dihapus permanen dari disk server.\n\nData histori & alasan izin di DB tetap tersimpan.')) {
                            $wire.deletePermitFiles()
                        }
                    "
                    class="sc-btn-purple"
                >
                    <div class="sc-icon-sm" style="width: 18px; height: 18px; display: flex; align-items: center;">
                        <x-heroicon-o-trash style="width: 18px; height: 18px;" />
                    </div>
                    Hapus File Surat Lampiran
                </button>
            </div>
        </div>

    </div>
</x-filament-panels::page>
