<?php

namespace App\Filament\Resources\LibraryBookResource\Pages;

use App\Filament\Resources\LibraryBookResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLibraryBook extends CreateRecord
{
    protected static string $resource = LibraryBookResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
