<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use App\Models\SchoolClass;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static string|\BackedEnum|null $navigationIcon      = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null   $navigationGroup     = 'Kesiswaan';
    protected static ?string                 $navigationParentItem = 'Presensi';
    protected static ?string                 $navigationLabel     = 'Hari Libur & Sekolah Khusus';
    protected static ?string                 $modelLabel          = 'Hari Libur';
    protected static ?string                 $pluralModelLabel    = 'Hari Libur & Sekolah Khusus';
    protected static ?int                    $navigationSort      = 5;

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi')->schema([

                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->native(false)
                    ->displayFormat('d F Y')
                    ->helperText('Pilih tanggal yang akan dikonfigurasi'),

                TextInput::make('description')
                    ->label('Nama / Keterangan')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('Contoh: Hari Raya Idul Fitri, Pentas Seni, Rapat Koordinasi'),

            ])->columns(2),

            Section::make('Jenis & Cakupan')->schema([

                Radio::make('type')
                    ->label('Jenis Hari')
                    ->required()
                    ->default('libur')
                    ->options([
                        'libur'          => 'Hari Libur — siswa tidak perlu hadir',
                        'sekolah_khusus' => 'Hari Sekolah Khusus — siswa wajib hadir meskipun hari Minggu/libur',
                    ])
                    ->descriptions([
                        'libur'          => 'Absen tidak diperlukan pada hari ini (cth: hari raya, cuti bersama).',
                        'sekolah_khusus' => 'Hari khusus dimana kelas tertentu tetap masuk meskipun hari libur/akhir pekan.',
                    ]),

                Radio::make('applies_to')
                    ->label('Berlaku Untuk')
                    ->required()
                    ->default('semua')
                    ->live()
                    ->options([
                        'semua'          => 'Semua Kelas',
                        'kelas_tertentu' => 'Kelas Tertentu (pilih di bawah)',
                    ]),

                CheckboxList::make('schoolClasses')
                    ->label('Pilih Kelas')
                    ->relationship('schoolClasses', 'name')
                    ->options(SchoolClass::orderBy('grade')->orderBy('name')->pluck('name', 'id'))
                    ->columns(3)
                    ->searchable()
                    ->bulkToggleable()
                    ->helperText('Centang kelas yang terkena dampak pengaturan ini')
                    ->visible(fn (Get $get) => $get('applies_to') === 'kelas_tertentu')
                    ->required(fn (Get $get) => $get('applies_to') === 'kelas_tertentu'),

            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->wrap(),

                BadgeColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'libur'          => 'Hari Libur',
                        'sekolah_khusus' => 'Sekolah Khusus',
                        default          => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'libur'          => 'danger',
                        'sekolah_khusus' => 'success',
                        default          => 'gray',
                    }),

                BadgeColumn::make('applies_to')
                    ->label('Berlaku')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'semua'          => 'Semua Kelas',
                        'kelas_tertentu' => 'Kelas Tertentu',
                        default          => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'semua'          => 'gray',
                        'kelas_tertentu' => 'info',
                        default          => 'gray',
                    }),

                TextColumn::make('schoolClasses.name')
                    ->label('Kelas')
                    ->badge()
                    ->separator(',')
                    ->color('info')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'libur'          => 'Hari Libur',
                        'sekolah_khusus' => 'Sekolah Khusus',
                    ]),

                SelectFilter::make('applies_to')
                    ->label('Berlaku')
                    ->options([
                        'semua'          => 'Semua Kelas',
                        'kelas_tertentu' => 'Kelas Tertentu',
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit'   => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
