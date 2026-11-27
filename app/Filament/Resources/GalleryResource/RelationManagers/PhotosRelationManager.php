<?php

namespace App\Filament\Resources\GalleryResource\RelationManagers;

use App\Models\GalleryPhoto;
use App\Services\GalleryMediaService;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';
    protected static ?string $title = 'Foto Dokumentasi';
    protected static string|\BackedEnum|null $icon = 'heroicon-o-photo';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('caption')
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Foto')
                    ->getStateUsing(fn (GalleryPhoto $record): string => $record->thumbnailUrl())
                    ->width(72)
                    ->height(54)
                    ->rounded(),

                TextColumn::make('caption')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('uploader.name')
                    ->label('Pengunggah')
                    ->default('Admin')
                    ->description(fn (GalleryPhoto $record): string => $record->uploader?->role ? strtoupper($record->uploader->role) : 'ADMIN')
                    ->searchable(),

                TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 0) . ' KB' : '—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Waktu Upload')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->headerActions([
                TableAction::make('upload_photos')
                    ->label('Upload Foto (Auto-Kompres ke Synology)')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->form([
                        FileUpload::make('files')
                            ->label('Pilih Foto (Bisa Banyak Sekaligus)')
                            ->image()
                            ->multiple()
                            ->maxFiles(20)
                            ->maxSize(15360)
                            ->storeFiles(false)
                            ->required(),

                        TextInput::make('caption')
                            ->label('Keterangan (Opsional, berlaku untuk foto yang diupload)')
                            ->maxLength(200)
                            ->nullable(),
                    ])
                    ->action(function (array $data): void {
                        $gallery = $this->getOwnerRecord();
                        $service = app(GalleryMediaService::class);
                        $user    = Auth::user();
                        $caption = $data['caption'] ?? null;
                        $files   = $data['files'] ?? [];

                        $saved = 0;
                        foreach ($files as $file) {
                            if ($file instanceof TemporaryUploadedFile || $file instanceof UploadedFile) {
                                $service->storePhoto($gallery, $file, $user, $caption);
                                $saved++;
                            } elseif (is_string($file) && Storage::disk('local')->exists($file)) {
                                $fullPath = Storage::disk('local')->path($file);
                                $uploaded = new UploadedFile($fullPath, basename($fullPath), null, null, true);
                                $service->storePhoto($gallery, $uploaded, $user, $caption);
                                Storage::disk('local')->delete($file);
                                $saved++;
                            }
                        }

                        Notification::make()
                            ->title("{$saved} foto berhasil dikonversi & disimpan ke Synology.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                TableAction::make('open_full')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn (GalleryPhoto $record): string => $record->url())
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
