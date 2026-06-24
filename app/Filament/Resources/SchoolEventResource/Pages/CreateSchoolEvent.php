<?php

namespace App\Filament\Resources\SchoolEventResource\Pages;

use App\Filament\Resources\SchoolEventResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSchoolEvent extends CreateRecord
{
    protected static string $resource = SchoolEventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }
}
