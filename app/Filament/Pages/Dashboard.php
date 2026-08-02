<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AppInfoWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SystemOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            AppInfoWidget::class,
            AccountWidget::class,
            StatsOverviewWidget::class,
            SystemOverviewWidget::class,
        ];
    }
}
