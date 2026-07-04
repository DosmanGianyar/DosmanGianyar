<?php

namespace App\Filament\Resources\ScanEventResource\Pages;

use App\Filament\Resources\ScanEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScanEvents extends ListRecords
{
    protected static string $resource = ScanEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Buat Kegiatan'),
        ];
    }
}
