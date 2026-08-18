<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Resources\LibraryLoanResource;
use Filament\Auth\Pages\Login as BaseLogin;
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

    protected function getRedirectUrl(): string
    {
        if (auth()->user()?->role === 'admin_perpustakaan') {
            return LibraryLoanResource::getUrl();
        }

        return parent::getRedirectUrl();
    }
}
