<?php

namespace App\Filament\Resources\ConductLogResource\Pages;

use App\Filament\Resources\ConductLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConductLogs extends ListRecords
{
    protected static string $resource = ConductLogResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
