<?php

namespace App\Providers\Filament;

use App\Filament\Resources\ActivityResource;
use App\Filament\Resources\FaqResource;
use App\Filament\Resources\PackageResource;
use App\Filament\Resources\RentalServiceResource;
use App\Filament\Resources\TicketResource;
use App\Filament\Resources\UserResource;
use BezhanSalleh\FilamentShield\Resources\RoleResource;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Platform;
use Filament\Widgets;
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
            ->login()
            ->brandLogo('/images/logo.png')
            ->brandLogoHeight('60px')
            ->favicon('/images/favicon-logo.png')
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->groups([
                    NavigationGroup::make()
                        ->label('Quản lí nội dung')
                        ->items([
                            ...TicketResource::getNavigationItems(),
                            ...ActivityResource::getNavigationItems(),
                            ...RentalServiceResource::getNavigationItems(),
                            ...FaqResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make()
                        ->label('Quản lí gói tiết kiệm')
                        ->items([
                            NavigationItem::make('Package')
                                ->label('Gói tiết kiệm')
                                ->icon('heroicon-o-rectangle-stack')
                                ->url(fn(): string => PackageResource::getUrl())
                        ]),
                    NavigationGroup::make()
                        ->label('Tài khoản & phân quyền')
                        ->items([
                            ...UserResource::getNavigationItems(),
                            ...RoleResource::getNavigationItems(),
                        ]),
                ])->items([
                    NavigationItem::make('Dashboard')
                        ->label('Bảng điều khiển')
                        ->icon('heroicon-o-home')
                        ->url(fn(): string => Pages\Dashboard::getUrl()),
                ]);
            })
            ->colors([
                'primary' => Color::Green,
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 2,
                        'sm' => 1
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->spa()
            ->maxContentWidth('full')
            ->globalSearchFieldSuffix(fn(): ?string => match (Platform::detect()) {
                Platform::Windows, Platform::Linux => 'CTRL+K',
                Platform::Mac => '⌘K',
                default => null,
            });
    }
}
