<?php

namespace Webkul\Claims\Filament\Resources\ClaimCategoryResource\Pages;

use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Webkul\Claims\Filament\Resources\ClaimCategoryResource;

class ViewClaimCategory extends ViewRecord
{
    protected static string $resource = ClaimCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Category Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        
                        Infolists\Components\TextEntry::make('code'),
                        
                        Infolists\Components\TextEntry::make('description')
                            ->placeholder('No description')
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('max_amount')
                            ->money('USD')
                            ->placeholder('No limit'),
                        
                        Infolists\Components\IconEntry::make('requires_receipt')
                            ->label('Requires Receipt')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('System Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                        
                        Infolists\Components\TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('N/A'),
                        
                        Infolists\Components\TextEntry::make('updater.name')
                            ->label('Updated By')
                            ->placeholder('N/A'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}