<?php

namespace App\Filament\Resources\PasswordResetRequestResource\Pages;

use App\Filament\Resources\PasswordResetRequestResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;

class ListPasswordResetRequests extends ListRecords
{
    protected static string $resource = PasswordResetRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_demo')
                ->label('⚡ Reset Akun Demo PlayStore')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset Password Akun Demo')
                ->modalDescription('Reset password playstore.demo@sims.sch.id (NISN: 0000000001) kembali ke "PlayReview123" & bersihkan device lock?')
                ->action(function (): void {
                    $demoUser = User::where('email', 'playstore.demo@sims.sch.id')
                        ->orWhere('nisn', '0000000001')
                        ->first();

                    if ($demoUser) {
                        $demoUser->update([
                            'password'             => Hash::make('PlayReview123'),
                            'must_change_password' => false,
                        ]);
                        $demoUser->resetDevices();
                    } else {
                        (new \Database\Seeders\PlayStoreDemoSeeder())->run();
                    }

                    Notification::make()
                        ->title('Akun Demo PlayStore Berhasil Direset!')
                        ->body('Password diatur ke: PlayReview123 & Device Lock telah dibersihkan.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
