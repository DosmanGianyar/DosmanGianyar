<?php

namespace App\Filament\Resources\ExtracurricularMemberResource\Pages;

use App\Filament\Resources\ExtracurricularMemberResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListExtracurricularMembers extends ListRecords
{
    protected static string $resource = ExtracurricularMemberResource::class;

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }
}
