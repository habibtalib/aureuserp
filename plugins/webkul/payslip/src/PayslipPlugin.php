<?php

namespace Webkul\Payslip;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Webkul\Payslip\Filament\Resources\PayslipResource;
use Webkul\Payslip\Filament\Resources\SalaryComponentResource;
use Webkul\Payslip\Filament\Resources\SalaryStructureResource;

class PayslipPlugin implements Plugin
{
    use EvaluatesClosures;

    public function getId(): string
    {
        return 'payslip';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                PayslipResource::class,
                SalaryStructureResource::class,
                SalaryComponentResource::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Payroll')
                    ->icon('heroicon-o-banknotes'),
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