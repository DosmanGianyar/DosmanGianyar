<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentGradeResource\Pages;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StudentGradeResource extends Resource
{
    protected static ?string $model = StudentGrade::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kurikulum';
    protected static ?string                 $navigationLabel = 'Nilai Siswa';
    protected static ?string                 $modelLabel       = 'Nilai';
    protected static ?string                 $pluralModelLabel = 'Nilai Siswa';

    public static function canAccess(): bool { return AdminAccess::can('Kurikulum'); }

    public static function form(Schema $schema): Schema
    {
        $year = StudentGrade::currentAcademicYear();

        return $schema->components([
            Select::make('student_id')
                ->label('Siswa')
                ->options(User::where('role', 'siswa')->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('subject_id')
                ->label('Mata Pelajaran')
                ->options(Subject::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('type')
                ->label('Jenis Penilaian')
                ->options(['UH' => 'Ulangan Harian', 'UTS' => 'UTS', 'UAS' => 'UAS'])
                ->required()
                ->default('UH'),

            TextInput::make('score')
                ->label('Nilai')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->step(0.01)
                ->required(),

            Select::make('semester')
                ->label('Semester')
                ->options([1 => 'Semester 1', 2 => 'Semester 2'])
                ->required()
                ->default(StudentGrade::currentSemester()),

            TextInput::make('academic_year')
                ->label('Tahun Ajaran')
                ->default($year)
                ->placeholder('2025/2026')
                ->required(),

            Textarea::make('notes')
                ->label('Catatan')
                ->rows(2)
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'UTS' => 'warning',
                        'UAS' => 'danger',
                        default => 'primary',
                    }),

                TextColumn::make('score')
                    ->label('Nilai')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn (StudentGrade $r): string =>
                        $r->score >= 80 ? 'success' : ($r->score >= 65 ? 'warning' : 'danger')
                    ),

                TextColumn::make('semester')
                    ->label('Sem.')
                    ->formatStateUsing(fn (int $state): string => "Sem {$state}"),

                TextColumn::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(['UH' => 'Ulangan Harian', 'UTS' => 'UTS', 'UAS' => 'UAS']),

                SelectFilter::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->options(
                        StudentGrade::distinct()->pluck('academic_year', 'academic_year')->toArray()
                        ?: [StudentGrade::currentAcademicYear() => StudentGrade::currentAcademicYear()]
                    ),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['recorded_by'] = Auth::id();
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudentGrades::route('/'),
            'create' => Pages\CreateStudentGrade::route('/create'),
            'edit'   => Pages\EditStudentGrade::route('/{record}/edit'),
        ];
    }
}
