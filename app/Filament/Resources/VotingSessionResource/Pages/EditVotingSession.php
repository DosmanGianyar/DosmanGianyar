<?php

namespace App\Filament\Resources\VotingSessionResource\Pages;

use App\Filament\Resources\VotingSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVotingSession extends EditRecord
{
    protected static string $resource = VotingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
