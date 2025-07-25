<?php

namespace Webkul\BOM;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Webkul\BOM\Filament\Resources\BillOfMaterialResource;

class BOMPlugin implements Plugin
{
    use EvaluatesClosures;

    public function getId(): string
    {
        return 'bom';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                BillOfMaterialResource::class,
            ])
            ->navigationGroups([
                'Manufacturing' => [
                    'label' => 'Manufacturing',
                    'icon' => 'heroicon-o-wrench-screwdriver',
                    'sort' => 3,
                ],
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
