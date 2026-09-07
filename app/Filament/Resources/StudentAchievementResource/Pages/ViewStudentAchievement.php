<?php

namespace App\Filament\Resources\StudentAchievementResource\Pages;

use App\Filament\Resources\StudentAchievementResource;
use App\Filament\Resources\UserResource;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
            Action::make('edit_files')
                ->label('Bantu Edit Berkas & Data')
                ->tooltip('Bantu siswa mengunggah / memperbaiki berkas atau data prestasi langsung di sini')
                ->icon('heroicon-o-pencil-square')
                ->color('info')
                ->modalHeading('Bantu Edit Berkas & Data Ajuan Siswa')
                ->modalDescription('Verifikator/Admin dapat membantu mengunggah atau mengganti berkas (piagam, surat tugas, foto, berkas kurasi P1-P5) serta memperbaiki data lomba tanpa perlu berpindah halaman.')
                ->modalSubmitActionLabel('Simpan Perubahan')
                ->modalWidth('4xl')
                ->fillForm(fn (StudentAchievement $record): array => [
                    'title'                      => $record->title,
                    'event_name'                 => $record->event_name,
                    'organizer'                  => $record->organizer,
                    'rank'                       => $record->rank,
                    'level'                      => $record->level,
                    'field_category'             => $record->field_category,
                    'achievement_date'           => $record->achievement_date?->format('Y-m-d'),
                    'event_url'                  => $record->event_url,
                    'description'                => $record->description,
                    'certificate'                => $record->certificate,
                    'assignment_letter'          => $record->assignment_letter,
                    'photo'                      => $record->photo,
                    'doc_standard_file'          => $record->doc_standard_file,
                    'selection_level_file'       => $record->selection_level_file,
                    'frequency_consistency_file' => $record->frequency_consistency_file,
                    'infrastructure_file'        => $record->infrastructure_file,
                    'reward_recap_file'          => $record->reward_recap_file,
                ])
                ->form([
                    FormSection::make('Tagihan Berkas Utama (Piagam, Surat Tugas, & Foto)')
                        ->description('Upload ulang atau ganti berkas jika buram, salah format, atau belum diisi oleh siswa.')
                        ->schema([
                            FileUpload::make('certificate')
                                ->label('Tagihan #11: Scan Piagam / Sertifikat (Khusus PDF)')
                                ->disk('public')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('achievements/certificates')
                                ->maxSize(10240),

                            FileUpload::make('assignment_letter')
                                ->label('Tagihan #12: Surat Tugas / Rekomendasi (Khusus PDF)')
                                ->disk('public')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('achievements/letters')
                                ->maxSize(10240),

                            FileUpload::make('photo')
                                ->label('Tagihan #13: Foto Dokumentasi Kegiatan')
                                ->disk('public')
                                ->image()
                                ->directory('achievements/photos')
                                ->maxSize(5120),
                        ])
                        ->columns(3),

                    FormSection::make('Tagihan Berkas Kurasi (P1 s/d P5 - Opsional)')
                        ->description('Berkas kelengkapan kurasi ajang talenta (bisa diunggah jika siswa melampirkan secara terpisah).')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            FileUpload::make('doc_standard_file')
                                ->label('Tagihan #14: P1 Juknis / Pedoman Lomba (PDF)')
                                ->disk('public')
                                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                ->directory('curations/doc_standards')
                                ->maxSize(10240),

                            FileUpload::make('selection_level_file')
                                ->label('Tagihan #15: P2 Bukti Jenjang Seleksi (PDF)')
                                ->disk('public')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('curations/selection_levels')
                                ->maxSize(10240),

                            FileUpload::make('frequency_consistency_file')
                                ->label('Tagihan #16: P3 Bukti Konsistensi Lintas Tahun (PDF)')
                                ->disk('public')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('curations/frequencies')
                                ->maxSize(20480),

                            FileUpload::make('infrastructure_file')
                                ->label('Tagihan #17: P4 Dokumentasi Sarpras/Venue')
                                ->disk('public')
                                ->directory('curations/infrastructures')
                                ->maxSize(10240),

                            FileUpload::make('reward_recap_file')
                                ->label('Tagihan #18: P5 Rekap Juara & Bukti Apresiasi')
                                ->disk('public')
                                ->acceptedFileTypes(['application/pdf', 'image/*'])
                                ->directory('curations/rewards/recaps')
                                ->maxSize(10240),
                        ])
                        ->columns(2),

                    FormSection::make('Koreksi Data Pokok Prestasi / Lomba')
                        ->description('Perbaiki nama ajang, tingkat, juara, atau tanggal jika terdapat kekeliruan ketik dari siswa.')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            TextInput::make('title')
                                ->label('Tagihan #1: Judul Prestasi / Kejuaraan')
                                ->required()
                                ->maxLength(200)
                                ->columnSpanFull(),

                            TextInput::make('event_name')
                                ->label('Tagihan #2: Nama Ajang / Event')
                                ->maxLength(200),

                            TextInput::make('organizer')
                                ->label('Tagihan #3: Penyelenggara Lomba')
                                ->maxLength(200),

                            Select::make('level')
                                ->label('Tagihan #4: Tingkat Kejuaraan')
                                ->options([
                                    'sekolah'       => 'Sekolah',
                                    'kabupaten'     => 'Kabupaten/Kota',
                                    'provinsi'      => 'Provinsi',
                                    'nasional'      => 'Nasional',
                                    'internasional' => 'Internasional',
                                ])
                                ->required(),

                            TextInput::make('rank')
                                ->label('Tagihan #5: Peringkat / Juara')
                                ->maxLength(50),

                            Select::make('field_category')
                                ->label('Tagihan #6: Rumpun Bidang / Talenta')
                                ->options([
                                    'akademik'     => 'Akademik',
                                    'sains_riset'  => 'Sains & Riset',
                                    'olahraga'     => 'Olahraga',
                                    'seni_budaya'  => 'Seni & Budaya',
                                    'bahasa_debat' => 'Bahasa & Debat',
                                    'keagamaan'    => 'Keagamaan',
                                    'lainnya'      => 'Lainnya',
                                ])
                                ->required(),

                            DatePicker::make('achievement_date')
                                ->label('Tagihan #8: Tanggal Lomba / Capaian')
                                ->required(),

                            TextInput::make('event_url')
                                ->label('Tagihan #9: Website Resmi Ajang (URL)')
                                ->url()
                                ->maxLength(500),

                            Textarea::make('description')
                                ->label('Tagihan #10: Deskripsi / Catatan Tambahan')
                                ->columnSpanFull()
                                ->rows(2),
                        ])
                        ->columns(2),
                ])
                ->action(function (StudentAchievement $record, array $data): void {
                    if ($record->isBeregu() && ! empty($record->team_code)) {
                        StudentAchievement::where('team_code', $record->team_code)->update($data);
                    } else {
                        $record->update($data);
                    }
                    Notification::make()->title('Data & Berkas Ajuan Siswa Berhasil Diperbarui')->success()->send();
                }),

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
