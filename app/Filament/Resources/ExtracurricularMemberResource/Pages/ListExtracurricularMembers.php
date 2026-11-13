<?php

namespace App\Filament\Resources\ExtracurricularMemberResource\Pages;

use App\Filament\Resources\ExtracurricularMemberResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListExtracurricularMembers extends ListRecords
{
    protected static string $resource = ExtracurricularMemberResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }
}
