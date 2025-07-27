<?php

namespace Webkul\Payslip\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Employee\Models\Employee;
use Webkul\Payslip\Database\Factories\AttendanceSummaryFactory;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

class AttendanceSummary extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payslip_attendance_summaries';

    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'total_working_days',
        'days_present',
        'days_absent',
        'days_weekend',
        'days_holiday',
        'days_leave',
        'regular_hours',
        'overtime_hours',
        'late_hours',
        'early_departure_hours',
        'daily_breakdown',
        'is_finalized',
        'finalized_at',
        'finalized_by',
        'company_id',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_working_days' => 'integer',
        'days_present' => 'integer',
        'days_absent' => 'integer',
        'days_weekend' => 'integer',
        'days_holiday' => 'integer',
        'days_leave' => 'integer',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'late_hours' => 'decimal:2',
        'early_departure_hours' => 'decimal:2',
        'daily_breakdown' => 'array',
        'is_finalized' => 'boolean',
        'finalized_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return AttendanceSummaryFactory::new();
    }

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Helper methods
    public function finalize(User $user): void
    {
        $this->update([
            'is_finalized' => true,
            'finalized_at' => now(),
            'finalized_by' => $user->id,
        ]);
    }

    public function getMonthName(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    public function getAttendancePercentage(): float
    {
        if ($this->total_working_days === 0) {
            return 0;
        }

        return ($this->days_present / $this->total_working_days) * 100;
    }

    public function getRegularPayDays(): float
    {
        // Calculate pay days based on attendance
        $attendancePercentage = $this->getAttendancePercentage();
        return ($this->total_working_days * $attendancePercentage) / 100;
    }

    public function getOvertimeAmount(float $hourlyRate = 0): float
    {
        $overtimeMultiplier = config('payslip.attendance_integration.overtime_multiplier', 1.5);
        return $this->overtime_hours * $hourlyRate * $overtimeMultiplier;
    }

    // Scopes
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeFinalized($query)
    {
        return $query->where('is_finalized', true);
    }

    public function scopePendingFinalization($query)
    {
        return $query->where('is_finalized', false);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}