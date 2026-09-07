<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentAchievementResource\Pages;
use App\Filament\Resources\UserResource;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentAchievementResource extends Resource
{
    protected static ?string $model = StudentAchievement::class;

    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-clipboard-document-check';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Prestasi & Ekskul';
    protected static ?string                 $navigationLabel      = 'Verifikasi & Pendataan Prestasi';
    protected static ?string                 $modelLabel           = 'Prestasi Siswa';
    protected static ?string                 $pluralModelLabel     = 'Verifikasi & Pendataan Prestasi Sekolah';
    protected static ?int                    $navigationSort       = 12;

    public static function canAccess(): bool { return AdminAccess::can('Prestasi & Ekskul'); }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->where('curation_status', '!=', 'revision')->count();
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
        return $schema->components([
            Section::make('Status Verifikasi & Pendataan Admin')
                ->icon('heroicon-o-check-badge')
                ->schema([
                    Select::make('status')
                        ->label('Status Verifikasi')
                        ->options([
                            'pending'  => '⏳ Menunggu Verifikasi',
                            'approved' => '✅ Disetujui / Valid (Masuk Rekap Sekolah)',
                            'rejected' => '❌ Ditolak / Tidak Valid',
                        ])
                        ->required(),

                    Select::make('curation_status')
                        ->label('Kategori Pendataan')
                        ->options([
                            'pending'       => 'Menunggu Penilaian',
                            'curated'       => 'Prestasi Kurasi Resmi (SIMT/Puspresnas)',
                            'not_curatable' => 'Prestasi Internal Sekolah',
                            'revision'      => 'Perlu Revisi Berkas',
                            'rejected'      => 'Tidak Layak / Ditolak',
                        ])
                        ->required(),

                    Textarea::make('curation_note')
                        ->label('Catatan Verifikasi / Petunjuk Revisi / Alasan Penolakan')
                        ->placeholder('Isi catatan internal atau petunjuk revisi untuk siswa')
                        ->columnSpanFull()
                        ->rows(2),
                ])
                ->columns(2),

            Section::make('Informasi Utama Prestasi Siswa')
                ->icon('heroicon-o-trophy')
                ->schema([
                    Select::make('student_id')
                        ->label('Siswa Utama')
                        ->relationship('student', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('category_id')
                        ->label('Kategori Prestasi')
                        ->options(\App\Models\AchievementCategory::pluck('name', 'id'))
                        ->required(),

                    Select::make('field_category')
                        ->label('Bidang Lomba / Capaian')
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

                    Select::make('level')
                        ->label('Tingkat Perlombaan')
                        ->options([
                            'sekolah'       => 'Sekolah',
                            'kabupaten'     => 'Kabupaten/Kota',
                            'provinsi'      => 'Provinsi',
                            'nasional'      => 'Nasional',
                            'internasional' => 'Internasional',
                        ])
                        ->required(),

                    TextInput::make('title')
                        ->label('Judul Capaian / Nama Lomba')
                        ->placeholder('Contoh: Juara 1 Porsenijar Catur Tingkat Kabupaten Gianyar 2026')
                        ->required()
                        ->maxLength(200)
                        ->columnSpanFull(),

                    TextInput::make('event_name')
                        ->label('Nama Ajang / Kejuaraan')
                        ->placeholder('Contoh: Porsenijar Kabupaten Gianyar 2026')
                        ->maxLength(200),

                    TextInput::make('organizer')
                        ->label('Penyelenggara Ajang')
                        ->placeholder('Contoh: Disdikpora Kabupaten Gianyar')
                        ->maxLength(200),

                    TextInput::make('rank')
                        ->label('Juara / Raihan')
                        ->placeholder('Contoh: Juara 1 / Medali Emas / Harapan 1')
                        ->maxLength(50),

                    Select::make('participation_type')
                        ->label('Jenis Keikutsertaan')
                        ->options([
                            'individu' => 'Perorangan (Individu)',
                            'beregu'   => 'Beregu (Kelompok)',
                        ]),

                    DatePicker::make('achievement_date')
                        ->label('Tanggal Pelaksanaan / Capaian')
                        ->required(),

                    TextInput::make('event_url')
                        ->label('Tautan Website Resmi Ajang (URL)')
                        ->url()
                        ->placeholder('https://...')
                        ->maxLength(500),

                    Textarea::make('description')
                        ->label('Deskripsi / Catatan Tambahan Lomba')
                        ->columnSpanFull()
                        ->rows(3),
                ])
                ->columns(2),

            Section::make('Berkas Utama & Lampiran Foto / Sertifikat')
                ->icon('heroicon-o-document-arrow-up')
                ->schema([
                    FileUpload::make('photo')
                        ->label('Foto Kegiatan / Penyerahan Piagam')
                        ->disk('public')
                        ->image()
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('achievements/photos')
                        ->maxSize(5120)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->photo)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada foto</span>');
                            $url = str_starts_with($record->photo, 'kurasi/') ? asset($record->photo) : asset('storage/' . $record->photo);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📷 Buka Foto Kegiatan Siswa ↗</a>');
                        }),

                    FileUpload::make('certificate')
                        ->label('Scan Piagam / Sertifikat')
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('achievements/certificates')
                        ->maxSize(10240)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->certificate)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada sertifikat</span>');
                            $url = str_starts_with($record->certificate, 'kurasi/') ? asset($record->certificate) : asset('storage/' . $record->certificate);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📄 Buka Sertifikat Siswa ↗</a>');
                        }),

                    FileUpload::make('assignment_letter')
                        ->label('Surat Tugas / Surat Rekomendasi Sekolah')
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('achievements/letters')
                        ->maxSize(10240)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->assignment_letter)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada surat tugas</span>');
                            $url = str_starts_with($record->assignment_letter, 'kurasi/') ? asset($record->assignment_letter) : asset('storage/' . $record->assignment_letter);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📑 Buka Surat Tugas Siswa ↗</a>');
                        }),
                ])
                ->columns(3),

            Section::make('Berkas Pendukung Kurasi (5 Poin Puspresnas)')
                ->icon('heroicon-o-folder-open')
                ->collapsible()
                ->collapsed(fn (?StudentAchievement $record): bool => ! ($record && ($record->doc_standard_file || $record->selection_level_file || $record->frequency_consistency_file || $record->infrastructure_file || $record->reward_certificate_file)))
                ->schema([
                    FileUpload::make('doc_standard_file')
                        ->label('P1: Juknis / Pedoman Lomba')
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('curations/doc_standards')
                        ->maxSize(10240)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->doc_standard_file)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada berkas P1</span>');
                            $url = str_starts_with($record->doc_standard_file, 'kurasi/') ? asset($record->doc_standard_file) : asset('storage/' . $record->doc_standard_file);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📄 Buka Berkas P1 (Juknis) ↗</a>');
                        }),

                    FileUpload::make('selection_level_file')
                        ->label('P2: Berkas Jenjang Seleksi')
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('curations/selection_levels')
                        ->maxSize(10240)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->selection_level_file)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada berkas P2</span>');
                            $url = str_starts_with($record->selection_level_file, 'kurasi/') ? asset($record->selection_level_file) : asset('storage/' . $record->selection_level_file);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📄 Buka Berkas P2 (Seleksi) ↗</a>');
                        }),

                    FileUpload::make('frequency_consistency_file')
                        ->label('P3: Bukti Konsistensi Penyelenggaraan')
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('curations/frequencies')
                        ->maxSize(20480)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->frequency_consistency_file)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada berkas P3</span>');
                            $url = str_starts_with($record->frequency_consistency_file, 'kurasi/') ? asset($record->frequency_consistency_file) : asset('storage/' . $record->frequency_consistency_file);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📄 Buka Berkas P3 (Konsistensi) ↗</a>');
                        }),

                    FileUpload::make('infrastructure_file')
                        ->label('P4: Bukti Sarana & Prasarana')
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('curations/infrastructures')
                        ->maxSize(10240)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->infrastructure_file)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada berkas P4</span>');
                            $url = str_starts_with($record->infrastructure_file, 'kurasi/') ? asset($record->infrastructure_file) : asset('storage/' . $record->infrastructure_file);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📷 Buka Berkas P4 (Sarpras) ↗</a>');
                        }),

                    FileUpload::make('reward_certificate_file')
                        ->label('P5: Sertifikat Penghargaan')
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('curations/rewards/certificates')
                        ->maxSize(10240)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->reward_certificate_file)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada berkas P5 Piagam</span>');
                            $url = str_starts_with($record->reward_certificate_file, 'kurasi/') ? asset($record->reward_certificate_file) : asset('storage/' . $record->reward_certificate_file);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📜 Buka Berkas P5 (Piagam) ↗</a>');
                        }),

                    FileUpload::make('reward_photo_file')
                        ->label('P5: Foto Penyerahan Penghargaan')
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('curations/rewards/photos')
                        ->maxSize(10240)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->reward_photo_file)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada berkas P5 Foto</span>');
                            $url = str_starts_with($record->reward_photo_file, 'kurasi/') ? asset($record->reward_photo_file) : asset('storage/' . $record->reward_photo_file);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📷 Buka Berkas P5 (Foto Penyerahan) ↗</a>');
                        }),

                    FileUpload::make('reward_recap_file')
                        ->label('P5: Rekapitulasi Hasil Lomba')
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->directory('curations/rewards/recaps')
                        ->maxSize(10240)
                        ->helperText(function (?StudentAchievement $record): ?\Illuminate\Support\HtmlString {
                            if (! $record || blank($record->reward_recap_file)) return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Belum ada berkas P5 Rekap</span>');
                            $url = str_starts_with($record->reward_recap_file, 'kurasi/') ? asset($record->reward_recap_file) : asset('storage/' . $record->reward_recap_file);
                            return new \Illuminate\Support\HtmlString('<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1">📑 Buka Berkas P5 (Rekap Hasil) ↗</a>');
                        }),
                ])
                ->columns(2),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            // Section 1: Ringkasan Prestasi & Status Verifikasi
            Section::make('Informasi Kejuaraan & Status Verifikasi')
                ->icon('heroicon-o-trophy')
                ->schema([
                    TextEntry::make('title')
                        ->label('Judul Prestasi / Kejuaraan')
                        ->weight('bold')
                        ->color('warning')
                        ->icon('heroicon-o-sparkles')
                        ->columnSpanFull(),

                    TextEntry::make('status')
                        ->label('Status Verifikasi Saat Ini')
                        ->badge()
                        ->color(fn (StudentAchievement $record): string => match ($record->status) {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default    => $record->curation_status === 'revision' ? 'warning' : 'amber',
                        })
                        ->formatStateUsing(fn (StudentAchievement $record): string => match ($record->status) {
                            'approved' => 'Disetujui / Valid (Masuk Rekap Sekolah)',
                            'rejected' => 'Ditolak',
                            default    => $record->curation_status === 'revision' ? 'Perlu Revisi Berkas' : 'Menunggu Verifikasi Admin',
                        }),

                    TextEntry::make('curation_note')
                        ->label('Catatan Verifikasi / Alasan Revisi / Penolakan')
                        ->placeholder('Tidak ada catatan verifikasi')
                        ->columnSpanFull()
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->curation_note)),

                    TextEntry::make('event_name')
                        ->label('Nama Ajang / Event')
                        ->icon('heroicon-o-flag')
                        ->placeholder('—'),

                    TextEntry::make('organizer')
                        ->label('Penyelenggara')
                        ->icon('heroicon-o-building-office')
                        ->placeholder('—'),

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

                    TextEntry::make('field_category')
                        ->label('Rumpun Bidang')
                        ->badge()
                        ->color('info')
                        ->formatStateUsing(fn (StudentAchievement $record): string => $record->fieldCategoryLabel()),

                    TextEntry::make('participation_type')
                        ->label('Jenis Partisipasi')
                        ->badge()
                        ->color(fn (StudentAchievement $record): string => $record->isBeregu() ? 'purple' : 'gray')
                        ->formatStateUsing(function (StudentAchievement $record): string {
                            if (! $record->isBeregu()) return '👤 Perorangan (Individu)';
                            $count = $record->team_members->count();
                            return "👥 Beregu ({$count} Siswa)";
                        }),

                    TextEntry::make('achievement_date')
                        ->label('Tanggal Lomba / Capaian')
                        ->date('d F Y')
                        ->icon('heroicon-o-calendar'),

                    TextEntry::make('verifier.name')
                        ->label('Diverifikasi Oleh')
                        ->weight('bold')
                        ->color('info')
                        ->placeholder('Belum diverifikasi'),

                    TextEntry::make('verified_at')
                        ->label('Waktu Verifikasi')
                        ->dateTime('d F Y, H:i')
                        ->placeholder('—'),

                    TextEntry::make('event_url')
                        ->label('Website Resmi Ajang / Berita')
                        ->url(fn ($state) => $state)
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('primary')
                        ->placeholder('—')
                        ->columnSpanFull(),

                    TextEntry::make('description')
                        ->label('Deskripsi / Catatan Tambahan Lomba')
                        ->placeholder('Tidak ada deskripsi tambahan')
                        ->columnSpanFull(),
                ])
                ->columns(3)
                ->columnSpanFull(),

            // Section 2: Data Siswa & Anggota Tim
            Section::make('Siswa Berprestasi & Anggota Tim')
                ->icon('heroicon-o-user-group')
                ->schema([
                    ViewEntry::make('team_members_view')
                        ->hiddenLabel()
                        ->view('filament.components.team-members-list')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            // Section 3: Bukti Fisik & Dokumentasi Utama
            Section::make('Bukti Fisik & Dokumentasi Utama')
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    TextEntry::make('certificate')
                        ->label('Sertifikat / Piagam Utama')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Buka / Unduh File Sertifikat ↗' : 'Tidak ada sertifikat')
                        ->url(fn (StudentAchievement $record): ?string => $record->certificate ? (str_starts_with($record->certificate, 'kurasi/') ? asset($record->certificate) : asset('storage/' . $record->certificate)) : null)
                        ->openUrlInNewTab()
                        ->color('primary')
                        ->weight('bold')
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->certificate)),

                    TextEntry::make('assignment_letter')
                        ->label('Surat Tugas / Rekomendasi Sekolah')
                        ->formatStateUsing(fn ($state) => $state ? '📑 Buka / Unduh Surat Tugas ↗' : 'Tidak ada surat tugas')
                        ->url(fn (StudentAchievement $record): ?string => $record->assignment_letter ? (str_starts_with($record->assignment_letter, 'kurasi/') ? asset($record->assignment_letter) : asset('storage/' . $record->assignment_letter)) : null)
                        ->openUrlInNewTab()
                        ->color('primary')
                        ->weight('bold')
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->assignment_letter)),

                    ImageEntry::make('photo')
                        ->label('Foto Dokumentasi Kegiatan / Penyerahan Piagam')
                        ->disk('public')
                        ->extraImgAttributes([
                            'style' => 'max-height: 320px !important; width: auto !important; height: auto !important; border-radius: 16px !important; object-fit: cover !important;',
                            'class' => 'shadow-md border border-gray-200 dark:border-gray-700 mt-2',
                        ])
                        ->columnSpanFull()
                        ->visible(fn (StudentAchievement $record): bool => ! empty($record->photo)),
                ])
                ->columns(2)
                ->columnSpanFull(),

            // Section 4: Berkas Kurasi Formal (Collapsed)
            Section::make('Berkas Pendukung Kurasi Formal (Puspresnas)')
                ->icon('heroicon-o-academic-cap')
                ->collapsible()
                ->collapsed(true)
                ->visible(fn (StudentAchievement $record): bool => (bool) $record->is_curation || ! empty($record->doc_standard_file))
                ->schema([
                    TextEntry::make('doc_standard_file')
                        ->label('P1. File Juknis / Pedoman Lomba')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Lihat File Juknis (P1)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->doc_standard_file ? (str_starts_with($record->doc_standard_file, 'kurasi/') ? asset($record->doc_standard_file) : asset('storage/' . $record->doc_standard_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('selection_level_file')
                        ->label('P2. File Bukti Tahapan Seleksi')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Lihat Berkas Seleksi (P2)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->selection_level_file ? (str_starts_with($record->selection_level_file, 'kurasi/') ? asset($record->selection_level_file) : asset('storage/' . $record->selection_level_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('frequency_consistency_file')
                        ->label('P3. File Juknis Lintas Tahun')
                        ->formatStateUsing(fn ($state) => $state ? '📄 Lihat File Lintas Tahun (P3)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->frequency_consistency_file ? (str_starts_with($record->frequency_consistency_file, 'kurasi/') ? asset($record->frequency_consistency_file) : asset('storage/' . $record->frequency_consistency_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('infrastructure_file')
                        ->label('P4. File Dokumentasi Sarpras / Venue')
                        ->formatStateUsing(fn ($state) => $state ? '📷 Lihat Dokumentasi Sarpras (P4)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->infrastructure_file ? (str_starts_with($record->infrastructure_file, 'kurasi/') ? asset($record->infrastructure_file) : asset('storage/' . $record->infrastructure_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),

                    TextEntry::make('reward_certificate_file')
                        ->label('P5. Scan Piagam / Sertifikat')
                        ->formatStateUsing(fn ($state) => $state ? '📜 Lihat Scan Piagam (P5)' : '—')
                        ->url(fn (StudentAchievement $record): ?string => $record->reward_certificate_file ? (str_starts_with($record->reward_certificate_file, 'kurasi/') ? asset($record->reward_certificate_file) : asset('storage/' . $record->reward_certificate_file)) : null)
                        ->openUrlInNewTab()
                        ->color('primary'),
                ])
                ->columns(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),

                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"));
                    })
                    ->icon('heroicon-o-user')
                    ->color('primary')
                    ->weight('semibold')
                    ->wrap()
                    ->url(fn (StudentAchievement $record): ?string => $record->student_id ? UserResource::getUrl('view', ['record' => $record->student_id]) : null)
                    ->openUrlInNewTab()
                    ->tooltip('Klik untuk lihat profil siswa'),

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
                    ->wrap()
                    ->weight('medium'),

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
                    ->placeholder('—'),

                TextColumn::make('participation_type')
                    ->label('Partisipasi')
                    ->badge()
                    ->color(fn (StudentAchievement $record): string => $record->isBeregu() ? 'purple' : 'gray')
                    ->formatStateUsing(function (StudentAchievement $record): string {
                        if (! $record->isBeregu()) {
                            return '👤 Perorangan';
                        }
                        $teamCount = $record->team_members->count();
                        return "👥 Beregu ({$teamCount} Siswa)";
                    })
                    ->tooltip(function (StudentAchievement $record): ?string {
                        if (! $record->isBeregu()) return null;
                        $names = $record->team_members->pluck('student.name')->filter()->implode(', ');
                        return 'Anggota Tim: ' . ($names ?: '—');
                    }),

                TextColumn::make('status')
                    ->label('Status Verifikasi')
                    ->badge()
                    ->color(fn (StudentAchievement $record): string => match ($record->status) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => $record->curation_status === 'revision' ? 'warning' : 'amber',
                    })
                    ->formatStateUsing(fn (StudentAchievement $record): string => match ($record->status) {
                        'approved' => 'Disetujui / Valid',
                        'rejected' => 'Ditolak',
                        default    => $record->curation_status === 'revision' ? 'Perlu Revisi' : 'Menunggu Verifikasi',
                    }),

                TextColumn::make('achievement_date')
                    ->label('Tanggal')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->translatedFormat('d F Y') : '—')
                    ->width('140px')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('date_range')
                    ->label('Periode Tanggal Prestasi')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->startOfYear()->toDateString()),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->toDateString()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('achievement_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('achievement_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['from'])->translatedFormat('d F Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['until'])->translatedFormat('d F Y');
                        }
                        return $indicators;
                    }),

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
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail & Berkas')
                        ->icon('heroicon-o-eye'),

                    EditAction::make()
                        ->label('Edit Data & Upload Berkas')
                        ->icon('heroicon-o-pencil'),

                    Action::make('student_profile')
                        ->label('Buka Profil Lengkap Siswa')
                        ->tooltip('Buka Profil Lengkap Siswa')
                        ->icon('heroicon-o-user-circle')
                        ->color('info')
                        ->url(fn (StudentAchievement $record): ?string => $record->student_id ? UserResource::getUrl('view', ['record' => $record->student_id]) : null)
                        ->openUrlInNewTab(),

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
                        ->label('Minta Revisi Berkas ke Siswa')
                        ->tooltip('Minta Revisi Berkas')
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

                    Action::make('reset_pending')
                        ->label('Batalkan Status & Reset Ke Menunggu')
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

                    DeleteAction::make()
                        ->label('Hapus Ajuan Prestasi')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Ajuan Prestasi?')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data ajuan prestasi ini secara permanen? Data yang dihapus tidak dapat dikembalikan.'),
                ])
                ->label('Menu Aksi')
                ->icon('heroicon-m-chevron-down')
                ->color('primary')
                ->button()
                ->size('sm')
            ])
            ->actionsColumnLabel('Aksi')
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentAchievements::route('/'),
            'view'  => Pages\ViewStudentAchievement::route('/{record}'),
            'edit'  => Pages\EditStudentAchievement::route('/{record}/edit'),
        ];
    }
}
