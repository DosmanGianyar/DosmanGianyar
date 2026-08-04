<x-filament-panels::page>

    {{-- ─── Stats Ringkasan ─────────────────────────────────────────── --}}
    @php
        $totalSiswa   = \App\Models\User::where('role', 'siswa')->count();
        $withEkstra   = \App\Models\ExtracurricularMember::where('status', 'active')
                            ->distinct('user_id')->count('user_id');
        $tanpaEkstra  = max(0, $totalSiswa - $withEkstra);
        $percentage   = $totalSiswa > 0 ? round(($withEkstra / $totalSiswa) * 100) : 0;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        {{-- Total Siswa --}}
        <div class="rounded-xl bg-gray-800 border border-gray-700 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center shrink-0">
                <x-heroicon-o-users class="w-5 h-5 text-blue-400"/>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ $totalSiswa }}</p>
                <p class="text-xs text-gray-400">Total Siswa</p>
            </div>
        </div>

        {{-- Sudah Punya Ekstra --}}
        <div class="rounded-xl bg-gray-800 border border-gray-700 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center shrink-0">
                <x-heroicon-o-check-badge class="w-5 h-5 text-green-400"/>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-400">{{ $withEkstra }}</p>
                <p class="text-xs text-gray-400">Punya Ekstra ({{ $percentage }}%)</p>
            </div>
        </div>

        {{-- Belum Punya Ekstra --}}
        <div class="rounded-xl bg-gray-800 border border-gray-700 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-400"/>
            </div>
            <div>
                <p class="text-2xl font-bold text-red-400">{{ $tanpaEkstra }}</p>
                <p class="text-xs text-gray-400">Belum Punya Ekstra</p>
            </div>
        </div>
    </div>

    {{-- ─── Tabel Siswa Tanpa Ekstra ────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-700 overflow-hidden">
        <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex items-center gap-2">
            <x-heroicon-o-user-minus class="w-4 h-4 text-red-400"/>
            <h3 class="text-sm font-semibold text-gray-200">Siswa Tanpa Ekstrakurikuler</h3>
        </div>
        {{ $this->table }}
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
