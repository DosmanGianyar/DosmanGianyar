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
                ->modalHeading('Reset Perangkat?')
                ->modalDescription(fn (): string =>
                    'Perangkat terdaftar pengguna ini akan dihapus. '
                    . 'Pengguna bisa login dari HP baru setelah ini.'
                )
                ->modalSubmitActionLabel('Ya, Reset')
                ->action(function (): void {
                    /** @var User $record */
                    $record = $this->getRecord();
                    $record->resetDevice();
                    Notification::make()
                        ->title("Perangkat {$record->name} berhasil direset.")
                        ->success()
                        ->send();
                })
                ->visible(fn (): bool => $this->getRecord()->hasDeviceLocked()),
        ];
    }
}
