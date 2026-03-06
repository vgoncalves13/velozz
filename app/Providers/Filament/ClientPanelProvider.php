<?php

namespace App\Providers\Filament;

use App\Filament\Client\Pages\Dashboard;
use App\Http\Middleware\CheckTenantLimits;
use App\Http\Middleware\InitializeTenancy;
use App\Http\Middleware\SetLocale;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
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
                $tenant = Filament::getTenant();

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

        // Add meta tags for SEO and social sharing
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            function (): string {
                $tenant = Filament::getTenant();
                $tenantName = $tenant?->name ?? 'VELOZZ.DIGITAL';
                $description = "Professional CRM platform for {$tenantName} - Manage leads, WhatsApp conversations, and sales pipeline in one place.";

                return Blade::render('
                    <meta name="description" content="{{ $description }}">
                    <meta name="author" content="{{ $tenantName }}">

                    <!-- Open Graph / Facebook -->
                    <meta property="og:type" content="website">
                    <meta property="og:title" content="{{ $tenantName }} - CRM Platform">
                    <meta property="og:description" content="{{ $description }}">
                    <meta property="og:site_name" content="{{ $tenantName }}">

                    <!-- Twitter -->
                    <meta name="twitter:card" content="summary_large_image">
                    <meta name="twitter:title" content="{{ $tenantName }} - CRM Platform">
                    <meta name="twitter:description" content="{{ $description }}">

                    <!-- Favicon -->
                    <link rel="icon" type="image/x-icon" href="{{ asset(\'favicon.ico\') }}">
                    <link rel="apple-touch-icon" href="{{ asset(\'favicon.ico\') }}">
                ', [
                    'tenantName' => $tenantName,
                    'description' => $description,
                ]);
            },
        );

        // Add loading indicator
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn (): string => '<div
                x-data="{ loading: false }"
                x-on:livewire:navigating.window="loading = true"
                x-on:livewire:navigated.window="loading = false"
                x-show="loading"
                x-transition:enter="transition ease-out duration-300"
                x-transition:leave="transition ease-in duration-300"
                class="fixed top-0 left-0 right-0 h-1 bg-primary-600 z-[9999]"
                style="display: none;">
                <div class="h-full bg-primary-400 animate-pulse"></div>
            </div>',
        );

        // Add language switcher to footer
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): string => Blade::render('<div class="flex justify-center p-4 border-t border-gray-200 dark:border-gray-700">
                <livewire:language-switcher />
            </div>'),
        );

        // For admin_master: intercept tenant switcher navigation and route through
        // the impersonation flow, since sessions are not shared across subdomains.
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            function (): string {
                if (! auth()->check() || ! auth()->user()->isAdminMaster()) {
                    return '';
                }

                return <<<'HTML'
                <script>
                    (function () {
                        var baseDomain = window.location.hostname.includes('.')
                            ? window.location.hostname.split('.').slice(1).join('.')
                            : null;

                        if (!baseDomain) return;

                        document.addEventListener('click', function (e) {
                            var link = e.target.closest('a[href]');
                            if (!link) return;

                            var targetHost = link.hostname;
                            if (!targetHost) return;
                            if (targetHost === window.location.hostname) return;
                            if (!targetHost.endsWith('.' + baseDomain)) return;

                            var slug = targetHost.split('.')[0];
                            e.preventDefault();
                            e.stopPropagation();
                            window.location.href = '/app/switch-tenant/' + slug;
                        }, true);
                    })();
                </script>
                HTML;
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
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber, // Default, will be overridden by CSS
            ])
            ->brandName(function () {
                $tenant = Filament::getTenant();

                return $tenant?->name ?? 'VELOZZ.DIGITAL';
            })
            ->brandLogo(function () {
                $tenant = Filament::getTenant();

                if (! $tenant) {
                    return null;
                }

                $logo = $tenant->settings['logo'] ?? null;

                return $logo ? asset('storage/'.$logo) : null;
            })
            ->brandLogoHeight('4rem')
            ->favicon(asset('favicon.ico'))
            ->tenant(Tenant::class, slugAttribute: 'slug')
            ->tenantDomain(config('tenancy.domain'))
            ->tenantMenu(fn (): bool => auth()->check() && auth()->user()->isAdminMaster())
            ->tenantMiddleware([
                InitializeTenancy::class,
                CheckTenantLimits::class,
            ], isPersistent: true)
            ->discoverResources(in: app_path('Filament/Client/Resources'), for: 'App\Filament\Client\Resources')
            ->discoverPages(in: app_path('Filament/Client/Pages'), for: 'App\Filament\Client\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->maxContentWidth('full')
            ->discoverWidgets(in: app_path('Filament/Client/Widgets'), for: 'App\Filament\Client\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                SetLocale::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
