<?php

namespace App\Filament\Resources\ExtracurricularResource\Pages;

use App\Filament\Resources\ExtracurricularResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExtracurricular extends EditRecord
{
    protected static string $resource = ExtracurricularResource::class;

    protected function afterSave(): void
    {
        $firstTeacherId = $this->record->teachers()->first()?->id;
        if ($this->record->pembina_id !== $firstTeacherId) {
            $this->record->update(['pembina_id' => $firstTeacherId]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
