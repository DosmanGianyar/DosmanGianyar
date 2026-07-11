<?php

namespace App\Filament\Resources\AssetIssuanceResource\Pages;

use App\Filament\Resources\AssetIssuanceResource;
use App\Models\Asset;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateAssetIssuance extends CreateRecord
{
    protected static string $resource = AssetIssuanceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $asset = Asset::whereKey($data['asset_id'])->lockForUpdate()->firstOrFail();

            if ($data['quantity'] > $asset->quantity) {
                Notification::make()
                    ->title('Stok tidak mencukupi.')
                    ->body("Sisa stok {$asset->name}: {$asset->quantity}.")
                    ->danger()
                    ->send();

                $this->halt();
            }

            $record = static::getModel()::create($data);
            $asset->decrement('quantity', $data['quantity']);

            return $record;
        });
    }
}
