<?php

namespace Webkul\Payslip\Filament\Resources\SalaryStructureResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Webkul\Payslip\Filament\Resources\SalaryStructureResource;

class ViewSalaryStructure extends ViewRecord
{
    protected static string $resource = SalaryStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}