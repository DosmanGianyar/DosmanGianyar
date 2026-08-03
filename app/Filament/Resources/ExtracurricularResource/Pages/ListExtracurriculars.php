<?php

namespace App\Filament\Resources\ExtracurricularResource\Pages;

use App\Filament\Resources\ExtracurricularResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExtracurriculars extends ListRecords
{
    protected static string $resource = ExtracurricularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_csv')
                ->label('Import CSV (ekstra.csv)')
                ->icon('heroicon-o-document-arrow-up')
                ->color('info')
                ->url(fn () => route('admin.extracurriculars.import')),
            CreateAction::make(),
        ];
    }
}
