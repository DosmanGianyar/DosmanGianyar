<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TujuanPembelajaranResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\TujuanPembelajaran;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TujuanPembelajaranResource extends Resource
{
    protected static ?string $model = TujuanPembelajaran::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kurikulum';
    protected static ?string                 $navigationLabel = 'Tujuan Pembelajaran';
    protected static ?string                 $modelLabel       = 'Tujuan Pembelajaran';
    protected static ?string                 $pluralModelLabel = 'Tujuan Pembelajaran';
    protected static ?int                    $navigationSort   = 10;

    public static function canAccess(): bool { return AdminAccess::can('Kurikulum'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name', fn ($query) => $query->whereIn('role', ['guru', 'admin']))
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('code')
                    ->label('Kode TP')
                    ->maxLength(30)
                    ->nullable()
                    ->placeholder('Contoh: TP 1.1'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                Textarea::make('description')
                    ->label('Deskripsi Tujuan Pembelajaran')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull()
                    ->placeholder('Peserta didik mampu...'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(70)
                    ->tooltip(fn ($record) => $record->description),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name'),

                SelectFilter::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name'),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTujuanPembelajarans::route('/'),
            'create' => Pages\CreateTujuanPembelajaran::route('/create'),
            'edit'   => Pages\EditTujuanPembelajaran::route('/{record}/edit'),
        ];
    }
}
