<?php

namespace Webkul\Payslip\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Webkul\Payslip\Enums\PayPeriod;
use Webkul\Payslip\Filament\Resources\SalaryStructureResource\Pages;
use Webkul\Payslip\Models\SalaryStructure;

class SalaryStructureResource extends Resource
{
    protected static ?string $model = SalaryStructure::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Payroll';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Structure Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->alphaDash(),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('pay_period')
                            ->options(PayPeriod::class)
                            ->default(PayPeriod::MONTHLY)
                            ->required(),
                        
                        Forms\Components\TextInput::make('basic_salary')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_default')
                            ->label('Default Structure')
                            ->helperText('Only one structure can be set as default per company'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Allowances Configuration')
                    ->schema([
                        Forms\Components\Repeater::make('allowances')
                            ->schema([
                                Forms\Components\TextInput::make('component')
                                    ->required()
                                    ->maxLength(50),
                                
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->required(),
                                
                                Forms\Components\TextInput::make('description')
                                    ->maxLength(255),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add Allowance')
                            ->reorderableWithButtons()
                            ->collapsible()
                    ]),

                Forms\Components\Section::make('Deductions Configuration')
                    ->schema([
                        Forms\Components\Repeater::make('deductions')
                            ->schema([
                                Forms\Components\TextInput::make('component')
                                    ->required()
                                    ->maxLength(50),
                                
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),
                                
                                Forms\Components\TextInput::make('rate')
                                    ->numeric()
                                    ->suffix('%')
                                    ->step(0.01),
                                
                                Forms\Components\TextInput::make('description')
                                    ->maxLength(255),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add Deduction')
                            ->reorderableWithButtons()
                            ->collapsible()
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pay_period')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('basic_salary')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                
                Tables\Columns\TextColumn::make('employeeSalaryStructures_count')
                    ->counts('employeeSalaryStructures')
                    ->label('Employees')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Structure')
                    ->boolean()
                    ->trueLabel('Default only')
                    ->falseLabel('Non-default only'),
                
                Tables\Filters\SelectFilter::make('pay_period')
                    ->options(PayPeriod::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListSalaryStructures::route('/'),
            'create' => Pages\CreateSalaryStructure::route('/create'),
            'view' => Pages\ViewSalaryStructure::route('/{record}'),
            'edit' => Pages\EditSalaryStructure::route('/{record}/edit'),
        ];
    }
}