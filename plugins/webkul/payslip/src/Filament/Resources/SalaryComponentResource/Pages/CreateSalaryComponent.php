<?php

namespace Webkul\Payslip\Filament\Resources\SalaryComponentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\Payslip\Filament\Resources\SalaryComponentResource;

class CreateSalaryComponent extends CreateRecord
{
    protected static string $resource = SalaryComponentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        
        return $data;
    }
}