<?php

namespace App\Filament\Resources\AssetIssuanceResource\Pages;

use App\Filament\Resources\AssetIssuanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssetIssuances extends ListRecords
{
    protected static string $resource = AssetIssuanceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
