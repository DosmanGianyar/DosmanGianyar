<?php

namespace App\Filament\Resources\ScanEventResource\Pages;

use App\Filament\Resources\ScanEventResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateScanEvent extends CreateRecord
{
    protected static string $resource = ScanEventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
