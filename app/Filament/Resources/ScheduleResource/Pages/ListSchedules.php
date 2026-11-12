<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Pages\ImportSchedulePage;
use App\Filament\Pages\TeacherSchedulePage;
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
            Action::make('viewTeacherGrid')
                ->label('Jadwal Per Guru (Grid)')
                ->icon('heroicon-o-table-cells')
                ->color('warning')
                ->url(fn (): string => TeacherSchedulePage::getUrl()),

            Action::make('downloadExcel')
                ->label('Download Jadwal (Excel)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    return (new \App\Services\ScheduleMatrixExport())->download();
                }),

            Action::make('importExcel')
                ->label('Import Excel Jadwal')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->url(fn (): string => ImportSchedulePage::getUrl()),
            CreateAction::make(),
        ];
    }
}
