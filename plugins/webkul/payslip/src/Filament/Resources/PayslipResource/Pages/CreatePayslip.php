<?php

namespace Webkul\Payslip\Filament\Resources\PayslipResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\Payslip\Filament\Resources\PayslipResource;
use Webkul\Payslip\Services\PayslipCalculationService;

class CreatePayslip extends CreateRecord
{
    protected static string $resource = PayslipResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Automatically calculate the payslip after creation
        $calculationService = app(PayslipCalculationService::class);
        $calculationService->recalculatePayslip($this->getRecord());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}