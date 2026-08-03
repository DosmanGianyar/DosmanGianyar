<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['must_change_password'] = true;

        if (empty($data['password'])) {
            $role = $data['role'] ?? 'siswa';
            $defaultPassword = match ($role) {
                'siswa'    => !empty($data['nisn']) ? $data['nisn'] : (!empty($data['nis']) ? $data['nis'] : ($data['username'] ?? 'siswa123')),
                'guru'     => !empty($data['nip']) ? $data['nip'] : ($data['username'] ?? 'guru123'),
                'orangtua' => !empty($data['phone']) ? $data['phone'] : ($data['username'] ?? 'ortu123'),
                default    => $data['username'] ?? $data['email'] ?? 'password123',
            };
            $data['password'] = Hash::make($defaultPassword);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
