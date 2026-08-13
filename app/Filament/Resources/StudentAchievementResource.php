<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentAchievementResource\Pages;
use App\Filament\Resources\UserResource;
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
use Illuminate\Database\Eloquent\Builder;

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
            // Card 1: Status Kurasi
            Section::make('Status Kurasi & Verifikasi')
                ->icon('heroicon-o-check-badge')
                ->schema([
                    TextEntry::make('curation_status')
                        ->label('Status Kurasi')
                        ->badge()
                        ->color(fn (StudentAchievement $record): string => $record->curationStatusColor())
                        ->formatStateUsing(fn (StudentAchievement $record): string => $record->curationStatusLabel()),

                    TextEntry::make('verifier.name')
                        ->label('Diverifikasi Oleh')
                        ->weight('bold')
                        ->color('info')
                        ->placeholder('—'),

                    TextEntry::make('verified_at')
                        ->label('Waktu Verifikasi')
                        ->dateTime('d F Y, H:i')
                        ->placeholder('—'),

                    TextEntry::make('curation_note')
                        ->label('Catatan Kurasi / Alasan Revisi')
                        ->placeholder('Tidak ada catatan')
                        ->columnSpanFull(),
                ])
                ->columns(3)
                ->columnSpanFull(),

            // Card 2: Identitas & Profil Data Diri Siswa
            Section::make('Identitas & Profil Data Diri Siswa')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    TextEntry::make('student.name')
                        ->label('Nama Lengkap Siswa')
                        ->weight('bold')
                        ->color('primary')
                        ->icon('heroicon-o-user')
                        ->url(fn (StudentAchievement $record): ?string => $record->student_id ? UserResource::getUrl('view', ['record' => $record->student_id]) : null)
                        ->openUrlInNewTab()
                        ->tooltip('Klik untuk membuka profil lengkap siswa'),

                    TextEntry::make('student.nisn')
                        ->label('NISN')
                        ->fontFamily('mono')
                        ->placeholder('—'),

                    TextEntry::make('student.nis')
                        ->label('NIS')
                        ->fontFamily('mono')
                        ->placeholder('—'),

                    TextEntry::make('student.schoolClass.name')
                        ->label('Kelas')
                        ->badge()
                        ->color('info')
                        ->placeholder('—'),

                    TextEntry::make('student.gender')
                        ->label('Jenis Kelamin')
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                            default => '—',
                        })
                        ->placeholder('—'),

                    TextEntry::make('student.blood_type')
                        ->label('Golongan Darah')
                        ->badge()
                        ->color('danger')
                        ->formatStateUsing(fn ($state) => filled($state) ? 'Gol. ' . strtoupper($state) : '—')
                        ->placeholder('—'),

                    TextEntry::make('student.phone')
                        ->label('No. HP Siswa')
                        ->icon('heroicon-o-phone')
                        ->color('success')
                        ->weight('bold')
                        ->formatStateUsing(fn (?string $state) => filled($state) ? $state : 'Belum diisi')
                        ->url(function (StudentAchievement $record): ?string {
                            $phone = $record->student?->phone;
                            if (blank($phone)) return null;
                            $clean = preg_replace('/[^0-9]/', '', $phone);
                            if (str_starts_with($clean, '0')) $clean = '62' . substr($clean, 1);
                            return 'https://wa.me/' . $clean;
                        })
                        ->openUrlInNewTab()
                        ->tooltip('Klik untuk chat WhatsApp siswa'),

                    TextEntry::make('student.parent_name')
                        ->label('Nama Orang Tua / Wali')
                        ->placeholder('—'),

                    TextEntry::make('student.parent_phone')
                        ->label('No. HP Orang Tua / Wali')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->formatStateUsing(fn (?string $state) => filled($state) ? $state : 'Belum diisi')
                        ->url(function (StudentAchievement $record): ?string {
                            $phone = $record->student?->parent_phone;
                            if (blank($phone)) return null;
                            $clean = preg_replace('/[^0-9]/', '', $phone);
                            if (str_starts_with($clean, '0')) $clean = '62' . substr($clean, 1);
                            return 'https://wa.me/' . $clean;
                        })
                        ->openUrlInNewTab()
                        ->tooltip('Klik untuk chat WhatsApp Orang Tua'),

                    TextEntry::make('student_address_formatted')
                        ->label('Alamat Tempat Tinggal Siswa')
                        ->formatStateUsing(function (StudentAchievement $record): string {
                            $s = $record->student;
                            if (! $s) return '—';
                            $parts = array_filter([
                                $s->address,
                                $s->rt_rw ? 'RT/RW ' . $s->rt_rw : null,
                                $s->kelurahan ? 'Kel. ' . $s->kelurahan : null,
                                $s->kecamatan ? 'Kec. ' . $s->kecamatan : null,
                                $s->kabupaten ? 'Kab. ' . $s->kabupaten : null,
                            ]);
                            return count($parts) > 0 ? implode(', ', $parts) : '—';
                        })
                        ->icon('heroicon-o-map-pin')
                        ->columnSpanFull(),

                    TextEntry::make('achievement_date')
                        ->label('Tanggal Prestasi')
                        ->date('d F Y')
                        ->icon('heroicon-o-calendar')
                        ->weight('semibold'),
                ])
                ->columns(3)
                ->columnSpanFull(),

            // Card 3: Detail Kejuaraan & Lomba
            Section::make('Detail Kejuaraan & Lomba')
                ->icon('heroicon-o-trophy')
                ->schema([
                    TextEntry::make('title')
                        ->label('Judul Prestasi / Kejuaraan')
                        ->weight('bold')
                        ->color('warning')
                        ->icon('heroicon-o-sparkles')
                        ->columnSpanFull(),

                    TextEntry::make('event_name')
                        ->label('Nama Lomba / Event')
                        ->icon('heroicon-o-flag')
                        ->placeholder('—'),

                    TextEntry::make('organizer')
                        ->label('Penyelenggara')
                        ->icon('heroicon-o-building-office')
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
                        ->badge()
                        ->color('warning')
                        ->placeholder('—'),

                    TextEntry::make('participation_type')
                        ->label('Jenis Partisipasi')
                        ->badge()
                        ->color('gray')
                        ->formatStateUsing(fn (StudentAchievement $record): string => $record->participationTypeLabel()),

                    TextEntry::make('event_url')
                        ->label('URL Event / Berita')
                        ->url(fn ($state) => $state)
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('primary')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ])
                ->columns(3)
                ->columnSpanFull(),

            // Card 4: Deskripsi Prestasi
            Section::make('Deskripsi Prestasi')
                ->icon('heroicon-o-document-text')
                ->schema([
                    TextEntry::make('description')
                        ->label('Deskripsi Lengkap')
                        ->placeholder('Tidak ada deskripsi tambahan')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            // Card 5: Berkas & Lampiran
            Section::make('Berkas & Lampiran Dokumentasi')
                ->icon('heroicon-o-paper-clip')
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
                        ->imageWidth(400)
                        ->columnSpanFull()
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->photo)),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"));
                    })
                    ->icon('heroicon-o-user')
                    ->color('primary')
                    ->weight('semibold')
                    ->url(fn (StudentAchievement $record): ?string => $record->student_id ? UserResource::getUrl('view', ['record' => $record->student_id]) : null)
                    ->openUrlInNewTab()
                    ->tooltip('Klik untuk lihat profil siswa')
                    ->limit(18),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextColumn::make('student.phone')
                    ->label('No. HP Siswa')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', fn (Builder $q) => $q->where('phone', 'like', "%{$search}%")->orWhere('parent_phone', 'like', "%{$search}%"));
                    })
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->weight('medium')
                    ->formatStateUsing(function (StudentAchievement $record): string {
                        $phone = $record->student?->phone;
                        if (filled($phone)) return $phone;
                        $parentPhone = $record->student?->parent_phone;
                        if (filled($parentPhone)) return $parentPhone . ' (Ortu)';
                        return '—';
                    })
                    ->url(function (StudentAchievement $record): ?string {
                        $phone = $record->student?->phone ?: $record->student?->parent_phone;
                        if (blank($phone)) return null;
                        $clean = preg_replace('/[^0-9]/', '', $phone);
                        if (str_starts_with($clean, '0')) $clean = '62' . substr($clean, 1);
                        return 'https://wa.me/' . $clean;
                    })
                    ->openUrlInNewTab()
                    ->tooltip('Klik untuk chat WhatsApp'),

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

                Action::make('student_profile')
                    ->label('Profil Siswa')
                    ->tooltip('Buka Profil Lengkap Siswa')
                    ->icon('heroicon-o-user-circle')
                    ->color('info')
                    ->iconButton()
                    ->url(fn (StudentAchievement $record): ?string => $record->student_id ? UserResource::getUrl('view', ['record' => $record->student_id]) : null)
                    ->openUrlInNewTab(),

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
