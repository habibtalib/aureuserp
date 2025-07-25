<?php

namespace Webkul\Claims;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Webkul\Claims\Filament\Resources\ClaimResource;
use Webkul\Claims\Filament\Resources\ClaimCategoryResource;

class ClaimsPlugin implements Plugin
{
    use EvaluatesClosures;

    public function getId(): string
    {
        return 'claims';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ClaimResource::class,
                ClaimCategoryResource::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Human Resources')
                    ->icon('heroicon-o-users'),
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }
}