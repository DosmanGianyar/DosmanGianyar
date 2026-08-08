<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherJournalResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\TeacherJournal;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
        return $schema->components([
            Select::make('class_id')
                ->label('Kelas')
                ->relationship('schoolClass', 'name')
                ->required(),

            Select::make('subject_id')
                ->label('Mata Pelajaran')
                ->relationship('subject', 'name')
                ->nullable(),

            Select::make('tp_id')
                ->label('Tujuan Pembelajaran (TP)')
                ->relationship('tp', 'description')
                ->getOptionLabelFromRecordUsing(fn ($record) => ($record->code ? "[{$record->code}] " : '') . $record->description)
                ->nullable(),

            DatePicker::make('date')
                ->label('Tanggal')
                ->required(),

            TextInput::make('period')
                ->label('Jam Ke-')
                ->numeric()
                ->nullable(),

            TextInput::make('period_end')
                ->label('Jam Sampai')
                ->numeric()
                ->nullable(),

            Textarea::make('material')
                ->label('Materi')
                ->required()
                ->rows(3),

            Textarea::make('activity')
                ->label('Aktivitas Pembelajaran')
                ->required()
                ->rows(3),

            Textarea::make('notes')
                ->label('Catatan Tambahan')
                ->nullable()
                ->rows(2),
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
                EditAction::make()->iconButton(),
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
