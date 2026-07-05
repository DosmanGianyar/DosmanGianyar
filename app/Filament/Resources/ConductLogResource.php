<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConductLogResource\Pages;
use App\Models\ConductCategory;
use App\Models\ConductLog;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConductLogResource extends Resource
{
    protected static ?string $model = ConductLog::class;

    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Kesiswaan';
    protected static ?string                 $navigationLabel      = 'Catatan Perilaku Siswa';
    protected static ?string                 $modelLabel           = 'Catatan Perilaku';
    protected static ?string                 $pluralModelLabel     = 'Catatan Perilaku Siswa';
    protected static ?int                    $navigationSort       = 1;

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Dasar')->schema([
                Select::make('student_id')
                    ->label('Siswa')
                    ->options(
                        User::where('role', 'siswa')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn ($u) => [$u->id => "{$u->name} ({$u->nis})"])
                    )
                    ->searchable()
                    ->required(),

                Select::make('teacher_id')
                    ->label('Dilaporkan Oleh')
                    ->options(
                        User::whereIn('role', ['guru', 'admin'])
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required()
                    ->default(fn () => auth()->id()),
            ])->columns(2),

            Section::make('Jenis Catatan')->schema([
                Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'pelanggaran' => 'Catatan Negatif',
                        'prestasi'    => 'Catatan Positif',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('category_id', null)),

                // ── Catatan Negatif ─────────────────────────────────────

                Select::make('severity')
                    ->label('Tingkat')
                    ->options([
                        'ringan' => 'Ringan',
                        'sedang' => 'Sedang',
                        'berat'  => 'Berat',
                    ])
                    ->visible(fn ($get) => $get('type') === 'pelanggaran')
                    ->required(fn ($get) => $get('type') === 'pelanggaran'),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->visible(fn ($get) => $get('type') === 'pelanggaran')
                    ->required(fn ($get) => $get('type') === 'pelanggaran')
                    ->columnSpan(fn ($get) => $get('type') === 'pelanggaran' ? 2 : 1),

                // ── Catatan Positif ────────────────────────────────────

                Select::make('category_id')
                    ->label('Kategori Perilaku Positif')
                    ->options(
                        ConductCategory::where('type', 'prestasi')
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->visible(fn ($get) => $get('type') === 'prestasi')
                    ->required(fn ($get) => $get('type') === 'prestasi')
                    ->columnSpanFull(),

            ])->columns(2),

            Section::make('Catatan Tambahan')->schema([
                Textarea::make('note')
                    ->label('Catatan (opsional)')
                    ->rows(2)
                    ->columnSpan(2),
            ])->columns(2)->collapsed(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->student?->nis ?? ''),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'pelanggaran' => 'danger',
                        'prestasi'    => 'success',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pelanggaran' => 'Catatan Negatif',
                        'prestasi'    => 'Catatan Positif',
                        default       => '—',
                    }),

                TextColumn::make('sub_type')
                    ->label('Tingkat / Sub')
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->severity !== null            => match ($record->severity) {
                            'berat'  => 'danger',
                            default  => 'warning',
                        },
                        $record->prestasi_type === 'lomba'    => 'info',
                        $record->prestasi_type === 'perilaku' => 'success',
                        default => 'gray',
                    })
                    ->getStateUsing(fn ($record) => match (true) {
                        $record->severity !== null            => ucfirst($record->severity),
                        $record->prestasi_type === 'lomba'    => 'Prestasi Lomba',
                        $record->prestasi_type === 'perilaku' => 'Perilaku',
                        default => '—',
                    }),

                TextColumn::make('detail')
                    ->label('Detail')
                    ->getStateUsing(fn ($record) => match (true) {
                        $record->type === 'pelanggaran'        => $record->description,
                        $record->prestasi_type === 'lomba'     => $record->lomba_name,
                        default => $record->category?->name ?? $record->description,
                    })
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'pelanggaran' => 'Catatan Negatif',
                        'prestasi'    => 'Catatan Positif',
                    ]),

                SelectFilter::make('severity')
                    ->label('Tingkat Catatan Negatif')
                    ->options([
                        'ringan' => 'Ringan',
                        'sedang' => 'Sedang',
                        'berat'  => 'Berat',
                    ]),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListConductLogs::route('/'),
            'create' => Pages\CreateConductLog::route('/create'),
            'edit'   => Pages\EditConductLog::route('/{record}/edit'),
        ];
    }
}
