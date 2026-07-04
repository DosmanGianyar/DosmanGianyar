<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('resetDevice')
                ->label('Reset Perangkat')
                ->icon('heroicon-o-device-phone-mobile')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset Semua Perangkat?')
                ->modalDescription(fn (): string => sprintf(
                    'Pengguna ini terdaftar di %d perangkat. Semua perangkat akan dihapus '
                    . 'dan token login akan dicabut. Pengguna dapat login kembali dari perangkat baru.',
                    $this->getRecord()->deviceCount(),
                ))
                ->modalSubmitActionLabel('Ya, Reset Semua')
                ->action(function (): void {
                    /** @var User $record */
                    $record = $this->getRecord();
                    $count  = $record->deviceCount();
                    $record->resetDevices();
                    Notification::make()
                        ->title("{$record->name}: {$count} perangkat berhasil direset.")
                        ->success()
                        ->send();
                })
                ->visible(fn (): bool => $this->getRecord()->hasDeviceLocked()),
        ];
    }
}
