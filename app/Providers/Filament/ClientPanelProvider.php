<?php

namespace App\Providers\Filament;

use App\Http\Middleware\CheckTenantLimits;
use App\Http\Middleware\InitializeTenancy;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ClientPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn (): string => Vite::useHotFile('hot')
                ->useBuildDirectory('build')
                ->withEntryPoints(['resources/js/app.js'])
                ->toHtml(),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => auth()->check() && auth()->user()->tenant_id
                ? '<meta name="tenant-id" content="'.auth()->user()->tenant_id.'">'
                : '',
        );

        // Apply tenant branding (primary color) dynamically
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            function (): string {
                // Get tenant from app container (works even when not authenticated)
                $tenant = app()->bound('tenant') ? app('tenant') : null;

                if (! $tenant) {
                    return '';
                }

                $primaryColor = $tenant->settings['primary_color'] ?? null;

                if (! $primaryColor) {
                    return '';
                }

                return Blade::render('<style>
                    :root {
                        --primary-50: {{ $color }}0D;
                        --primary-100: {{ $color }}1A;
                        --primary-200: {{ $color }}33;
                        --primary-300: {{ $color }}4D;
                        --primary-400: {{ $color }}80;
                        --primary-500: {{ $color }};
                        --primary-600: {{ $color }};
                        --primary-700: {{ $color }};
                        --primary-800: {{ $color }};
                        --primary-900: {{ $color }};
                        --primary-950: {{ $color }};
                    }

                    /* Ensure button text is white/visible on colored backgrounds */
                    .fi-btn.fi-color-primary,
                    .fi-btn-primary,
                    .fi-ac-btn-action.fi-color-primary,
                    button.fi-color-primary,
                    a.fi-color-primary {
                        color: white !important;
                    }

                    .fi-btn.fi-color-primary:hover,
                    .fi-btn-primary:hover,
                    .fi-ac-btn-action.fi-color-primary:hover {
                        color: white !important;
                    }

                    /* Force white text on badge/chip primary backgrounds */
                    .fi-badge.fi-color-primary,
                    .fi-badge.fi-bg-color-500 {
                        color: white !important;
                    }
                </style>', ['color' => $primaryColor]);
            },
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('client')
            ->path('app')
            ->viteTheme('resources/css/filament/client/theme.css')
            ->login()
            ->colors([
                'primary' => Color::Amber, // Default, will be overridden by CSS
            ])
            ->brandName(function () {
                // Try to get tenant from app instance (works even when not authenticated)
                $tenant = app()->bound('tenant') ? app('tenant') : null;

                if ($tenant) {
                    return $tenant->name;
                }

                return 'VELOZZ.DIGITAL';
            })
            ->brandLogo(function () {
                // Try to get tenant from app instance (works even when not authenticated)
                $tenant = app()->bound('tenant') ? app('tenant') : null;

                if (! $tenant) {
                    return null;
                }

                $logo = $tenant->settings['logo'] ?? null;

                if ($logo) {
                    return asset('storage/'.$logo);
                }

                return null;
            })
            ->brandLogoHeight('2.5rem')
            ->discoverResources(in: app_path('Filament/Client/Resources'), for: 'App\Filament\Client\Resources')
            ->discoverPages(in: app_path('Filament/Client/Pages'), for: 'App\Filament\Client\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Client/Widgets'), for: 'App\Filament\Client\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                InitializeTenancy::class, // MUST be before authentication
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckTenantLimits::class,
            ]);
    }
}
