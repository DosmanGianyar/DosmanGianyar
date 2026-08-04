<x-filament-panels::page>

    {{-- ─── Stats Ringkasan ─────────────────────────────────────────── --}}
    @php
        $totalSiswa   = \App\Models\User::where('role', 'siswa')->count();
        $withEkstra   = \App\Models\ExtracurricularMember::where('status', 'active')
                            ->distinct('user_id')->count('user_id');
        $tanpaEkstra  = max(0, $totalSiswa - $withEkstra);
        $percentage   = $totalSiswa > 0 ? round(($withEkstra / $totalSiswa) * 100) : 0;
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Total Siswa --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); display: flex; align-items: center; justify-content: center; color: #60a5fa;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #ffffff; line-height: 1.2; margin: 0;">{{ $totalSiswa }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Total Siswa</p>
            </div>
        </div>

        {{-- Sudah Punya Ekstra --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(34, 197, 94, 0.15); display: flex; align-items: center; justify-content: center; color: #4ade80;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #4ade80; line-height: 1.2; margin: 0;">{{ $withEkstra }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Punya Ekstra ({{ $percentage }}%)</p>
            </div>
        </div>

        {{-- Belum Punya Ekstra --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; color: #f87171;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #f87171; line-height: 1.2; margin: 0;">{{ $tanpaEkstra }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Belum Punya Ekstra</p>
            </div>
        </div>
    </div>

    {{-- ─── Tabel Siswa Tanpa Ekstra ────────────────────────────────── --}}
    <div>
        {{ $this->table }}
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
