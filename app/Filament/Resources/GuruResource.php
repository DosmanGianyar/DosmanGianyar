<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuruResource\Pages;
use App\Models\Subject;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class GuruResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-briefcase';
    protected static string|\UnitEnum|null   $navigationGroup = 'Manajemen User';
    protected static ?string $navigationLabel = 'Data Guru';
    protected static ?string $modelLabel       = 'Guru / Admin';
    protected static ?string $pluralModelLabel = 'Data Guru';
    protected static ?int    $navigationSort   = 2;

    // ── Scope: hanya guru dan admin ───────────────────────────────────────────

    public static function canAccess(): bool { return auth()->user()?->role === 'admin'; }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('role', ['guru', 'admin']);
    }

    // ── Form ──────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas')->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(100),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('role')
                    ->label('Role')
                    ->options([
                        'guru'  => 'Guru',
                        'admin' => 'Admin',
                    ])
                    ->default('guru')
                    ->required(),

                TextInput::make('phone')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Kosongkan jika tidak ingin mengubah password'),
            ])->columns(2),

            Section::make('Data Kepegawaian')->schema([
                TextInput::make('nip')
                    ->label('NIP')
                    ->maxLength(30)
                    ->unique(ignoreRecord: true)
                    ->placeholder('198001012006041001'),

                Select::make('subjects')
                    ->label('Mata Pelajaran (bisa lebih dari satu)')
                    ->relationship('subjects', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'admin' => 'danger',
                        'guru'  => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin' => 'Admin',
                        'guru'  => 'Guru',
                        default => $state,
                    }),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->placeholder('—')
                    ->fontFamily('mono')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('subjects.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label('No. HP')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Filter Role')
                    ->options([
                        'guru'  => 'Guru',
                        'admin' => 'Admin',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGurus::route('/'),
            'create' => Pages\CreateGuru::route('/create'),
            'edit'   => Pages\EditGuru::route('/{record}/edit'),
        ];
    }
}
