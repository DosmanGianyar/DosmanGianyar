<?php

namespace App\Filament\Resources\GuruResource\Pages;

use App\Filament\Resources\GuruResource;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditGuru extends EditRecord
{
    protected static string $resource = GuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resetPassword')
                ->label('Reset Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->modalHeading(fn (): string => "Reset Password: {$this->record->name}")
                ->modalDescription(fn (): string => "Password akun '{$this->record->name}' akan diubah. Nilai default terisi NIP pengguna. Anda juga dapat menentukan password baru secara manual tanpa spasi.")
                ->modalSubmitActionLabel('Ya, Simpan Password')
                ->form([
                    TextInput::make('new_password')
                        ->label('Password Baru')
                        ->default(fn (): string => $this->record->nip ?: ($this->record->email ?: 'Guru123'))
                        ->required()
                        ->minLength(6)
                        ->regex('/^\S+$/')
                        ->validationMessages([
                            'regex' => 'Password tidak boleh mengandung spasi.',
                        ])
                        ->helperText('Default terisi NIP. Pengguna wajib mengganti password saat login pertama kali.'),
                ])
                ->action(function (array $data): void {
                    $newPass = trim($data['new_password']);
                    $this->record->update([
                        'password'             => Hash::make($newPass),
                        'must_change_password' => true,
                    ]);
                    $this->record->resetDevices();

                    Notification::make()
                        ->title("Password {$this->record->name} berhasil di-reset.")
                        ->body("Password baru: {$newPass} (Wajib ganti password saat login).")
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
