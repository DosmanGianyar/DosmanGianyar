<?php

namespace App\Filament\Resources\StudentAchievementResource\Pages;

use App\Filament\Resources\StudentAchievementResource;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentAchievement extends ViewRecord
{
    protected static string $resource = StudentAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Data & Berkas')
                ->icon('heroicon-o-pencil-square')
                ->color('primary'),
            Action::make('curate')
                ->label('Lolos Kurasi Resmi')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Loloskan Kurasi Resmi')
                ->modalDescription('Prestasi ini akan disahkan sebagai Lolos Kurasi Resmi Standar Puspresnas/SIMT.')
                ->action(function (StudentAchievement $record): void {
                    $record->update([
                        'is_curation'     => true,
                        'curation_status' => 'curated',
                        'status'          => 'approved',
                        'curation_note'   => null,
                        'verified_by'     => auth()->id(),
                        'verified_at'     => now(),
                    ]);
                    Notification::make()->title('Prestasi Lolos Kurasi Resmi')->success()->send();
                }),

            Action::make('not_curatable')
                ->label('Prestasi Internal')
                ->icon('heroicon-o-bookmark')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Tandai Prestasi Internal Sekolah')
                ->modalDescription('Prestasi ini akan tetap dicatat & diakui sebagai Prestasi Siswa Sekolah, tetapi ditandai TIDAK masuk kurasi resmi Puspresnas/SIMT.')
                ->action(function (StudentAchievement $record): void {
                    $record->update([
                        'is_curation'     => false,
                        'curation_status' => 'not_curatable',
                        'status'          => 'approved',
                        'curation_note'   => 'Dicatat sebagai Prestasi Catatan Internal Sekolah',
                        'verified_by'     => auth()->id(),
                        'verified_at'     => now(),
                    ]);
                    Notification::make()->title('Prestasi Diakui sebagai Catatan Internal Sekolah')->info()->send();
                }),

            Action::make('revision')
                ->label('Perlu Revisi')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Textarea::make('curation_note')
                        ->label('Catatan Revisi untuk Siswa')
                        ->placeholder('Jelaskan berkas yang perlu diperbaiki')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    $record->update([
                        'curation_status' => 'revision',
                        'curation_note'   => $data['curation_note'],
                        'verified_by'     => auth()->id(),
                        'verified_at'     => now(),
                    ]);
                    Notification::make()->title('Diminta Revisi Berkas')->warning()->send();
                }),

            Action::make('reject')
                ->label('Tolak / Tidak Layak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('curation_note')
                        ->label('Alasan Tidak Layak Kurasi')
                        ->placeholder('Jelaskan alasan penolakan')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    $record->update([
                        'is_curation'      => false,
                        'curation_status'  => 'rejected',
                        'status'           => 'rejected',
                        'curation_note'    => $data['curation_note'],
                        'rejection_reason' => $data['curation_note'],
                        'verified_by'      => auth()->id(),
                        'verified_at'      => now(),
                    ]);
                    Notification::make()->title('Prestasi Ditolak / Tidak Layak')->danger()->send();
                }),
        ];
    }
}
