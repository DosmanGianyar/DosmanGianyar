<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConductCategoryResource\Pages;
use App\Models\ConductCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConductCategoryResource extends Resource
{
    protected static ?string $model = ConductCategory::class;

    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-tag';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Kesiswaan';
    protected static ?string                 $navigationParentItem = 'Prestasi';
    protected static ?string                 $navigationLabel      = 'Kategori Poin';
    protected static ?string                 $modelLabel           = 'Kategori Poin';
    protected static ?string                 $pluralModelLabel     = 'Kategori Poin';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Kategori')
                ->required()
                ->maxLength(100)
                ->columnSpan(2),

            Select::make('type')
                ->label('Tipe')
                ->options(['prestasi' => 'Prestasi', 'pelanggaran' => 'Pelanggaran'])
                ->required()
                ->live(),

            Select::make('context')
                ->label('Konteks')
                ->options([
                    'akademik' => 'Prestasi Akademik',
                    'lomba'    => 'Prestasi Lomba',
                    'kelas'    => 'Pelanggaran Kelas',
                    'sidak'    => 'Pelanggaran Sidak',
                ])
                ->required(),

            TextInput::make('point_value')
                ->label('Nilai Poin')
                ->numeric()
                ->required()
                ->helperText('Positif untuk prestasi, negatif untuk pelanggaran'),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true)
                ->columnSpan(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'prestasi'    => 'success',
                        'pelanggaran' => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('context')
                    ->label('Konteks')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'akademik' => 'info',
                        'lomba'    => 'success',
                        'kelas'    => 'warning',
                        'sidak'    => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'akademik' => 'Akademik',
                        'lomba'    => 'Lomba',
                        'kelas'    => 'Kelas',
                        'sidak'    => 'Sidak',
                        default    => '—',
                    }),

                TextColumn::make('point_value')
                    ->label('Poin')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '') . $state),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('logs_count')
                    ->label('Digunakan')
                    ->counts('logs')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options(['prestasi' => 'Prestasi', 'pelanggaran' => 'Pelanggaran']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListConductCategories::route('/'),
            'create' => Pages\CreateConductCategory::route('/create'),
            'edit'   => Pages\EditConductCategory::route('/{record}/edit'),
        ];
    }
}
