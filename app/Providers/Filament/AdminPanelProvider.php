<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
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
                \App\Filament\Widgets\AttendanceChartWidget::class,
                \App\Filament\Widgets\ConductChartWidget::class,
                \App\Filament\Widgets\ExtracurricularChartWidget::class,
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
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): string => Blade::render('
                    <div class="px-4 py-3 my-2 border-t border-slate-700/60">
                        <form method="POST" action="{{ route("filament.admin.auth.logout") }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-600/80 border border-rose-500/20 hover:border-rose-500 rounded-xl transition-all group shadow-xs">
                                <svg class="w-4 h-4 text-rose-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar / Logout Admin</span>
                            </button>
                        </form>
                    </div>
                ')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render("@include('filament.sweetalert') @include('components.image-lightbox')")
            );
    }
}
