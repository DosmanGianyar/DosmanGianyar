<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Exports\SiswaDataExport;
use App\Filament\Resources\UserResource;
use App\Imports\SiswaDataImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_excel')
                ->label('Download Data Siswa (Excel)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function (): BinaryFileResponse {
                    return Excel::download(
                        new SiswaDataExport(),
                        'data-siswa-' . date('Ymd-His') . '.xlsx'
                    );
                }),

            Actions\Action::make('upload_excel')
                ->label('Upload / Update Data Siswa')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->modalHeading('Upload File Excel Data Siswa')
                ->modalDescription('Unggah file Excel yang berisi data siswa (menggunakan NISN sebagai acuan update). Jika ada perubahan data pada baris Excel, data di database akan diperbarui secara otomatis. Jika tidak ada perubahan, data akan dilewati.')
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

                    $importer = new SiswaDataImport();
                    Excel::import($importer, $uploadedFile);

                    $body = sprintf(
                        '📊 **Rekap Impor Siswa:**' . "\n" .
                        '• **%d** data diperbarui' . "\n" .
                        '• **%d** data tidak ada perubahan (dilewati)' . "\n" .
                        '• **%d** siswa baru dibuat',
                        $importer->updated,
                        $importer->unchanged,
                        $importer->created
                    );

                    if (! empty($importer->errors)) {
                        $body .= "\n\n⚠️ " . count($importer->errors) . ' baris bermasalah/dilewati: ' . $importer->errors[0];
                    }

                    Notification::make()
                        ->title('Proses Impor / Update Siswa Selesai')
                        ->body($body)
                        ->success()
                        ->persistent()
                        ->send();
                }),

            Actions\CreateAction::make()->label('Tambah Siswa'),
        ];
    }
}
