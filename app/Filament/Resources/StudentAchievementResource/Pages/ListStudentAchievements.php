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
            Action::make('download_guide')
                ->label('Unduh Buku Panduan Kurasi (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->url(asset('kurasi/Persyaratan Pengisian Kurasi.pdf'))
                ->openUrlInNewTab(),
        ];
    }

    public function getTabs(): array
    {
        $pendingCount  = StudentAchievement::whereIn('curation_status', ['pending', 'revision'])->count();
        $curatedCount  = StudentAchievement::where('curation_status', 'curated')->count();
        $internalCount = StudentAchievement::where('curation_status', 'not_curatable')->count();
        $rejectedCount = StudentAchievement::where('curation_status', 'rejected')->count();
        $totalCount    = StudentAchievement::count();

        return [
            'pending' => Tab::make('Menunggu Persetujuan')
                ->icon('heroicon-o-clock')
                ->badge($pendingCount > 0 ? (string) $pendingCount : null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('curation_status', ['pending', 'revision'])),

            'curated' => Tab::make('Lolos Kurasi Resmi')
                ->icon('heroicon-o-check-badge')
                ->badge($curatedCount > 0 ? (string) $curatedCount : null)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('curation_status', 'curated')),

            'internal' => Tab::make('Prestasi Internal')
                ->icon('heroicon-o-bookmark')
                ->badge($internalCount > 0 ? (string) $internalCount : null)
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('curation_status', 'not_curatable')),

            'rejected' => Tab::make('Ditolak')
                ->icon('heroicon-o-x-circle')
                ->badge($rejectedCount > 0 ? (string) $rejectedCount : null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('curation_status', 'rejected')),

            'all' => Tab::make('Semua Ajuan')
                ->icon('heroicon-o-square-3-stack-3d')
                ->badge($totalCount > 0 ? (string) $totalCount : null)
                ->badgeColor('gray'),
        ];
    }
}

