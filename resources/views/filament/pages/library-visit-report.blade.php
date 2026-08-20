<x-filament-panels::page>

    {{-- ─── Stats Ringkasan Kunjungan ─────────────────────────────────── --}}
    @php
        $stats = $this->stats;
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Total Kunjungan Bulan Ini --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: #34d399;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #ffffff; line-height: 1.2; margin: 0;">{{ $stats['total'] }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Total Kunjungan (Bulan Ini)</p>
            </div>
        </div>

        {{-- Siswa Unik Membaca --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); display: flex; align-items: center; justify-content: center; color: #60a5fa;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #60a5fa; line-height: 1.2; margin: 0;">{{ $stats['unique_students'] }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Siswa Unik Membaca</p>
            </div>
        </div>

        {{-- Kunjungan Hari Ini --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(168, 85, 247, 0.15); display: flex; align-items: center; justify-content: center; color: #c084fc;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #c084fc; line-height: 1.2; margin: 0;">{{ $stats['today'] }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Kunjungan Hari Ini</p>
            </div>
        </div>
    </div>

    {{-- ─── Filament Table Data ────────────────────────────────────────── --}}
    {{ $this->table }}

</x-filament-panels::page>
