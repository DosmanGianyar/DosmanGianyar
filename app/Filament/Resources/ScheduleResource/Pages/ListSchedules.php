<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Pages\ImportSchedulePage;
use App\Filament\Resources\ScheduleResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importExcel')
                ->label('Import Excel Jadwal')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->url(fn (): string => ImportSchedulePage::getUrl()),
            CreateAction::make(),
        ];
    }
}
