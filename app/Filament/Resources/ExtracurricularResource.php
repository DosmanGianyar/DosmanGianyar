<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtracurricularResource\Pages;
use App\Filament\Resources\ExtracurricularResource\RelationManagers;
use App\Models\Extracurricular;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ExtracurricularResource extends Resource
{
    protected static ?string $model = Extracurricular::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kesiswaan';
    protected static ?string                 $navigationLabel = 'Ekstrakurikuler';
    protected static ?string                 $modelLabel      = 'Ekstrakurikuler';
    protected static ?string                 $pluralModelLabel = 'Ekstrakurikuler';
    protected static ?int                    $navigationSort  = 10;

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Ekstrakurikuler')
                ->required()
                ->maxLength(100)
                ->columnSpanFull(),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->nullable()
                ->columnSpanFull(),

            Select::make('pembina_id')
                ->label('Guru Pembina')
                ->options(User::where('role', 'guru')->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->nullable()
                ->placeholder('— Pilih pembina —'),

            TextInput::make('max_members')
                ->label('Kuota Anggota')
                ->numeric()
                ->minValue(1)
                ->nullable()
                ->placeholder('Kosongkan = tidak terbatas')
                ->helperText('Maks. anggota aktif yang dapat diterima'),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true)
                ->helperText('Nonaktifkan agar tidak muncul di aplikasi siswa'),

            FileUpload::make('logo')
                ->label('Logo / Foto')
                ->image()
                ->directory('extracurriculars')
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('')
                    ->disk('public')
                    ->width(44)
                    ->height(44)
                    ->rounded()
                    ->defaultImageUrl(asset('img/logo_sekolah.png')),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->weight('semibold')
                    ->sortable(),

                TextColumn::make('pembina.name')
                    ->label('Pembina')
                    ->placeholder('—')
                    ->limit(30),

                TextColumn::make('active_members_count')
                    ->label('Anggota')
                    ->counts('activeMembers')
                    ->badge()
                    ->color('success'),

                TextColumn::make('pending_members_count')
                    ->label('Permintaan')
                    ->counts('pendingMembers')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\MembersRelationManager::class,
            RelationManagers\SessionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExtracurriculars::route('/'),
            'create' => Pages\CreateExtracurricular::route('/create'),
            'edit'   => Pages\EditExtracurricular::route('/{record}/edit'),
        ];
    }
}
