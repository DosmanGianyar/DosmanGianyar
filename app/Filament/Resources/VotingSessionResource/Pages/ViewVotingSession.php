<?php

namespace App\Filament\Resources\VotingSessionResource\Pages;

use App\Filament\Resources\VotingSessionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVotingSession extends ViewRecord
{
    protected static string $resource = VotingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
