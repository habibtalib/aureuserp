<?php

namespace Webkul\Claims\Filament\Resources\ClaimCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Webkul\Claims\Filament\Resources\ClaimCategoryResource;

class EditClaimCategory extends EditRecord
{
    protected static string $resource = ClaimCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        
        return $data;
    }
}