<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent()
                    ->label('Nama Lengkap'),

                $this->getEmailFormComponent()
                    ->label('Alamat Email')
                    ->required(),

                TextInput::make('phone')
                    ->label('Nomor WhatsApp / HP')
                    ->tel()
                    ->placeholder('08xxxxxxxxxx')
                    ->nullable(),

                $this->getPasswordFormComponent()
                    ->label('Password Baru')
                    ->helperText('Kosongkan jika tidak ingin mengganti password.')
                    ->validationMessages([
                        'min' => 'Password minimal harus 6 karakter.',
                    ]),

                $this->getPasswordConfirmationFormComponent()
                    ->label('Konfirmasi Password Baru'),

                $this->getCurrentPasswordFormComponent()
                    ->label('Password Saat Ini (Konfirmasi Keamanan)')
                    ->requiredWith('password'),
            ]);
    }
}
