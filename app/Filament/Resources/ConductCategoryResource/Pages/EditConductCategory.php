<?php

namespace App\Filament\Resources\ConductCategoryResource\Pages;

use App\Filament\Resources\ConductCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConductCategory extends EditRecord
{
    protected static string $resource = ConductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
