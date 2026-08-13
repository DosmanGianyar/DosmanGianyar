<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentAchievementResource\Pages;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Siswa & Prestasi')
                ->schema([
                    TextEntry::make('student.name')
                        ->label('Nama Siswa')
                        ->weight('bold'),

                    TextEntry::make('student.schoolClass.name')
                        ->label('Kelas')
                        ->placeholder('—'),

                    TextEntry::make('title')
                        ->label('Judul Prestasi / Kejuaraan')
                        ->weight('semibold')
                        ->columnSpan(2),

                    TextEntry::make('event_name')
                        ->label('Nama Lomba / Event')
                        ->placeholder('—'),

                    TextEntry::make('organizer')
                        ->label('Penyelenggara')
                        ->placeholder('—'),

                    TextEntry::make('field_category')
                        ->label('Rumpun Bidang')
                        ->badge()
                        ->color('info')
                        ->formatStateUsing(fn (StudentAchievement $record): string => $record->fieldCategoryLabel()),

                    TextEntry::make('level')
                        ->label('Tingkat Kejuaraan')
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

                    TextEntry::make('rank')
                        ->label('Peringkat / Juara')
                        ->placeholder('—'),

                    TextEntry::make('participation_type')
                        ->label('Jenis Partisipasi')
                        ->formatStateUsing(fn (StudentAchievement $record): string => $record->participationTypeLabel()),

                    TextEntry::make('achievement_date')
                        ->label('Tanggal Prestasi')
                        ->date('d MMMM Y'),

                    TextEntry::make('event_url')
                        ->label('URL Event / Berita')
                        ->url(fn ($state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—'),

                    TextEntry::make('description')
                        ->label('Deskripsi Prestasi')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ])
                ->columns(3),

            Section::make('Status Kurasi & Verifikasi')
                ->schema([
                    TextEntry::make('curation_status')
                        ->label('Status Kurasi')
                        ->badge()
                        ->color(fn (StudentAchievement $record): string => $record->curationStatusColor())
                        ->formatStateUsing(fn (StudentAchievement $record): string => $record->curationStatusLabel()),

                    TextEntry::make('verifier.name')
                        ->label('Diverifikasi Oleh')
                        ->placeholder('—'),

                    TextEntry::make('verified_at')
                        ->label('Waktu Verifikasi')
                        ->dateTime('d MMMM Y, HH:mm')
                        ->placeholder('—'),

                    TextEntry::make('curation_note')
                        ->label('Catatan Kurasi / Alasan Revisi')
                        ->placeholder('Tidak ada catatan')
                        ->columnSpanFull(),
                ])
                ->columns(3),

            Section::make('Berkas & Lampiran')
                ->schema([
                    TextEntry::make('certificate')
                        ->label('Sertifikat / Piagam')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Lihat File Sertifikat' : 'Tidak ada berkas')
                        ->url(fn (StudentAchievement $record): ?string => $record->certificateUrl())
                        ->openUrlInNewTab()
                        ->color('primary')
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->certificate)),

                    TextEntry::make('assignment_letter')
                        ->label('Surat Tugas')
                        ->formatStateUsing(fn ($state) => $state ? '📑 Lihat Surat Tugas' : 'Tidak ada berkas')
                        ->url(fn (StudentAchievement $record): ?string => $record->assignmentLetterUrl())
                        ->openUrlInNewTab()
                        ->color('primary')
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->assignment_letter)),

                    ImageEntry::make('photo')
                        ->label('Foto Dokumentasi / Penyerahan')
                        ->disk('public')
                        ->imageWidth(300)
                        ->columnSpanFull()
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->photo)),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->limit(16),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextColumn::make('title')
                    ->label('Judul Prestasi')
                    ->searchable()
                    ->limit(20)
                    ->wrap(),

                TextColumn::make('organizer')
                    ->label('Penyelenggara')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->limit(12),

                TextColumn::make('curation_status')
                    ->label('Status')
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
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('Lihat Detail Prestasi'),

                Action::make('curate')
                    ->label('Lolos Kurasi')
                    ->tooltip('Sahkan Lolos Kurasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->iconButton()
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
                    ->tooltip('Minta Revisi Berkas')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->iconButton()
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
                    ->tooltip('Tolak / Tidak Layak Kurasi')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->iconButton()
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
