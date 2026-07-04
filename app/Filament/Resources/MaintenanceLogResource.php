<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceLogResource\Pages;
use App\Models\Asset;
use App\Models\MaintenanceLog;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MaintenanceLogResource extends Resource
{
    protected static ?string $model = MaintenanceLog::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-wrench-screwdriver';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sarpras';
    protected static ?string                 $navigationLabel = 'Log Perawatan';
    protected static ?string                 $modelLabel       = 'Log Perawatan';
    protected static ?string                 $pluralModelLabel = 'Log Perawatan';

    public static function canAccess(): bool { return AdminAccess::can('Sarpras'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('asset_id')
                ->label('Aset')
                ->options(Asset::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('tech_name')
                ->label('Nama Teknisi')
                ->required()
                ->maxLength(100),

            DatePicker::make('date')
                ->label('Tanggal')
                ->required()
                ->displayFormat('d/m/Y'),

            TextInput::make('cost')
                ->label('Biaya (Rp)')
                ->numeric()
                ->default(0)
                ->prefix('Rp'),

            Textarea::make('note')
                ->label('Catatan Pekerjaan')
                ->rows(4)
                ->required()
                ->columnSpan(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tech_name')
                    ->label('Teknisi')
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(60),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMaintenanceLogs::route('/'),
            'create' => Pages\CreateMaintenanceLog::route('/create'),
            'edit'   => Pages\EditMaintenanceLog::route('/{record}/edit'),
        ];
    }
}
