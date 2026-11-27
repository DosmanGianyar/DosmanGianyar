<?php

namespace App\Filament\Resources\ExtracurricularResource\RelationManagers;

use App\Models\ExtracurricularGrade;
use App\Models\ExtracurricularMember;
use App\Models\StudentGrade;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GradesRelationManager extends RelationManager
{
    protected static string $relationship = 'grades';
    protected static ?string $title = 'Penilaian Anggota';
    protected static string|\BackedEnum|null $icon = 'heroicon-o-academic-cap';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Siswa Anggota Aktif')
                ->options(function () {
                    $extra = $this->getOwnerRecord();

                    $fromMembers = ExtracurricularMember::with(['user:id,name,nis,class_id', 'user.schoolClass:id,name'])
                        ->where('extracurricular_id', $extra->id)
                        ->where('status', 'active')
                        ->get()
                        ->mapWithKeys(function (ExtracurricularMember $m) {
                            if (! $m->user) {
                                return [];
                            }
                            $kelas = $m->user->schoolClass?->name ?? '—';
                            return [$m->user_id => "{$m->user->name} ({$kelas})"];
                        });

                    $fromPivot = $extra->students()
                        ->with('schoolClass:id,name')
                        ->get(['users.id', 'users.name', 'users.class_id'])
                        ->mapWithKeys(function ($u) {
                            $kelas = $u->schoolClass?->name ?? '—';
                            return [$u->id => "{$u->name} ({$kelas})"];
                        });

                    return $fromMembers->union($fromPivot)->sort()->all();
                })
                ->searchable()
                ->required(),

            TextInput::make('academic_year')
                ->label('Tahun Ajaran')
                ->default(fn () => StudentGrade::currentAcademicYear())
                ->required()
                ->maxLength(9)
                ->placeholder('2026/2027'),

            Select::make('semester')
                ->label('Semester')
                ->options([
                    1 => 'Semester 1 (Ganjil)',
                    2 => 'Semester 2 (Genap)',
                ])
                ->default(fn () => StudentGrade::currentSemester())
                ->required(),

            Select::make('predicate')
                ->label('Predikat')
                ->options([
                    'A' => 'A - Sangat Baik',
                    'B' => 'B - Baik',
                    'C' => 'C - Cukup',
                    'D' => 'D - Kurang',
                ])
                ->required(),

            Textarea::make('description')
                ->label('Deskripsi Rapor (Opsional)')
                ->rows(3)
                ->maxLength(500)
                ->nullable()
                ->placeholder('Contoh: Aktif berpartisipasi dan menunjukkan kedisiplinan yang baik.')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),

                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('user.nis')
                    ->label('NIS')
                    ->placeholder('—'),

                TextColumn::make('user.schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—'),

                TextColumn::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->sortable(),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? '1 (Ganjil)' : '2 (Genap)')
                    ->sortable(),

                TextColumn::make('predicate')
                    ->label('Predikat')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state . ' · ' . (ExtracurricularGrade::labelFor($state) ?? $state))
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        'D' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('grader.name')
                    ->label('Penilai')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->options(fn () => ExtracurricularGrade::where('extracurricular_id', $this->getOwnerRecord()->id)
                        ->distinct()
                        ->pluck('academic_year', 'academic_year')
                        ->all()),

                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        1 => 'Semester 1 (Ganjil)',
                        2 => 'Semester 2 (Genap)',
                    ]),

                SelectFilter::make('predicate')
                    ->label('Predikat')
                    ->options([
                        'A' => 'A - Sangat Baik',
                        'B' => 'B - Baik',
                        'C' => 'C - Cukup',
                        'D' => 'D - Kurang',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Nilai')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['graded_by'] = Auth::id();
                        return $data;
                    }),

                TableAction::make('cetak_nilai_pdf')
                    ->label('Cetak Nilai PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->form([
                        TextInput::make('academic_year')
                            ->label('Tahun Ajaran')
                            ->default(fn () => StudentGrade::currentAcademicYear())
                            ->required()
                            ->maxLength(9),
                        Select::make('semester')
                            ->label('Semester')
                            ->options([
                                1 => 'Semester 1 (Ganjil)',
                                2 => 'Semester 2 (Genap)',
                            ])
                            ->default(fn () => StudentGrade::currentSemester())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $url = route('extracurricular.grades.pdf', [
                            'extracurricular' => $this->getOwnerRecord()->id,
                            'academic_year'   => $data['academic_year'],
                            'semester'        => $data['semester'],
                        ]);

                        return redirect()->away($url);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['graded_by'] = Auth::id();
                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
