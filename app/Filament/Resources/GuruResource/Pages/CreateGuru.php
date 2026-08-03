<?php

namespace App\Filament\Resources\GuruResource\Pages;

use App\Filament\Resources\GuruResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateGuru extends CreateRecord
{
    protected static string $resource = GuruResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role']                 = 'guru';
        $data['must_change_password'] = true;

        if (empty($data['password'])) {
            $defaultPassword  = !empty($data['nip']) ? $data['nip'] : (!empty($data['username']) ? $data['username'] : $data['email']);
            $data['password'] = Hash::make($defaultPassword);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
