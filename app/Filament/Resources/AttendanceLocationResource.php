<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceLocationResource\Pages;
use App\Models\AttendanceLocation;
use App\Models\SchoolClass;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceLocationResource extends Resource
{
    protected static ?string $model = AttendanceLocation::class;

    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-map-pin';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Kesiswaan';
    protected static ?string                 $navigationParentItem = 'Presensi';
    protected static ?string                 $navigationLabel      = 'Lokasi Presensi';
    protected static ?string                 $modelLabel           = 'Lokasi Presensi';
    protected static ?string                 $pluralModelLabel     = 'Lokasi Presensi';

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Lokasi')
                ->required()
                ->maxLength(100)
                ->placeholder('Contoh: Lapangan Puputan Gianyar'),

            Toggle::make('is_default')
                ->label('Lokasi Default Sekolah')
                ->helperText('Aktifkan jika ini adalah titik presensi utama sekolah. Hanya boleh ada satu.')
                ->live()
                ->default(false),

            TextInput::make('latitude')
                ->label('Latitude')
                ->required()
                ->numeric()
                ->step('any')
                ->placeholder('-8.542304297173528')
                ->helperText('Salin dari Google Maps: klik kanan → "Apa yang ada di sini?"'),

            TextInput::make('longitude')
                ->label('Longitude')
                ->required()
                ->numeric()
                ->step('any')
                ->placeholder('115.33400530740592'),

            TextInput::make('radius_meters')
                ->label('Radius (meter)')
                ->required()
                ->numeric()
                ->default(50)
                ->minValue(10)
                ->maxValue(5000)
                ->suffix('m')
                ->helperText('Jarak maksimal dari titik lokasi agar presensi diterima'),

            // ─── Pengaturan khusus kelas (disembunyikan jika is_default) ────────
            Select::make('class_id')
                ->label('Khusus Kelas')
                ->options(SchoolClass::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->nullable()
                ->placeholder('— Semua Kelas —')
                ->hidden(fn (Get $get): bool => (bool) $get('is_default'))
                ->helperText('Tentukan kelas yang mendapat aturan khusus ini'),

            DateTimePicker::make('start_at')
                ->label('Aktif Dari')
                ->native(false)
                ->hidden(fn (Get $get): bool => (bool) $get('is_default'))
                ->helperText('Tanggal & jam mulai aturan ini berlaku'),

            DateTimePicker::make('end_at')
                ->label('Aktif Sampai')
                ->native(false)
                ->after('start_at')
                ->hidden(fn (Get $get): bool => (bool) $get('is_default'))
                ->helperText('Tanggal & jam berakhirnya aturan ini'),

            Textarea::make('notes')
                ->label('Catatan')
                ->rows(2)
                ->nullable()
                ->placeholder('Contoh: Upacara di Lapangan Puputan, Kunjungan industri ke Denpasar')
                ->columnSpanFull(),

            // ─── Override Waktu Presensi (opsional, khusus kelas) ──────────────
            Section::make('Override Waktu Presensi')
                ->description('Isi jika waktu presensi pada hari ini berbeda dari jadwal normal sekolah. Kosongkan untuk mengikuti pengaturan global.')
                ->icon('heroicon-o-clock')
                ->collapsed()
                ->hidden(fn (Get $get): bool => (bool) $get('is_default'))
                ->schema([
                    TimePicker::make('check_in_open')
                        ->label('Absen Masuk Dibuka')
                        ->seconds(false)
                        ->nullable()
                        ->helperText('Jam mulai bisa absen masuk'),

                    TimePicker::make('check_in_late')
                        ->label('Batas Hadir Tepat Waktu')
                        ->seconds(false)
                        ->nullable()
                        ->helperText('Lewat jam ini status = Terlambat'),

                    TimePicker::make('check_in_close')
                        ->label('Absen Masuk Ditutup')
                        ->seconds(false)
                        ->nullable()
                        ->helperText('Lewat jam ini tidak bisa absen'),

                    TimePicker::make('check_out_open')
                        ->label('Absen Pulang Dibuka')
                        ->seconds(false)
                        ->nullable()
                        ->helperText('Jam paling awal bisa absen pulang'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lokasi')
                    ->searchable()
                    ->weight('semibold'),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-map-pin')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                TextColumn::make('schoolClass.name')
                    ->label('Kelas')
                    ->default('Semua Kelas')
                    ->badge()
                    ->color('info'),

                TextColumn::make('latitude')
                    ->label('Koordinat')
                    ->formatStateUsing(fn ($record) => number_format($record->latitude, 6) . ', ' . number_format($record->longitude, 6))
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('radius_meters')
                    ->label('Radius')
                    ->suffix(' m'),

                TextColumn::make('start_at')
                    ->label('Aktif Dari')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('end_at')
                    ->label('Sampai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),

                TextColumn::make('check_in_open')
                    ->label('Buka')
                    ->formatStateUsing(fn ($state) => $state ? substr($state, 0, 5) : '—')
                    ->badge()
                    ->color('success'),

                TextColumn::make('check_in_close')
                    ->label('Tutup Masuk')
                    ->formatStateUsing(fn ($state) => $state ? substr($state, 0, 5) : '—')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (AttendanceLocation $record): string => $record->statusLabel())
                    ->color(fn (AttendanceLocation $record): string => $record->statusColor()),
            ])
            ->defaultSort('is_default', 'desc')
            ->actions([EditAction::make(), DeleteAction::make()
                ->before(function (AttendanceLocation $record) {
                    if ($record->is_default) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tidak bisa dihapus')
                            ->body('Lokasi default sekolah tidak dapat dihapus.')
                            ->danger()
                            ->send();
                        return false;
                    }
                }),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttendanceLocations::route('/'),
            'create' => Pages\CreateAttendanceLocation::route('/create'),
            'edit'   => Pages\EditAttendanceLocation::route('/{record}/edit'),
        ];
    }
}
