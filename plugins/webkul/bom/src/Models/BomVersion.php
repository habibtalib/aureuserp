<?php

namespace Webkul\BOM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BOM\Database\Factories\BomVersionFactory;

class BomVersion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bom_bom_versions';

    protected $fillable = [
        'bom_id',
        'version_number',
        'effective_date',
        'expiry_date',
        'change_description',
        'change_reason',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    // Scopes
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

    public function scopeLatest($query)
    {
        return $query->orderBy('version_number', 'desc');
    }

    // Methods
    public function isEffective($date = null): bool
    {
        $date = $date ?: now();
        
        $effectiveCheck = !$this->effective_date || $this->effective_date <= $date;
        $expiryCheck = !$this->expiry_date || $this->expiry_date >= $date;
        
        return $effectiveCheck && $expiryCheck;
    }

    protected static function newFactory()
    {
        return BomVersionFactory::new();
    }
}