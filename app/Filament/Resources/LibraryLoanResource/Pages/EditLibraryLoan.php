<?php

namespace App\Filament\Resources\LibraryLoanResource\Pages;

use App\Filament\Resources\LibraryLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLibraryLoan extends EditRecord
{
    protected static string $resource = LibraryLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
