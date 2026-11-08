<?php

namespace App\Filament\Resources\HolidayResource\Pages;

use App\Filament\Resources\HolidayResource;
use App\Services\NotificationService;
use Filament\Resources\Pages\CreateRecord;

class CreateHoliday extends CreateRecord
{
    protected static string $resource = HolidayResource::class;

    protected function afterCreate(): void
    {
        if ($this->record) {
            NotificationService::broadcastHoliday($this->record);
        }
    }
}
