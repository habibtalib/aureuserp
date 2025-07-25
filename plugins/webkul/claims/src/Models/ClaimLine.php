<?php

namespace Webkul\Claims\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Claims\Database\Factories\ClaimLineFactory;
use Webkul\Support\Models\Company;

class ClaimLine extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'claims_claim_lines';

    protected $fillable = [
        'claim_id',
        'description',
        'amount',
        'currency',
        'expense_date',
        'receipt_reference',
        'notes',
        'sequence',
        'company_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'sequence' => 'integer',
    ];

    protected static function newFactory()
    {
        return ClaimLineFactory::new();
    }

    // Relationships
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClaimAttachment::class);
    }

    // Scopes
    public function scopeForClaim($query, $claimId)
    {
        return $query->where('claim_id', $claimId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence')->orderBy('created_at');
    }
}