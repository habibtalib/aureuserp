<?php

namespace Webkul\Payslip\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Employee\Models\Employee;
use Webkul\Payslip\Database\Factories\EmployeeSalaryStructureFactory;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

class EmployeeSalaryStructure extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payslip_employee_salary_structures';

    protected $fillable = [
        'employee_id',
        'salary_structure_id',
        'basic_salary',
        'custom_allowances',
        'custom_deductions',
        'effective_from',
        'effective_to',
        'is_active',
        'notes',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'custom_allowances' => 'array',
        'custom_deductions' => 'array',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function newFactory()
    {
        return EmployeeSalaryStructureFactory::new();
    }

    protected static function boot()
    {
        parent::boot();
        
        // Ensure only one active salary structure per employee
        static::saving(function ($employeeSalaryStructure) {
            if ($employeeSalaryStructure->is_active) {
                static::where('employee_id', $employeeSalaryStructure->employee_id)
                    ->where('id', '!=', $employeeSalaryStructure->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

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

    // Helper methods
    public function isEffectiveOn(\DateTime $date): bool
    {
        $checkDate = $date->format('Y-m-d');
        
        if ($this->effective_from > $checkDate) {
            return false;
        }
        
        if ($this->effective_to && $this->effective_to < $checkDate) {
            return false;
        }
        
        return true;
    }

    public function getTotalAllowances(): float
    {
        $structureAllowances = $this->salaryStructure->getTotalAllowances();
        $customAllowances = $this->custom_allowances ? collect($this->custom_allowances)->sum('amount') : 0;
        
        return $structureAllowances + $customAllowances;
    }

    public function getTotalDeductions(): float
    {
        $structureDeductions = $this->salaryStructure->getTotalDeductions();
        $customDeductions = $this->custom_deductions ? collect($this->custom_deductions)->sum('amount') : 0;
        
        return $structureDeductions + $customDeductions;
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

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeEffectiveOn($query, \DateTime $date)
    {
        $checkDate = $date->format('Y-m-d');
        
        return $query->where('effective_from', '<=', $checkDate)
            ->where(function ($q) use ($checkDate) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $checkDate);
            });
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}