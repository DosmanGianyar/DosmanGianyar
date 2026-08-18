<?php

namespace App\Filament\Resources\StudentAchievementResource\Pages;

use App\Filament\Resources\StudentAchievementResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListStudentAchievements extends ListRecords
{
    protected static string $resource = StudentAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_guide')
                ->label('Unduh Buku Panduan Kurasi (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->url(asset('kurasi/Persyaratan Pengisian Kurasi.pdf'))
                ->openUrlInNewTab(),
        ];
    }
}

