<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\LibraryVisit;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LibraryVisitReportPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null   $navigationGroup = 'Perpustakaan';
    protected static ?string                 $navigationLabel = 'Rekap & Cetak Kunjungan';
    protected static ?string                 $title            = 'Rekapitulasi & Cetak Laporan Kunjungan Perpustakaan';
    protected static ?string                 $slug             = 'library-visit-report';
    protected static ?int                    $navigationSort   = 11;

    protected string $view = 'filament.pages.library-visit-report';

    public static function canAccess(): bool
    {
        return AdminAccess::can('Perpustakaan');
    }

    public int $selectedMonth;
    public int $selectedYear;

    public function mount(): void
    {
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear  = (int) now()->year;
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Cetak PDF Laporan Kunjungan')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn (): string => route('admin.library.visit-report', [
                    'month'    => $this->tableFilters['month']['value'] ?? $this->selectedMonth,
                    'year'     => $this->tableFilters['year']['value'] ?? $this->selectedYear,
                    'class_id' => $this->tableFilters['class_id']['value'] ?? null,
                    'purpose'  => $this->tableFilters['purpose']['value'] ?? null,
                ]))
                ->openUrlInNewTab(),
        ];
    }

    public function getStatsProperty(): array
    {
        $month = (int) ($this->tableFilters['month']['value'] ?? $this->selectedMonth);
        $year  = (int) ($this->tableFilters['year']['value'] ?? $this->selectedYear);

        $query = LibraryVisit::whereYear('visited_at', $year)
            ->whereMonth('visited_at', $month);

        $all = $query->get();

        $todayCount = LibraryVisit::whereDate('visited_at', today())->count();

        return [
            'total'          => $all->count(),
            'unique_students'=> $all->pluck('student_id')->unique()->filter()->count(),
            'today'          => $todayCount,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LibraryVisit::query()
                    ->with(['student.schoolClass'])
            )
            ->columns([
                TextColumn::make('row_num')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"));
                    })
                    ->weight('bold')
                    ->icon('heroicon-o-user'),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info')
                    ->default('—'),

                TextColumn::make('visited_at')
                    ->label('Waktu Kunjungan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('purpose')
                    ->label('Keperluan / Tujuan')
                    ->searchable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('notes')
                    ->label('Catatan / Judul Buku')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('Bulan Kunjungan')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ])
                    ->default(now()->month)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereMonth('visited_at', (int) $data['value'])
                        : $query
                    ),

                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(fn ($y) => [$y => $y])->toArray())
                    ->default(now()->year)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereYear('visited_at', (int) $data['value'])
                        : $query
                    ),

                SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->options(SchoolClass::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('student', fn ($q) => $q->where('class_id', $data['value']))
                        : $query
                    ),
            ])
            ->defaultSort('visited_at', 'desc');
    }
}
