<?php

namespace App\Filament\Resources\LibraryLoanResource\Pages;

use App\Filament\Resources\LibraryLoanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLibraryLoan extends CreateRecord
{
    protected static string $resource = LibraryLoanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = Auth::id();
        return $data;
    }
}
