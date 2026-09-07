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
        $pendingCount  = StudentAchievement::where('status', 'pending')->where('curation_status', '!=', 'revision')->count();
        $approvedCount = StudentAchievement::where('status', 'approved')->count();
        $revisionCount = StudentAchievement::where('curation_status', 'revision')->count();
        $rejectedCount = StudentAchievement::where('status', 'rejected')->count();
        $totalCount    = StudentAchievement::count();

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

