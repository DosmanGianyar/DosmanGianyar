<?php

namespace App\Filament\Resources\SchoolEventResource\Pages;

use App\Filament\Resources\SchoolEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchoolEvents extends ListRecords
{
    protected static string $resource = SchoolEventResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
