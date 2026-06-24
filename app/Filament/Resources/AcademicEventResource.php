<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicEventResource\Pages;
use App\Models\AcademicEvent;
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
use Illuminate\Support\Facades\Auth;

class AcademicEventResource extends Resource
{
    protected static ?string $model = AcademicEvent::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kurikulum';
    protected static ?string                 $navigationLabel = 'Kalender Akademik';
    protected static ?string                 $modelLabel       = 'Event Akademik';
    protected static ?string                 $pluralModelLabel = 'Kalender Akademik';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Judul Kegiatan')
                ->required()
                ->maxLength(150)
                ->columnSpanFull(),

            DatePicker::make('start_date')
                ->label('Tanggal Mulai')
                ->native(false)
                ->required(),

            DatePicker::make('end_date')
                ->label('Tanggal Selesai')
                ->native(false)
                ->required()
                ->after('start_date'),

            Select::make('type')
                ->label('Tipe')
                ->options([
                    'uts'      => 'UTS (Ulangan Tengah Semester)',
                    'uas'      => 'UAS (Ulangan Akhir Semester)',
                    'ujian'    => 'Ujian / Ulangan',
                    'libur'    => 'Hari Libur',
                    'kegiatan' => 'Kegiatan Sekolah',
                    'upacara'  => 'Upacara',
                    'lainnya'  => 'Lainnya',
                ])
                ->required()
                ->default('kegiatan'),

            Select::make('color')
                ->label('Warna Label')
                ->options([
                    'blue'   => 'Biru (default)',
                    'green'  => 'Hijau',
                    'red'    => 'Merah',
                    'yellow' => 'Kuning',
                    'purple' => 'Ungu',
                    'orange' => 'Oranye',
                ])
                ->required()
                ->default('blue'),

            Textarea::make('description')
                ->label('Keterangan')
                ->rows(3)
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('start_date')
                    ->label('Tanggal')
                    ->formatStateUsing(fn (AcademicEvent $r): string =>
                        $r->start_date->isSameDay($r->end_date)
                            ? $r->start_date->isoFormat('D MMM Y')
                            : $r->start_date->isoFormat('D MMM') . ' – ' . $r->end_date->isoFormat('D MMM Y')
                    )
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Kegiatan')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (AcademicEvent $r): string => $r->typeLabel())
                    ->color(fn (string $state): string => match($state) {
                        'uts', 'uas', 'ujian' => 'warning',
                        'libur'               => 'danger',
                        'kegiatan'            => 'info',
                        'upacara'             => 'success',
                        default               => 'gray',
                    }),

                TextColumn::make('color')
                    ->label('Warna')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'green'  => 'success',
                        'red'    => 'danger',
                        'yellow' => 'warning',
                        'purple' => 'info',
                        default  => 'primary',
                    }),
            ])
            ->defaultSort('start_date')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'uts' => 'UTS', 'uas' => 'UAS', 'ujian' => 'Ujian',
                        'libur' => 'Libur', 'kegiatan' => 'Kegiatan', 'upacara' => 'Upacara',
                    ]),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAcademicEvents::route('/'),
            'create' => Pages\CreateAcademicEvent::route('/create'),
            'edit'   => Pages\EditAcademicEvent::route('/{record}/edit'),
        ];
    }
}
