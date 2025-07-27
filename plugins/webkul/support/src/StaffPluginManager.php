<?php

namespace Webkul\Support;

use Filament\Contracts\Plugin;
use Filament\Panel;

class StaffPluginManager implements Plugin
{
    public function getId(): string
    {
        return 'staff-plugin-manager';
    }

    public function register(Panel $panel): void
    {
        $plugins = $this->getStaffPlugins();

        foreach ($plugins as $modulePlugin) {
            $panel->plugin($modulePlugin::make());
        }
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    protected function getStaffPlugins(): array
    {
        // Only include plugins that staff users should have access to
        $staffPlugins = [
            \Webkul\Payslip\PayslipPlugin::class,
            \Webkul\Claims\ClaimsPlugin::class,
            \Webkul\BOM\BOMPlugin::class,
        ];

        return collect($staffPlugins)
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}