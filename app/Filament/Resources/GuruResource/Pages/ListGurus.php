<?php

namespace App\Filament\Resources\GuruResource\Pages;

use App\Exports\GuruDataExport;
use App\Filament\Resources\GuruResource;
use App\Imports\GuruDataImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
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
            Actions\Action::make('download_excel')
                ->label('Download Data Guru (Excel)')
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
