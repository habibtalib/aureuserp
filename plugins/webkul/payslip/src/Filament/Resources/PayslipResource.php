<?php

namespace Webkul\Payslip\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Webkul\Employee\Models\Employee;
use Webkul\Payslip\Enums\PayslipStatus;
use Webkul\Payslip\Filament\Resources\PayslipResource\Pages;
use Webkul\Payslip\Models\Payslip;
use Webkul\Payslip\Services\PayslipCalculationService;

class PayslipResource extends Resource
{
    protected static ?string $model = Payslip::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Payroll';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'payslip_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payslip Information')
                    ->schema([
                        Forms\Components\TextInput::make('payslip_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->disabled(),
                        
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Employee $record): string => "{$record->name}")
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Select::make('salary_structure_id')
                            ->relationship('salaryStructure', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Select::make('pay_year')
                            ->options(array_combine(
                                range(date('Y') - 2, date('Y') + 1),
                                range(date('Y') - 2, date('Y') + 1)
                            ))
                            ->default(date('Y'))
                            ->required(),
                        
                        Forms\Components\Select::make('pay_month')
                            ->options([
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December',
                            ])
                            ->default(date('n'))
                            ->required(),
                        
                        Forms\Components\Select::make('status')
                            ->options(PayslipStatus::class)
                            ->default(PayslipStatus::DRAFT)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Salary Breakdown')
                    ->schema([
                        Forms\Components\TextInput::make('basic_salary')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('total_earnings')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('total_deductions')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('gross_salary')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('net_salary')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('employer_contributions')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->collapsed(),

                Forms\Components\Section::make('Attendance Information')
                    ->schema([
                        Forms\Components\TextInput::make('total_working_days')
                            ->numeric()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('days_present')
                            ->numeric()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('days_absent')
                            ->numeric()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('overtime_hours')
                            ->numeric()
                            ->step(0.01)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('overtime_amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->collapsed(),

                Forms\Components\Section::make('Additional Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payslip_number')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pay_year')
                    ->label('Year')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pay_month')
                    ->label('Month')
                    ->formatStateUsing(fn ($state) => date('F', mktime(0, 0, 0, $state, 1)))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('gross_salary')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('net_salary')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => PayslipStatus::DRAFT,
                        'warning' => PayslipStatus::PENDING,
                        'success' => PayslipStatus::APPROVED,
                        'info' => PayslipStatus::PAID,
                        'danger' => PayslipStatus::CANCELLED,
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(PayslipStatus::class)
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('pay_year')
                    ->options(array_combine(
                        range(date('Y') - 5, date('Y')),
                        range(date('Y') - 5, date('Y'))
                    )),
                
                Tables\Filters\SelectFilter::make('pay_month')
                    ->options([
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ]),
                
                Tables\Filters\Filter::make('salary_range')
                    ->form([
                        Forms\Components\TextInput::make('salary_from')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('salary_to')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['salary_from'],
                                fn (Builder $query, $amount): Builder => $query->where('net_salary', '>=', $amount),
                            )
                            ->when(
                                $data['salary_to'],
                                fn (Builder $query, $amount): Builder => $query->where('net_salary', '<=', $amount),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('calculate')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->action(function (Payslip $record) {
                        app(PayslipCalculationService::class)->recalculatePayslip($record);
                    })
                    ->visible(fn (Payslip $record) => $record->status === PayslipStatus::DRAFT),
                
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Payslip $record) {
                        $record->approve(auth()->user());
                    })
                    ->visible(fn (Payslip $record) => $record->status === PayslipStatus::PENDING),
                
                Tables\Actions\Action::make('mark_paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->action(function (Payslip $record) {
                        $record->markAsPaid(auth()->user());
                    })
                    ->visible(fn (Payslip $record) => $record->status === PayslipStatus::APPROVED),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('bulk_calculate')
                        ->label('Recalculate Selected')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->action(function ($records) {
                            $calculationService = app(PayslipCalculationService::class);
                            foreach ($records as $payslip) {
                                if ($payslip->status === PayslipStatus::DRAFT) {
                                    $calculationService->recalculatePayslip($payslip);
                                }
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayslips::route('/'),
            'create' => Pages\CreatePayslip::route('/create'),
            'view' => Pages\ViewPayslip::route('/{record}'),
            'edit' => Pages\EditPayslip::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', PayslipStatus::PENDING)->count();
    }
}