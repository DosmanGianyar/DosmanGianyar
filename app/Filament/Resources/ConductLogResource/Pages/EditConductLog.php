<?php

namespace App\Filament\Resources\ConductLogResource\Pages;

use App\Filament\Resources\ConductLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConductLog extends EditRecord
{
    protected static string $resource = ConductLogResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['type'] === 'pelanggaran') {
            $data['prestasi_type'] = null;
            $data['category_id']   = null;
            $data['lomba_name']    = null;
            $data['lomba_level']   = null;
            $data['lomba_rank']    = null;
        } elseif (($data['prestasi_type'] ?? null) === 'perilaku') {
            $data['description']  = null;
            $data['severity']     = null;
            $data['lomba_name']   = null;
            $data['lomba_level']  = null;
            $data['lomba_rank']   = null;
        } else {
            $data['description'] = null;
            $data['severity']    = null;
            $data['category_id'] = null;
        }

        return $data;
    }
}
