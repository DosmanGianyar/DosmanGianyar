<?php

namespace App\Filament\Pages;

use App\Models\SchoolClass;
use App\Models\User;
use App\Models\ExtracurricularMember;
use App\Models\Extracurricular;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                    ->whereDoesntHave('memberExtracurriculars', fn (Builder $q) => $q->where('status', 'active'))
                    ->with('schoolClass')
                    ->orderBy('name')
            )
            ->columns([
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

                TextColumn::make('angkatan')
                    ->label('Angkatan')
                    ->placeholder('—'),
            ])
            ->filters([
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
                    ])
                    ->action(function (array $data) {
                        $url = route('admin.extracurricular.members.pdf', $data['extracurricular_id']);
                        return redirect()->away($url);
                    }),

                Action::make('cetak_pdf')
                    ->label('Cetak Siswa Tanpa Ekstra')
                    ->icon('heroicon-o-printer')
                    ->color('danger')
                    ->url(function () {
                        $filters = $this->tableFilters ?? [];
                        $classId = $filters['class_id']['value'] ?? null;
                        $className = null;
                        if ($classId) {
                            $className = SchoolClass::find($classId)?->name;
                        }
                        return route('admin.extracurricular.no-ekstra.pdf', array_filter([
                            'class_id'   => $classId,
                            'class_name' => $className,
                        ]));
                    })
                    ->openUrlInNewTab(),
            ])
            ->defaultPaginationPageOption(100)
            ->paginationPageOptions([10, 25, 50, 100, 'all'])
            ->emptyStateIcon('heroicon-o-check-badge')
            ->emptyStateHeading('Semua Siswa Sudah Punya Ekstra!')
            ->emptyStateDescription('Tidak ada siswa yang belum mengikuti ekstrakurikuler aktif.')
            ->defaultSort('name');
    }

    public function getTitle(): string
    {
        return 'Laporan Ekstrakurikuler';
    }
}
