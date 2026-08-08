<?php

namespace App\Filament\Pages;

use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExtracurricularReportPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null   $navigationGroup = 'Prestasi & Ekskul';
    protected static ?string                 $navigationLabel = 'Laporan Ekstra';
    protected static ?string                 $slug            = 'extracurricular-report';
    protected static ?int                    $navigationSort  = 12;

    protected string $view = 'filament.pages.extracurricular-report';

    public static function canAccess(): bool
    {
        return \App\Filament\Support\AdminAccess::can('Prestasi & Ekskul');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('role', 'siswa')
                    ->with(['schoolClass', 'memberExtracurriculars.extracurricular'])
                    ->orderBy('name')
            )
            ->columns([
                TextColumn::make('row_num')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->placeholder('—'),

                TextColumn::make('schoolClass.name')
                    ->label('Kelas')
                    ->placeholder('—')
                    ->badge()
                    ->color('info'),

                TextColumn::make('memberExtracurriculars')
                    ->label('Ekstrakurikuler Diikuti')
                    ->badge()
                    ->color(fn (User $record) => $record->memberExtracurriculars->where('status', 'active')->count() > 0 ? 'success' : 'danger')
                    ->formatStateUsing(function (User $record) {
                        $active = $record->memberExtracurriculars->where('status', 'active');
                        if ($active->isEmpty()) {
                            return 'Tanpa Ekstra';
                        }
                        return $active->map(fn ($m) => $m->extracurricular?->name)->filter()->implode(', ');
                    }),
            ])
            ->filters([
                SelectFilter::make('status_ekstra')
                    ->label('Status Ekstrakurikuler')
                    ->options([
                        'tanpa_ekstra' => 'Tanpa Ekstrakurikuler',
                        'ber_ekstra'   => 'Memiliki Ekstrakurikuler',
                    ])
                    ->default('tanpa_ekstra')
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'tanpa_ekstra') {
                            $query->whereDoesntHave('memberExtracurriculars', fn (Builder $q) => $q->where('status', 'active'));
                        } elseif (($data['value'] ?? null) === 'ber_ekstra') {
                            $query->whereHas('memberExtracurriculars', fn (Builder $q) => $q->where('status', 'active'));
                        }
                    }),

                SelectFilter::make('extracurricular_id')
                    ->label('Filter Ekstrakurikuler')
                    ->options(Extracurricular::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] ?? null) {
                            $query->whereHas('memberExtracurriculars', fn (Builder $q) => $q->where('status', 'active')->where('extracurricular_id', $data['value']));
                        }
                    }),

                SelectFilter::make('grade')
                    ->label('Filter Per Angkatan')
                    ->options([
                        'X'   => 'Angkatan / Kelas X',
                        'XI'  => 'Angkatan / Kelas XI',
                        'XII' => 'Angkatan / Kelas XII',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('schoolClass', fn ($q) => $q->where('grade', $data['value']))
                        : $query
                    ),

                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('schoolClass', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Action::make('cetak_per_ekstra')
                    ->label('Cetak Per Ekstra')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Select::make('extracurricular_id')
                            ->label('Pilih Ekstrakurikuler')
                            ->options(Extracurricular::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        \Filament\Forms\Components\Select::make('grade')
                            ->label('Angkatan (Opsional)')
                            ->options([
                                'X'   => 'Angkatan X',
                                'XI'  => 'Angkatan XI',
                                'XII' => 'Angkatan XII',
                            ]),
                        \Filament\Forms\Components\Select::make('class_id')
                            ->label('Kelas (Opsional)')
                            ->options(SchoolClass::orderBy('name')->pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        $params = array_filter([
                            'grade'    => $data['grade'] ?? null,
                            'class_id' => $data['class_id'] ?? null,
                        ]);
                        $url = route('admin.extracurricular.members.pdf', array_merge([$data['extracurricular_id']], $params));
                        return redirect()->away($url);
                    }),

                Action::make('cetak_pdf')
                    ->label('Cetak Siswa Tanpa Ekstra')
                    ->icon('heroicon-o-printer')
                    ->color('danger')
                    ->url(function () {
                        $filters = $this->tableFilters ?? [];
                        $classId = $filters['class_id']['value'] ?? null;
                        $grade   = $filters['grade']['value'] ?? null;
                        $className = null;
                        if ($classId) {
                            $className = SchoolClass::find($classId)?->name;
                        }
                        return route('admin.extracurricular.no-ekstra.pdf', array_filter([
                            'class_id'   => $classId,
                            'grade'      => $grade,
                            'class_name' => $className,
                        ]));
                    })
                    ->openUrlInNewTab(),
            ])
            ->defaultPaginationPageOption(100)
            ->paginationPageOptions([10, 25, 50, 100, 'all'])
            ->emptyStateIcon('heroicon-o-check-badge')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('Tidak ada siswa yang sesuai dengan filter yang dipilih.')
            ->defaultSort('name');
    }

    public function getTitle(): string
    {
        return 'Laporan Ekstrakurikuler';
    }
}
