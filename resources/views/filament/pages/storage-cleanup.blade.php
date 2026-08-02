@php
    $attStats    = $this->getAttendanceStats();
    $permitStats = $this->getPermitStats();
@endphp

<x-filament-panels::page>
    <style>
        /* Modern & Ultra-Clean Custom Styling for Storage Cleanup Page */
        .sc-wrap {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        /* Top Warning Alert */
        .sc-notice-box {
            background-color: #0f1d33;
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 0.875rem;
            padding: 1.25rem 1.5rem;
            color: #fef3c7;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .sc-notice-icon {
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            color: #f59e0b;
            margin-top: 0.15rem;
            flex-shrink: 0;
        }
        .sc-notice-icon svg {
            width: 24px !important;
            height: 24px !important;
        }

        /* Grid Stat Cards */
        .sc-grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.25rem;
        }
        .sc-stat-card {
            background-color: #0f1d33;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.875rem;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .sc-stat-label {
            font-size: 0.725rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: block;
        }
        .sc-stat-val {
            font-size: 1.75rem;
            font-weight: 900;
            color: #ffffff;
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }
        .sc-stat-sub {
            font-size: 0.775rem;
            color: #64748b;
        }
        .sc-stat-sub strong {
            color: #cbd5e1;
        }
        .sc-stat-badge-red {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            background-color: rgba(239, 68, 68, 0.15);
            color: #f87171;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sc-stat-badge-red svg {
            width: 26px !important;
            height: 26px !important;
        }
        .sc-stat-badge-purple {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            background-color: rgba(168, 85, 247, 0.15);
            color: #c084fc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sc-stat-badge-purple svg {
            width: 26px !important;
            height: 26px !important;
        }

        /* Action Cards */
        .sc-action-card {
            background-color: #0f1d33;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.875rem;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .sc-action-header {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }
        .sc-action-icon-red {
            width: 40px;
            height: 40px;
            border-radius: 0.625rem;
            background-color: rgba(239, 68, 68, 0.15);
            color: #f87171;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sc-action-icon-red svg {
            width: 22px !important;
            height: 22px !important;
        }
        .sc-action-icon-purple {
            width: 40px;
            height: 40px;
            border-radius: 0.625rem;
            background-color: rgba(168, 85, 247, 0.15);
            color: #c084fc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sc-action-icon-purple svg {
            width: 22px !important;
            height: 22px !important;
        }
        .sc-action-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
        }
        .sc-action-desc {
            font-size: 0.775rem;
            color: #94a3b8;
            margin-top: 0.2rem;
        }

        /* Filter Grid & Inputs */
        .sc-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }
        .sc-field-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .sc-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #cbd5e1;
        }
        .sc-date-input {
            width: 100%;
            background-color: #0b1329;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.5rem;
            color: #ffffff;
            padding: 0.6rem 0.85rem;
            font-size: 0.875rem;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.15s ease-in-out;
        }
        .sc-date-input:focus {
            border-color: #f59e0b;
        }

        /* Action Footer */
        .sc-action-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
        }
        .sc-foot-note {
            font-size: 0.775rem;
            color: #64748b;
        }
        .sc-foot-note strong {
            color: #cbd5e1;
        }

        /* Custom Buttons */
        .sc-btn-red {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            padding: 0.65rem 1.25rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
            transition: all 0.15s ease-in-out;
        }
        .sc-btn-red:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4);
        }
        .sc-btn-red svg {
            width: 16px !important;
            height: 16px !important;
        }

        .sc-btn-purple {
            background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            padding: 0.65rem 1.25rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(147, 51, 234, 0.3);
            transition: all 0.15s ease-in-out;
        }
        .sc-btn-purple:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(147, 51, 234, 0.4);
        }
        .sc-btn-purple svg {
            width: 16px !important;
            height: 16px !important;
        }
    </style>

    <div class="sc-wrap">

        {{-- Petunjuk Keamanan / Info --}}
        <div class="sc-notice-box">
            <div class="sc-notice-icon">
                <x-heroicon-o-information-circle />
            </div>
            <div>
                <strong class="block font-bold text-sm mb-1 text-amber-400">💡 Petunjuk Pembersihan Disk Server:</strong>
                <p class="text-xs leading-relaxed text-gray-300">
                    Fitur ini digunakan untuk menghapus <strong>file foto fisik & file lampiran surat</strong> di folder storage server guna membebaskan ruang disk server.
                </p>
                <ul class="list-disc list-inside mt-1.5 space-y-0.5 text-xs text-gray-400">
                    <li><strong class="text-emerald-400">Keamanan Database 100%:</strong> Histori kehadiran, jam presensi, status Hadir/Izin/Sakit/Alpa, dan rekap siswa <strong>TETAP UTUH & TIDAK HILANG</strong>.</li>
                    <li>Sangat direkomendasikan membersihkan foto presensi bulan-bulan lalu yang sudah tidak dipergunakan lagi.</li>
                </ul>
            </div>
        </div>

        {{-- Kartu Ringkasan Statistik Storage --}}
        <div class="sc-grid-stats">

            {{-- Stat Foto Presensi --}}
            <div class="sc-stat-card">
                <div>
                    <span class="sc-stat-label">Storage Foto Presensi</span>
                    <div class="sc-stat-val">{{ $attStats['size'] }}</div>
                    <p class="sc-stat-sub">
                        Total <strong>{{ number_format($attStats['files']) }}</strong> file foto ({{ number_format($attStats['records']) }} data presensi)
                    </p>
                </div>
                <div class="sc-stat-badge-red">
                    <x-heroicon-o-camera />
                </div>
            </div>

            {{-- Stat Surat Izin --}}
            <div class="sc-stat-card">
                <div>
                    <span class="sc-stat-label">Storage Surat Izin / Sakit / Dispensasi</span>
                    <div class="sc-stat-val">{{ $permitStats['size'] }}</div>
                    <p class="sc-stat-sub">
                        Total <strong>{{ number_format($permitStats['files']) }}</strong> file surat ({{ number_format($permitStats['records']) }} pengajuan)
                    </p>
                </div>
                <div class="sc-stat-badge-purple">
                    <x-heroicon-o-document-text />
                </div>
            </div>

        </div>

        {{-- SECTION 1: Pembersihan Foto Selfie Presensi --}}
        <div class="sc-action-card">
            <div class="sc-action-header">
                <div class="sc-action-icon-red">
                    <x-heroicon-o-photo />
                </div>
                <div>
                    <h2 class="sc-action-title">1. Pembersihan Foto Selfie Presensi (Masuk & Pulang)</h2>
                    <p class="sc-action-desc">Menghapus file foto fisik dari disk server. Data jam & status presensi di DB tetap tersimpan utuh.</p>
                </div>
            </div>

            <div class="sc-filter-grid">
                <div class="sc-field-group">
                    <label class="sc-label">Dari Tanggal (Presensi)</label>
                    <input
                        type="date"
                        wire:model="attendance_start_date"
                        class="sc-date-input"
                    />
                </div>
                <div class="sc-field-group">
                    <label class="sc-label">Sampai Tanggal (Presensi)</label>
                    <input
                        type="date"
                        wire:model="attendance_end_date"
                        class="sc-date-input"
                    />
                </div>
            </div>

            <div class="sc-action-footer">
                <p class="sc-foot-note">
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
                    class="sc-btn-red"
                >
                    <x-heroicon-o-trash />
                    Hapus Foto Presensi
                </button>
            </div>
        </div>

        {{-- SECTION 2: Pembersihan File Surat Lampiran --}}
        <div class="sc-action-card">
            <div class="sc-action-header">
                <div class="sc-action-icon-purple">
                    <x-heroicon-o-document-duplicate />
                </div>
                <div>
                    <h2 class="sc-action-title">2. Pembersihan File Surat Lampiran (Izin, Sakit, Dispensasi)</h2>
                    <p class="sc-action-desc">Menghapus file lampiran surat dari disk server. Record histori & alasan izin di DB tetap tersimpan utuh.</p>
                </div>
            </div>

            <div class="sc-filter-grid">
                <div class="sc-field-group">
                    <label class="sc-label">Dari Tanggal (Pengajuan Izin)</label>
                    <input
                        type="date"
                        wire:model="permit_start_date"
                        class="sc-date-input"
                    />
                </div>
                <div class="sc-field-group">
                    <label class="sc-label">Sampai Tanggal (Pengajuan Izin)</label>
                    <input
                        type="date"
                        wire:model="permit_end_date"
                        class="sc-date-input"
                    />
                </div>
            </div>

            <div class="sc-action-footer">
                <p class="sc-foot-note">
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
                    <x-heroicon-o-trash />
                    Hapus File Surat Lampiran
                </button>
            </div>
        </div>

    </div>
</x-filament-panels::page>
