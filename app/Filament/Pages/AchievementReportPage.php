<?php

namespace App\Filament\Pages;

use App\Filament\Resources\StudentAchievementResource;
use App\Filament\Resources\UserResource;
use App\Filament\Support\AdminAccess;
use App\Filament\Widgets\AchievementStatsOverview;
use App\Models\Extracurricular;
use App\Models\SchoolClass;
use App\Models\StudentAchievement;
use App\Models\User;
use App\Services\AchievementPembinaMatcher;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Set;
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
            Action::make('auto_sync_pembina')
                ->label('Sinkronkan Otomatis Pembina & Ekstra')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sinkronkan Otomatis Pembina Ekstra & Lomba')
                ->modalDescription('Sistem akan mencocokkan prestasi siswa yang belum memiliki Ekstrakurikuler atau Guru Pembina ke ekstrakurikuler yang paling mendekati beserta guru pembinanya.')
                ->action(function (): void {
                    $count = AchievementPembinaMatcher::syncAllAchievements();
                    Notification::make()
                        ->title("Sinkronisasi selesai ({$count} data prestasi dikaitkan dengan Pembina Ekstra)")
                        ->success()
                        ->send();
                }),

            Action::make('export_pdf')
                ->label('Cetak PDF Laporan')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->url(fn (): string => route('admin.achievement-report.pdf', [
                    'curation_status'    => $this->tableFilters['curation_status']['value'] ?? null,
                    'participation_type' => $this->tableFilters['participation_type']['value'] ?? null,
                    'level'              => $this->tableFilters['level']['value'] ?? null,
                    'field_category'     => $this->tableFilters['field_category']['value'] ?? null,
                    'extracurricular_id' => $this->tableFilters['extracurricular_id']['value'] ?? null,
                    'pembina_id'         => $this->tableFilters['pembina_id']['value'] ?? null,
                    'class_id'           => $this->tableFilters['class_id']['value'] ?? null,
                    'year'               => $this->tableFilters['year']['value'] ?? null,
                    'month'              => $this->tableFilters['month']['value'] ?? null,
                    'from'               => $this->tableFilters['date_range']['from'] ?? null,
                    'until'              => $this->tableFilters['date_range']['until'] ?? null,
                ]))
                ->openUrlInNewTab(),

            Action::make('export_excel')
                ->label('Export CSV / Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => route('admin.achievement-report.excel', [
                    'curation_status'    => $this->tableFilters['curation_status']['value'] ?? null,
                    'participation_type' => $this->tableFilters['participation_type']['value'] ?? null,
                    'level'              => $this->tableFilters['level']['value'] ?? null,
                    'field_category'     => $this->tableFilters['field_category']['value'] ?? null,
                    'extracurricular_id' => $this->tableFilters['extracurricular_id']['value'] ?? null,
                    'pembina_id'         => $this->tableFilters['pembina_id']['value'] ?? null,
                    'class_id'           => $this->tableFilters['class_id']['value'] ?? null,
                    'year'               => $this->tableFilters['year']['value'] ?? null,
                    'month'              => $this->tableFilters['month']['value'] ?? null,
                    'from'               => $this->tableFilters['date_range']['from'] ?? null,
                    'until'              => $this->tableFilters['date_range']['until'] ?? null,
                ]))
                ->openUrlInNewTab(),
        ];
    }

    public function table(Table $table): Table
    {
        $representativeIds = StudentAchievement::query()
            ->where(function ($q) {
                $q->where('status', 'approved')
                  ->orWhereIn('curation_status', ['curated', 'not_curatable']);
            })
            ->orderBy('id')
            ->get(['id', 'team_code', 'title', 'achievement_date', 'rank', 'level', 'participation_type'])
            ->groupBy(fn (StudentAchievement $item) => $item->competitionGroupKey())
            ->map(fn ($group) => $group->first()->id)
            ->values()
            ->all();

        return $table
            ->query(
                StudentAchievement::query()
                    ->whereIn('id', $representativeIds ?: [0])
                    ->with(['student.schoolClass', 'pembina', 'extracurricular.teachers', 'extracurricular.pembina'])
            )
            ->columns([
                TextColumn::make('row_num')
                    ->label('No')
                    ->rowIndex()
                    ->alignCenter()
                    ->extraCellAttributes(['style' => 'vertical-align: top;'])
                    ->width('40px'),

                TextColumn::make('title')
                    ->label('Nama Lomba / Kejuaraan')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->extraCellAttributes(['style' => 'vertical-align: top;'])
                    ->description(function (StudentAchievement $record): ?string {
                        $desc = [];
                        if ($record->event_name && mb_strtolower(trim($record->event_name)) !== mb_strtolower(trim($record->title))) {
                            $desc[] = 'Ajang: ' . $record->event_name;
                        }
                        if ($record->organizer) {
                            $desc[] = 'Penyelenggara: ' . $record->organizer;
                        }
                        if ($record->achievement_code) {
                            $desc[] = 'Kode: ' . $record->achievement_code;
                        }
                        return count($desc) ? implode(' • ', $desc) : null;
                    }),

                TextColumn::make('student.name')
                    ->label('Nama Siswa / Kelompok Juara')
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
                              })
                              ->orWhereIn('title', function ($sub) use ($search) {
                                  $sub->select('title')
                                      ->from('student_achievements')
                                      ->join('users', 'users.id', '=', 'student_achievements.student_id')
                                      ->where('users.name', 'like', "%{$search}%")
                                      ->orWhere('users.nisn', 'like', "%{$search}%")
                                      ->orWhere('users.nis', 'like', "%{$search}%");
                              });
                        });
                    })
                    ->html()
                    ->wrap()
                    ->extraCellAttributes(['style' => 'vertical-align: top;'])
                    ->formatStateUsing(function (StudentAchievement $record): string {
                        $members = $record->team_members;
                        $totalMembers = $members->count();
                        if ($totalMembers > 1 || $record->isBeregu()) {
                            $lines = $members->values()->map(function (StudentAchievement $m, int $idx) use ($totalMembers) {
                                $name = e($m->student?->name ?? 'Siswa');
                                $cls = $m->student?->schoolClass?->name ? ' <span style="font-size:0.74rem; color:#93c5fd;">(' . e($m->student->schoolClass->name) . ')</span>' : '';
                                $border = ($idx < $totalMembers - 1) ? 'border-bottom:1px solid rgba(148,163,184,0.28);' : '';
                                return "<div style=\"line-height:1.4; padding:3px 0; {$border}\"><strong>" . ($idx + 1) . ". {$name}</strong>{$cls}</div>";
                            })->implode('');

                            return "<div style=\"display:flex; flex-direction:column;\">"
                                . "<div style=\"font-size:0.68rem; font-weight:700; color:#fcd34d; text-transform:uppercase; letter-spacing:0.03em; padding-bottom:4px; border-bottom:1px solid rgba(148,163,184,0.45); margin-bottom:2px;\">👥 Kelompok Juara ({$totalMembers} Siswa):</div>"
                                . $lines
                                . "</div>";
                        }

                        $name = e($record->student?->name ?? '—');
                        $cls = $record->student?->schoolClass?->name
                            ? "<div style=\"font-size:0.75rem; color:#93c5fd; font-weight:600;\">Kelas " . e($record->student->schoolClass->name) . "</div>"
                            : '';

                        return "<div><strong>{$name}</strong>{$cls}</div>";
                    })
                    ->url(fn (StudentAchievement $record): ?string => StudentAchievementResource::getUrl('view', ['record' => $record]))
                    ->tooltip('Klik untuk lihat detail prestasi & seluruh anggota tim'),

                TextColumn::make('field_category')
                    ->label('Bidang & Ekstra')
                    ->badge()
                    ->color('info')
                    ->extraCellAttributes(['style' => 'vertical-align: top;'])
                    ->formatStateUsing(fn (StudentAchievement $record): string => $record->fieldCategoryLabel())
                    ->description(fn (StudentAchievement $record): ?string => $record->extracurricularName() !== '—' ? '🏅 ' . $record->extracurricularName() : null),

                TextColumn::make('level')
                    ->label('Tingkat & Partisipasi')
                    ->html()
                    ->extraCellAttributes(['style' => 'vertical-align: top;'])
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

                        $teamCount = $record->team_members->count();
                        if ($record->isBeregu() || $teamCount > 1) {
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
                    ->extraCellAttributes(['style' => 'vertical-align: top;'])
                    ->placeholder('—'),

                TextColumn::make('pembina_id')
                    ->label('Pembina Ekstrakurikuler')
                    ->html()
                    ->wrap()
                    ->extraCellAttributes(['style' => 'vertical-align: top;'])
                    ->formatStateUsing(function (StudentAchievement $record): string {
                        $list = $record->pembinaList();
                        $extraName = $record->extracurricularName();
                        $extraBadge = $extraName !== '—'
                            ? "<div style=\"font-size:0.72rem; color:#a5b4fc; margin-top:2px; font-weight:600;\">🏅 Ekstra: " . e($extraName) . "</div>"
                            : '';

                        $totalPembina = count($list);
                        if ($totalPembina > 1) {
                            $items = collect($list)->values()->map(function (string $name, int $idx) use ($totalPembina) {
                                $border = ($idx < $totalPembina - 1) ? 'border-bottom:1px solid rgba(148,163,184,0.25);' : '';
                                return "<div style=\"line-height:1.35; padding:2px 0; color:#6ee7b7; font-weight:600; {$border}\">" . ($idx + 1) . ". " . e($name) . "</div>";
                            })->implode('');
                            return "<div>{$items}{$extraBadge}</div>";
                        }

                        if ($totalPembina === 1) {
                            return "<div><span style=\"color:#6ee7b7; font-weight:700;\">👨‍🏫 " . e($list[0]) . "</span>{$extraBadge}</div>";
                        }

                        return "<span style=\"color:#94a3b8;\">—</span>";
                    }),

                TextColumn::make('achievement_date')
                    ->label('Tanggal')
                    ->extraCellAttributes(['style' => 'vertical-align: top;'])
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '—')
                    ->description(fn ($state) => $state ? \Carbon\Carbon::parse($state)->translatedFormat('F Y') : null)
                    ->sortable(),
            ])
            ->actions([
                Action::make('edit_pembina')
                    ->label('Pembina')
                    ->tooltip('Pilih / Ganti Pembina Ekstra & Ekstrakurikuler')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->size('xs')
                    ->outlined()
                    ->modalHeading('Pilih / Ganti Pembina Ekstra')
                    ->modalDescription('Kaitkan prestasi ini dengan Ekstrakurikuler dan Guru Pembina yang sesuai.')
                    ->fillForm(fn (StudentAchievement $record): array => [
                        'extracurricular_id' => $record->extracurricular_id,
                        'pembina_id'         => $record->pembina_id,
                    ])
                    ->form([
                        Select::make('extracurricular_id')
                            ->label('Ekstrakurikuler Terkait')
                            ->options(fn () => Extracurricular::with(['pembina', 'teachers'])->orderBy('name')->get()->mapWithKeys(fn (Extracurricular $e) => [
                                $e->id => $e->name . ($e->pembina_names !== '—' ? " (Pembina: {$e->pembina_names})" : ''),
                            ])->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if ($state) {
                                    $extra = Extracurricular::with(['pembina', 'teachers'])->find($state);
                                    $pembinaId = AchievementPembinaMatcher::resolvePembinaFromExtra($extra);
                                    if ($pembinaId) {
                                        $set('pembina_id', $pembinaId);
                                    }
                                }
                            })
                            ->placeholder('— Pilih Ekstrakurikuler —'),

                        Select::make('pembina_id')
                            ->label('Guru Pembina / Pendamping')
                            ->options(fn () => User::where('role', 'guru')->with('extracurricularsAsTeacher')->orderBy('name')->get()->mapWithKeys(function (User $g) {
                                $ekstra = $g->extracurricularsAsTeacher?->pluck('name')->implode(', ');
                                return [$g->id => $g->name . ($ekstra ? " — Pembina {$ekstra}" : ($g->subject ? " ({$g->subject})" : ''))];
                            })->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('— Pilih Nama Guru Pembina —'),
                    ])
                    ->action(function (StudentAchievement $record, array $data): void {
                        $updatePayload = [
                            'extracurricular_id' => $data['extracurricular_id'] ?? null,
                            'pembina_id'         => $data['pembina_id'] ?? null,
                        ];
                        $memberIds = $record->team_members->pluck('id')->push($record->id)->unique()->all();
                        StudentAchievement::whereIn('id', $memberIds)->update($updatePayload);

                        Notification::make()
                            ->title('Pembina Ekstra berhasil diperbarui')
                            ->success()
                            ->send();
                    }),

                Action::make('view_detail')
                    ->label('Periksa')
                    ->tooltip('Periksa Berkas & Detail Prestasi')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->size('xs')
                    ->outlined()
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

                SelectFilter::make('month')
                    ->label('Bulan Perolehan')
                    ->options([
                        '1'  => 'Januari',
                        '2'  => 'Februari',
                        '3'  => 'Maret',
                        '4'  => 'April',
                        '5'  => 'Mei',
                        '6'  => 'Juni',
                        '7'  => 'Juli',
                        '8'  => 'Agustus',
                        '9'  => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'])) return $query;
                        return $query->whereMonth('achievement_date', $data['value']);
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

                SelectFilter::make('extracurricular_id')
                    ->label('Ekstrakurikuler')
                    ->options(fn () => Extracurricular::orderBy('name')->pluck('name', 'id')->toArray()),

                SelectFilter::make('pembina_id')
                    ->label('Guru Pembina')
                    ->options(fn () => User::where('role', 'guru')->orderBy('name')->pluck('name', 'id')->toArray()),

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
                            ->pluck('achievement_date')
                            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('Y'))
                            ->unique()
                            ->sortDesc()
                            ->mapWithKeys(fn ($y) => [$y => $y])
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
