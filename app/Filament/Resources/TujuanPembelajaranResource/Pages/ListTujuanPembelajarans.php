<?php

namespace App\Filament\Resources\TujuanPembelajaranResource\Pages;

use App\Filament\Resources\TujuanPembelajaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTujuanPembelajarans extends ListRecords
{
    protected static string $resource = TujuanPembelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah TP'),
        ];
    }
}
