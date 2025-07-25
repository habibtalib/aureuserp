<?php

namespace Webkul\BOM\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BomType: string implements HasLabel, HasColor, HasIcon
{
    case STANDARD = 'standard';
    case KIT = 'kit';
    case PHANTOM = 'phantom';
    case ASSEMBLY = 'assembly';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::STANDARD => 'Standard BOM',
            self::KIT => 'Kit BOM',
            self::PHANTOM => 'Phantom BOM',
            self::ASSEMBLY => 'Assembly BOM',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::STANDARD => 'primary',
            self::KIT => 'success',
            self::PHANTOM => 'warning',
            self::ASSEMBLY => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::STANDARD => 'heroicon-o-squares-2x2',
            self::KIT => 'heroicon-o-cube',
            self::PHANTOM => 'heroicon-o-eye-slash',
            self::ASSEMBLY => 'heroicon-o-wrench-screwdriver',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::STANDARD => 'Regular manufacturing BOM with material consumption and production output',
            self::KIT => 'Kit BOM for selling components together without manufacturing',
            self::PHANTOM => 'Phantom BOM that explodes components but is not manufactured itself',
            self::ASSEMBLY => 'Assembly BOM for complex products with sub-assemblies',
        };
    }
}