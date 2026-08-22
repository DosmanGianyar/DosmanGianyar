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
use Filament\Infolists\Components\ViewEntry;
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

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('curation_status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

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

            // Card 2: Foto Profil & Anggota Tim Siswa
            Section::make('Foto Profil & Data Anggota Siswa')
                ->icon('heroicon-o-user-group')
                ->schema([
                    ViewEntry::make('team_members_view')
                        ->hiddenLabel()
                        ->view('filament.components.team-members-list')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            // Card 3: Identitas & Profil Data Diri Siswa
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

            // Card 5: Rincian 5 Poin Kurasi Kemendikdasmen (SIMT / Puspresnas)
            Section::make('Rincian Berkas 5 Poin Kurasi Kemendikdasmen (SIMT / Puspresnas)')
                ->icon('heroicon-o-academic-cap')
                ->visible(fn (StudentAchievement $record): bool => (bool) $record->is_curation)
                ->schema([
                    TextEntry::make('doc_standard_checklist')
                        ->label('P1. Checklist Dokumen Juknis Standar')
                        ->formatStateUsing(function ($state): string {
                            if (empty($state) || !is_array($state)) return '—';
                            return implode(', ', array_map(fn($item) => ucwords(str_replace('_', ' ', $item)), $state));
                        })
                        ->badge()
                        ->color('info'),

                    TextEntry::make('doc_standard_file')
                        ->label('P1. File Juknis / Pedoman Lomba')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Lihat File Juknis (P1)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->doc_standard_file ? (str_starts_with($record->doc_standard_file, 'kurasi/') ? asset($record->doc_standard_file) : asset('storage/' . $record->doc_standard_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('doc_standard_url')
                        ->label('P1. URL Juknis / Website Resmi')
                        ->url(fn ($state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—'),

                    TextEntry::make('selection_level')
                        ->label('P2. Tingkatan Seleksi Ajang')
                        ->badge()
                        ->color('warning')
                        ->formatStateUsing(fn ($state) => $state ? strtoupper(str_replace('_', ' ', $state)) : '—'),

                    TextEntry::make('selection_level_file')
                        ->label('P2. File Bukti Tahapan Seleksi')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Lihat Berkas Seleksi (P2)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->selection_level_file ? (str_starts_with($record->selection_level_file, 'kurasi/') ? asset($record->selection_level_file) : asset('storage/' . $record->selection_level_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('frequency_consistency')
                        ->label('P3. Konsistensi Frekuensi Penyelenggaraan')
                        ->badge()
                        ->color('info')
                        ->formatStateUsing(fn ($state) => $state ? ucwords(str_replace('_', ' ', $state)) : '—'),

                    TextEntry::make('frequency_consistency_file')
                        ->label('P3. File Juknis Lintas Tahun')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Lihat File Lintas Tahun (P3)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->frequency_consistency_file ? (str_starts_with($record->frequency_consistency_file, 'kurasi/') ? asset($record->frequency_consistency_file) : asset('storage/' . $record->frequency_consistency_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('infrastructure_type')
                        ->label('P4. Sarana & Prasarana Ajang')
                        ->badge()
                        ->color('success')
                        ->formatStateUsing(fn ($state) => $state ? ucwords(str_replace('_', ' ', $state)) : '—'),

                    TextEntry::make('infrastructure_file')
                        ->label('P4. File Dokumentasi Sarpras / Venue')
                        ->formatStateUsing(fn ($state) => $state ? '📷 Lihat Dokumentasi Sarpras (P4)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->infrastructure_file ? (str_starts_with($record->infrastructure_file, 'kurasi/') ? asset($record->infrastructure_file) : asset('storage/' . $record->infrastructure_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('reward_types')
                        ->label('P5. Jenis Penghargaan & Apresiasi')
                        ->formatStateUsing(function ($state): string {
                            if (empty($state) || !is_array($state)) return '—';
                            return implode(', ', array_map(fn($item) => ucwords(str_replace('_', ' ', $item)), $state));
                        })
                        ->badge()
                        ->color('success'),

                    TextEntry::make('reward_certificate_file')
                        ->label('P5. Scan Piagam / Sertifikat')
                        ->formatStateUsing(fn ($state) => $state ? '📜 Lihat Scan Piagam (P5)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->reward_certificate_file ? (str_starts_with($record->reward_certificate_file, 'kurasi/') ? asset($record->reward_certificate_file) : asset('storage/' . $record->reward_certificate_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('reward_photo_file')
                        ->label('P5. Foto Penyerahan Hadiah / Medali')
                        ->formatStateUsing(fn ($state) => $state ? '📷 Lihat Foto Penyerahan (P5)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->reward_photo_file ? (str_starts_with($record->reward_photo_file, 'kurasi/') ? asset($record->reward_photo_file) : asset('storage/' . $record->reward_photo_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('reward_recap_file')
                        ->label('P5. SK / Rekap Pemenang Lomba')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Lihat Rekap Pemenang (P5)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->reward_recap_file ? (str_starts_with($record->reward_recap_file, 'kurasi/') ? asset($record->reward_recap_file) : asset('storage/' . $record->reward_recap_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),
                ])
                ->columns(3)
                ->columnSpanFull(),

            // Card 6: Berkas & Lampiran
            Section::make('Berkas & Lampiran Dokumentasi Utama')
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    TextEntry::make('certificate')
                        ->label('Sertifikat / Piagam Utama')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Lihat File Sertifikat' : 'Tidak ada berkas')
                        ->url(fn (StudentAchievement $record): ?string => $record->certificate ? (str_starts_with($record->certificate, 'kurasi/') ? asset($record->certificate) : asset('storage/' . $record->certificate)) : null)
                        ->openUrlInNewTab()
                        ->color('primary')
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->certificate)),

                    TextEntry::make('assignment_letter')
                        ->label('Surat Tugas Utama')
                        ->formatStateUsing(fn ($state) => $state ? '📑 Lihat Surat Tugas' : 'Tidak ada berkas')
                        ->url(fn (StudentAchievement $record): ?string => $record->assignment_letter ? (str_starts_with($record->assignment_letter, 'kurasi/') ? asset($record->assignment_letter) : asset('storage/' . $record->assignment_letter)) : null)
                        ->openUrlInNewTab()
                        ->color('primary')
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->assignment_letter)),

                    ImageEntry::make('photo')
                        ->label('Foto Dokumentasi / Penyerahan Utama')
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
                    ->limit(15),

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
                    ->tooltip('Klik untuk chat WhatsApp')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('title')
                    ->label('Judul Prestasi')
                    ->searchable()
                    ->limit(18),

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
                    ->limit(10),

                TextColumn::make('is_curation')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Kurasi' : 'Regular')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('curation_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (StudentAchievement $record): string => $record->curationStatusColor())
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->curationStatusLabel()),

                TextColumn::make('achievement_date')
                    ->label('Tanggal')
                    ->date('d MMM Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('is_curation')
                    ->label('Tipe Pengajuan')
                    ->options([
                        '1' => '🎖️ Pengajuan Kurasi Kemendikdasmen',
                        '0' => '🏆 Prestasi Internal Sekolah',
                    ]),
                SelectFilter::make('curation_status')
                    ->label('Status Kurasi')
                    ->options([
                        'pending'       => 'Pengajuan Kurasi (Menunggu)',
                        'curated'       => 'Lolos Kurasi Resmi',
                        'not_curatable' => 'Prestasi Internal (Tidak Dikurasi)',
                        'revision'      => 'Perlu Revisi Berkas',
                        'rejected'      => 'Tidak Layak',
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
                    ->label('Lolos Kurasi Resmi')
                    ->tooltip('Sahkan Lolos Kurasi Resmi (SIMT/Puspresnas)')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->iconButton()
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
                    ->tooltip('Tandai sebagai Prestasi Internal Sekolah (Tidak Dikurasi)')
                    ->icon('heroicon-o-bookmark')
                    ->color('info')
                    ->iconButton()
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
