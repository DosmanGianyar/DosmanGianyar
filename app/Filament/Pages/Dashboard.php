<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\ToggleButtons;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                ToggleButtons::make('grade')
                    ->hiddenLabel()
                    ->options([
                        'all' => 'Seluruh Siswa',
                        '10'  => 'Kelas 10 (X)',
                        '11'  => 'Kelas 11 (XI)',
                        '12'  => 'Kelas 12 (XII)',
                    ])
                    ->icons([
                        'all' => 'heroicon-m-user-group',
                        '10'  => 'heroicon-m-academic-cap',
                        '11'  => 'heroicon-m-academic-cap',
                        '12'  => 'heroicon-m-academic-cap',
                    ])
                    ->colors([
                        'all' => 'primary',
                        '10'  => 'info',
                        '11'  => 'warning',
                        '12'  => 'success',
                    ])
                    ->default('all')
                    ->inline()
                    ->live()
                    ->columnSpanFull(),
            ]);
    }
}


