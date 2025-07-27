<?php

namespace Webkul\Payslip\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Chatter\Traits\HasChatter;
use Webkul\Employee\Models\Employee;
use Webkul\Field\Traits\HasCustomFields;
use Webkul\Payslip\Database\Factories\PayslipFactory;
use Webkul\Payslip\Enums\PayslipStatus;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

class Payslip extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasChatter, HasCustomFields;

    protected $table = 'payslip_payslips';

    protected $fillable = [
        'payslip_number',
        'employee_id',
        'salary_structure_id',
        'pay_year',
        'pay_month',
        'pay_period_start',
        'pay_period_end',
        'status',
        'basic_salary',
        'total_earnings',
        'total_deductions',
        'gross_salary',
        'net_salary',
        'employer_contributions',
        'total_working_days',
        'days_present',
        'days_absent',
        'overtime_hours',
        'overtime_amount',
        'taxable_income',
        'tax_deducted',
        'provident_fund',
        'processed_date',
        'processed_by',
        'approved_date',
        'approved_by',
        'paid_date',
        'paid_by',
        'notes',
        'calculation_details',
        'pdf_path',
        'email_sent',
        'email_sent_at',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => PayslipStatus::class,
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'basic_salary' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'employer_contributions' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'tax_deducted' => 'decimal:2',
        'provident_fund' => 'decimal:2',
        'processed_date' => 'date',
        'approved_date' => 'date',
        'paid_date' => 'date',
        'calculation_details' => 'array',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return PayslipFactory::new();
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payslip) {
            if (empty($payslip->payslip_number)) {
                $payslip->payslip_number = static::generatePayslipNumber();
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

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
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

    public function payslipItems(): HasMany
    {
        return $this->hasMany(PayslipItem::class);
    }

    public function attendanceSummary(): BelongsTo
    {
        return $this->belongsTo(AttendanceSummary::class, 'employee_id', 'employee_id')
            ->where('year', $this->pay_year)
            ->where('month', $this->pay_month);
    }

    // Helper methods
    public static function generatePayslipNumber(): string
    {
        $prefix = config('payslip.payslip_number_prefix', 'PS-');
        $length = config('payslip.payslip_number_length', 6);
        
        $lastPayslip = static::withTrashed()
            ->where('payslip_number', 'like', $prefix . '%')
            ->orderBy('payslip_number', 'desc')
            ->first();

        if (!$lastPayslip) {
            return $prefix . str_pad(1, $length, '0', STR_PAD_LEFT);
        }

        $lastNumber = (int) str_replace($prefix, '', $lastPayslip->payslip_number);
        return $prefix . str_pad($lastNumber + 1, $length, '0', STR_PAD_LEFT);
    }

    public function getPayPeriodDescription(): string
    {
        return $this->pay_period_start->format('M d') . ' - ' . $this->pay_period_end->format('M d, Y');
    }

    public function getMonthYearDescription(): string
    {
        return $this->pay_period_start->format('F Y');
    }

    public function markAsProcessed(User $user): void
    {
        $this->update([
            'status' => PayslipStatus::PENDING,
            'processed_date' => now(),
            'processed_by' => $user->id,
        ]);
    }

    public function approve(User $approver): void
    {
        $this->update([
            'status' => PayslipStatus::APPROVED,
            'approved_date' => now(),
            'approved_by' => $approver->id,
        ]);
    }

    public function markAsPaid(User $payer): void
    {
        $this->update([
            'status' => PayslipStatus::PAID,
            'paid_date' => now(),
            'paid_by' => $payer->id,
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => PayslipStatus::CANCELLED,
        ]);
    }

    public function markEmailSent(): void
    {
        $this->update([
            'email_sent' => true,
            'email_sent_at' => now(),
        ]);
    }

    // Scopes
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByStatus($query, PayslipStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('pay_year', $year)->where('pay_month', $month);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('pay_year', $year);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', PayslipStatus::PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', PayslipStatus::APPROVED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', PayslipStatus::PAID);
    }

    public function scopeEmailNotSent($query)
    {
        return $query->where('email_sent', false);
    }
}