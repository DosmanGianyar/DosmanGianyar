<?php

namespace App\Filament\Resources\AcademicEventResource\Pages;

use App\Filament\Resources\AcademicEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademicEvents extends ListRecords
{
    protected static string $resource = AcademicEventResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
