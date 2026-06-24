<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AchievementCategoryResource\Pages;
use App\Models\AchievementCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AchievementCategoryResource extends Resource
{
    protected static ?string $model = AchievementCategory::class;

    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-tag';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Kesiswaan';
    protected static ?string                 $navigationParentItem = 'Prestasi';
    protected static ?string $modelLabel         = 'Kategori Prestasi';
    protected static ?string $pluralModelLabel  = 'Kategori Prestasi';
    protected static ?string $navigationLabel      = 'Kategori Prestasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Kategori')
                ->required()
                ->maxLength(100),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(2)
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->placeholder('—'),

                TextColumn::make('achievements_count')
                    ->label('Total Prestasi')
                    ->counts('achievements'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAchievementCategories::route('/'),
            'create' => Pages\CreateAchievementCategory::route('/create'),
            'edit'   => Pages\EditAchievementCategory::route('/{record}/edit'),
        ];
    }
}
