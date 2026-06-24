<?php

namespace App\Filament\Resources\SchoolRegulationResource\Pages;

use App\Filament\Resources\SchoolRegulationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchoolRegulation extends EditRecord
{
    protected static string $resource = SchoolRegulationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
