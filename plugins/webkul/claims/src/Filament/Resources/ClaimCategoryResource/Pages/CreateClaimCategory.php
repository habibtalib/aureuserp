<?php

namespace Webkul\Claims\Filament\Resources\ClaimCategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\Claims\Filament\Resources\ClaimCategoryResource;

class CreateClaimCategory extends CreateRecord
{
    protected static string $resource = ClaimCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        
        return $data;
    }
}