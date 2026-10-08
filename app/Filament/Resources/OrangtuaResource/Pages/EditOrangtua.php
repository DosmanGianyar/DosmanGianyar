<?php

namespace App\Filament\Resources\OrangtuaResource\Pages;

use App\Filament\Resources\OrangtuaResource;
use App\Models\User;
use App\Services\OrangtuaSyncService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditOrangtua extends EditRecord
{
    protected static string $resource = OrangtuaResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['phone'] ?? null)) {
            $data['phone'] = OrangtuaSyncService::normalizePhone($data['phone']) ?? trim($data['phone']);
        }

        if (empty($data['email']) && filled($data['phone'] ?? null)) {
            $data['email'] = $data['phone'] . '@ortu.sims.sch.id';
        }

        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
            $data['must_change_password'] = false;
        } else {
            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var User $record */
        $record = $this->record;
        $studentIds = $this->data['children'] ?? [];

        $record->children()->sync($studentIds);

        if (! empty($studentIds)) {
            // Sinkronkan nama dan nomor telepon ke profil siswa yang terhubung
            User::whereIn('id', $studentIds)->update([
                'parent_name'  => $record->name,
                'parent_phone' => $record->phone,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
