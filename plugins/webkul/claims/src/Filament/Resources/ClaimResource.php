<?php

namespace Webkul\Claims\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Webkul\Claims\Enums\ClaimStatus;
use Webkul\Claims\Filament\Resources\ClaimResource\Pages;
use Webkul\Claims\Models\Claim;
use Webkul\Claims\Models\ClaimCategory;
use Webkul\Employee\Models\Employee;

class ClaimResource extends Resource
{
    protected static ?string $model = Claim::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'claim_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Claim Information')
                    ->schema([
                        Forms\Components\TextInput::make('claim_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Employee $record): string => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->options(ClaimCategory::active()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required(),
                        
                        Forms\Components\TextInput::make('currency')
                            ->default('USD')
                            ->maxLength(3)
                            ->required(),
                        
                        Forms\Components\DatePicker::make('expense_date')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\Select::make('status')
                            ->options(ClaimStatus::class)
                            ->default(ClaimStatus::DRAFT)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Approval Information')
                    ->schema([
                        Forms\Components\DatePicker::make('submitted_at')
                            ->disabled(),
                        
                        Forms\Components\DatePicker::make('approved_at')
                            ->disabled(),
                        
                        Forms\Components\DatePicker::make('paid_at')
                            ->disabled(),
                        
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Employee $record): string => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->disabled(),
                        
                        Forms\Components\Textarea::make('approval_notes')
                            ->rows(2)
                            ->disabled(),
                        
                        Forms\Components\Textarea::make('rejection_reason')
                            ->rows(2)
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Forms\Components\Section::make('Claim Lines')
                    ->schema([
                        Forms\Components\Repeater::make('claimLines')
                            ->relationship()
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->required()
                                    ->rows(2),
                                
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->required(),
                                
                                Forms\Components\DatePicker::make('expense_date')
                                    ->required(),
                                
                                Forms\Components\TextInput::make('receipt_reference')
                                    ->maxLength(100),
                                
                                Forms\Components\Textarea::make('notes')
                                    ->rows(1),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add Expense Line')
                            ->reorderableWithButtons()
                            ->collapsible()
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('claim_number')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Employee')
                    ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => ClaimStatus::DRAFT,
                        'warning' => ClaimStatus::SUBMITTED,
                        'info' => ClaimStatus::UNDER_REVIEW,
                        'success' => ClaimStatus::APPROVED,
                        'danger' => ClaimStatus::REJECTED,
                        'success' => ClaimStatus::PAID,
                    ]),
                
                Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ClaimStatus::class)
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple(),
                
                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('amount_to')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '<=', $amount),
                            );
                    }),
                
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(function (Claim $record) {
                        $record->submit();
                    })
                    ->visible(fn (Claim $record) => $record->status === ClaimStatus::DRAFT),
                
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Approval Notes')
                            ->rows(3),
                    ])
                    ->action(function (Claim $record, array $data) {
                        $approver = Employee::where('user_id', auth()->id())->first();
                        $record->approve($approver, $data['notes'] ?? null);
                    })
                    ->visible(fn (Claim $record) => in_array($record->status, [ClaimStatus::SUBMITTED, ClaimStatus::UNDER_REVIEW])),
                
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Claim $record, array $data) {
                        $record->reject($data['reason']);
                    })
                    ->visible(fn (Claim $record) => in_array($record->status, [ClaimStatus::SUBMITTED, ClaimStatus::UNDER_REVIEW])),
                
                Tables\Actions\Action::make('mark_paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->action(function (Claim $record) {
                        $record->markAsPaid();
                    })
                    ->visible(fn (Claim $record) => $record->status === ClaimStatus::APPROVED),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListClaims::route('/'),
            'create' => Pages\CreateClaim::route('/create'),
            'view' => Pages\ViewClaim::route('/{record}'),
            'edit' => Pages\EditClaim::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', ClaimStatus::SUBMITTED)->count();
    }
}