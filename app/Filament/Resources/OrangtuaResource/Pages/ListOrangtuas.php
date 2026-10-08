<?php

namespace App\Filament\Resources\OrangtuaResource\Pages;

use App\Filament\Resources\OrangtuaResource;
use App\Services\OrangtuaSyncService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListOrangtuas extends ListRecords
{
    protected static string $resource = OrangtuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncAll')
                ->label('Sinkronkan Semua dari Data Siswa')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sinkronkan Akun Orang Tua dari Data Siswa?')
                ->modalDescription('Sistem akan memeriksa seluruh siswa yang memiliki No. HP Orang Tua. Akun orang tua yang belum ada akan otomatis dibuatkan akun dengan Username & Password sesuai No. HP tersebut, dan langsung dihubungkan ke anak masing-masing.')
                ->modalSubmitActionLabel('Ya, Sinkronkan Sekarang')
                ->action(function (): void {
                    $synced = OrangtuaSyncService::syncAll();

                    Notification::make()
                        ->title('Sinkronisasi Akun Orang Tua Berhasil')
                        ->body("Berhasil memproses & menyinkronkan {$synced} akun orang tua dari data siswa.")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Tambah Akun Orang Tua'),
        ];
    }
}
