<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Support\AdminAccess;
use App\Filament\Widgets\AchievementStatsOverview;
use App\Models\SchoolClass;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AchievementReportPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static string|\UnitEnum|null   $navigationGroup = 'Prestasi & Ekskul';
    protected static ?string                 $navigationLabel = 'Laporan Prestasi Siswa';
    protected static ?string                 $slug            = 'achievement-report';
    protected static ?int                    $navigationSort  = 13;

    protected string $view = 'filament.pages.achievement-report';

    public static function canAccess(): bool
    {
        return AdminAccess::can('Prestasi & Ekskul');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AchievementStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Cetak PDF Laporan')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->url(fn (): string => route('admin.achievement-report.pdf', [
                    'level'          => $this->tableFilters['level']['value'] ?? null,
                    'field_category' => $this->tableFilters['field_category']['value'] ?? null,
                    'class_id'       => $this->tableFilters['class_id']['value'] ?? null,
                    'year'           => $this->tableFilters['year']['value'] ?? null,
                ]))
                ->openUrlInNewTab(),

            Action::make('export_excel')
                ->label('Export CSV / Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => route('admin.achievement-report.excel', [
                    'level'          => $this->tableFilters['level']['value'] ?? null,
                    'field_category' => $this->tableFilters['field_category']['value'] ?? null,
                    'class_id'       => $this->tableFilters['class_id']['value'] ?? null,
                    'year'           => $this->tableFilters['year']['value'] ?? null,
                ]))
                ->openUrlInNewTab(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StudentAchievement::query()
                    ->where('curation_status', 'curated')
                    ->with(['student.schoolClass'])
            )
            ->columns([
                TextColumn::make('row_num')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('student', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%"));
                    })
                    ->icon('heroicon-o-user')
                    ->color('primary')
                    ->weight('bold')
                    ->url(fn (StudentAchievement $record): ?string => $record->student_id ? UserResource::getUrl('view', ['record' => $record->student_id]) : null)
                    ->openUrlInNewTab()
                    ->tooltip('Klik untuk melihat profil siswa'),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('student.phone')
                    ->label('No. HP')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->formatStateUsing(function (StudentAchievement $record): string {
                        $p = $record->student?->phone;
                        if (filled($p)) return $p;
                        $pp = $record->student?->parent_phone;
                        return filled($pp) ? $pp . ' (Ortu)' : '—';
                    })
                    ->url(function (StudentAchievement $record): ?string {
                        $phone = $record->student?->phone ?: $record->student?->parent_phone;
                        if (blank($phone)) return null;
                        $clean = preg_replace('/[^0-9]/', '', $phone);
                        if (str_starts_with($clean, '0')) $clean = '62' . substr($clean, 1);
                        return 'https://wa.me/' . $clean;
                    })
                    ->openUrlInNewTab()
                    ->tooltip('Klik untuk WhatsApp'),

                TextColumn::make('title')
                    ->label('Judul Prestasi / Kejuaraan')
                    ->searchable()
                    ->weight('semibold')
                    ->wrap(),

                TextColumn::make('event_name')
                    ->label('Event / Penyelenggara')
                    ->searchable()
                    ->formatStateUsing(function (StudentAchievement $record): string {
                        $event = $record->event_name ?: '—';
                        $org = $record->organizer ? " ({$record->organizer})" : '';
                        return $event . $org;
                    })
                    ->wrap(),

                TextColumn::make('field_category')
                    ->label('Rumpun')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->fieldCategoryLabel()),

                TextColumn::make('participation_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'beregu' => 'purple',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn (StudentAchievement $record): string => match ($record->participation_type) {
                        'beregu' => 'Beregu',
                        default  => 'Perorangan',
                    }),

                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sekolah'       => 'gray',
                        'kabupaten'     => 'info',
                        'provinsi'      => 'warning',
                        'nasional'      => 'success',
                        'internasional' => 'danger',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->levelLabel()),

                TextColumn::make('rank')
                    ->label('Peringkat')
                    ->badge()
                    ->color('warning')
                    ->placeholder('—'),

                TextColumn::make('achievement_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('certificate')
                    ->label('Berkas')
                    ->formatStateUsing(fn ($state) => $state ? '📄 Sertifikat' : '—')
                    ->url(fn (StudentAchievement $record): ?string => $record->certificateUrl())
                    ->openUrlInNewTab()
                    ->color('primary'),
            ])
            ->defaultSort('achievement_date', 'desc')
            ->filters([
                SelectFilter::make('participation_type')
                    ->label('Jenis Partisipasi')
                    ->options([
                        'individu' => 'Perorangan (Individu)',
                        'beregu'   => 'Beregu (Kelompok)',
                    ]),

                SelectFilter::make('level')
                    ->label('Tingkat Kejuaraan')
                    ->options([
                        'sekolah'       => 'Sekolah',
                        'kabupaten'     => 'Kabupaten/Kota',
                        'provinsi'      => 'Provinsi',
                        'nasional'      => 'Nasional',
                        'internasional' => 'Internasional',
                    ]),

                SelectFilter::make('field_category')
                    ->label('Rumpun Bidang')
                    ->options([
                        'sains_riset'  => 'Sains & Riset',
                        'olahraga'     => 'Olahraga',
                        'seni_budaya'  => 'Seni & Budaya',
                        'bahasa_debat' => 'Bahasa & Debat',
                        'keagamaan'    => 'Keagamaan',
                        'akademik'     => 'Akademik',
                        'lainnya'      => 'Lainnya',
                    ]),

                SelectFilter::make('class_id')
                    ->label('Kelas Siswa')
                    ->options(fn () => SchoolClass::orderBy('name')->pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'])) return $query;
                        return $query->whereHas('student', fn ($q) => $q->where('class_id', $data['value']));
                    }),

                SelectFilter::make('year')
                    ->label('Tahun Prestasi')
                    ->options(function () {
                        $years = StudentAchievement::where('curation_status', 'curated')
                            ->whereNotNull('achievement_date')
                            ->selectRaw('YEAR(achievement_date) as year')
                            ->distinct()
                            ->orderByDesc('year')
                            ->pluck('year', 'year')
                            ->toArray();
                        return $years ?: [date('Y') => date('Y')];
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'])) return $query;
                        return $query->whereYear('achievement_date', $data['value']);
                    }),
            ]);
    }
}
