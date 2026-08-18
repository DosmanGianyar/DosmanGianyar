<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LibraryBookResource\Pages;
use App\Models\LibraryBook;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Support\AdminAccess;

class LibraryBookResource extends Resource
{
    protected static ?string $model = LibraryBook::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-book-open';
    protected static string|\UnitEnum|null   $navigationGroup = 'Perpustakaan';
    protected static ?string                 $navigationLabel = 'Katalog Buku';
    protected static ?string                 $modelLabel       = 'Katalog Buku';
    protected static ?string                 $pluralModelLabel = 'Katalog Buku Perpustakaan';
    protected static ?int                    $navigationSort   = 1;

    public static function canAccess(): bool { return AdminAccess::can('Perpustakaan'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Utama Buku')->schema([
                TextInput::make('book_code')
                    ->label('Kode Inventaris / Panggil Buku')
                    ->placeholder('Contoh: BK-2026-001')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(fn () => 'BK-' . date('Y') . '-' . sprintf('%03d', rand(100, 999))),

                TextInput::make('isbn')
                    ->label('Nomor ISBN / NISB')
                    ->placeholder('Contoh: 978-602-1234-56-7')
                    ->nullable(),

                TextInput::make('title')
                    ->label('Judul Buku')
                    ->placeholder('Contoh: Matematika Peminatan Kelas XII')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('author')
                    ->label('Penulis / Pengarang')
                    ->placeholder('Contoh: Prof. Dr. I Wayan Sudra')
                    ->nullable(),

                TextInput::make('publisher')
                    ->label('Penerbit')
                    ->placeholder('Contoh: Erlangga')
                    ->nullable(),

                TextInput::make('publish_year')
                    ->label('Tahun Terbit')
                    ->numeric()
                    ->placeholder('2024')
                    ->nullable(),

                Select::make('category')
                    ->label('Kategori Buku')
                    ->options([
                        'Pelajaran'  => 'Buku Pelajaran Utama',
                        'Fiksi'      => 'Fiksi & Novel',
                        'Non-Fiksi'  => 'Non-Fiksi & Biografi',
                        'Sains'      => 'Sains & Teknologi',
                        'Sejarah'    => 'Sejarah & Kebudayaan',
                        'Agama'      => 'Agama & Budi Pekerti',
                        'Referensi'  => 'Referensi & Ensiklopedia',
                        'Umum'       => 'Umum & Karya Tulis',
                    ])
                    ->default('Pelajaran')
                    ->required(),

                TextInput::make('total_stock')
                    ->label('Total Eksemplar (Stok Fisik)')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->helperText('Jumlah total buku fisik yang dimiliki perpustakaan'),

                TextInput::make('shelf_location')
                    ->label('Lokasi Rak Buku')
                    ->placeholder('Contoh: Rak A-2 / Lemari 3')
                    ->nullable(),

                FileUpload::make('cover_image')
                    ->label('Gambar Sampul Buku (Wajib)')
                    ->image()
                    ->directory('books/covers')
                    ->disk('public')
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('3:4')
                    ->imageResizeTargetWidth('400')
                    ->imageResizeTargetHeight('533')
                    ->maxSize(3072)
                    ->required()
                    ->helperText('Unggah foto sampul depan (Format: JPG, PNG, WEBP). Otomatis diperkecil & dioptimalkan agar ringan.')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Sinopsis / Deskripsi Ringkas Buku')
                    ->placeholder('Tuliskan ringkasan isi buku atau informasi penting lainnya...')
                    ->rows(4)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->square()
                    ->disk('public')
                    ->defaultImageUrl(asset('img/default-book-cover.png')),

                TextColumn::make('book_code')
                    ->label('Kode / ISBN')
                    ->searchable()
                    ->sortable()
                    ->description(fn (LibraryBook $record): string => $record->isbn ? 'ISBN: ' . $record->isbn : '—'),

                TextColumn::make('title')
                    ->label('Judul Buku')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->description(fn (LibraryBook $record): string => $record->author ? 'Pengarang: ' . $record->author : ''),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('shelf_location')
                    ->label('Lokasi Rak')
                    ->badge()
                    ->color('gray')
                    ->default('—'),

                TextColumn::make('total_stock')
                    ->label('Total Stok')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('borrowed_count')
                    ->label('Dipinjam')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('available_stock')
                    ->label('Stok Tersedia')
                    ->badge()
                    ->color(fn (LibraryBook $record): string => match (true) {
                        $record->available_stock > 2 => 'success',
                        $record->available_stock > 0 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (LibraryBook $record): string => $record->available_stock . ' Buku')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori Buku')
                    ->options([
                        'Pelajaran' => 'Pelajaran',
                        'Fiksi'     => 'Fiksi',
                        'Non-Fiksi' => 'Non-Fiksi',
                        'Sains'     => 'Sains',
                        'Sejarah'   => 'Sejarah',
                        'Agama'     => 'Agama',
                        'Referensi' => 'Referensi',
                        'Umum'      => 'Umum',
                    ]),

                SelectFilter::make('stock_status')
                    ->label('Status Ketersediaan')
                    ->options([
                        'available'    => 'Tersedia Siap Dipinjam',
                        'out_of_stock' => 'Stok Habis (Sedang Dipinjam Semua)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'available') {
                            $query->whereRaw('total_stock - borrowed_count > 0');
                        } elseif (($data['value'] ?? null) === 'out_of_stock') {
                            $query->whereRaw('total_stock - borrowed_count <= 0');
                        }
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLibraryBooks::route('/'),
            'create' => Pages\CreateLibraryBook::route('/create'),
            'edit'   => Pages\EditLibraryBook::route('/{record}/edit'),
        ];
    }
}
