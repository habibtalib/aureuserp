<?php

namespace Webkul\Payslip\Enums;

use Filament\Support\Contracts\HasLabel;

enum PayPeriod: string implements HasLabel
{
    case MONTHLY = 'monthly';
    case BI_WEEKLY = 'bi_weekly';
    case WEEKLY = 'weekly';
    case ANNUAL = 'annual';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly',
            self::BI_WEEKLY => 'Bi-Weekly',
            self::WEEKLY => 'Weekly',
            self::ANNUAL => 'Annual',
        };
    }

    public function getDaysInPeriod(): int
    {
        return match ($this) {
            self::WEEKLY => 7,
            self::BI_WEEKLY => 14,
            self::MONTHLY => 30,
            self::ANNUAL => 365,
        };
    }
}