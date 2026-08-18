<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Support\AdminAccess;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Blade;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->userMenuItems([
                \Filament\Navigation\MenuItem::make()
                    ->label('Panduan Presentasi Fitur')
                    ->url(fn (): string => \App\Filament\Pages\SystemOverviewPage::getUrl())
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->visible(fn (): bool => auth()->user()?->role === 'admin'),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->darkMode(true, isForced: true)
            ->brandName('Admin Dosman')
            ->favicon('/img/logo_sekolah.png')
            ->font('Plus Jakarta Sans', provider: \Filament\FontProviders\GoogleFontProvider::class)
            ->navigationItems([
                NavigationItem::make('Guru Wali')
                    ->group('Kurikulum')
                    ->icon('heroicon-o-user-group')
                    ->url(fn () => route('admin.guru-wali.index'))
                    ->visible(fn () => AdminAccess::can('Kurikulum'))
                    ->sort(20),
            ])
            ->navigationGroups([
                NavigationGroup::make('Manajemen User'),
                NavigationGroup::make('Presensi Siswa'),
                NavigationGroup::make('SIPINTER (Pendidikan Karakter)'),
                NavigationGroup::make('Prestasi & Ekskul'),
                NavigationGroup::make('Kesiswaan & Layanan'),
                NavigationGroup::make('Kurikulum'),
                NavigationGroup::make('Akademik'),
                NavigationGroup::make('Sarpras'),
                NavigationGroup::make('Humas'),
                NavigationGroup::make('E-Voting'),
                NavigationGroup::make('Sistem'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\ExecutiveOverviewWidget::class,
                \App\Filament\Widgets\LibraryStatsOverviewWidget::class,
                \App\Filament\Widgets\AttendanceChartWidget::class,
                \App\Filament\Widgets\ConductChartWidget::class,
                \App\Filament\Widgets\ExtracurricularChartWidget::class,
                \App\Filament\Widgets\LibraryVisitChartWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => Blade::render("@include('filament.admin-styles')")
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): string => Blade::render("@include('filament.sidebar-footer')")
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render("@include('filament.sweetalert') @include('components.image-lightbox')")
            );
    }
}
