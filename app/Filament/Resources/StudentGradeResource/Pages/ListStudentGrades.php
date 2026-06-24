<?php

namespace App\Filament\Resources\StudentGradeResource\Pages;

use App\Filament\Resources\StudentGradeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentGrades extends ListRecords
{
    protected static string $resource = StudentGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
