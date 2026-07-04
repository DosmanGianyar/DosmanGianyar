<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DamageReportResource\Pages;
use App\Models\DamageReport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DamageReportResource extends Resource
{
    protected static ?string $model = DamageReport::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-exclamation-triangle';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sarpras';
    protected static ?string                 $navigationLabel = 'Laporan Kerusakan';
    protected static ?string                 $modelLabel       = 'Laporan Kerusakan';
    protected static ?string                 $pluralModelLabel = 'Laporan Kerusakan';

    public static function canAccess(): bool { return AdminAccess::can('Sarpras'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->label('Status')
                ->options([
                    'pending'     => 'Menunggu',
                    'in_progress' => 'Ditangani',
                    'resolved'    => 'Selesai',
                ])
                ->required(),

            Textarea::make('resolution_note')
                ->label('Catatan Penanganan')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->size(40),

                TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->description),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'     => 'warning',
                        'in_progress' => 'info',
                        'resolved'    => 'success',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (DamageReport $record) => $record->statusLabel()),

                TextColumn::make('created_at')
                    ->label('Dilaporkan')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'     => 'Menunggu',
                        'in_progress' => 'Ditangani',
                        'resolved'    => 'Selesai',
                    ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDamageReports::route('/'),
            'edit'  => Pages\EditDamageReport::route('/{record}/edit'),
        ];
    }
}
