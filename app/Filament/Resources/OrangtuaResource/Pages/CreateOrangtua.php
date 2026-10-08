<?php

namespace App\Filament\Resources\OrangtuaResource\Pages;

use App\Filament\Resources\OrangtuaResource;
use App\Models\User;
use App\Services\OrangtuaSyncService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateOrangtua extends CreateRecord
{
    protected static string $resource = OrangtuaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'orangtua';
        $data['must_change_password'] = false;

        $phone = OrangtuaSyncService::normalizePhone($data['phone'] ?? null) ?? trim($data['phone'] ?? '');
        $data['phone'] = $phone;

        if (empty($data['email'])) {
            $data['email'] = $phone . '@ortu.sims.sch.id';
        }

        if (empty($data['password'])) {
            $data['password'] = Hash::make($phone);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var User $record */
        $record = $this->record;
        $studentIds = $this->data['children'] ?? [];

        if (! empty($studentIds)) {
            $record->children()->sync($studentIds);

            // Sinkronkan nama dan nomor telepon ke profil siswa yang terhubung
            User::whereIn('id', $studentIds)->update([
                'parent_name'  => $record->name,
                'parent_phone' => $record->phone,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
