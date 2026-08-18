<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LibraryLoanResource;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        if (auth()->user()?->role === 'admin_perpustakaan') {
            redirect()->to(LibraryLoanResource::getUrl());
            return;
        }

        parent::mount();
    }
}
