<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->login(Login::class)
            ->darkMode(false)
            // tidak perlu refresh untuk pindah halaman
            ->spa(true)
            ->defaultThemeMode(ThemeMode::Light)
            // ->assets([
            //     Css::make('custom')->relativePublicPath('css/filament-custom.css'),
            // ])
            ->colors([
                'primary' => '#e8a020',
                'emerald' => Color::Emerald,
                'sky' => Color::Sky,
                'rose' => Color::Rose,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->navigationGroups([
                NavigationGroup::make()->label('CMS'),
                NavigationGroup::make()->label('Katalog Reward'),
                NavigationGroup::make()->label('Loyalty Point'),
                NavigationGroup::make()->label('Redeem Poin'),
                NavigationGroup::make()->label('Manajemen Pengguna'),
                NavigationGroup::make()->label('Master Lokasi'),
                NavigationGroup::make()->label('Konfigurasi'),
                NavigationGroup::make()->label('Notifikasi'),
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup('Manajemen Pengguna'),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): \Illuminate\Contracts\View\View => view('filament.partials.firebase-web-push'),
            );
    }
}
