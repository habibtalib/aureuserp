<?php

namespace Webkul\BOM\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BomState: string implements HasLabel, HasColor, HasIcon
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case OBSOLETE = 'obsolete';
    case ARCHIVED = 'archived';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::OBSOLETE => 'Obsolete',
            self::ARCHIVED => 'Archived',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'success',
            self::OBSOLETE => 'warning',
            self::ARCHIVED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-pencil-square',
            self::ACTIVE => 'heroicon-o-check-circle',
            self::OBSOLETE => 'heroicon-o-exclamation-triangle',
            self::ARCHIVED => 'heroicon-o-archive-box',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::DRAFT => 'BOM is being developed and not yet ready for production',
            self::ACTIVE => 'BOM is approved and active for production use',
            self::OBSOLETE => 'BOM is outdated but kept for reference',
            self::ARCHIVED => 'BOM is archived and no longer available',
        };
    }
}