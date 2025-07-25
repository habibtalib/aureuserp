<?php

namespace Webkul\BOM\Filament\Resources\BillOfMaterialResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Webkul\BOM\Filament\Resources\BillOfMaterialResource;

class ListBillOfMaterials extends ListRecords
{
    protected static string $resource = BillOfMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}