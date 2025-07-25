<?php

namespace Webkul\BOM;

use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Webkul\BOM\Commands\BOMInstallCommand;

class BOMServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('bom')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                'create_bom_bill_of_materials_table',
                'create_bom_bom_lines_table',
                'create_bom_bom_versions_table',
            ])
            ->hasCommands([
                BOMInstallCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        //
    }

    public function packageBooted(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            // Schedule BOM version cleanup (remove expired versions)
            $schedule->command('bom:cleanup-versions')
                ->daily()
                ->at('02:00');
        });
    }

    public function provides(): array
    {
        return [
            'bom',
        ];
    }
}