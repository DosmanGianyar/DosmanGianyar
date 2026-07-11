<?php

namespace App\Filament\Resources\AssetLoanResource\Pages;

use App\Filament\Resources\AssetLoanResource;
use Filament\Resources\Pages\EditRecord;

class EditAssetLoan extends EditRecord
{
    protected static string $resource = AssetLoanResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'] ?? null, ['approved', 'active'], true)
            && $this->record->status !== $data['status']
        ) {
            $data['approved_by'] = auth()->id();
        }

        return $data;
    }
}
