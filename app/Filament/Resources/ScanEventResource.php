<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScanEventResource\Pages;
use App\Models\ScanEvent;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
class ScanEventResource extends Resource
{
    protected static ?string $model = ScanEvent::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-qr-code';
    protected static string|\UnitEnum|null   $navigationGroup = 'Kesiswaan';
    protected static ?string                 $navigationLabel = 'Absen QR Kegiatan';
    protected static ?string                 $modelLabel       = 'Kegiatan';
    protected static ?string                 $pluralModelLabel = 'Absen QR Kegiatan';
    protected static ?int                    $navigationSort  = 25;

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Judul Kegiatan')
                ->required()
                ->maxLength(255)
                ->placeholder('Contoh: Upacara Hari Kemerdekaan'),

            DatePicker::make('date')
                ->label('Tanggal')
                ->required()
                ->default(now()),

            TextInput::make('location')
                ->label('Lokasi / Tempat')
                ->nullable()
                ->maxLength(255)
                ->placeholder('Contoh: Aula Sekolah'),

            Textarea::make('description')
                ->label('Keterangan')
                ->nullable()
                ->rows(2),

            Toggle::make('is_active')
                ->label('Kegiatan Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('attendances_count')
                    ->label('Peserta')
                    ->counts('attendances')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->recordActions([
                Action::make('scanner')
                    ->label('Buka Scanner')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->url(fn (ScanEvent $record) => route('admin.scan-events.scanner', $record))
                    ->openUrlInNewTab(),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListScanEvents::route('/'),
            'create' => Pages\CreateScanEvent::route('/create'),
            'edit'   => Pages\EditScanEvent::route('/{record}/edit'),
        ];
    }

}
