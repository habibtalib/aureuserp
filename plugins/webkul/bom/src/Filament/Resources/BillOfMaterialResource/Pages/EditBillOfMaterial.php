<?php

namespace Webkul\BOM\Filament\Resources\BillOfMaterialResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Webkul\BOM\Enums\BomState;
use Webkul\BOM\Filament\Resources\BillOfMaterialResource;

class EditBillOfMaterial extends EditRecord
{
    protected static string $resource = BillOfMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            
            Actions\Action::make('activate')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->getRecord()->state === BomState::DRAFT)
                ->action(fn () => $this->getRecord()->activate()),

            Actions\Action::make('make_obsolete')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->getRecord()->state === BomState::ACTIVE)
                ->action(fn () => $this->getRecord()->makeObsolete()),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}