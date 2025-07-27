<?php

namespace Webkul\Payslip\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Payslip\Database\Factories\PayslipItemFactory;
use Webkul\Payslip\Enums\ComponentType;

class PayslipItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payslip_payslip_items';

    protected $fillable = [
        'payslip_id',
        'salary_component_id',
        'component_name',
        'component_code',
        'component_type',
        'base_amount',
        'rate',
        'calculated_amount',
        'calculation_notes',
        'display_order',
    ];

    protected $casts = [
        'component_type' => ComponentType::class,
        'base_amount' => 'decimal:2',
        'rate' => 'decimal:4',
        'calculated_amount' => 'decimal:2',
        'display_order' => 'integer',
    ];

    protected static function newFactory()
    {
        return PayslipItemFactory::new();
    }

    // Relationships
    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }

    // Scopes
    public function scopeEarnings($query)
    {
        return $query->where('component_type', ComponentType::EARNING);
    }

    public function scopeDeductions($query)
    {
        return $query->where('component_type', ComponentType::DEDUCTION);
    }

    public function scopeEmployerContributions($query)
    {
        return $query->where('component_type', ComponentType::EMPLOYER_CONTRIBUTION);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('component_name');
    }
}