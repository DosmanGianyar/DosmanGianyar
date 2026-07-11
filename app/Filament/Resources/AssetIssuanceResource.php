<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetIssuanceResource\Pages;
use App\Models\Asset;
use App\Models\AssetIssuance;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetIssuanceResource extends Resource
{
    protected static ?string $model = AssetIssuance::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sarpras';
    protected static ?string                 $navigationLabel = 'Barang Keluar';
    protected static ?string                 $modelLabel      = 'Barang Keluar';
    protected static ?string                 $pluralModelLabel = 'Barang Keluar';

    public static function canAccess(): bool { return AdminAccess::can('Sarpras'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Barang Keluar')->schema([
                Select::make('asset_id')
                    ->label('Aset / Barang')
                    ->options(
                        Asset::orderBy('name')->get()->mapWithKeys(
                            fn (Asset $a) => [$a->id => "{$a->name} — stok: {$a->quantity}"]
                        )
                    )
                    ->searchable()
                    ->live()
                    ->required(),

                Select::make('user_id')
                    ->label('Penerima')
                    ->options(
                        User::whereIn('role', ['siswa', 'pengelola', 'guru'])
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (User $u) => [
                                $u->id => $u->name . ' (' . ($u->nisn ?? $u->nis ?? $u->nip ?? ucfirst($u->role)) . ')',
                            ])
                    )
                    ->searchable()
                    ->required(),

                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(fn ($get) => Asset::find($get('asset_id'))?->quantity ?? 1)
                    ->required(),

                TextInput::make('purpose')
                    ->label('Keperluan / Keterangan')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                Hidden::make('issued_by')->default(fn () => auth()->id()),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('asset.category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (AssetIssuance $record) => $record->asset?->categoryLabel()),

                TextColumn::make('user.name')
                    ->label('Penerima')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->sortable(),

                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->limit(50),

                TextColumn::make('issuer.name')
                    ->label('Diproses Oleh'),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssetIssuances::route('/'),
            'create' => Pages\CreateAssetIssuance::route('/create'),
        ];
    }
}
