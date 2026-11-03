<?php

namespace App\Filament\Resources\GuruResource\Pages;

use App\Exports\GuruDataExport;
use App\Exports\TeacherCodeExport;
use App\Filament\Resources\GuruResource;
use App\Imports\GuruDataImport;
use App\Services\TeacherCodeImportService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListGurus extends ListRecords
{
    protected static string $resource = GuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_teacher_codes')
                ->label('Download Kode Guru (Excel)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function (): BinaryFileResponse {
                    return Excel::download(
                        new TeacherCodeExport(),
                        'daftar-kode-guru-' . date('Ymd-His') . '.xlsx'
                    );
                }),

            Actions\Action::make('upload_teacher_codes')
                ->label('Upload Kode Guru')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->modalHeading('Upload & Kelola Kode Guru (Excel)')
                ->modalDescription('Unggah file Excel daftar kode guru. Pilih mode "Update" untuk hanya memperbarui guru yang tercantum, atau "Ganti Semua" untuk menimpa total.')
                ->modalSubmitActionLabel('Proses Kode Guru')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel Kode Guru (.xlsx, .xls, .csv)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            'application/csv',
                        ])
                        ->maxSize(10240)
                        ->required()
                        ->storeFiles(false),

                    Radio::make('mode')
                        ->label('Pilihan Mode Sinkronisasi')
                        ->options([
                            'update'  => 'Update / Tambah Kode (Rekomendasi)',
                            'replace' => 'Ganti Semua / Timpa Total (Sesuaikan Penuh)',
                        ])
                        ->descriptions([
                            'update'  => 'Hanya memperbarui kode guru yang tercantum di file. Kode guru lain yang tidak ada di file tetap dipertahankan.',
                            'replace' => 'Menyelaraskan seluruh kode guru sesuai file. Jika ada kode guru yang dihapus atau dikosongkan di file, di database juga akan dikosongkan.',
                        ])
                        ->default('update')
                        ->required(),
                ])
                ->action(function (array $data, TeacherCodeImportService $service): void {
                    $uploaded = $data['file'];
                    $mode     = $data['mode'] ?? 'update';
                    $filePath = is_string($uploaded) ? $uploaded : $uploaded->getRealPath();

                    $res = $service->import($filePath, $mode);

                    if (!$res['success']) {
                        Notification::make()
                            ->title('Gagal Memproses File Kode Guru')
                            ->body($res['message'] ?? 'Terjadi kesalahan.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $body = "Mode: " . ($mode === 'replace' ? 'Ganti Semua / Timpa Total' : 'Update / Tambah') . ".\n" .
                            "Total baris diproses: {$res['total']}\n" .
                            "• Kode diperbarui: {$res['updated']}\n" .
                            "• Tidak berubah: {$res['unchanged']}";

                    if ($res['cleared'] > 0) {
                        $body .= "\n• Kode dikosongkan/dihapus: {$res['cleared']}";
                    }
                    if ($res['not_found'] > 0) {
                        $body .= "\n• Guru tidak ditemukan: {$res['not_found']}";
                    }

                    Notification::make()
                        ->title('Kode Guru Berhasil Diperbarui!')
                        ->body($body)
                        ->success()
                        ->persistent()
                        ->send();
                }),

            Actions\Action::make('sync_teacher_codes')
                ->label('Sinkron Kode Guru')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sinkronkan Kode Guru')
                ->modalDescription('Akan mencocokkan kode guru resmi (64 guru) dengan database guru secara otomatis.')
                ->action(function () {
                    $result = \App\Services\TeacherCodeSyncService::syncAll();
                    Notification::make()
                        ->title('Sinkronisasi Kode Guru Selesai')
                        ->body("Total diproses: {$result['total']}. Berhasil diupdate: {$result['updated']}, sudah cocok: {$result['skipped']}, tidak cocok: {$result['not_found']}.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('download_excel')
                ->label('Download Data Guru & Pegawai (Excel)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function (): BinaryFileResponse {
                    return Excel::download(
                        new GuruDataExport(),
                        'data-guru-' . date('Ymd-His') . '.xlsx'
                    );
                }),

            Actions\Action::make('upload_excel')
                ->label('Upload / Update Data Guru')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->modalHeading('Upload File Excel Data Guru')
                ->modalDescription('Unggah file Excel yang berisi data guru (menggunakan NIP sebagai acuan update). Jika ada perubahan data pada baris Excel, data di database akan diperbarui secara otomatis. Jika tidak ada perubahan, data akan dilewati.')
                ->modalSubmitActionLabel('Proses Upload & Update')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel (.xlsx / .xls)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->maxSize(10240)
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data): void {
                    $uploadedFile = $data['file'] ?? null;
                    if (! $uploadedFile) {
                        Notification::make()->title('File tidak terunggah dengan benar.')->danger()->send();
                        return;
                    }

                    $importer = new GuruDataImport();
                    Excel::import($importer, $uploadedFile);

                    $body = sprintf(
                        '📊 **Rekap Impor Guru:**' . "\n" .
                        '• **%d** data diperbarui' . "\n" .
                        '• **%d** data tidak ada perubahan (dilewati)' . "\n" .
                        '• **%d** guru baru dibuat',
                        $importer->updated,
                        $importer->unchanged,
                        $importer->created
                    );

                    if (! empty($importer->errors)) {
                        $body .= "\n\n⚠️ " . count($importer->errors) . ' baris bermasalah/dilewati: ' . $importer->errors[0];
                    }

                    Notification::make()
                        ->title('Proses Impor / Update Guru Selesai')
                        ->body($body)
                        ->success()
                        ->persistent()
                        ->send();
                }),

            Actions\CreateAction::make()->label('Tambah Guru / Admin'),
        ];
    }
}
