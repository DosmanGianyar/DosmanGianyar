<x-filament-panels::page>
    {{-- Card Ringkasan Jadwal Baku Default Sekolah --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-xs p-5 mb-6">
        <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400 rounded-lg">
                    <x-heroicon-o-clock class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Jadwal Jam Presensi Baku (Default Sekolah)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Acuan jadwal standar sekolah untuk Senin s.d. Minggu. Anda dapat menyesuaikan per hari pada form di bawah.</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800/60 uppercase font-semibold text-[11px] text-gray-600 dark:text-gray-400 border-y border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="py-2.5 px-3">Hari</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Absen Masuk Dibuka</th>
                        <th class="py-2.5 px-3">Tepat Waktu</th>
                        <th class="py-2.5 px-3">Absen Masuk Ditutup</th>
                        <th class="py-2.5 px-3">Absen Pulang Dibuka</th>
                        <th class="py-2.5 px-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                        <td class="py-2 px-3 font-semibold text-gray-900 dark:text-white">Senin – Jumat</td>
                        <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Aktif</span></td>
                        <td class="py-2 px-3 font-mono text-gray-900 dark:text-gray-100">05:00</td>
                        <td class="py-2 px-3 font-mono text-gray-900 dark:text-gray-100">07:15</td>
                        <td class="py-2 px-3 font-mono text-gray-900 dark:text-gray-100">08:00</td>
                        <td class="py-2 px-3 font-mono font-bold text-primary-600 dark:text-primary-400">13:30</td>
                        <td class="py-2 px-3 text-gray-500 dark:text-gray-400">KBM Penuh (Pulang 13:30)</td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                        <td class="py-2 px-3 font-semibold text-gray-900 dark:text-white">Sabtu</td>
                        <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Aktif</span></td>
                        <td class="py-2 px-3 font-mono text-gray-900 dark:text-gray-100">05:00</td>
                        <td class="py-2 px-3 font-mono text-gray-900 dark:text-gray-100">07:15</td>
                        <td class="py-2 px-3 font-mono text-gray-900 dark:text-gray-100">08:00</td>
                        <td class="py-2 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">11:00</td>
                        <td class="py-2 px-3 text-amber-600 dark:text-amber-400">Pulang Awal / Ekstrakurikuler (11:00)</td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                        <td class="py-2 px-3 font-semibold text-gray-900 dark:text-white">Minggu</td>
                        <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">Non-Aktif</span></td>
                        <td class="py-2 px-3 font-mono text-gray-400">05:00</td>
                        <td class="py-2 px-3 font-mono text-gray-400">07:15</td>
                        <td class="py-2 px-3 font-mono text-gray-400">08:00</td>
                        <td class="py-2 px-3 font-mono text-gray-400">13:30</td>
                        <td class="py-2 px-3 text-gray-500 dark:text-gray-400">Hari Libur Sekolah</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Form Setting Presensi Per Hari --}}
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Simpan Pengaturan Jam Presensi
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
