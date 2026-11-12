<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use Filament\Pages\Page;

class KopSuratPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-document-arrow-down';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sarpras';
    protected static ?string                 $navigationLabel = 'Download KOP Surat';
    protected static ?string                 $title           = 'KOP Surat Resmi Sekolah';
    protected static ?string                 $slug            = 'sarpras/kop-surat';
    protected static ?int                    $navigationSort  = 50;

    public static function canAccess(): bool
    {
        return AdminAccess::can('Sarpras') || AdminAccess::isAnyAdmin();
    }

    protected string $view = 'filament.pages.kop-surat';

    public string $orientation = 'portrait'; // portrait | landscape

    public function setOrientation(string $orientation): void
    {
        $this->orientation = in_array($orientation, ['portrait', 'landscape']) ? $orientation : 'portrait';
    }
}
