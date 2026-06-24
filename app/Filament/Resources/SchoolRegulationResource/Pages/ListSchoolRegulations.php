<?php

namespace App\Filament\Resources\SchoolRegulationResource\Pages;

use App\Filament\Resources\SchoolRegulationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchoolRegulations extends ListRecords
{
    protected static string $resource = SchoolRegulationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
