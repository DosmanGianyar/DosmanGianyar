<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetLoanResource\Pages;
use App\Models\AssetLoan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssetLoanResource extends Resource
{
    protected static ?string $model = AssetLoan::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-arrow-right-on-rectangle';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sarpras';
    protected static ?string                 $navigationLabel = 'Peminjaman Aset';
    protected static ?string                 $modelLabel       = 'Peminjaman';
    protected static ?string                 $pluralModelLabel = 'Peminjaman Aset';

    public static function canAccess(): bool { return AdminAccess::can('Sarpras'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->label('Status')
                ->options([
                    'pending'  => 'Menunggu',
                    'approved' => 'Disetujui',
                    'active'   => 'Dipinjam',
                    'returned' => 'Dikembalikan',
                    'rejected' => 'Ditolak',
                ])
                ->required(),

            Textarea::make('rejection_note')
                ->label('Catatan Penolakan')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Peminjam')
                    ->searchable(),

                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->limit(50),

                TextColumn::make('start_date')
                    ->label('Dari')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Sampai')
                    ->date('d M Y'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'info',
                        'active'   => 'success',
                        'returned' => 'gray',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (AssetLoan $record) => $record->statusLabel()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'active'   => 'Dipinjam',
                        'returned' => 'Dikembalikan',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetLoans::route('/'),
            'edit'  => Pages\EditAssetLoan::route('/{record}/edit'),
        ];
    }
}
