<?php

namespace App\Filament\Resources\AcademicEventResource\Pages;

use App\Filament\Resources\AcademicEventResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAcademicEvent extends CreateRecord
{
    protected static string $resource = AcademicEventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }
}
