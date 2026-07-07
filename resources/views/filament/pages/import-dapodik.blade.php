<x-filament-panels::page>
    {{-- Panduan ----------------------------------------------------------------}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-sm text-blue-800 space-y-1">
        <p class="font-semibold">Cara menggunakan Import Dapodik:</p>
        <ol class="list-decimal list-inside space-y-0.5 text-blue-700">
            <li>Login ke Dapodik → menu <strong>Peserta Didik</strong></li>
            <li>Pilih <strong>Rekap Data Peserta Didik</strong> → klik tombol <strong>Export Excel</strong></li>
            <li>Upload file <code>.xlsx</code> hasil export di sini</li>
            <li>Sistem otomatis <strong>menambah</strong> siswa baru atau <strong>memperbarui</strong> data yang sudah ada berdasarkan <strong>NISN</strong></li>
        </ol>
        <p class="text-blue-600 mt-2">⚠ Pastikan kelas-kelas sudah dibuat di <strong>Data Kelas</strong> sebelum import, agar siswa bisa langsung terhubung ke kelasnya.</p>
        <p class="mt-3">
            <a href="{{ asset('templates/contoh-import-dapodik.xlsx') }}" download
               class="inline-flex items-center gap-1.5 text-blue-700 font-semibold underline hover:text-blue-900">
                📄 Unduh contoh format Excel
            </a>
            <span class="text-blue-600"> — kalau belum punya file ekspor Dapodik, bisa isi manual mengikuti format ini.</span>
        </p>
    </div>

    {{-- Upload Form (hidden while processing) --------------------------------}}
    @if(!$processing)
    <form wire:submit="startImport" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="startImport">Proses Import</span>
                <span wire:loading wire:target="startImport">Membaca file…</span>
            </x-filament::button>
        </div>
    </form>
    @endif

    {{-- Progress Bar (shown while processing) --------------------------------}}
    @if($processing)
    @php $pct = $totalRows > 0 ? min(100, (int)(($processedRows / $totalRows) * 100)) : 0; @endphp
    <div wire:poll.300ms="processChunk">

        {{-- Card wrapper --}}
        <div style="border:1px solid #bfdbfe;border-radius:12px;padding:24px;background:#eff6ff;">

            {{-- Header row --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    {{-- CSS-only spinner (no SVG) --}}
                    <div style="width:18px;height:18px;min-width:18px;border-radius:50%;border:3px solid #bfdbfe;border-top-color:#2563eb;animation:spin 0.8s linear infinite;"></div>
                    <span style="font-size:14px;font-weight:600;color:#1e40af;">Mengimpor data Dapodik…</span>
                </div>
                <span style="font-size:22px;font-weight:700;color:#1d4ed8;">{{ $pct }}%</span>
            </div>

            {{-- Progress bar --}}
            <div style="width:100%;background:#dbeafe;border-radius:9999px;height:14px;overflow:hidden;margin-bottom:8px;">
                <div style="width:{{ $pct }}%;background:#2563eb;height:14px;border-radius:9999px;transition:width 0.3s ease;"></div>
            </div>

            {{-- Row counter --}}
            <p style="font-size:12px;color:#6b7280;text-align:center;margin-bottom:20px;">
                {{ number_format($processedRows) }} / {{ number_format($totalRows) }} baris diproses
            </p>

            {{-- Live counts --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:24px;font-weight:700;color:#15803d;">{{ $createdCount }}</div>
                    <div style="font-size:11px;color:#16a34a;margin-top:2px;">Ditambahkan</div>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:24px;font-weight:700;color:#1d4ed8;">{{ $updatedCount }}</div>
                    <div style="font-size:11px;color:#2563eb;margin-top:2px;">Diperbarui</div>
                </div>
                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
                    <div style="font-size:24px;font-weight:700;color:#6b7280;">{{ $skippedCount }}</div>
                    <div style="font-size:11px;color:#9ca3af;margin-top:2px;">Dilewati</div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    @endif

    {{-- Hasil Import ----------------------------------------------------------}}
    @if($results !== null && !$processing)
    <div class="mt-8 space-y-4">
        <h3 class="text-base font-semibold text-gray-800">Hasil Import</h3>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-green-700">{{ $results['created'] }}</p>
                <p class="text-xs text-green-600 mt-0.5">Siswa Ditambahkan</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-blue-700">{{ $results['updated'] }}</p>
                <p class="text-xs text-blue-600 mt-0.5">Data Diperbarui</p>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-gray-500">{{ $results['skipped'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Baris Dilewati</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-red-600">{{ count($results['errors']) }}</p>
                <p class="text-xs text-red-500 mt-0.5">Error</p>
            </div>
        </div>

        {{-- Default password notice --}}
        @if($results['created'] > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-sm text-amber-800">
            <span class="font-semibold">Password default siswa baru</span> = NISN masing-masing siswa.
            Siswa dapat mengubah password setelah login pertama kali.
        </div>
        @endif

        {{-- Warnings --}}
        @if(count($results['warnings']) > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-yellow-800 mb-2">Peringatan ({{ count($results['warnings']) }})</p>
            <ul class="text-xs text-yellow-700 space-y-1 list-disc list-inside">
                @foreach($results['warnings'] as $warn)
                    <li>{{ $warn }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Errors --}}
        @if(count($results['errors']) > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-red-800 mb-2">Error ({{ count($results['errors']) }})</p>
            <ul class="text-xs text-red-700 space-y-1 list-disc list-inside">
                @foreach($results['errors'] as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Import another --}}
        <div class="flex justify-end pt-2">
            <x-filament::button wire:click="$set('results', null)" color="gray" icon="heroicon-o-arrow-up-tray">
                Import File Lain
            </x-filament::button>
        </div>
    </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
