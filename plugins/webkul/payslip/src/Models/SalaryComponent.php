<?php

namespace Webkul\Payslip\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Chatter\Traits\HasChatter;
use Webkul\Field\Traits\HasCustomFields;
use Webkul\Payslip\Database\Factories\SalaryComponentFactory;
use Webkul\Payslip\Enums\CalculationType;
use Webkul\Payslip\Enums\ComponentType;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

class SalaryComponent extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasChatter, HasCustomFields;

    protected $table = 'payslip_salary_components';

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'calculation_type',
        'default_amount',
        'default_rate',
        'formula',
        'is_taxable',
        'is_provident_fund_applicable',
        'is_active',
        'display_order',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type' => ComponentType::class,
        'calculation_type' => CalculationType::class,
        'default_amount' => 'decimal:2',
        'default_rate' => 'decimal:4',
        'is_taxable' => 'boolean',
        'is_provident_fund_applicable' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    protected static function newFactory()
    {
        return SalaryComponentFactory::new();
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($component) {
            if (empty($component->code)) {
                $component->code = static::generateCode($component->type);
            }
        });
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function payslipItems(): HasMany
    {
        return $this->hasMany(PayslipItem::class);
    }

    // Helper methods
    public static function generateCode(ComponentType $type): string
    {
        $prefix = match ($type) {
            ComponentType::EARNING => 'ERN-',
            ComponentType::DEDUCTION => 'DED-',
            ComponentType::EMPLOYER_CONTRIBUTION => 'EMP-',
        };

        $lastComponent = static::withTrashed()
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastComponent) {
            return $prefix . '001';
        }

        $lastNumber = (int) str_replace($prefix, '', $lastComponent->code);
        return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    public function calculateAmount(float $baseAmount = 0, array $context = []): float
    {
        return match ($this->calculation_type) {
            CalculationType::FIXED => $this->default_amount ?? 0,
            CalculationType::PERCENTAGE => ($baseAmount * ($this->default_rate ?? 0)) / 100,
            CalculationType::COMPUTED => $this->evaluateFormula($context),
            CalculationType::VARIABLE => $context['variable_amount'] ?? 0,
        };
    }

    protected function evaluateFormula(array $context = []): float
    {
        if (!$this->formula) {
            return 0;
        }

        // Simple formula evaluation - in production, use a more robust parser
        $formula = $this->formula;
        
        // Replace context variables in formula
        foreach ($context as $key => $value) {
            $formula = str_replace('{' . $key . '}', $value, $formula);
        }

        // Basic safety check - only allow numbers and basic operators
        if (preg_match('/^[0-9+\-*\/().\s]+$/', $formula)) {
            try {
                return eval("return $formula;");
            } catch (Throwable $e) {
                return 0;
            }
        }

        return 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, ComponentType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeEarnings($query)
    {
        return $query->where('type', ComponentType::EARNING);
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', ComponentType::DEDUCTION);
    }

    public function scopeEmployerContributions($query)
    {
        return $query->where('type', ComponentType::EMPLOYER_CONTRIBUTION);
    }

    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }

    public function scopeProvidentFundApplicable($query)
    {
        return $query->where('is_provident_fund_applicable', true);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}