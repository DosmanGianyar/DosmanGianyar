<?php

namespace App\Filament\Resources\GalleryResource\RelationManagers;

use App\Models\GalleryReport;
use App\Services\GalleryMediaService;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';
    protected static ?string $title = 'File Laporan Kegiatan';
    protected static string|\BackedEnum|null $icon = 'heroicon-o-document-text';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Laporan')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('original_name')
                    ->label('Nama File')
                    ->searchable(),

                TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 0) . ' KB' : '—'),

                TextColumn::make('uploader.name')
                    ->label('Diunggah Oleh')
                    ->default('Admin'),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->headerActions([
                TableAction::make('upload_report')
                    ->label('Upload File Laporan')
                    ->icon('heroicon-o-document-plus')
                    ->color('warning')
                    ->form([
                        TextInput::make('title')
                            ->label('Judul Laporan')
                            ->required()
                            ->maxLength(180)
                            ->placeholder('Contoh: Laporan Pertanggungjawaban Kegiatan'),

                        FileUpload::make('file')
                            ->label('File Laporan (PDF / Word / Excel / PowerPoint)')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            ])
                            ->maxSize(25600)
                            ->storeFiles(false)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $gallery = $this->getOwnerRecord();
                        $service = app(GalleryMediaService::class);
                        $user    = Auth::user();
                        $file    = $data['file'] ?? null;

                        if ($file instanceof TemporaryUploadedFile || $file instanceof UploadedFile) {
                            $service->storeReport($gallery, $file, $user, $data['title'] ?? null);
                            Notification::make()
                                ->title('File laporan berhasil disimpan ke folder Laporan di Synology.')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                TableAction::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (GalleryReport $record): string => $record->downloadUrl())
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
