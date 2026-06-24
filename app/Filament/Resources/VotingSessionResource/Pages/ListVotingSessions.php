<?php

namespace App\Filament\Resources\VotingSessionResource\Pages;

use App\Filament\Resources\VotingSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVotingSessions extends ListRecords
{
    protected static string $resource = VotingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
