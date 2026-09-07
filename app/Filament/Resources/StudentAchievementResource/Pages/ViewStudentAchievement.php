<?php

namespace App\Filament\Resources\StudentAchievementResource\Pages;

use App\Filament\Resources\StudentAchievementResource;
use App\Filament\Resources\UserResource;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentAchievement extends ViewRecord
{
    protected static string $resource = StudentAchievementResource::class;

    public function getTitle(): string
    {
        return 'Detail & Verifikasi Prestasi Siswa';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve_achievement')
                ->label('Setujui & Sahkan Prestasi')
                ->tooltip('Setujui & Masukkan ke Rekap Prestasi Sekolah')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Setujui & Sahkan Prestasi')
                ->modalDescription(function (StudentAchievement $record): string {
                    if ($record->isBeregu()) {
                        $count = $record->team_members->count();
                        return "Prestasi beregu ini akan disetujui untuk SELURUH tim ({$count} siswa). Data akan langsung masuk ke Rekapitulasi Sekolah (dihitung 1 Prestasi Sekolah).";
                    }
                    return 'Prestasi siswa ini akan disetujui & disahkan ke Rekapitulasi Prestasi Sekolah.';
                })
                ->action(function (StudentAchievement $record): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        $affected = StudentAchievement::where('team_code', $record->team_code)->update([
                            'status'          => 'approved',
                            'curation_status' => $record->is_curation ? 'curated' : 'not_curatable',
                            'curation_note'   => null,
                            'verified_by'     => auth()->id(),
                            'verified_at'     => now(),
                        ]);
                    } elseif ($record->isBeregu()) {
                        $affected = StudentAchievement::where('participation_type', 'beregu')
                            ->where('title', $record->title)
                            ->where('achievement_date', $record->achievement_date)
                            ->update([
                                'status'          => 'approved',
                                'curation_status' => $record->is_curation ? 'curated' : 'not_curatable',
                                'curation_note'   => null,
                                'verified_by'     => auth()->id(),
                                'verified_at'     => now(),
                            ]);
                    } else {
                        $record->update([
                            'status'          => 'approved',
                            'curation_status' => $record->is_curation ? 'curated' : 'not_curatable',
                            'curation_note'   => null,
                            'verified_by'     => auth()->id(),
                            'verified_at'     => now(),
                        ]);
                        $affected = 1;
                    }
                    Notification::make()->title("Prestasi Disetujui ({$affected} Siswa)")->success()->send();
                }),

            Action::make('revision')
                ->label('Minta Revisi Berkas')
                ->tooltip('Minta Siswa Memperbaiki Berkas/Data')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Textarea::make('curation_note')
                        ->label('Catatan Revisi untuk Siswa')
                        ->placeholder('Jelaskan nomor tagihan & berkas yang perlu diperbaiki (contoh: Mohon upload ulang Tagihan #11 Scan Piagam karena buram)')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        $affected = StudentAchievement::where('team_code', $record->team_code)->update([
                            'status'          => 'pending',
                            'curation_status' => 'revision',
                            'curation_note'   => $data['curation_note'],
                            'verified_by'     => auth()->id(),
                            'verified_at'     => now(),
                        ]);
                    } else {
                        $record->update([
                            'status'          => 'pending',
                            'curation_status' => 'revision',
                            'curation_note'   => $data['curation_note'],
                            'verified_by'     => auth()->id(),
                            'verified_at'     => now(),
                        ]);
                        $affected = 1;
                    }
                    Notification::make()->title("Diminta Revisi Berkas ({$affected} Siswa)")->warning()->send();
                }),

            Action::make('reject')
                ->label('Tolak / Tidak Valid')
                ->tooltip('Tolak Ajuan Prestasi')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('curation_note')
                        ->label('Alasan Penolakan')
                        ->placeholder('Jelaskan alasan penolakan (contoh: Sertifikat tidak valid/bukan atas nama siswa)')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        $affected = StudentAchievement::where('team_code', $record->team_code)->update([
                            'status'           => 'rejected',
                            'curation_status'  => 'rejected',
                            'curation_note'    => $data['curation_note'],
                            'rejection_reason' => $data['curation_note'],
                            'verified_by'      => auth()->id(),
                            'verified_at'      => now(),
                        ]);
                    } else {
                        $record->update([
                            'status'           => 'rejected',
                            'curation_status'  => 'rejected',
                            'curation_note'    => $data['curation_note'],
                            'rejection_reason' => $data['curation_note'],
                            'verified_by'      => auth()->id(),
                            'verified_at'      => now(),
                        ]);
                        $affected = 1;
                    }
                    Notification::make()->title("Prestasi Ditolak ({$affected} Siswa)")->danger()->send();
                }),

            ActionGroup::make([
                Action::make('reset_pending')
                    ->label('Reset Ke Menunggu Verifikasi')
                    ->tooltip('Batalkan penetapan dan kembalikan ke status Menunggu Verifikasi')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Status Verifikasi')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan status verifikasi ini dan mengembalikannya ke status Menunggu Verifikasi?')
                    ->action(function (StudentAchievement $record): void {
                        if ($record->isBeregu() && ! empty($record->team_code)) {
                            StudentAchievement::where('team_code', $record->team_code)->update([
                                'status'           => 'pending',
                                'curation_status'  => 'pending',
                                'curation_note'    => null,
                                'rejection_reason' => null,
                                'verified_by'      => null,
                                'verified_at'      => null,
                            ]);
                        } else {
                            $record->update([
                                'status'           => 'pending',
                                'curation_status'  => 'pending',
                                'curation_note'    => null,
                                'rejection_reason' => null,
                                'verified_by'      => null,
                                'verified_at'      => null,
                            ]);
                        }
                        Notification::make()->title('Status verifikasi dibatalkan & dikembalikan ke Menunggu Verifikasi')->info()->send();
                    }),

                EditAction::make()
                    ->label('Edit Data & Berkas')
                    ->icon('heroicon-o-pencil-square'),

                Action::make('student_profile')
                    ->label('Buka Profil Siswa')
                    ->tooltip('Buka Profil Lengkap Siswa')
                    ->icon('heroicon-o-user-circle')
                    ->color('info')
                    ->url(fn (StudentAchievement $record): ?string => $record->student_id ? UserResource::getUrl('view', ['record' => $record->student_id]) : null)
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->label('Hapus Ajuan')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Ajuan Prestasi?')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data ajuan prestasi ini secara permanen? Data yang dihapus tidak dapat dikembalikan.'),
            ])
                ->label('Opsi Lainnya')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button(),
        ];
    }
}
