<?php

namespace Webkul\Payslip\Filament\Resources\SalaryComponentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Webkul\Payslip\Filament\Resources\SalaryComponentResource;

class ViewSalaryComponent extends ViewRecord
{
    protected static string $resource = SalaryComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}