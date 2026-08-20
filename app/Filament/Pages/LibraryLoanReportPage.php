<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\LibraryLoan;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LibraryLoanReportPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static string|\UnitEnum|null   $navigationGroup = 'Perpustakaan';
    protected static ?string                 $navigationLabel = 'Rekap & Cetak Peminjaman';
    protected static ?string                 $title            = 'Rekapitulasi & Cetak Laporan Peminjaman Buku';
    protected static ?string                 $slug             = 'library-loan-report';
    protected static ?int                    $navigationSort   = 10;

    protected string $view = 'filament.pages.library-loan-report';

    public static function canAccess(): bool
    {
        return AdminAccess::can('Perpustakaan');
    }

    public int $selectedMonth;
    public int $selectedYear;
    public string $selectedStatus = 'all';

    public function mount(): void
    {
        $this->selectedMonth  = (int) now()->month;
        $this->selectedYear   = (int) now()->year;
        $this->selectedStatus = 'all';
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Cetak PDF Laporan Peminjaman')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->url(fn (): string => route('admin.library.monthly-loan-report', [
                    'month'  => $this->tableFilters['month']['value'] ?? $this->selectedMonth,
                    'year'   => $this->tableFilters['year']['value'] ?? $this->selectedYear,
                    'status' => $this->tableFilters['status']['value'] ?? $this->selectedStatus,
                ]))
                ->openUrlInNewTab(),
        ];
    }

    public function getStatsProperty(): array
    {
        $month  = (int) ($this->tableFilters['month']['value'] ?? $this->selectedMonth);
        $year   = (int) ($this->tableFilters['year']['value'] ?? $this->selectedYear);

        $query = LibraryLoan::whereYear('borrowed_at', $year)
            ->whereMonth('borrowed_at', $month);

        $all = $query->get();

        return [
            'total'    => $all->count(),
            'borrowed' => $all->where('status', 'borrowed')->count(),
            'returned' => $all->where('status', 'returned')->count(),
            'overdue'  => $all->filter(fn ($l) => $l->isOverdue())->count(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LibraryLoan::query()
                    ->with(['student.schoolClass'])
            )
            ->columns([
                TextColumn::make('row_num')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('student_name')
                    ->label('Nama Peminjam')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('manual_student_name', 'like', "%{$search}%")
                            ->orWhereHas('student', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->weight('bold')
                    ->icon('heroicon-o-user'),

                TextColumn::make('class_name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                TextColumn::make('book_title')
                    ->label('Judul Buku')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('book_code')
                    ->label('Kode Inventaris')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('borrowed_at')
                    ->label('Tgl Pinjam')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('due_at')
                    ->label('Batas Kembali')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (LibraryLoan $record): string => match ($record->status) {
                        'returned' => 'success',
                        'overdue'  => 'danger',
                        default    => 'warning',
                    })
                    ->formatStateUsing(fn (LibraryLoan $record): string => $record->statusLabel()),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('Bulan Peminjaman')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ])
                    ->default(now()->month)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereMonth('borrowed_at', (int) $data['value'])
                        : $query
                    ),

                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(fn ($y) => [$y => $y])->toArray())
                    ->default(now()->year)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereYear('borrowed_at', (int) $data['value'])
                        : $query
                    ),

                SelectFilter::make('status')
                    ->label('Status Peminjaman')
                    ->options([
                        'all'      => 'Semua Status',
                        'borrowed' => 'Sedang Dipinjam',
                        'returned' => 'Sudah Dikembalikan',
                        'overdue'  => 'Terlambat',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $val = $data['value'] ?? null;
                        if (!$val || $val === 'all') return;
                        if ($val === 'overdue') {
                            $query->where(function ($q) {
                                $q->where('status', 'overdue')
                                  ->orWhere(function ($sub) {
                                      $sub->where('status', 'borrowed')
                                          ->where('due_at', '<', now()->toDateString());
                                  });
                            });
                        } else {
                            $query->where('status', $val);
                        }
                    }),
            ])
            ->defaultSort('borrowed_at', 'desc');
    }
}
