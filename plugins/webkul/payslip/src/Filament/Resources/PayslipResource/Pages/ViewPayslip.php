<?php

namespace Webkul\Payslip\Filament\Resources\PayslipResource\Pages;

use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Webkul\Payslip\Enums\PayslipStatus;
use Webkul\Payslip\Filament\Resources\PayslipResource;
use Webkul\Payslip\Services\PayslipCalculationService;

class ViewPayslip extends ViewRecord
{
    protected static string $resource = PayslipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('calculate')
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->action(function () {
                    app(PayslipCalculationService::class)->recalculatePayslip($this->getRecord());
                    $this->refreshFormData();
                })
                ->visible(fn () => $this->getRecord()->status === PayslipStatus::DRAFT),
            
            Actions\Action::make('approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $this->getRecord()->approve(auth()->user());
                    $this->refreshFormData();
                })
                ->visible(fn () => $this->getRecord()->status === PayslipStatus::PENDING),
            
            Actions\Action::make('mark_paid')
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->action(function () {
                    $this->getRecord()->markAsPaid(auth()->user());
                    $this->refreshFormData();
                })
                ->visible(fn () => $this->getRecord()->status === PayslipStatus::APPROVED),
            
            Actions\Action::make('download_pdf')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->url(fn () => route('payslip.pdf', $this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn () => in_array($this->getRecord()->status, [PayslipStatus::APPROVED, PayslipStatus::PAID])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Payslip Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('payslip_number')
                            ->label('Payslip Number'),
                        
                        Infolists\Components\TextEntry::make('employee.name')
                            ->label('Employee'),
                        
                        Infolists\Components\TextEntry::make('salaryStructure.name')
                            ->label('Salary Structure'),
                        
                        Infolists\Components\TextEntry::make('pay_year')
                            ->label('Year'),
                        
                        Infolists\Components\TextEntry::make('pay_month')
                            ->label('Month')
                            ->formatStateUsing(fn ($state) => date('F', mktime(0, 0, 0, $state, 1))),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (PayslipStatus $state): string => match ($state) {
                                PayslipStatus::DRAFT => 'gray',
                                PayslipStatus::PENDING => 'warning',
                                PayslipStatus::APPROVED => 'success',
                                PayslipStatus::PAID => 'info',
                                PayslipStatus::CANCELLED => 'danger',
                            }),
                        
                        Infolists\Components\TextEntry::make('pay_period_start')
                            ->label('Pay Period')
                            ->formatStateUsing(fn ($record) => $record->getPayPeriodDescription()),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Salary Breakdown')
                    ->schema([
                        Infolists\Components\TextEntry::make('basic_salary')
                            ->money('USD'),
                        
                        Infolists\Components\TextEntry::make('total_earnings')
                            ->money('USD'),
                        
                        Infolists\Components\TextEntry::make('total_deductions')
                            ->money('USD'),
                        
                        Infolists\Components\TextEntry::make('gross_salary')
                            ->money('USD'),
                        
                        Infolists\Components\TextEntry::make('net_salary')
                            ->money('USD')
                            ->weight('bold'),
                        
                        Infolists\Components\TextEntry::make('employer_contributions')
                            ->money('USD'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Payslip Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('payslipItems')
                            ->schema([
                                Infolists\Components\TextEntry::make('component_name')
                                    ->label('Component'),
                                
                                Infolists\Components\TextEntry::make('component_type')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'earning' => 'success',
                                        'deduction' => 'danger',
                                        'employer_contribution' => 'info',
                                    }),
                                
                                Infolists\Components\TextEntry::make('base_amount')
                                    ->money('USD'),
                                
                                Infolists\Components\TextEntry::make('rate')
                                    ->suffix('%')
                                    ->placeholder('N/A'),
                                
                                Infolists\Components\TextEntry::make('calculated_amount')
                                    ->money('USD')
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('calculation_notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ])
                            ->columns(5)
                    ]),

                Infolists\Components\Section::make('Attendance Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_working_days')
                            ->label('Working Days'),
                        
                        Infolists\Components\TextEntry::make('days_present')
                            ->label('Present Days'),
                        
                        Infolists\Components\TextEntry::make('days_absent')
                            ->label('Absent Days'),
                        
                        Infolists\Components\TextEntry::make('overtime_hours')
                            ->label('Overtime Hours')
                            ->suffix(' hrs'),
                        
                        Infolists\Components\TextEntry::make('overtime_amount')
                            ->label('Overtime Amount')
                            ->money('USD'),
                    ])
                    ->columns(3)
                    ->collapsed(),

                Infolists\Components\Section::make('Processing Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('processed_date')
                            ->date()
                            ->placeholder('Not processed'),
                        
                        Infolists\Components\TextEntry::make('processor.name')
                            ->label('Processed By')
                            ->placeholder('N/A'),
                        
                        Infolists\Components\TextEntry::make('approved_date')
                            ->date()
                            ->placeholder('Not approved'),
                        
                        Infolists\Components\TextEntry::make('approver.name')
                            ->label('Approved By')
                            ->placeholder('N/A'),
                        
                        Infolists\Components\TextEntry::make('paid_date')
                            ->date()
                            ->placeholder('Not paid'),
                        
                        Infolists\Components\TextEntry::make('payer.name')
                            ->label('Paid By')
                            ->placeholder('N/A'),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}