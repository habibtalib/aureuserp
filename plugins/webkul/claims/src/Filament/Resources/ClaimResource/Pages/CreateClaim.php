<?php

namespace Webkul\Claims\Filament\Resources\ClaimResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\Claims\Filament\Resources\ClaimResource;

class CreateClaim extends CreateRecord
{
    protected static string $resource = ClaimResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}