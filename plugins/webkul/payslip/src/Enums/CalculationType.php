<?php

namespace Webkul\Payslip\Enums;

use Filament\Support\Contracts\HasLabel;

enum CalculationType: string implements HasLabel
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';
    case COMPUTED = 'computed';
    case VARIABLE = 'variable';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FIXED => 'Fixed Amount',
            self::PERCENTAGE => 'Percentage of Base',
            self::COMPUTED => 'Computed/Formula Based',
            self::VARIABLE => 'Variable/Manual Entry',
        };
    }
}