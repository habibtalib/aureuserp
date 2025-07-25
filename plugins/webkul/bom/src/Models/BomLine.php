<?php

namespace Webkul\BOM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BOM\Database\Factories\BomLineFactory;
use Webkul\BOM\Enums\ComponentType;
use Webkul\Product\Models\Product;

class BomLine extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bom_bom_lines';

    protected $fillable = [
        'bom_id',
        'product_id',
        'quantity',
        'unit_id',
        'sequence',
        'component_type',
        'sub_bom_id',
        'waste_percentage',
        'is_optional',
        'notes',
        'company_id',
    ];

    protected $casts = [
        'component_type' => ComponentType::class,
        'quantity' => 'decimal:4',
        'waste_percentage' => 'decimal:2',
        'is_optional' => 'boolean',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Product\Models\UOM::class);
    }

    public function subBom(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class, 'sub_bom_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    // Methods
    public function getQuantityWithWaste(): float
    {
        $wasteMultiplier = 1 + ($this->waste_percentage / 100);
        return $this->quantity * $wasteMultiplier;
    }

    public function getTotalQuantity($bomQuantity = 1): float
    {
        return $this->getQuantityWithWaste() * $bomQuantity;
    }

    public function getUnitCost(): float
    {
        // Get the cost from product or latest purchase price
        return $this->product->cost ?? $this->product->price ?? 0;
    }

    public function getTotalCost($bomQuantity = 1): float
    {
        return $this->getTotalQuantity($bomQuantity) * $this->getUnitCost();
    }

    public function isSubAssembly(): bool
    {
        return $this->component_type === ComponentType::SUB_ASSEMBLY && $this->sub_bom_id;
    }

    public function isByProduct(): bool
    {
        return $this->component_type === ComponentType::BYPRODUCT;
    }

    public function isConsumable(): bool
    {
        return $this->component_type === ComponentType::CONSUMABLE;
    }

    // Scopes
    public function scopeBySequence($query)
    {
        return $query->orderBy('sequence');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_optional', false);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
    }

    public function scopeByComponentType($query, ComponentType $type)
    {
        return $query->where('component_type', $type);
    }

    protected static function newFactory()
    {
        return BomLineFactory::new();
    }
}