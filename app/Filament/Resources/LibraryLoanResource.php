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
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Support\AdminAccess;

class LibraryLoanResource extends Resource
{
    protected static ?string $model = LibraryLoan::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-book-open';
    protected static string|\UnitEnum|null   $navigationGroup = 'Perpustakaan';
    protected static ?string                 $navigationLabel = 'Perpustakaan (Buku)';
    protected static ?string                 $modelLabel       = 'Peminjaman Buku';
    protected static ?string                 $pluralModelLabel = 'Perpustakaan (Peminjaman Buku)';

    public static function canAccess(): bool { return AdminAccess::can('Perpustakaan'); }

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
            ->header(view('filament.library-loans-header'))
            ->headerActions([
                Action::make('print_monthly_report')
                    ->label('Cetak Rekap Peminjaman Bulanan')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->form([
                        Select::make('month')
                            ->label('Pilih Bulan')
                            ->options([
                                1  => 'Januari',
                                2  => 'Februari',
                                3  => 'Maret',
                                4  => 'April',
                                5  => 'Mei',
                                6  => 'Juni',
                                7  => 'Juli',
                                8  => 'Agustus',
                                9  => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default(now()->month)
                            ->required(),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->default(now()->year)
                            ->required(),
                        Select::make('status')
                            ->label('Filter Status')
                            ->options([
                                'all'      => 'Semua Status',
                                'borrowed' => 'Sedang Dipinjam',
                                'returned' => 'Sudah Dikembalikan',
                                'overdue'  => 'Terlambat',
                            ])
                            ->default('all'),
                    ])
                    ->action(function (array $data) {
                        return redirect()->route('admin.library.monthly-loan-report', [
                            'month'  => $data['month'],
                            'year'   => $data['year'],
                            'status' => $data['status'],
                        ]);
                    }),
                Action::make('print_clearance_modal')
                    ->label('Cetak Kartu Bebas Perpustakaan')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->form([
                        Select::make('student_id')
                            ->label('Pilih Siswa')
                            ->options(
                                User::where('role', 'siswa')
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (User $u) => [
                                        $u->id => $u->name . ' (' . ($u->schoolClass?->name ?? '—') . ')',
                                    ])
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        return redirect()->route('admin.library.clearance-card', $data['student_id']);
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('mark_returned')
                        ->label('Tandai Dikembalikan')
                        ->iconButton()
                        ->tooltip('Buku Telah Dikembalikan')
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
                        ->iconButton()
                        ->tooltip('Kartu Bebas Perpustakaan')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->visible(fn (LibraryLoan $record): bool => ! empty($record->student_id))
                        ->url(fn (LibraryLoan $record): string => route('admin.library.clearance-card', $record->student_id))
                        ->openUrlInNewTab(),

                    EditAction::make()
                        ->iconButton()
                        ->tooltip('Edit Peminjaman'),

                    DeleteAction::make()
                        ->iconButton()
                        ->tooltip('Hapus Peminjaman'),
                ])
                ->dropdown(false)
                ->extraAttributes([
                    'style' => 'display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 4px !important; width: max-content !important;',
                    'class' => '!grid !grid-cols-2 !gap-1',
                ]),
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
