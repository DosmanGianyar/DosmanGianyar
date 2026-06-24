<?php

namespace App\Filament\Resources\VotingSessionResource\Pages;

use App\Filament\Resources\VotingSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVotingSession extends CreateRecord
{
    protected static string $resource = VotingSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
