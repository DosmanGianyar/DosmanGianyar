<?php

namespace App\Filament\Resources\ExtracurricularResource\Pages;

use App\Filament\Resources\ExtracurricularResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExtracurriculars extends ListRecords
{
    protected static string $resource = ExtracurricularResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
