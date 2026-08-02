<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AppInfoWidget extends Widget
{
    protected static string $view = 'filament.widgets.app-info-widget';

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';
}
