<?php

namespace Webkul\Claims\Filament\Resources\ClaimCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Webkul\Claims\Filament\Resources\ClaimCategoryResource;

class ListClaimCategories extends ListRecords
{
    protected static string $resource = ClaimCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}