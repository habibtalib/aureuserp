<?php

namespace Webkul\BOM\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Webkul\BOM\Enums\BomState;
use Webkul\BOM\Enums\BomType;
use Webkul\BOM\Enums\ComponentType;
use Webkul\BOM\Filament\Resources\BillOfMaterialResource\Pages;
use Webkul\BOM\Models\BillOfMaterial;

class BillOfMaterialResource extends Resource
{
    protected static ?string $model = BillOfMaterial::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Manufacturing';

    protected static ?string $navigationLabel = 'Bills of Material';

    protected static ?string $modelLabel = 'Bill of Material';

    protected static ?string $pluralModelLabel = 'Bills of Material';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter BOM name'),

                                Forms\Components\TextInput::make('reference')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('BOM-001'),

                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Select product'),

                                Forms\Components\TextInput::make('version')
                                    ->required()
                                    ->default('1.0')
                                    ->maxLength(255),

                                Forms\Components\Select::make('type')
                                    ->enum(BomType::class)
                                    ->options(BomType::class)
                                    ->required()
                                    ->default(BomType::STANDARD),

                                Forms\Components\Select::make('state')
                                    ->enum(BomState::class)
                                    ->options(BomState::class)
                                    ->required()
                                    ->default(BomState::DRAFT),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('quantity_to_produce')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0.0001)
                                    ->step(0.0001),

                                Forms\Components\Select::make('unit_id')
                                    ->relationship('unit', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select unit'),

                                Forms\Components\DatePicker::make('effective_date')
                                    ->placeholder('Select effective date'),
                            ]),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->placeholder('Select expiry date')
                            ->after('effective_date'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Enter BOM description'),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Enter any additional notes'),
                    ]),

                Forms\Components\Section::make('Components')
                    ->schema([
                        Forms\Components\Repeater::make('bomLines')
                            ->relationship()
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Select component'),

                                        Forms\Components\TextInput::make('quantity')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0.0001)
                                            ->step(0.0001)
                                            ->placeholder('Quantity'),

                                        Forms\Components\Select::make('unit_id')
                                            ->relationship('unit', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Unit'),

                                        Forms\Components\TextInput::make('sequence')
                                            ->numeric()
                                            ->default(10)
                                            ->placeholder('Sequence'),
                                    ]),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('component_type')
                                            ->enum(ComponentType::class)
                                            ->options(ComponentType::class)
                                            ->required()
                                            ->default(ComponentType::MATERIAL),

                                        Forms\Components\TextInput::make('waste_percentage')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%')
                                            ->placeholder('Waste %'),

                                        Forms\Components\Toggle::make('is_optional')
                                            ->default(false),
                                    ]),

                                Forms\Components\Select::make('sub_bom_id')
                                    ->relationship('subBom', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select sub-BOM (if applicable)')
                                    ->visible(fn (Forms\Get $get) => $get('component_type') === ComponentType::SUB_ASSEMBLY->value),

                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(500)
                                    ->rows(2)
                                    ->placeholder('Component notes'),
                            ])
                            ->columns(1)
                            ->defaultItems(0)
                            ->reorderable('sequence')
                            ->collapsible()
                            ->cloneable()
                            ->addActionLabel('Add Component')
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action
                                    ->requiresConfirmation()
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->label('Product'),

                Tables\Columns\TextColumn::make('version')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (BomType $state): string => $state->getColor()),

                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn (BomState $state): string => $state->getColor()),

                Tables\Columns\TextColumn::make('quantity_to_produce')
                    ->numeric(2)
                    ->sortable()
                    ->label('Qty to Produce'),

                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Unit'),

                Tables\Columns\TextColumn::make('effective_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('bomLines_count')
                    ->counts('bomLines')
                    ->label('Components')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(BomType::class),

                Tables\Filters\SelectFilter::make('state')
                    ->options(BomState::class),

                Tables\Filters\Filter::make('effective')
                    ->query(fn (Builder $query): Builder => $query->effective())
                    ->label('Currently Effective'),

                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (BillOfMaterial $record) => $record->state === BomState::DRAFT)
                    ->action(fn (BillOfMaterial $record) => $record->activate()),

                Tables\Actions\Action::make('make_obsolete')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (BillOfMaterial $record) => $record->state === BomState::ACTIVE)
                    ->action(fn (BillOfMaterial $record) => $record->makeObsolete()),

                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBillOfMaterials::route('/'),
            'create' => Pages\CreateBillOfMaterial::route('/create'),
            'view' => Pages\ViewBillOfMaterial::route('/{record}'),
            'edit' => Pages\EditBillOfMaterial::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['product', 'unit', 'bomLines']);
    }
}