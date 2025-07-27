<?php

namespace Webkul\Payslip;

use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Webkul\Payslip\Console\Commands\GeneratePayslipsCommand;
use Webkul\Payslip\Console\Commands\InstallCommand;
use Webkul\Payslip\Console\Commands\SendPayslipEmailsCommand;

class PayslipServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('payslip')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                'create_payslip_salary_structures_table',
                'create_payslip_salary_components_table',
                'create_payslip_payslips_table',
                'create_payslip_payslip_items_table',
                'create_payslip_attendance_summaries_table',
                'create_payslip_employee_salary_structures_table',
            ])
            ->hasCommands([
                InstallCommand::class,
                GeneratePayslipsCommand::class,
                SendPayslipEmailsCommand::class,
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
            
            // Schedule automatic payslip generation on the last day of each month
            $schedule->command('payslip:generate-monthly')
                ->monthlyOn(31, '23:00')
                ->description('Generate monthly payslips');
            
            // Schedule payslip email sending
            $schedule->command('payslip:send-emails')
                ->dailyAt('09:00')
                ->description('Send pending payslip emails');
        });
    }

    public function provides(): array
    {
        return [
            'payslip',
        ];
    }
}