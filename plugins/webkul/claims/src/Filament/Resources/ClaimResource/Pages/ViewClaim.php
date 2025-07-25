<?php

namespace Webkul\Claims\Filament\Resources\ClaimResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Webkul\Claims\Enums\ClaimStatus;
use Webkul\Claims\Filament\Resources\ClaimResource;
use Webkul\Employee\Models\Employee;

class ViewClaim extends ViewRecord
{
    protected static string $resource = ClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('submit')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->action(function () {
                    $this->getRecord()->submit();
                    $this->refreshFormData(['status', 'submitted_at']);
                })
                ->visible(fn () => $this->getRecord()->status === ClaimStatus::DRAFT),
            
            Actions\Action::make('approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label('Approval Notes')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $approver = Employee::where('user_id', auth()->id())->first();
                    $this->getRecord()->approve($approver, $data['notes'] ?? null);
                    $this->refreshFormData(['status', 'approved_at', 'approved_by', 'approval_notes']);
                })
                ->visible(fn () => in_array($this->getRecord()->status, [ClaimStatus::SUBMITTED, ClaimStatus::UNDER_REVIEW])),
            
            Actions\Action::make('reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->getRecord()->reject($data['reason']);
                    $this->refreshFormData(['status', 'rejection_reason']);
                })
                ->visible(fn () => in_array($this->getRecord()->status, [ClaimStatus::SUBMITTED, ClaimStatus::UNDER_REVIEW])),
            
            Actions\Action::make('mark_paid')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->action(function () {
                    $this->getRecord()->markAsPaid();
                    $this->refreshFormData(['status', 'paid_at']);
                })
                ->visible(fn () => $this->getRecord()->status === ClaimStatus::APPROVED),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Claim Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('claim_number')
                            ->label('Claim Number'),
                        
                        Infolists\Components\TextEntry::make('employee.first_name')
                            ->label('Employee')
                            ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}"),
                        
                        Infolists\Components\TextEntry::make('category.name')
                            ->label('Category'),
                        
                        Infolists\Components\TextEntry::make('title'),
                        
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('total_amount')
                            ->money('USD'),
                        
                        Infolists\Components\TextEntry::make('currency'),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (ClaimStatus $state): string => match ($state) {
                                ClaimStatus::DRAFT => 'gray',
                                ClaimStatus::SUBMITTED => 'warning',
                                ClaimStatus::UNDER_REVIEW => 'info',
                                ClaimStatus::APPROVED => 'success',
                                ClaimStatus::REJECTED => 'danger',
                                ClaimStatus::PAID => 'success',
                            }),
                        
                        Infolists\Components\TextEntry::make('expense_date')
                            ->date(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Approval Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('submitted_at')
                            ->date()
                            ->placeholder('Not submitted'),
                        
                        Infolists\Components\TextEntry::make('approved_at')
                            ->date()
                            ->placeholder('Not approved'),
                        
                        Infolists\Components\TextEntry::make('paid_at')
                            ->date()
                            ->placeholder('Not paid'),
                        
                        Infolists\Components\TextEntry::make('approver.first_name')
                            ->label('Approved By')
                            ->formatStateUsing(fn ($record) => $record->approver ? "{$record->approver->first_name} {$record->approver->last_name}" : 'N/A')
                            ->placeholder('N/A'),
                        
                        Infolists\Components\TextEntry::make('approval_notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->placeholder('N/A')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->status === ClaimStatus::REJECTED),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Infolists\Components\Section::make('Claim Lines')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('claimLines')
                            ->schema([
                                Infolists\Components\TextEntry::make('description'),
                                
                                Infolists\Components\TextEntry::make('amount')
                                    ->money('USD'),
                                
                                Infolists\Components\TextEntry::make('expense_date')
                                    ->date(),
                                
                                Infolists\Components\TextEntry::make('receipt_reference')
                                    ->placeholder('N/A'),
                                
                                Infolists\Components\TextEntry::make('notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                    ]),

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