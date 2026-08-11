<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentAchievementResource\Pages;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentAchievementResource extends Resource
{
    protected static ?string $model = StudentAchievement::class;

    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-trophy';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Prestasi & Ekskul';
    protected static ?string                 $navigationLabel      = 'Kurasi Prestasi';
    protected static ?string                 $modelLabel           = 'Prestasi Siswa';
    protected static ?string                 $pluralModelLabel     = 'Kurasi Prestasi Siswa';

    public static function canAccess(): bool { return AdminAccess::can('Prestasi & Ekskul'); }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->limit(20),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextColumn::make('title')
                    ->label('Judul Prestasi')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('organizer')
                    ->label('Penyelenggara')
                    ->searchable()
                    ->placeholder('—')
                    ->limit(20),

                TextColumn::make('field_category')
                    ->label('Rumpun')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->fieldCategoryLabel()),

                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sekolah'       => 'gray',
                        'kabupaten'     => 'info',
                        'provinsi'      => 'warning',
                        'nasional'      => 'success',
                        'internasional' => 'danger',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->levelLabel()),

                TextColumn::make('rank')
                    ->label('Peringkat')
                    ->placeholder('—')
                    ->limit(15),

                TextColumn::make('curation_status')
                    ->label('Status Kurasi')
                    ->badge()
                    ->color(fn (StudentAchievement $record): string => $record->curationStatusColor())
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->curationStatusLabel()),

                TextColumn::make('achievement_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('curation_status')
                    ->label('Status Kurasi')
                    ->options([
                        'pending'  => 'Menunggu Kurasi',
                        'curated'  => 'Lolos Kurasi',
                        'revision' => 'Perlu Revisi',
                        'rejected' => 'Tidak Layak',
                    ]),
                SelectFilter::make('field_category')
                    ->label('Rumpun Bidang')
                    ->options([
                        'sains_riset'  => 'Sains & Riset',
                        'olahraga'     => 'Olahraga',
                        'seni_budaya'  => 'Seni & Budaya',
                        'bahasa_debat' => 'Bahasa & Debat',
                        'keagamaan'    => 'Keagamaan',
                        'akademik'     => 'Akademik',
                        'lainnya'      => 'Lainnya',
                    ]),
                SelectFilter::make('level')
                    ->options([
                        'sekolah'       => 'Sekolah',
                        'kabupaten'     => 'Kabupaten/Kota',
                        'provinsi'      => 'Provinsi',
                        'nasional'      => 'Nasional',
                        'internasional' => 'Internasional',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('curate')
                    ->label('Lolos Kurasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Loloskan Kurasi Prestasi')
                    ->modalDescription('Prestasi ini akan disahkan sebagai Lolos Kurasi Standar Puspresnas/SIMT.')
                    ->action(function (StudentAchievement $record): void {
                        $record->update([
                            'curation_status' => 'curated',
                            'status'          => 'approved',
                            'curation_note'   => null,
                            'verified_by'     => auth()->id(),
                            'verified_at'     => now(),
                        ]);
                        Notification::make()->title('Prestasi Lolos Kurasi')->success()->send();
                    }),

                Action::make('revision')
                    ->label('Perlu Revisi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Textarea::make('curation_note')
                            ->label('Catatan Revisi untuk Siswa')
                            ->placeholder('Jelaskan berkas yang perlu diperbaiki (contoh: Upload ulang Surat Tugas / Sertifikat buram)')
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
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('curation_note')
                            ->label('Alasan Tidak Layak Kurasi')
                            ->placeholder('Jelaskan alasan penolakan kurasi (contoh: Lomba tidak resmi / komersial tanpa seleksi)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (StudentAchievement $record, array $data): void {
                        $record->update([
                            'curation_status'  => 'rejected',
                            'status'           => 'rejected',
                            'curation_note'    => $data['curation_note'],
                            'rejection_reason' => $data['curation_note'],
                            'verified_by'      => auth()->id(),
                            'verified_at'      => now(),
                        ]);
                        Notification::make()->title('Prestasi Ditolak / Tidak Layak')->danger()->send();
                    }),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentAchievements::route('/'),
            'view'  => Pages\ViewStudentAchievement::route('/{record}'),
        ];
    }
}
