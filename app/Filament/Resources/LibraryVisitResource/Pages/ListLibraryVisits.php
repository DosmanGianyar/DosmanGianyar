<?php

namespace App\Filament\Resources\LibraryVisitResource\Pages;

use App\Filament\Resources\LibraryVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLibraryVisits extends ListRecords
{
    protected static string $resource = LibraryVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Kunjungan Manual'),
        ];
    }
}
