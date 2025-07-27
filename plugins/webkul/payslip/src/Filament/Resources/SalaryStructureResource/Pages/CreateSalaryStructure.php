<?php

namespace Webkul\Payslip\Filament\Resources\SalaryStructureResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\Payslip\Filament\Resources\SalaryStructureResource;

class CreateSalaryStructure extends CreateRecord
{
    protected static string $resource = SalaryStructureResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        
        return $data;
    }
}