<?php

namespace App\Filament\Resources\ScanEventResource\Pages;

use App\Filament\Resources\ScanEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScanEvent extends EditRecord
{
    protected static string $resource = ScanEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
