<?php

namespace Webkul\Claims;

use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Webkul\Claims\Console\Commands\InstallCommand;

class ClaimsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('claims')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                'create_claims_categories_table',
                'create_claims_claims_table',
                'create_claims_claim_lines_table',
                'create_claims_approvals_table',
                'create_claims_attachments_table',
            ])
            ->hasCommands([
                InstallCommand::class,
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
            
            // Schedule claim reminders for pending approvals
            $schedule->command('claims:send-reminders')
                ->daily()
                ->at('09:00');
        });
    }

    public function provides(): array
    {
        return [
            'claims',
        ];
    }
}