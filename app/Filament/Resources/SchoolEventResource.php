<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolEventResource\Pages;
use App\Models\Gallery;
use App\Models\SchoolEvent;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SchoolEventResource extends Resource
{
    protected static ?string $model = SchoolEvent::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-megaphone';
    protected static string|\UnitEnum|null   $navigationGroup = 'Humas';
    protected static ?string                 $navigationLabel = 'Agenda Sekolah';
    protected static ?string                 $modelLabel       = 'Agenda';
    protected static ?string                 $pluralModelLabel = 'Agenda Sekolah';

    public static function canAccess(): bool { return AdminAccess::can('Humas'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Judul Agenda')
                ->required()
                ->maxLength(150)
                ->columnSpanFull(),

            DatePicker::make('event_date')
                ->label('Tanggal Mulai')
                ->native(false)
                ->required(),

            DatePicker::make('end_date')
                ->label('Tanggal Selesai')
                ->native(false)
                ->nullable()
                ->after('event_date')
                ->helperText('Kosongkan jika hanya satu hari'),

            Select::make('type')
                ->label('Tipe Kegiatan')
                ->options([
                    'kegiatan' => 'Kegiatan Umum',
                    'lomba'    => 'Perlombaan',
                    'rapat'    => 'Rapat / Pertemuan',
                    'upacara'  => 'Upacara',
                    'wisuda'   => 'Wisuda / Pelepasan',
                    'lainnya'  => 'Lainnya',
                ])
                ->required()
                ->default('kegiatan'),

            TextInput::make('location')
                ->label('Lokasi')
                ->maxLength(150)
                ->nullable()
                ->placeholder('Contoh: Aula SMA Negeri 1 Gianyar'),

            Toggle::make('is_published')
                ->label('Publikasikan')
                ->default(true)
                ->helperText('Aktifkan agar agenda terlihat oleh siswa'),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(4)
                ->nullable()
                ->columnSpanFull(),

            FileUpload::make('cover_photo')
                ->label('Foto Cover')
                ->image()
                ->directory('school-events')
                ->nullable()
                ->columnSpanFull(),

            Select::make('gallery_id')
                ->label('Galeri Terkait')
                ->relationship('gallery', 'title')
                ->options(Gallery::where('is_published', true)->pluck('title', 'id'))
                ->searchable()
                ->nullable()
                ->placeholder('— Pilih galeri (opsional) —')
                ->helperText('Galeri foto yang ditampilkan saat event ini dilihat siswa')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_photo')
                    ->label('')
                    ->disk('public')
                    ->width(56)
                    ->height(40)
                    ->defaultImageUrl(asset('img/logo_sekolah.png'))
                    ->rounded(),

                TextColumn::make('event_date')
                    ->label('Tanggal')
                    ->formatStateUsing(fn (SchoolEvent $r): string =>
                        $r->end_date
                            ? $r->event_date->isoFormat('D MMM') . ' – ' . $r->end_date->isoFormat('D MMM Y')
                            : $r->event_date->isoFormat('D MMM Y')
                    )
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Agenda')
                    ->searchable()
                    ->weight('semibold')
                    ->limit(50),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (SchoolEvent $r): string => $r->typeLabel())
                    ->color(fn (string $state): string => match($state) {
                        'lomba'   => 'warning',
                        'rapat'   => 'gray',
                        'upacara' => 'danger',
                        'wisuda'  => 'info',
                        default   => 'primary',
                    }),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('—')
                    ->limit(30),

                IconColumn::make('is_published')
                    ->label('Publik')
                    ->boolean(),
            ])
            ->defaultSort('event_date')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options(['kegiatan' => 'Kegiatan', 'lomba' => 'Lomba', 'rapat' => 'Rapat', 'upacara' => 'Upacara']),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchoolEvents::route('/'),
            'create' => Pages\CreateSchoolEvent::route('/create'),
            'edit'   => Pages\EditSchoolEvent::route('/{record}/edit'),
        ];
    }
}
