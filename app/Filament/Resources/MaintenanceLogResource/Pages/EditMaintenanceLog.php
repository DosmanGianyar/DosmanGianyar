<?php

namespace App\Filament\Resources\MaintenanceLogResource\Pages;

use App\Filament\Resources\MaintenanceLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceLog extends EditRecord
{
    protected static string $resource = MaintenanceLogResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
