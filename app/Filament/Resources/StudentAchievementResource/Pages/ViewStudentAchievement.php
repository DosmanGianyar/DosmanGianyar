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

            Action::make('reset_pending')
                ->label('Batalkan Status / Reset')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Batalkan Status Kurasi')
                ->modalDescription('Apakah Anda yakin ingin membatalkan status kurasi ini dan mengembalikannya ke status Menunggu Penilaian Kurasi?')
                ->action(function (StudentAchievement $record): void {
                    $record->update([
                        'is_curation'      => false,
                        'curation_status'  => 'pending',
                        'status'           => 'pending',
                        'curation_note'    => null,
                        'rejection_reason' => null,
                        'verified_by'      => null,
                        'verified_at'      => null,
                    ]);
                    Notification::make()->title('Status kurasi dibatalkan & dikembalikan ke Menunggu Penilaian')->info()->send();
                }),

            Action::make('delete_student_photo')
                ->label('Hapus Foto Profil Siswa')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn (StudentAchievement $record): bool => ! empty($record->student?->photo))
                ->requiresConfirmation()
                ->modalHeading('Hapus Foto Profil Siswa?')
                ->modalDescription(fn (StudentAchievement $record): string => "Apakah Anda yakin ingin menghapus foto profil milik '" . ($record->student?->name ?? 'Siswa') . "'? Foto yang melanggar aturan akan dihapus dan dikembalikan ke avatar standar UI-Avatars.")
                ->action(function (StudentAchievement $record): void {
                    if ($record->student) {
                        if ($record->student->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($record->student->photo)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($record->student->photo);
                        }
                        $record->student->update(['photo' => null]);
                        Notification::make()->title("Foto profil " . $record->student->name . " berhasil dihapus.")->success()->send();
                    }
                }),
        ];
    }
}
