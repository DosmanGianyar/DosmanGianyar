<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LibraryLoanResource\Pages;
use App\Models\LibraryLoan;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Support\AdminAccess;

class LibraryLoanResource extends Resource
{
    protected static ?string $model = LibraryLoan::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-book-open';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sarpras';
    protected static ?string                 $navigationLabel = 'Perpustakaan (Buku)';
    protected static ?string                 $modelLabel       = 'Peminjaman Buku';
    protected static ?string                 $pluralModelLabel = 'Perpustakaan (Peminjaman Buku)';

    public static function canAccess(): bool { return AdminAccess::can('Sarpras'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Peminjam & Buku')->schema([
                Select::make('student_id')
                    ->label('Pilih Siswa (Jika Terdaftar)')
                    ->options(
                        User::where('role', 'siswa')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (User $u) => [
                                $u->id => $u->name . ' (' . ($u->schoolClass?->name ?? '—') . ')',
                            ])
                    )
                    ->searchable()
                    ->nullable()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $user = User::find($state);
                            if ($user) {
                                $set('phone_number', $user->phone);
                            }
                        }
                    })
                    ->helperText('Kosongkan jika siswa belum terdaftar dan isi manual di bawah'),

                TextInput::make('manual_student_name')
                    ->label('Nama Siswa (Manual)')
                    ->placeholder('Contoh: I Gede Agus Merta')
                    ->hidden(fn (callable $get) => (bool) $get('student_id')),

                TextInput::make('manual_class_name')
                    ->label('Kelas (Manual)')
                    ->placeholder('Contoh: XII MIPA 1')
                    ->hidden(fn (callable $get) => (bool) $get('student_id')),

                TextInput::make('phone_number')
                    ->label('No. HP / WhatsApp Peminjam')
                    ->tel()
                    ->placeholder('081234567890')
                    ->required(),

                TextInput::make('book_title')
                    ->label('Judul Buku')
                    ->placeholder('Contoh: Matematika Peminatan Kelas XII')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('book_code')
                    ->label('Kode / No. Inventaris Buku (Opsional)')
                    ->placeholder('Contoh: BIB-2026-004'),

                DatePicker::make('borrowed_at')
                    ->label('Tanggal Peminjaman')
                    ->default(now())
                    ->required(),

                DatePicker::make('due_at')
                    ->label('Tanggal Batas Pengembalian')
                    ->default(now()->addDays(7))
                    ->required(),

                Select::make('status')
                    ->label('Status Peminjaman')
                    ->options([
                        'borrowed' => 'Sedang Dipinjam',
                        'returned' => 'Sudah Dikembalikan',
                        'overdue'  => 'Terlambat',
                    ])
                    ->default('borrowed')
                    ->required(),

                TextInput::make('purpose')
                    ->label('Keperluan Peminjaman')
                    ->placeholder('Contoh: BELAJAR / MEMBACA / MEMINJAM BUKU/REFERENSI')
                    ->default('BELAJAR'),

                DatePicker::make('returned_at')
                    ->label('Tanggal Dikembalikan')
                    ->nullable(),

                Textarea::make('notes')
                    ->label('Catatan / Keterangan')
                    ->placeholder('Catatan kondisi buku atau keterangan tambahan')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_name')
                    ->label('Nama Siswa')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })->orWhere('manual_student_name', 'like', "%{$search}%");
                    })
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('class_name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone_number')
                    ->label('No. HP')
                    ->icon('heroicon-o-phone')
                    ->searchable(),

                TextColumn::make('book_title')
                    ->label('Judul Buku')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('borrowed_at')
                    ->label('Tanggal Pinjam')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('due_at')
                    ->label('Batas Kembali')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (LibraryLoan $record): string => $record->isOverdue() ? 'danger' : 'gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (LibraryLoan $record): string => $record->statusLabel())
                    ->color(fn (LibraryLoan $record): string => $record->statusColor()),

                TextColumn::make('returned_at')
                    ->label('Dikembalikan Pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'borrowed' => 'Sedang Dipinjam',
                        'returned' => 'Sudah Dikembalikan',
                        'overdue'  => 'Terlambat',
                    ]),
            ])
            ->actions([
                Action::make('mark_returned')
                    ->label('Buku Telah Dikembalikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LibraryLoan $record): bool => $record->status !== 'returned')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengembalian Buku')
                    ->modalDescription(fn (LibraryLoan $record): string => "Apakah Anda yakin buku '{$record->book_title}' yang dipinjam oleh {$record->student_name} telah dikembalikan?")
                    ->action(function (LibraryLoan $record) {
                        $record->update([
                            'status'      => 'returned',
                            'returned_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Pengembalian Berhasil')
                            ->body("Buku '{$record->book_title}' telah ditandai Sudah Dikembalikan.")
                            ->success()
                            ->send();
                    }),

                Action::make('print_clearance')
                    ->label('Kartu Bebas')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn (LibraryLoan $record): bool => ! empty($record->student_id))
                    ->url(fn (LibraryLoan $record): string => route('admin.library.clearance-card', $record->student_id))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLibraryLoans::route('/'),
            'create' => Pages\CreateLibraryLoan::route('/create'),
            'edit'   => Pages\EditLibraryLoan::route('/{record}/edit'),
        ];
    }
}
