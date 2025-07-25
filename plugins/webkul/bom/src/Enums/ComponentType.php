<?php

namespace Webkul\BOM\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ComponentType: string implements HasLabel, HasColor, HasIcon
{
    case MATERIAL = 'material';
    case COMPONENT = 'component';
    case SUB_ASSEMBLY = 'sub_assembly';
    case CONSUMABLE = 'consumable';
    case BYPRODUCT = 'byproduct';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MATERIAL => 'Raw Material',
            self::COMPONENT => 'Component',
            self::SUB_ASSEMBLY => 'Sub-assembly',
            self::CONSUMABLE => 'Consumable',
            self::BYPRODUCT => 'By-product',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MATERIAL => 'blue',
            self::COMPONENT => 'green',
            self::SUB_ASSEMBLY => 'purple',
            self::CONSUMABLE => 'yellow',
            self::BYPRODUCT => 'orange',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::MATERIAL => 'heroicon-o-cube',
            self::COMPONENT => 'heroicon-o-cog-6-tooth',
            self::SUB_ASSEMBLY => 'heroicon-o-squares-plus',
            self::CONSUMABLE => 'heroicon-o-beaker',
            self::BYPRODUCT => 'heroicon-o-arrow-path',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::MATERIAL => 'Basic raw materials used in production',
            self::COMPONENT => 'Manufactured or purchased components',
            self::SUB_ASSEMBLY => 'Pre-assembled components with their own BOM',
            self::CONSUMABLE => 'Items consumed during production but not part of final product',
            self::BYPRODUCT => 'Secondary products produced during manufacturing',
        };
    }
}