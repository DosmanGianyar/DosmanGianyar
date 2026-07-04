<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-building-office-2';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sarpras';
    protected static ?string                 $navigationLabel = 'Ruangan';
    protected static ?string                 $modelLabel      = 'Ruangan';
    protected static ?string                 $pluralModelLabel = 'Data Ruangan';

    public static function canAccess(): bool { return AdminAccess::can('Sarpras'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Ruangan')
                ->required()
                ->maxLength(100),

            TextInput::make('building')
                ->label('Gedung / Lantai')
                ->maxLength(100),

            TextInput::make('capacity')
                ->label('Kapasitas')
                ->numeric()
                ->minValue(1),

            Textarea::make('description')
                ->label('Keterangan')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Ruangan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('building')
                    ->label('Gedung')
                    ->placeholder('—'),

                TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->placeholder('—')
                    ->suffix(' orang'),

                TextColumn::make('assets_count')
                    ->label('Jumlah Aset')
                    ->counts('assets')
                    ->badge()
                    ->color('info'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit'   => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
