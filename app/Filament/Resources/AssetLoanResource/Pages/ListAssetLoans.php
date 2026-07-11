<?php

namespace App\Filament\Resources\AssetLoanResource\Pages;

use App\Filament\Resources\AssetLoanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssetLoans extends ListRecords
{
    protected static string $resource = AssetLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
