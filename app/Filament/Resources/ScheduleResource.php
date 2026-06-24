<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kurikulum';
    protected static ?string                 $navigationLabel = 'Jadwal Pelajaran';
    protected static ?string                 $modelLabel       = 'Jadwal';
    protected static ?string                 $pluralModelLabel = 'Jadwal Pelajaran';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('class_id')
                ->label('Kelas')
                ->options(SchoolClass::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('subject_id')
                ->label('Mata Pelajaran')
                ->options(Subject::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('teacher_id')
                ->label('Guru Pengampu')
                ->options(User::where('role', 'guru')->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->nullable()
                ->placeholder('— Pilih Guru —'),

            Select::make('day')
                ->label('Hari')
                ->options([
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                ])
                ->required(),

            Select::make('period')
                ->label('Jam Ke-')
                ->options(array_combine(range(1, 10), range(1, 10)))
                ->required(),

            TimePicker::make('start_time')
                ->label('Mulai')
                ->native(false)
                ->required()
                ->seconds(false),

            TimePicker::make('end_time')
                ->label('Selesai')
                ->native(false)
                ->required()
                ->seconds(false)
                ->after('start_time'),

            TextInput::make('room')
                ->label('Ruangan')
                ->maxLength(50)
                ->nullable()
                ->placeholder('Contoh: XII IPA 1 / Lab Kimia'),

            TextInput::make('academic_year')
                ->label('Tahun Ajaran')
                ->default('2025/2026')
                ->required()
                ->maxLength(9),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schoolClass.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('day')
                    ->label('Hari')
                    ->formatStateUsing(fn (Schedule $r): string => $r->dayName())
                    ->sortable(),

                TextColumn::make('period')
                    ->label('Jam ke-')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('start_time')
                    ->label('Waktu')
                    ->formatStateUsing(fn (Schedule $r): string =>
                        \Carbon\Carbon::parse($r->start_time)->format('H:i') . ' – ' .
                        \Carbon\Carbon::parse($r->end_time)->format('H:i')
                    ),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->placeholder('—')
                    ->limit(25),

                TextColumn::make('room')
                    ->label('Ruangan')
                    ->placeholder('—'),
            ])
            ->defaultSort('day')
            ->filters([
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->options(SchoolClass::orderBy('name')->pluck('name', 'id')),
                SelectFilter::make('day')
                    ->label('Hari')
                    ->options([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat']),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit'   => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
