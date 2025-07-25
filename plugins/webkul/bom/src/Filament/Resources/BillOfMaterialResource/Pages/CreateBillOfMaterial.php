<?php

namespace Webkul\BOM\Filament\Resources\BillOfMaterialResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\BOM\Filament\Resources\BillOfMaterialResource;

class CreateBillOfMaterial extends CreateRecord
{
    protected static string $resource = BillOfMaterialResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id ?? 1;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}