<?php

namespace App\Filament\Resources\MaintenanceLogResource\Pages;

use App\Filament\Resources\MaintenanceLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenanceLog extends CreateRecord
{
    protected static string $resource = MaintenanceLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['recorded_by'] = auth()->id();
        return $data;
    }
}
