<?php

namespace App\Filament\Resources\LibraryLoanResource\Pages;

use App\Filament\Resources\LibraryLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLibraryLoans extends ListRecords
{
    protected static string $resource = LibraryLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Catat Peminjaman Buku Baru')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
