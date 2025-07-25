<?php

namespace Webkul\BOM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\BOM\Database\Factories\BillOfMaterialFactory;
use Webkul\BOM\Enums\BomState;
use Webkul\BOM\Enums\BomType;
use Webkul\Chatter\Traits\HasChatter;
use Webkul\Field\Traits\HasCustomFields;
use Webkul\Product\Models\Product;

class BillOfMaterial extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasChatter, HasCustomFields;

    protected $table = 'bom_bill_of_materials';

    protected $fillable = [
        'name',
        'reference',
        'product_id',
        'version',
        'type',
        'state',
        'quantity_to_produce',
        'unit_id',
        'effective_date',
        'expiry_date',
        'description',
        'notes',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type' => BomType::class,
        'state' => BomState::class,
        'quantity_to_produce' => 'decimal:4',
        'effective_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Support\Models\UOM::class);
    }

    public function bomLines(): HasMany
    {
        return $this->hasMany(BomLine::class, 'bom_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(BomVersion::class, 'bom_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('state', BomState::ACTIVE);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?: now();
        
        return $query->where(function ($q) use ($date) {
            $q->whereNull('effective_date')
              ->orWhere('effective_date', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', $date);
        });
    }

    // Methods
    public function getTotalCost(): float
    {
        return $this->bomLines->sum(function ($line) {
            return $line->getTotalCost();
        });
    }

    public function getUnitCost(): float
    {
        return $this->quantity_to_produce > 0 
            ? $this->getTotalCost() / $this->quantity_to_produce 
            : 0;
    }

    public function explodeBom($quantity = 1, $level = 0): array
    {
        $explosion = [];
        
        foreach ($this->bomLines as $line) {
            $requiredQuantity = $line->quantity * $quantity;
            
            $explosion[] = [
                'level' => $level,
                'product_id' => $line->product_id,
                'product' => $line->product,
                'quantity' => $requiredQuantity,
                'unit' => $line->unit,
                'component_type' => $line->component_type,
                'bom_line' => $line,
            ];

            // If component has its own BOM, recursively explode it
            if ($line->subBom) {
                $subExplosion = $line->subBom->explodeBom($requiredQuantity, $level + 1);
                $explosion = array_merge($explosion, $subExplosion);
            }
        }

        return $explosion;
    }

    public function whereUsed(): array
    {
        $whereUsed = [];
        
        // Find BOMs that use this product as a component
        $bomLines = BomLine::where('product_id', $this->product_id)
            ->with(['bom.product'])
            ->get();

        foreach ($bomLines as $line) {
            $whereUsed[] = [
                'bom_id' => $line->bom_id,
                'bom' => $line->bom,
                'parent_product' => $line->bom->product,
                'quantity' => $line->quantity,
                'unit' => $line->unit,
            ];
        }

        return $whereUsed;
    }

    public function isActive(): bool
    {
        return $this->state === BomState::ACTIVE;
    }

    public function isEffective($date = null): bool
    {
        $date = $date ?: now();
        
        $effectiveCheck = !$this->effective_date || $this->effective_date <= $date;
        $expiryCheck = !$this->expiry_date || $this->expiry_date >= $date;
        
        return $effectiveCheck && $expiryCheck;
    }

    public function activate(): bool
    {
        if ($this->state === BomState::DRAFT) {
            $this->state = BomState::ACTIVE;
            return $this->save();
        }
        
        return false;
    }

    public function makeObsolete(): bool
    {
        if ($this->state === BomState::ACTIVE) {
            $this->state = BomState::OBSOLETE;
            return $this->save();
        }
        
        return false;
    }

    protected static function newFactory()
    {
        return BillOfMaterialFactory::new();
    }
}