<?php

namespace App\Filament\Resources\ConductLogResource\Pages;

use App\Filament\Resources\ConductLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConductLog extends CreateRecord
{
    protected static string $resource = ConductLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Bersihkan field yang tidak relevan dengan jenis catatan
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
            // lomba
            $data['description'] = null;
            $data['severity']    = null;
            $data['category_id'] = null;
        }

        return $data;
    }
}
