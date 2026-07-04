<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use App\Models\Room;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cube';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sarpras';
    protected static ?string                 $navigationLabel = 'Inventaris Aset';
    protected static ?string                 $modelLabel      = 'Aset';
    protected static ?string                 $pluralModelLabel = 'Data Aset';

    public static function canAccess(): bool { return AdminAccess::can('Sarpras'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Aset')
                ->required()
                ->maxLength(150)
                ->columnSpan(2),

            Select::make('category')
                ->label('Kategori')
                ->options([
                    'furniture'    => 'Furnitur',
                    'elektronik'   => 'Elektronik',
                    'olahraga'     => 'Olahraga',
                    'lab'          => 'Lab',
                    'perpustakaan' => 'Perpustakaan',
                    'lain'         => 'Lain-lain',
                ])
                ->required(),

            Select::make('condition')
                ->label('Kondisi')
                ->options([
                    'baik'         => 'Baik',
                    'rusak_ringan' => 'Rusak Ringan',
                    'rusak_berat'  => 'Rusak Berat',
                ])
                ->required()
                ->default('baik'),

            Select::make('room_id')
                ->label('Ruangan')
                ->options(Room::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->nullable(),

            TextInput::make('quantity')
                ->label('Jumlah')
                ->numeric()
                ->default(1)
                ->minValue(1),

            TextInput::make('purchase_year')
                ->label('Tahun Pengadaan')
                ->numeric()
                ->minValue(2000)
                ->maxValue(now()->year),

            FileUpload::make('photo')
                ->label('Foto Aset')
                ->image()
                ->directory('assets')
                ->nullable()
                ->columnSpan(2),

            Textarea::make('description')
                ->label('Keterangan')
                ->rows(3)
                ->columnSpan(2),

            Placeholder::make('qr_preview')
                ->label('QR Code')
                ->content(fn ($record) => $record
                    ? new HtmlString(
                        '<img src="' . ($record->qrImageUrl() ?? '') . '" class="w-32 h-32 border border-gray-200 rounded-lg">'
                        . '<p class="text-xs text-gray-500 mt-1">' . $record->qr_code . '</p>'
                    )
                    : new HtmlString('<p class="text-sm text-gray-400">QR dibuat otomatis setelah disimpan.</p>')
                )
                ->columnSpan(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=Aset&background=3b82f6&color=fff')
                    ->size(40),

                TextColumn::make('name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (Asset $record) => $record->categoryLabel()),

                TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'baik'         => 'success',
                        'rusak_ringan' => 'warning',
                        'rusak_berat'  => 'danger',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn (Asset $record) => $record->conditionLabel()),

                TextColumn::make('room.name')
                    ->label('Ruangan')
                    ->placeholder('—'),

                TextColumn::make('quantity')
                    ->label('Jml')
                    ->sortable(),

                TextColumn::make('damage_reports_count')
                    ->label('Kerusakan')
                    ->counts('damageReports')
                    ->badge()
                    ->color('danger'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'furniture'    => 'Furnitur',
                        'elektronik'   => 'Elektronik',
                        'olahraga'     => 'Olahraga',
                        'lab'          => 'Lab',
                        'perpustakaan' => 'Perpustakaan',
                        'lain'         => 'Lain-lain',
                    ]),
                SelectFilter::make('condition')
                    ->label('Kondisi')
                    ->options([
                        'baik'         => 'Baik',
                        'rusak_ringan' => 'Rusak Ringan',
                        'rusak_berat'  => 'Rusak Berat',
                    ]),
                SelectFilter::make('room_id')
                    ->label('Ruangan')
                    ->relationship('room', 'name'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit'   => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}
