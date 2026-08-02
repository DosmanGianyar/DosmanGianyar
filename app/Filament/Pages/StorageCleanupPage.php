<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\Attendance;
use App\Models\Permit;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class StorageCleanupPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-server-stack';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sistem';
    protected static ?string                 $navigationLabel = 'Pembersihan Storage';
    protected static ?string                 $title           = 'Pembersihan Storage Media (Foto & Surat)';
    protected static ?int                    $navigationSort  = 30;

    public static function canAccess(): bool
    {
        return AdminAccess::can('Sistem') || auth()->user()?->role === 'admin';
    }

    protected string $view = 'filament.pages.storage-cleanup';

    public ?string $attendance_start_date = null;
    public ?string $attendance_end_date   = null;

    public ?string $permit_start_date     = null;
    public ?string $permit_end_date       = null;

    public function mount(): void
    {
        // Default filter: foto/surat sampai akhir bulan kemarin (opsional)
    }

    /**
     * Ringkasan penggunaan storage foto presensi.
     */
    public function getAttendanceStats(): array
    {
        $query = Attendance::query()
            ->where(function ($q) {
                $q->whereNotNull('photo')->orWhereNotNull('check_out_photo');
            });

        $totalRecords = $query->count();
        $totalBytes   = 0;
        $fileCount    = 0;

        $disk = Storage::disk('public');

        $query->select(['id', 'photo', 'check_out_photo'])->chunk(500, function ($attendances) use ($disk, &$totalBytes, &$fileCount) {
            foreach ($attendances as $att) {
                if ($att->photo && $disk->exists($att->photo)) {
                    $totalBytes += $disk->size($att->photo);
                    $fileCount++;
                }
                if ($att->check_out_photo && $disk->exists($att->check_out_photo)) {
                    $totalBytes += $disk->size($att->check_out_photo);
                    $fileCount++;
                }
            }
        });

        return [
            'records'   => $totalRecords,
            'files'     => $fileCount,
            'size'      => $this->formatBytes($totalBytes),
            'raw_bytes' => $totalBytes,
        ];
    }

    /**
     * Ringkasan penggunaan storage surat izin/sakit/dispensasi.
     */
    public function getPermitStats(): array
    {
        $query = Permit::query()->whereNotNull('file');

        $totalRecords = $query->count();
        $totalBytes   = 0;
        $fileCount    = 0;

        $disk = Storage::disk('public');

        $query->select(['id', 'file'])->chunk(500, function ($permits) use ($disk, &$totalBytes, &$fileCount) {
            foreach ($permits as $permit) {
                if ($permit->file && $disk->exists($permit->file)) {
                    $totalBytes += $disk->size($permit->file);
                    $fileCount++;
                }
            }
        });

        return [
            'records'   => $totalRecords,
            'files'     => $fileCount,
            'size'      => $this->formatBytes($totalBytes),
            'raw_bytes' => $totalBytes,
        ];
    }

    /**
     * Proses Pembersihan Foto Selfie Presensi (Masuk & Pulang)
     */
    public function deleteAttendancePhotos(): void
    {
        $query = Attendance::query()
            ->where(function ($q) {
                $q->whereNotNull('photo')->orWhereNotNull('check_out_photo');
            });

        if ($this->attendance_start_date) {
            $query->whereDate('date', '>=', $this->attendance_start_date);
        }
        if ($this->attendance_end_date) {
            $query->whereDate('date', '<=', $this->attendance_end_date);
        }

        $disk = Storage::disk('public');
        $deletedFiles = 0;
        $freedBytes   = 0;
        $recordCount  = 0;

        $query->chunk(200, function ($attendances) use ($disk, &$deletedFiles, &$freedBytes, &$recordCount) {
            foreach ($attendances as $att) {
                $updated = false;

                if ($att->photo) {
                    if ($disk->exists($att->photo)) {
                        $freedBytes += $disk->size($att->photo);
                        $disk->delete($att->photo);
                        $deletedFiles++;
                    }
                    $att->photo = null;
                    $updated = true;
                }

                if ($att->check_out_photo) {
                    if ($disk->exists($att->check_out_photo)) {
                        $freedBytes += $disk->size($att->check_out_photo);
                        $disk->delete($att->check_out_photo);
                        $deletedFiles++;
                    }
                    $att->check_out_photo = null;
                    $updated = true;
                }

                if ($updated) {
                    $att->save();
                    $recordCount++;
                }
            }
        });

        $formattedFreed = $this->formatBytes($freedBytes);

        Notification::make()
            ->title('Pembersihan Foto Presensi Selesai')
            ->body("Berhasil menghapus {$deletedFiles} file foto dari {$recordCount} data presensi. Kapasitas yang dibebaskan: {$formattedFreed}. Database presensi tetap utuh.")
            ->success()
            ->send();
    }

    /**
     * Proses Pembersihan File Surat Lampiran (Izin, Sakit, Dispensasi)
     */
    public function deletePermitFiles(): void
    {
        $query = Permit::query()->whereNotNull('file');

        if ($this->permit_start_date) {
            $query->whereDate('start_date', '>=', $this->permit_start_date);
        }
        if ($this->permit_end_date) {
            $query->whereDate('start_date', '<=', $this->permit_end_date);
        }

        $disk = Storage::disk('public');
        $deletedFiles = 0;
        $freedBytes   = 0;
        $recordCount  = 0;

        $query->chunk(200, function ($permits) use ($disk, &$deletedFiles, &$freedBytes, &$recordCount) {
            foreach ($permits as $permit) {
                if ($permit->file) {
                    if ($disk->exists($permit->file)) {
                        $freedBytes += $disk->size($permit->file);
                        $disk->delete($permit->file);
                        $deletedFiles++;
                    }
                    $permit->file = null;
                    $permit->save();
                    $recordCount++;
                }
            }
        });

        $formattedFreed = $this->formatBytes($freedBytes);

        Notification::make()
            ->title('Pembersihan File Surat Selesai')
            ->body("Berhasil menghapus {$deletedFiles} file surat lampiran dari {$recordCount} data izin/sakit/dispensasi. Kapasitas yang dibebaskan: {$formattedFreed}. Database rekap izin tetap utuh.")
            ->success()
            ->send();
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
