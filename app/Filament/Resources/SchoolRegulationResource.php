<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolRegulationResource\Pages;
use App\Models\SchoolRegulation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SchoolRegulationResource extends Resource
{
    protected static ?string $model = SchoolRegulation::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-scale';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kesiswaan';
    protected static ?string $navigationLabel   = 'Tata Tertib Sekolah';
    protected static ?string $modelLabel        = 'Peraturan';
    protected static ?string $pluralModelLabel  = 'Tata Tertib Sekolah';
    protected static ?int    $navigationSort    = 20;

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category')
                ->label('Kategori')
                ->options(SchoolRegulation::categories())
                ->required()
                ->native(false),

            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(0)
                ->helperText('Angka kecil = tampil lebih dulu'),

            TextInput::make('title')
                ->label('Judul Peraturan')
                ->required()
                ->maxLength(200)
                ->columnSpanFull(),

            Textarea::make('content')
                ->label('Isi / Penjelasan')
                ->required()
                ->rows(5)
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true)
                ->helperText('Nonaktifkan agar tidak tampil di aplikasi siswa'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (SchoolRegulation $r) => $r->categoryLabel())
                    ->color(fn (string $state) => match($state) {
                        'kehadiran'  => 'info',
                        'berpakaian' => 'warning',
                        'perilaku'   => 'success',
                        'larangan'   => 'danger',
                        default      => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(40),

                TextColumn::make('title')
                    ->label('Judul Peraturan')
                    ->searchable()
                    ->weight('semibold')
                    ->wrap(),

                TextColumn::make('content')
                    ->label('Isi')
                    ->limit(60)
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(SchoolRegulation::categories()),

                TernaryFilter::make('is_active')
                    ->label('Status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort(fn ($query) => $query->orderBy('category')->orderBy('sort_order'));
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchoolRegulations::route('/'),
            'create' => Pages\CreateSchoolRegulation::route('/create'),
            'edit'   => Pages\EditSchoolRegulation::route('/{record}/edit'),
        ];
    }
}
