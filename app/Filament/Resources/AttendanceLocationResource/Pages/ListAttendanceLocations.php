<?php

namespace App\Filament\Resources\AttendanceLocationResource\Pages;

use App\Filament\Resources\AttendanceLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceLocations extends ListRecords
{
    protected static string $resource = AttendanceLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
