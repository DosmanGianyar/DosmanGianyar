<?php

namespace App\Filament\Resources\StudentAchievementResource\Pages;

use App\Filament\Resources\StudentAchievementResource;
use App\Models\StudentAchievement;
use Filament\Actions\Action;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListStudentAchievements extends ListRecords
{
    protected static string $resource = StudentAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('auto_sync_pembina')
                ->label('Sinkronkan Otomatis Pembina & Ekstra')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Sinkronkan Otomatis Pembina Ekstra & Lomba')
                ->modalDescription('Sistem akan mencocokkan prestasi siswa yang belum memiliki Ekstrakurikuler atau Guru Pembina ke ekstrakurikuler yang paling mendekati beserta guru pembinanya.')
                ->action(function (): void {
                    $count = \App\Services\AchievementPembinaMatcher::syncAllAchievements();
                    \Filament\Notifications\Notification::make()
                        ->title("Sinkronisasi selesai ({$count} data prestasi dikaitkan dengan Pembina Ekstra)")
                        ->success()
                        ->send();
                }),

            Action::make('export_pdf')
                ->label('Cetak Rekap Prestasi (PDF)')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->url(fn (): string => route('admin.achievement-report.pdf'))
                ->openUrlInNewTab(),

            Action::make('download_guide')
                ->label('Unduh Buku Panduan Kurasi (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->url(asset('kurasi/Persyaratan Pengisian Kurasi.pdf'))
                ->openUrlInNewTab(),
        ];
    }

    protected function countDistinctAchievements(Builder $query): int
    {
        return (clone $query)
            ->orderBy('id')
            ->get(['id', 'student_id', 'team_code', 'title', 'achievement_date', 'rank', 'level', 'participation_type'])
            ->groupBy(fn (StudentAchievement $item) => $item->competitionGroupKey())
            ->count();
    }

    public function getDefaultActiveTab(): string|int|null
    {
        $hasPending = StudentAchievement::where('status', 'pending')->where('curation_status', '!=', 'revision')->exists();
        return $hasPending ? 'pending' : 'approved';
    }

    public function getTabs(): array
    {
        $pendingQuery  = StudentAchievement::where('status', 'pending')->where('curation_status', '!=', 'revision');
        $approvedQuery = StudentAchievement::where('status', 'approved');
        $revisionQuery = StudentAchievement::where('curation_status', 'revision');
        $rejectedQuery = StudentAchievement::where('status', 'rejected');
        $totalQuery    = StudentAchievement::query();

        $pendingCount  = $this->countDistinctAchievements($pendingQuery);
        $approvedCount = $this->countDistinctAchievements($approvedQuery);
        $revisionCount = $this->countDistinctAchievements($revisionQuery);
        $rejectedCount = $this->countDistinctAchievements($rejectedQuery);
        $totalCount    = $this->countDistinctAchievements($totalQuery);

        return [
            'pending' => Tab::make('Menunggu Verifikasi')
                ->icon('heroicon-o-clock')
                ->badge($pendingCount > 0 ? (string) $pendingCount : null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')->where('curation_status', '!=', 'revision')),

            'approved' => Tab::make('Disetujui / Valid')
                ->icon('heroicon-o-check-circle')
                ->badge($approvedCount > 0 ? (string) $approvedCount : null)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),

            'revision' => Tab::make('Perlu Revisi')
                ->icon('heroicon-o-arrow-path')
                ->badge($revisionCount > 0 ? (string) $revisionCount : null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('curation_status', 'revision')),

            'rejected' => Tab::make('Ditolak')
                ->icon('heroicon-o-x-circle')
                ->badge($rejectedCount > 0 ? (string) $rejectedCount : null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),

            'all' => Tab::make('Semua Data')
                ->icon('heroicon-o-square-3-stack-3d')
                ->badge($totalCount > 0 ? (string) $totalCount : null)
                ->badgeColor('gray'),
        ];
    }
}

