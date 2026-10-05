<?php

namespace App\Filament\Pages;

use App\Filament\Resources\StudentAchievementResource;
use App\Filament\Resources\UserResource;
use App\Filament\Support\AdminAccess;
use App\Filament\Widgets\AchievementStatsOverview;
use App\Models\SchoolClass;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AchievementReportPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-trophy';
    protected static string|\UnitEnum|null   $navigationGroup = 'Prestasi & Ekskul';
    protected static ?string                 $navigationLabel = '🏆 Rekap Prestasi Disetujui';
    protected static ?string                 $title           = 'Rekapitulasi & Detail Prestasi Disetujui';
    protected static ?string                 $slug            = 'achievement-report';
    protected static ?int                    $navigationSort  = 11;

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
                    'curation_status' => $this->tableFilters['curation_status']['value'] ?? null,
                    'level'           => $this->tableFilters['level']['value'] ?? null,
                    'field_category'  => $this->tableFilters['field_category']['value'] ?? null,
                    'class_id'        => $this->tableFilters['class_id']['value'] ?? null,
                    'year'            => $this->tableFilters['year']['value'] ?? null,
                    'from'            => $this->tableFilters['date_range']['from'] ?? null,
                    'until'           => $this->tableFilters['date_range']['until'] ?? null,
                ]))
                ->openUrlInNewTab(),

            Action::make('export_excel')
                ->label('Export CSV / Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => route('admin.achievement-report.excel', [
                    'curation_status' => $this->tableFilters['curation_status']['value'] ?? null,
                    'level'           => $this->tableFilters['level']['value'] ?? null,
                    'field_category'  => $this->tableFilters['field_category']['value'] ?? null,
                    'class_id'        => $this->tableFilters['class_id']['value'] ?? null,
                    'year'            => $this->tableFilters['year']['value'] ?? null,
                    'from'            => $this->tableFilters['date_range']['from'] ?? null,
                    'until'           => $this->tableFilters['date_range']['until'] ?? null,
                ]))
                ->openUrlInNewTab(),
        ];
    }

    public function table(Table $table): Table
    {
        $representativeIds = StudentAchievement::where('status', 'approved')
            ->selectRaw('MIN(id) as id')
            ->groupByRaw('CASE WHEN participation_type = "beregu" AND team_code IS NOT NULL AND team_code != "" THEN team_code WHEN participation_type = "beregu" THEN CONCAT(title, "|", COALESCE(achievement_date, "")) ELSE CAST(id AS CHAR) END')
            ->pluck('id');

        return $table
            ->query(
                StudentAchievement::query()
                    ->whereIn('id', $representativeIds)
                    ->with(['student.schoolClass'])
            )
            ->columns([
                TextColumn::make('row_num')
                    ->label('No')
                    ->rowIndex()
                    ->alignCenter()
                    ->width('40px'),

                TextColumn::make('student.name')
                    ->label('Siswa / Tim')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('student', fn (Builder $s) => $s->where('name', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"))
                              ->orWhereIn('team_code', function ($sub) use ($search) {
                                  $sub->select('team_code')
                                      ->from('student_achievements')
                                      ->join('users', 'users.id', '=', 'student_achievements.student_id')
                                      ->where('users.name', 'like', "%{$search}%")
                                      ->orWhere('users.nisn', 'like', "%{$search}%")
                                      ->orWhere('users.nis', 'like', "%{$search}%");
                              });
                        });
                    })
                    ->icon(fn (StudentAchievement $record): string => $record->isBeregu() ? 'heroicon-o-user-group' : 'heroicon-o-user')
                    ->color(fn (StudentAchievement $record): string => $record->isBeregu() ? 'warning' : 'primary')
                    ->weight('bold')
                    ->wrap()
                    ->formatStateUsing(function (StudentAchievement $record): string {
                        if ($record->isBeregu()) {
                            $count = $record->team_members->count();
                            $name = $record->student?->name ?? 'Siswa';
                            return "Tim: {$name} dkk. ({$count} Siswa)";
                        }
                        return $record->student?->name ?? '—';
                    })
                    ->description(function (StudentAchievement $record): ?string {
                        $parts = [];
                        if ($record->student?->schoolClass?->name) {
                            $parts[] = 'Kelas ' . $record->student->schoolClass->name;
                        }
                        if ($record->isBeregu()) {
                            $parts[] = '👥 Lomba Beregu (' . $record->team_members->count() . ' Siswa)';
                        }
                        return count($parts) ? implode(' • ', $parts) : null;
                    })
                    ->url(fn (StudentAchievement $record): ?string => StudentAchievementResource::getUrl('view', ['record' => $record]))
                    ->tooltip('Klik untuk lihat detail prestasi & seluruh anggota tim'),

                TextColumn::make('student.schoolClass.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('title')
                    ->label('Judul Prestasi / Kejuaraan')
                    ->searchable()
                    ->weight('semibold')
                    ->wrap()
                    ->description(function (StudentAchievement $record): ?string {
                        $desc = [];
                        if ($record->event_name) {
                            $desc[] = 'Ajang: ' . $record->event_name;
                        }
                        if ($record->organizer) {
                            $desc[] = 'Penyelenggara: ' . $record->organizer;
                        }
                        return count($desc) ? implode(' • ', $desc) : null;
                    }),

                TextColumn::make('field_category')
                    ->label('Rumpun')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->fieldCategoryLabel()),

                TextColumn::make('level')
                    ->label('Tingkat & Partisipasi')
                    ->html()
                    ->formatStateUsing(function (StudentAchievement $record): string {
                        $levelLabel = e($record->levelLabel());
                        $levelColors = match ($record->level) {
                            'sekolah'       => 'background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.4);',
                            'kabupaten'     => 'background: rgba(56, 189, 248, 0.2); color: #7dd3fc; border: 1px solid rgba(56, 189, 248, 0.4);',
                            'provinsi'      => 'background: rgba(245, 158, 11, 0.2); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.4);',
                            'nasional'      => 'background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4);',
                            'internasional' => 'background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4);',
                            default         => 'background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.4);',
                        };

                        if ($record->isBeregu()) {
                            $teamCount = $record->team_members->count();
                            $names = e($record->team_members->pluck('student.name')->filter()->implode(', '));
                            $tooltipAttr = $names ? "title=\"Anggota Tim: {$names}\"" : '';
                            $partBadge = "<span {$tooltipAttr} style=\"display: inline-block; padding: 2px 7px; border-radius: 6px; font-size: 0.68rem; font-weight: 700; background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.4); white-space: nowrap;\">👥 Beregu ({$teamCount})</span>";
                        } else {
                            $partBadge = "<span style=\"display: inline-block; padding: 2px 7px; border-radius: 6px; font-size: 0.68rem; font-weight: 700; background: rgba(100, 116, 139, 0.2); color: #94a3b8; border: 1px solid rgba(100, 116, 139, 0.3); white-space: nowrap;\">👤 Perorangan</span>";
                        }

                        return "<div style=\"display: inline-flex; flex-direction: column; align-items: flex-start; gap: 4px;\">
                            <span style=\"display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; {$levelColors}\">{$levelLabel}</span>
                            {$partBadge}
                        </div>";
                    }),

                TextColumn::make('rank')
                    ->label('Peringkat')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-trophy')
                    ->placeholder('—'),

                TextColumn::make('curation_status')
                    ->label('Status Kurasi')
                    ->badge()
                    ->color(fn (StudentAchievement $record): string => $record->curationStatusColor())
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->curationStatusLabel()),

                TextColumn::make('achievement_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view_detail')
                    ->label('Lihat Detail & Berkas')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->button()
                    ->size('sm')
                    ->url(fn (StudentAchievement $record): string => StudentAchievementResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('achievement_date', 'desc')
            ->filters([
                Filter::make('date_range')
                    ->label('📅 Rentang Tanggal Perolehan Prestasi')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal Perolehan')
                            ->placeholder('dd/mm/yyyy')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal Perolehan')
                            ->placeholder('dd/mm/yyyy')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('achievement_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('achievement_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'Perolehan Dari: ' . \Carbon\Carbon::parse($data['from'])->translatedFormat('d F Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Perolehan Sampai: ' . \Carbon\Carbon::parse($data['until'])->translatedFormat('d F Y');
                        }
                        return $indicators;
                    }),

                SelectFilter::make('curation_status')
                    ->label('Status Kurasi')
                    ->options([
                        'curated'       => 'Hanya Lolos Kurasi Resmi',
                        'not_curatable' => 'Hanya Prestasi Internal (Tidak Dikurasi)',
                    ]),

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
                        $years = StudentAchievement::where('status', 'approved')
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
