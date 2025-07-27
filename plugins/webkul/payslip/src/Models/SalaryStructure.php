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
use Webkul\Payslip\Database\Factories\SalaryStructureFactory;
use Webkul\Payslip\Enums\PayPeriod;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

class SalaryStructure extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasChatter, HasCustomFields;

    protected $table = 'payslip_salary_structures';

    protected $fillable = [
        'name',
        'code',
        'description',
        'pay_period',
        'basic_salary',
        'allowances',
        'deductions',
        'is_active',
        'is_default',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'pay_period' => PayPeriod::class,
        'basic_salary' => 'decimal:2',
        'allowances' => 'array',
        'deductions' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function newFactory()
    {
        return SalaryStructureFactory::new();
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($salaryStructure) {
            if (empty($salaryStructure->code)) {
                $salaryStructure->code = static::generateCode();
            }
        });

        // Ensure only one default structure per company
        static::saving(function ($salaryStructure) {
            if ($salaryStructure->is_default) {
                static::where('company_id', $salaryStructure->company_id)
                    ->where('id', '!=', $salaryStructure->id)
                    ->update(['is_default' => false]);
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

    public function employeeSalaryStructures(): HasMany
    {
        return $this->hasMany(EmployeeSalaryStructure::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    // Helper methods
    public static function generateCode(): string
    {
        $prefix = 'SS-';
        $lastStructure = static::withTrashed()
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastStructure) {
            return $prefix . '0001';
        }

        $lastNumber = (int) str_replace($prefix, '', $lastStructure->code);
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getTotalAllowances(): float
    {
        if (!$this->allowances) {
            return 0;
        }

        return collect($this->allowances)->sum('amount');
    }

    public function getTotalDeductions(): float
    {
        if (!$this->deductions) {
            return 0;
        }

        return collect($this->deductions)->sum('amount');
    }

    public function getGrossSalary(): float
    {
        return $this->basic_salary + $this->getTotalAllowances();
    }

    public function getNetSalary(): float
    {
        return $this->getGrossSalary() - $this->getTotalDeductions();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByPayPeriod($query, PayPeriod $payPeriod)
    {
        return $query->where('pay_period', $payPeriod);
    }
}