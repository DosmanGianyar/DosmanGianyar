<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Announcement;
use App\Models\SchoolClass;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-megaphone';
    protected static string|\UnitEnum|null   $navigationGroup = 'Humas';
    protected static ?string                 $navigationLabel = 'Pengumuman';
    protected static ?string                 $modelLabel       = 'Pengumuman';
    protected static ?string                 $pluralModelLabel = 'Pengumuman Sekolah';

    public static function canAccess(): bool { return AdminAccess::can('Humas'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Judul Pengumuman')
                ->required()
                ->maxLength(255)
                ->placeholder('Contoh: Pengumuman Hari Libur Nasional')
                ->columnSpanFull(),

            Textarea::make('body')
                ->label('Isi / Deskripsi Pengumuman')
                ->rows(5)
                ->required()
                ->columnSpanFull(),

            FileUpload::make('image')
                ->label('Gambar / Poster Pengumuman (Opsional)')
                ->image()
                ->directory('announcements')
                ->nullable()
                ->helperText('Format JPG/PNG/WebP, maksimal 5MB. Gambar ini akan tampil di modal pop-up.')
                ->columnSpanFull(),

            Select::make('target')
                ->label('Target Sasaran')
                ->options([
                    'all'   => 'Semua Pengguna (Siswa & Guru)',
                    'siswa' => 'Khusus Siswa',
                    'guru'  => 'Khusus Guru',
                ])
                ->default('all')
                ->required(),

            Select::make('target_class_ids')
                ->label('Target Kelas Spesifik (Opsional)')
                ->multiple()
                ->options(SchoolClass::pluck('name', 'id'))
                ->placeholder('— Pilih Kelas (Biarkan kosong jika semua kelas) —')
                ->searchable(),

            Toggle::make('is_active')
                ->label('Status Aktif')
                ->default(true)
                ->helperText('Jika nonaktif, pengumuman tidak akan tampil di web/app.'),

            Toggle::make('show_as_modal')
                ->label('Tampilkan Pop-Up Modal (15 Detik)')
                ->default(true)
                ->helperText('Jika aktif, pengumuman akan meletup otomatis saat siswa membuka aplikasi/dashboard.'),

            Toggle::make('is_pinned')
                ->label('Sematkan di Atas (Pin)')
                ->default(false),

            DateTimePicker::make('published_at')
                ->label('Tanggal & Waktu Rilis')
                ->default(now())
                ->native(false)
                ->required(),

            DateTimePicker::make('expires_at')
                ->label('Tanggal Berakhir (Opsional)')
                ->native(false)
                ->nullable()
                ->after('published_at')
                ->helperText('Pengumuman otomatis berhenti tampil setelah tanggal ini.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->disk('public')
                    ->width(48)
                    ->height(48)
                    ->defaultImageUrl(asset('img/logo_sekolah.png'))
                    ->circular(),

                TextColumn::make('title')
                    ->label('Judul Pengumuman')
                    ->searchable()
                    ->weight('semibold')
                    ->limit(45),

                TextColumn::make('target')
                    ->label('Target')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'siswa' => 'Khusus Siswa',
                        'guru'  => 'Khusus Guru',
                        default => 'Semua',
                    })
                    ->color(fn (string $state): string => match($state) {
                        'siswa' => 'info',
                        'guru'  => 'warning',
                        default => 'success',
                    }),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                IconColumn::make('show_as_modal')
                    ->label('Modal Pop-Up')
                    ->boolean(),

                IconColumn::make('is_pinned')
                    ->label('Pin')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->label('Tanggal Dipublikasikan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('target')
                    ->label('Target')
                    ->options(['all' => 'Semua', 'siswa' => 'Siswa', 'guru' => 'Guru']),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit'   => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
