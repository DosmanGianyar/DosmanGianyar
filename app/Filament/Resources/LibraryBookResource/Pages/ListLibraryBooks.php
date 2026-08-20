<?php

namespace App\Filament\Resources\LibraryBookResource\Pages;

use App\Filament\Resources\LibraryBookResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLibraryBooks extends ListRecords
{
    protected static string $resource = LibraryBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Buku Baru')
                ->icon('heroicon-o-plus'),
        ];
    }
}
