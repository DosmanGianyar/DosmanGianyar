<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolClassResource\Pages;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null   $navigationGroup = 'Manajemen User';
    protected static ?string $navigationLabel = 'Data Kelas';
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Data Kelas';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool { return auth()->user()?->role === 'admin'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kelas')->schema([
                TextInput::make('name')
                    ->label('Nama Kelas')
                    ->placeholder('X MIPA 1')
                    ->required()
                    ->maxLength(50),

                Select::make('grade')
                    ->label('Tingkat')
                    ->options([
                        '10' => 'Tingkat 10',
                        '11' => 'Tingkat 11',
                        '12' => 'Tingkat 12',
                    ])
                    ->required(),

                TextInput::make('major')
                    ->label('Jurusan / Program')
                    ->placeholder('MIPA, IPS, Bahasa, ...')
                    ->maxLength(50),

                Select::make('homeroom_teacher_id')
                    ->label('Wali Kelas')
                    ->options(
                        User::where('role', 'guru')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade')
                    ->label('Tingkat')
                    ->badge()
                    ->sortable(),

                TextColumn::make('major')
                    ->label('Jurusan')
                    ->placeholder('—'),

                TextColumn::make('homeroomTeacher.name')
                    ->label('Wali Kelas')
                    ->placeholder('Belum ditentukan'),

                TextColumn::make('students_count')
                    ->label('Jumlah Siswa')
                    ->counts('students')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                SelectFilter::make('grade')
                    ->label('Tingkat')
                    ->options([
                        '10' => 'Tingkat 10',
                        '11' => 'Tingkat 11',
                        '12' => 'Tingkat 12',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('grade');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'edit'   => Pages\EditSchoolClass::route('/{record}/edit'),
        ];
    }
}
