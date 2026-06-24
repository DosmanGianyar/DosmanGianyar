<?php

namespace App\Filament\Resources\StudentGradeResource\Pages;

use App\Filament\Resources\StudentGradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentGrade extends CreateRecord
{
    protected static string $resource = StudentGradeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['recorded_by'] = \Illuminate\Support\Facades\Auth::id();
        return $data;
    }
}
