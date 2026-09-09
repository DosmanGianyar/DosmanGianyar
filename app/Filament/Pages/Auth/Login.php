<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Resources\PasswordResetRequestResource;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Support\Enums\Width;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public function getMaxWidth(): Width | string | null
    {
        return Width::FourExtraLarge;
    }

    public function hasLogo(): bool
    {
        return false;
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email atau Username / NIP')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = trim($data['email']);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';

        return [
            $field    => $login,
            'password' => $data['password'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        if (auth()->user()?->role === 'admin_reset_password') {
            return PasswordResetRequestResource::getUrl();
        }

        return parent::getRedirectUrl();
    }
}
