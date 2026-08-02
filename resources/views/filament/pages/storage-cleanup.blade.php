@php
    $attStats    = $this->getAttendanceStats();
    $permitStats = $this->getPermitStats();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Alert Info Keamanan --}}
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200 flex items-start gap-3">
            <x-heroicon-o-information-circle class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" />
            <div class="text-sm leading-relaxed">
                <strong class="font-semibold block text-base mb-1">💡 Petunjuk Keamanan Pembersihan Storage:</strong>
                Fitur ini digunakan untuk menghapus file media fisik (foto selfie & surat lampiran) di storage server guna **mencegah disk server penuh / overload**.
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs opacity-90">
                    <li><strong>Database Presensi & Izin TETAP UTUH 100%:</strong> Histori kehadiran, tanggal, jam check-in/out, status Hadir/Izin/Sakit/Alpa/Dispensasi <strong>TIDAK AKAN HILANG</strong> dan tetap bisa direkap ke laporan Excel/PDF.</li>
                    <li>File media yang sudah dihapus tidak dapat dikembalikan. Lakukan pembersihan secara berkala (misal: menghapus foto bulan-bulan lalu).</li>
                </ul>
            </div>
        </div>

        {{-- Ringkasan Statistik Storage --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Storage Foto Presensi</span>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $attStats['size'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Total {{ number_format($attStats['files']) }} file foto ({{ number_format($attStats['records']) }} data presensi)
                    </p>
                </div>
                <div class="p-3 bg-red-500/10 text-red-500 rounded-xl">
                    <x-heroicon-o-camera class="w-8 h-8" />
                </div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Storage Surat Izin / Sakit / Dispensasi</span>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $permitStats['size'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Total {{ number_format($permitStats['files']) }} file surat ({{ number_format($permitStats['records']) }} pengajuan izin)
                    </p>
                </div>
                <div class="p-3 bg-purple-500/10 text-purple-500 rounded-xl">
                    <x-heroicon-o-document-text class="w-8 h-8" />
                </div>
            </div>
        </div>

        {{-- SECTION 1: Pembersihan Foto Selfie Presensi --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div class="p-2 rounded-lg bg-red-500/10 text-red-600 dark:text-red-400">
                    <x-heroicon-o-photo class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">1. Pembersihan Foto Selfie Presensi (Masuk & Pulang)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Hapus file foto selfie dari storage disk server berdasarkan rentang tanggal. Data presensi di DB tetap utuh.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal (Presensi)</label>
                    <input type="date" wire:model.defer="attendance_start_date" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal (Presensi)</label>
                    <input type="date" wire:model.defer="attendance_end_date" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    *Kosongkan tanggal jika ingin menghapus <strong>SELURUH foto presensi</strong> yang ada.
                </p>
                <button
                    type="button"
                    x-data
                    x-on:click="
                        if (confirm('APAKAH ANDA YAKIN?\nFile foto selfie presensi pada rentang tanggal terpilih akan dihapus permanen dari storage server.\n\nData histori presensi pada database tetap tersimpan.')) {
                            $wire.deleteAttendancePhotos()
                        }
                    "
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow transition-colors"
                >
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Hapus Foto Presensi
                </button>
            </div>
        </div>

        {{-- SECTION 2: Pembersihan File Surat Lampiran (Izin / Sakit / Dispensasi) --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div class="p-2 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400">
                    <x-heroicon-o-document-duplicate class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">2. Pembersihan File Surat Lampiran (Izin, Sakit, Dispensasi)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Hapus file surat lampiran dari storage disk server berdasarkan rentang tanggal. Record pengajuan izin di DB tetap utuh.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal (Pengajuan Izin)</label>
                    <input type="date" wire:model.defer="permit_start_date" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal (Pengajuan Izin)</label>
                    <input type="date" wire:model.defer="permit_end_date" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    *Kosongkan tanggal jika ingin menghapus <strong>SELURUH file surat lampiran</strong> yang ada.
                </p>
                <button
                    type="button"
                    x-data
                    x-on:click="
                        if (confirm('APAKAH ANDA YAKIN?\nFile surat lampiran (Izin, Sakit, Dispensasi) pada rentang tanggal terpilih akan dihapus permanen dari storage server.\n\nData histori pengajuan izin pada database tetap tersimpan.')) {
                            $wire.deletePermitFiles()
                        }
                    "
                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg shadow transition-colors"
                >
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Hapus File Surat Lampiran
                </button>
            </div>
        </div>

    </div>
</x-filament-panels::page>
