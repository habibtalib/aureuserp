<?php

namespace Webkul\Payslip\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Webkul\Payslip\Enums\CalculationType;
use Webkul\Payslip\Enums\ComponentType;
use Webkul\Payslip\Filament\Resources\SalaryComponentResource\Pages;
use Webkul\Payslip\Models\SalaryComponent;

class SalaryComponentResource extends Resource
{
    protected static ?string $model = SalaryComponent::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Payroll';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Component Information')
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
                        
                        Forms\Components\Select::make('type')
                            ->options(ComponentType::class)
                            ->required(),
                        
                        Forms\Components\Select::make('calculation_type')
                            ->options(CalculationType::class)
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('default_amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->visible(fn (Forms\Get $get) => in_array($get('calculation_type'), ['fixed', 'variable'])),
                        
                        Forms\Components\TextInput::make('default_rate')
                            ->numeric()
                            ->suffix('%')
                            ->step(0.01)
                            ->visible(fn (Forms\Get $get) => $get('calculation_type') === 'percentage'),
                        
                        Forms\Components\Textarea::make('formula')
                            ->rows(3)
                            ->helperText('Use variables like {basic_salary}, {gross_salary}, etc.')
                            ->visible(fn (Forms\Get $get) => $get('calculation_type') === 'computed')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('is_taxable')
                            ->label('Taxable')
                            ->helperText('Whether this component is subject to income tax'),
                        
                        Forms\Components\Toggle::make('is_provident_fund_applicable')
                            ->label('PF Applicable')
                            ->helperText('Whether this component contributes to provident fund'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        
                        Forms\Components\TextInput::make('display_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Order in which this component appears in payslips'),
                    ])
                    ->columns(2),
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
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'success' => ComponentType::EARNING,
                        'danger' => ComponentType::DEDUCTION,
                        'info' => ComponentType::EMPLOYER_CONTRIBUTION,
                    ]),
                
                Tables\Columns\TextColumn::make('calculation_type')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('default_amount')
                    ->money('USD')
                    ->placeholder('N/A'),
                
                Tables\Columns\TextColumn::make('default_rate')
                    ->suffix('%')
                    ->placeholder('N/A'),
                
                Tables\Columns\IconColumn::make('is_taxable')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('display_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(ComponentType::class)
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('calculation_type')
                    ->options(CalculationType::class)
                    ->multiple(),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                
                Tables\Filters\TernaryFilter::make('is_taxable')
                    ->label('Taxable Status')
                    ->boolean()
                    ->trueLabel('Taxable only')
                    ->falseLabel('Non-taxable only'),
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
            ->defaultSort('display_order');
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
            'index' => Pages\ListSalaryComponents::route('/'),
            'create' => Pages\CreateSalaryComponent::route('/create'),
            'view' => Pages\ViewSalaryComponent::route('/{record}'),
            'edit' => Pages\EditSalaryComponent::route('/{record}/edit'),
        ];
    }
}