<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryResource\Pages;
use App\Filament\Resources\GalleryResource\RelationManagers;
use App\Filament\Support\AdminAccess;
use App\Models\Gallery;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-photo';
    protected static string|\UnitEnum|null   $navigationGroup = 'Humas';
    protected static ?string                 $navigationLabel = 'Galeri & Kegiatan';
    protected static ?string                 $modelLabel       = 'Kegiatan';
    protected static ?string                 $pluralModelLabel = 'Galeri & Kegiatan';

    public static function canAccess(): bool
    {
        return AdminAccess::can('Humas');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kegiatan')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul Kegiatan / Album')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),

                    DatePicker::make('event_date')
                        ->label('Tanggal Kegiatan')
                        ->default(now())
                        ->native(false)
                        ->nullable(),

                    Toggle::make('is_published')
                        ->label('Tampilkan Galeri ke Semua')
                        ->default(true)
                        ->helperText('Aktifkan agar hasil foto dapat dilihat di halaman Galeri'),

                    Textarea::make('description')
                        ->label('Deskripsi Kegiatan')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    FileUpload::make('cover_photo')
                        ->label('Foto Cover (Opsional)')
                        ->image()
                        ->disk('public')
                        ->directory('galleries/covers')
                        ->nullable()
                        ->columnSpanFull(),
                ]),

            Section::make('Pengaturan Upload Kontributor (Synology Data Sekolah)')
                ->description('Guru & pegawai otomatis dapat mengunggah foto. Siswa hanya dapat mengunggah jika dipilih oleh Admin di bawah ini.')
                ->columns(3)
                ->schema([
                    Toggle::make('allow_upload')
                        ->label('Buka Upload Foto dari HP')
                        ->default(true)
                        ->helperText('Jika dimatikan, guru & siswa tidak dapat menambah foto lagi'),

                    DateTimePicker::make('upload_closed_at')
                        ->label('Batas Akhir Upload (Opsional)')
                        ->native(false)
                        ->seconds(false)
                        ->nullable()
                        ->helperText('Kosongkan jika tidak ada batas waktu otomatis'),

                    TextInput::make('max_photos_per_user')
                        ->label('Maks. Foto per Akun')
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->maxValue(50)
                        ->required()
                        ->helperText('Default 10 foto per akun SIMAK'),

                    Select::make('allowedStudents')
                        ->label('Siswa yang Diizinkan Upload Foto')
                        ->relationship(
                            name: 'allowedStudents',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query
                                ->whereIn('role', ['siswa', 'pengelola'])
                                ->with('schoolClass')
                                ->orderBy('name'),
                        )
                        ->getOptionLabelFromRecordUsing(function (User $record): string {
                            $class = $record->schoolClass?->name ?? 'Tanpa Kelas';
                            $nis   = $record->nis ?: ($record->nisn ?: '-');
                            return "{$record->name} ({$class} — NIS: {$nis})";
                        })
                        ->searchable(['name', 'nis', 'nisn'])
                        ->multiple()
                        ->preload()
                        ->columnSpanFull()
                        ->helperText('Semua Guru/Pegawai otomatis boleh upload. Untuk siswa, hanya akun yang dipilih di sini yang bisa upload.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_photo')
                    ->label('')
                    ->getStateUsing(fn (Gallery $record) => $record->coverPhotoUrl())
                    ->width(64)
                    ->height(48)
                    ->defaultImageUrl(asset('img/logo_sekolah.png'))
                    ->rounded(),

                TextColumn::make('title')
                    ->label('Judul Kegiatan')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (Gallery $record): ?string => $record->folder_slug ? 'Synology: 01_Dokumentasi_Kegiatan/' . $record->folder_slug : null),

                TextColumn::make('event_date')
                    ->label('Tgl. Kegiatan')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('photos_count')
                    ->label('Foto')
                    ->counts('photos')
                    ->badge()
                    ->color('info'),

                TextColumn::make('reports_count')
                    ->label('Laporan')
                    ->counts('reports')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('allowed_students_count')
                    ->label('Siswa Diizinkan')
                    ->counts('allowedStudents')
                    ->badge()
                    ->color('success'),

                IconColumn::make('allow_upload')
                    ->label('Upload')
                    ->boolean(),

                IconColumn::make('is_published')
                    ->label('Publik')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PhotosRelationManager::class,
            RelationManagers\ReportsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit'   => Pages\EditGallery::route('/{record}/edit'),
        ];
    }
}
