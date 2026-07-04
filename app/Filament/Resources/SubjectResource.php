<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Subject;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-book-open';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kurikulum';
    protected static ?string                 $navigationLabel = 'Mata Pelajaran';
    protected static ?string                 $modelLabel       = 'Mata Pelajaran';
    protected static ?string                 $pluralModelLabel = 'Mata Pelajaran';

    public static function canAccess(): bool { return AdminAccess::can('Kurikulum'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Mata Pelajaran')
                ->required()
                ->maxLength(100)
                ->placeholder('Contoh: Matematika'),

            TextInput::make('code')
                ->label('Kode Mapel')
                ->maxLength(20)
                ->nullable()
                ->placeholder('Contoh: MTK'),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->placeholder('—'),

                TextColumn::make('schedules_count')
                    ->label('Jadwal')
                    ->counts('schedules')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('name')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit'   => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
