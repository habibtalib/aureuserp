<?php

namespace Webkul\Claims\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Webkul\Claims\Filament\Resources\ClaimCategoryResource\Pages;
use Webkul\Claims\Models\ClaimCategory;

class ClaimCategoryResource extends Resource
{
    protected static ?string $model = ClaimCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Claim Categories';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('max_amount')
                            ->label('Maximum Amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->helperText('Leave empty for no limit'),
                        
                        Forms\Components\Toggle::make('requires_receipt')
                            ->label('Requires Receipt')
                            ->default(true)
                            ->helperText('Whether claims in this category require receipts'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active categories can be used for new claims'),
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
                
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('max_amount')
                    ->money('USD')
                    ->placeholder('No limit')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('requires_receipt')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\ToggleColumn::make('is_active')
                    ->onColor('success')
                    ->offColor('gray'),
                
                Tables\Columns\TextColumn::make('claims_count')
                    ->counts('claims')
                    ->label('Claims')
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
                    ->falseLabel('Inactive only')
                    ->native(false),
                
                Tables\Filters\TernaryFilter::make('requires_receipt')
                    ->label('Receipt Required')
                    ->boolean()
                    ->trueLabel('Receipt required')
                    ->falseLabel('No receipt required')
                    ->native(false),
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
            'index' => Pages\ListClaimCategories::route('/'),
            'create' => Pages\CreateClaimCategory::route('/create'),
            'view' => Pages\ViewClaimCategory::route('/{record}'),
            'edit' => Pages\EditClaimCategory::route('/{record}/edit'),
        ];
    }
}