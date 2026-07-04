<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryResource\Pages;
use App\Models\Gallery;
use App\Models\GalleryPhoto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-photo';
    protected static string|\UnitEnum|null   $navigationGroup = 'Humas';
    protected static ?string                 $navigationLabel = 'Galeri Foto';
    protected static ?string                 $modelLabel       = 'Album Galeri';
    protected static ?string                 $pluralModelLabel = 'Galeri Foto';

    public static function canAccess(): bool { return AdminAccess::can('Humas'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Judul Album')
                ->required()
                ->maxLength(150)
                ->columnSpanFull(),

            DatePicker::make('event_date')
                ->label('Tanggal Kegiatan')
                ->native(false)
                ->nullable(),

            Toggle::make('is_published')
                ->label('Publikasikan')
                ->default(false)
                ->helperText('Aktifkan agar album terlihat oleh siswa'),

            Textarea::make('description')
                ->label('Deskripsi Album')
                ->rows(3)
                ->nullable()
                ->columnSpanFull(),

            FileUpload::make('cover_photo')
                ->label('Foto Cover Album')
                ->image()
                ->directory('galleries/covers')
                ->nullable()
                ->columnSpanFull(),

            Repeater::make('photos')
                ->label('Foto Album')
                ->relationship('photos')
                ->schema([
                    FileUpload::make('photo')
                        ->label('Foto')
                        ->image()
                        ->directory('galleries/photos')
                        ->required(),

                    TextInput::make('caption')
                        ->label('Keterangan Foto')
                        ->maxLength(200)
                        ->nullable(),

                    TextInput::make('sort_order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                ])
                ->columns(3)
                ->collapsible()
                ->columnSpanFull()
                ->addActionLabel('+ Tambah Foto'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_photo')
                    ->label('')
                    ->disk('public')
                    ->width(64)
                    ->height(48)
                    ->defaultImageUrl(asset('img/logo_sekolah.png'))
                    ->rounded(),

                TextColumn::make('title')
                    ->label('Judul Album')
                    ->searchable()
                    ->weight('semibold'),

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

                IconColumn::make('is_published')
                    ->label('Publik')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
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
