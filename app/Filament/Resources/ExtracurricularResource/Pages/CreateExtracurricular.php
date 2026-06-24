<?php

namespace App\Filament\Resources\ExtracurricularResource\Pages;

use App\Filament\Resources\ExtracurricularResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExtracurricular extends CreateRecord
{
    protected static string $resource = ExtracurricularResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
