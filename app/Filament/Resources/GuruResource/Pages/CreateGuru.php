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
        $data['role']                 = $data['role'] ?? 'guru';
        $data['must_change_password'] = true;

        if (!empty($data['nip'])) {
            $data['nip'] = preg_replace('/\s+/', '', $data['nip']);
        }

        if (empty($data['password'])) {
            $defaultPassword  = !empty($data['nip']) ? $data['nip'] : (!empty($data['username']) ? preg_replace('/\s+/', '', $data['username']) : str_replace(' ', '', $data['email']));
            $data['password'] = Hash::make($defaultPassword);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
