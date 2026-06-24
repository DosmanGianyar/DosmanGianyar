<?php

namespace App\Filament\Resources\ExtracurricularResource\Pages;

use App\Filament\Resources\ExtracurricularResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExtracurricular extends EditRecord
{
    protected static string $resource = ExtracurricularResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
