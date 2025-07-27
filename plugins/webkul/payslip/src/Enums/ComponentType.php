<?php

namespace Webkul\Payslip\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ComponentType: string implements HasLabel, HasColor, HasIcon
{
    case EARNING = 'earning';
    case DEDUCTION = 'deduction';
    case EMPLOYER_CONTRIBUTION = 'employer_contribution';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EARNING => 'Earning',
            self::DEDUCTION => 'Deduction',
            self::EMPLOYER_CONTRIBUTION => 'Employer Contribution',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::EARNING => 'success',
            self::DEDUCTION => 'danger',
            self::EMPLOYER_CONTRIBUTION => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::EARNING => 'heroicon-o-plus-circle',
            self::DEDUCTION => 'heroicon-o-minus-circle',
            self::EMPLOYER_CONTRIBUTION => 'heroicon-o-building-office',
        };
    }
}