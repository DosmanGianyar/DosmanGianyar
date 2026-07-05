<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherJournalResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\TeacherJournal;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TeacherJournalResource extends Resource
{
    protected static ?string $model = TeacherJournal::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kurikulum';
    protected static ?string                 $navigationLabel = 'Jurnal Mengajar';
    protected static ?string                 $modelLabel       = 'Jurnal Mengajar';
    protected static ?string                 $pluralModelLabel = 'Jurnal Mengajar';
    protected static ?int                    $navigationSort   = 11;

    public static function canAccess(): bool { return AdminAccess::can('Kurikulum'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('schoolClass.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('period')
                    ->label('Jam')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->period_end && $record->period_end > $record->period
                            ? "Jam {$record->period}–{$record->period_end}"
                            : "Jam {$record->period}"
                    ),

                TextColumn::make('tp.code')
                    ->label('Kode TP')
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),

                TextColumn::make('material')
                    ->label('Materi')
                    ->limit(50)
                    ->placeholder('—')
                    ->tooltip(fn ($record) => $record->material),

                TextColumn::make('activity')
                    ->label('Aktivitas')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name'),

                SelectFilter::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name'),

                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('schoolClass', 'name'),
            ])
            ->recordActions([
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherJournals::route('/'),
        ];
    }
}
