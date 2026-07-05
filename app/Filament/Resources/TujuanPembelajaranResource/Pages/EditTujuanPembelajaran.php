<?php

namespace App\Filament\Resources\TujuanPembelajaranResource\Pages;

use App\Filament\Resources\TujuanPembelajaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTujuanPembelajaran extends EditRecord
{
    protected static string $resource = TujuanPembelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
