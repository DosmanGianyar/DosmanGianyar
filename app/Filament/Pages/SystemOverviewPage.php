<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SystemOverviewPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-presentation-chart-bar';
    protected static string|\UnitEnum|null   $navigationGroup = 'Panduan & Presentasi';
    protected static ?string                 $navigationLabel = 'Panduan Presentasi Fitur';
    protected static ?string                 $title           = 'Panduan Presentasi & Ringkasan Fitur SIMS SMAN 1 Gianyar';
    protected static ?int                    $navigationSort  = 1;

    /**
     * Kunci hak akses navigasi sidebar & akses halaman HANYA untuk Super Admin (role === 'admin').
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    protected string $view = 'filament.pages.system-overview';
}
