<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetLoanResource\Pages;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use App\Filament\Support\AdminAccess;
use Filament\Schemas\Components\Section;
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
            Section::make('Detail Peminjaman')->schema([
                Select::make('asset_category')
                    ->label('Kategori')
                    ->options([
                        'perpus'    => 'Perpustakaan (buku)',
                        'sarana'    => 'Sarana (alat, bahan, perlengkapan)',
                        'prasarana' => 'Prasarana (lahan, gedung, ruang)',
                    ])
                    ->live()
                    ->dehydrated(false)
                    ->afterStateUpdated(fn ($set) => $set('asset_id', null))
                    ->default(fn ($record) => $record?->asset?->category),

                Select::make('asset_id')
                    ->label('Aset yang Dipinjam')
                    ->options(fn ($get) => Asset::when(
                        $get('asset_category'),
                        fn ($q, $cat) => $q->where('category', $cat)
                    )->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->helperText('Pilih kategori dulu untuk mempersempit daftar aset.'),

                Select::make('user_id')
                    ->label('Peminjam')
                    ->options(
                        User::whereIn('role', ['siswa', 'pengelola', 'guru'])
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (User $u) => [
                                $u->id => $u->name . ' (' . ($u->nisn ?? $u->nis ?? $u->nip ?? ucfirst($u->role)) . ')',
                            ])
                    )
                    ->searchable()
                    ->required(),

                DatePicker::make('start_date')
                    ->label('Tanggal Pinjam')
                    ->required()
                    ->default(now()),

                DatePicker::make('end_date')
                    ->label('Rencana Kembali')
                    ->required()
                    ->rule('after_or_equal:start_date'),

                TextInput::make('purpose')
                    ->label('Keperluan / Detail')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Status')->schema([
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'active'   => 'Dipinjam',
                        'returned' => 'Dikembalikan',
                        'rejected' => 'Ditolak',
                    ])
                    ->required()
                    ->default('active')
                    ->helperText('Peminjaman yang diinput admin langsung berstatus "Dipinjam".'),

                Textarea::make('rejection_note')
                    ->label('Catatan Penolakan')
                    ->rows(3),

                Hidden::make('approved_by')->default(fn () => auth()->id()),
            ])->columns(2),
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

                TextColumn::make('asset.category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (AssetLoan $record) => $record->asset?->categoryLabel()),

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
                SelectFilter::make('asset_category')
                    ->label('Kategori')
                    ->options([
                        'perpus'    => 'Perpustakaan',
                        'sarana'    => 'Sarana',
                        'prasarana' => 'Prasarana',
                    ])
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn ($q, $value) => $q->whereHas('asset', fn ($aq) => $aq->where('category', $value))
                    )),
            ])
            ->recordActions([
                Action::make('confirmReturn')
                    ->label('Konfirmasi Kembali')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->tooltip('Konfirmasi Kembali')
                    ->visible(fn (AssetLoan $record): bool => in_array($record->status, ['active', 'approved']))
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengembalian Barang?')
                    ->modalDescription(fn (AssetLoan $record): string => sprintf(
                        '%s dari %s akan ditandai sudah dikembalikan.',
                        $record->asset?->name, $record->user?->name,
                    ))
                    ->modalSubmitActionLabel('Ya, Sudah Kembali')
                    ->action(function (AssetLoan $record): void {
                        $record->update(['status' => 'returned']);
                        Notification::make()
                            ->title('Pengembalian dikonfirmasi.')
                            ->success()
                            ->send();
                    }),
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssetLoans::route('/'),
            'create' => Pages\CreateAssetLoan::route('/create'),
            'edit'   => Pages\EditAssetLoan::route('/{record}/edit'),
        ];
    }
}
