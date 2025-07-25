<?php

namespace Webkul\BOM\Filament\Resources\BillOfMaterialResource\Pages;

use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Webkul\BOM\Enums\BomState;
use Webkul\BOM\Filament\Resources\BillOfMaterialResource;

class ViewBillOfMaterial extends ViewRecord
{
    protected static string $resource = BillOfMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

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

            Actions\Action::make('explode_bom')
                ->icon('heroicon-o-list-bullet')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.bill-of-materials.explode', $this->getRecord()))
                ->openUrlInNewTab(),

            Actions\Action::make('where_used')
                ->icon('heroicon-o-magnifying-glass')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.bill-of-materials.where-used', $this->getRecord()))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('BOM Name'),

                                Infolists\Components\TextEntry::make('reference')
                                    ->label('Reference')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Product'),

                                Infolists\Components\TextEntry::make('version')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('type')
                                    ->badge()
                                    ->color(fn () => $this->getRecord()->type->getColor()),

                                Infolists\Components\TextEntry::make('state')
                                    ->badge()
                                    ->color(fn () => $this->getRecord()->state->getColor()),

                                Infolists\Components\TextEntry::make('quantity_to_produce')
                                    ->label('Quantity to Produce')
                                    ->numeric(4),

                                Infolists\Components\TextEntry::make('unit.name')
                                    ->label('Unit'),

                                Infolists\Components\TextEntry::make('effective_date')
                                    ->date(),

                                Infolists\Components\TextEntry::make('expiry_date')
                                    ->date(),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Components')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('bomLines')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('sequence')
                                            ->label('Seq'),

                                        Infolists\Components\TextEntry::make('product.name')
                                            ->label('Component'),

                                        Infolists\Components\TextEntry::make('quantity')
                                            ->numeric(4),

                                        Infolists\Components\TextEntry::make('unit.name')
                                            ->label('Unit'),
                                    ]),

                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('component_type')
                                            ->badge()
                                            ->color(fn ($state) => $state->getColor()),

                                        Infolists\Components\TextEntry::make('waste_percentage')
                                            ->suffix('%')
                                            ->label('Waste %'),

                                        Infolists\Components\IconEntry::make('is_optional')
                                            ->boolean()
                                            ->label('Optional'),
                                    ]),

                                Infolists\Components\TextEntry::make('subBom.name')
                                    ->label('Sub-BOM')
                                    ->visible(fn ($record) => $record->sub_bom_id),

                                Infolists\Components\TextEntry::make('notes')
                                    ->columnSpanFull()
                                    ->visible(fn ($record) => $record->notes),
                            ])
                            ->columns(1),
                    ]),

                Infolists\Components\Section::make('Cost Analysis')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_cost')
                                    ->label('Total Cost')
                                    ->money('USD')
                                    ->state(fn () => $this->getRecord()->getTotalCost()),

                                Infolists\Components\TextEntry::make('unit_cost')
                                    ->label('Unit Cost')
                                    ->money('USD')
                                    ->state(fn () => $this->getRecord()->getUnitCost()),

                                Infolists\Components\TextEntry::make('bomLines_count')
                                    ->label('Total Components')
                                    ->state(fn () => $this->getRecord()->bomLines->count()),
                            ]),
                    ]),

                Infolists\Components\Section::make('Audit Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('creator.name')
                                    ->label('Created By'),

                                Infolists\Components\TextEntry::make('updater.name')
                                    ->label('Updated By'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime(),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}