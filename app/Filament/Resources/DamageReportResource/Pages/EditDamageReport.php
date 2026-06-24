<?php

namespace App\Filament\Resources\DamageReportResource\Pages;

use App\Filament\Resources\DamageReportResource;
use Filament\Resources\Pages\EditRecord;

class EditDamageReport extends EditRecord
{
    protected static string $resource = DamageReportResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'] ?? '', ['in_progress', 'resolved']) && empty($data['handled_by'])) {
            $data['handled_by'] = auth()->id();
        }
        return $data;
    }
}
