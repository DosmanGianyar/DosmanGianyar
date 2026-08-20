<x-filament-panels::page>

    {{-- ─── Stats Ringkasan Peminjaman ─────────────────────────────────── --}}
    @php
        $stats = $this->stats;
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Total Peminjaman --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); display: flex; align-items: center; justify-content: center; color: #60a5fa;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #ffffff; line-height: 1.2; margin: 0;">{{ $stats['total'] }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Total Peminjaman Buku</p>
            </div>
        </div>

        {{-- Sedang Dipinjam --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: center; color: #fbbf24;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #fbbf24; line-height: 1.2; margin: 0;">{{ $stats['borrowed'] }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Sedang Dipinjam</p>
            </div>
        </div>

        {{-- Sudah Dikembalikan --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(34, 197, 94, 0.15); display: flex; align-items: center; justify-content: center; color: #4ade80;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #4ade80; line-height: 1.2; margin: 0;">{{ $stats['returned'] }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Sudah Dikembalikan</p>
            </div>
        </div>

        {{-- Terlambat --}}
        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; color: #f87171;">
                <svg style="width: 22px; height: 22px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p style="font-size: 24px; font-weight: 700; color: #f87171; line-height: 1.2; margin: 0;">{{ $stats['overdue'] }}</p>
                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Buku Terlambat</p>
            </div>
        </div>
    </div>

    {{-- ─── Filament Table Data ────────────────────────────────────────── --}}
    {{ $this->table }}

</x-filament-panels::page>
