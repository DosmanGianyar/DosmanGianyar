<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Review Tampilan')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading(fn (): string => 'Review Tampilan: ' . $this->record->title)
                ->modalWidth('3xl')
                ->modalContent(fn () => view('filament.components.announcement-preview-modal', [
                    'announcement' => $this->record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
            DeleteAction::make(),
        ];
    }
}
