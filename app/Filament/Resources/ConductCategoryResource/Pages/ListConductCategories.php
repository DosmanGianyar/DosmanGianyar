<?php

namespace App\Filament\Resources\ConductCategoryResource\Pages;

use App\Filament\Resources\ConductCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConductCategories extends ListRecords
{
    protected static string $resource = ConductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
