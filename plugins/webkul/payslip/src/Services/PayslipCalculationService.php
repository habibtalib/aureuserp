<?php

namespace Webkul\Payslip\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Webkul\Employee\Models\Employee;
use Webkul\Payslip\Enums\ComponentType;
use Webkul\Payslip\Enums\PayslipStatus;
use Webkul\Payslip\Models\AttendanceSummary;
use Webkul\Payslip\Models\EmployeeSalaryStructure;
use Webkul\Payslip\Models\Payslip;
use Webkul\Payslip\Models\PayslipItem;
use Webkul\Payslip\Models\SalaryComponent;

class PayslipCalculationService
{
    public function calculatePayslip(Employee $employee, int $year, int $month): Payslip
    {
        // Get employee's active salary structure
        $employeeSalaryStructure = $this->getEmployeeSalaryStructure($employee, $year, $month);
        
        if (!$employeeSalaryStructure) {
            throw new \Exception("No active salary structure found for employee {$employee->name}");
        }

        // Get or create attendance summary
        $attendanceSummary = $this->getAttendanceSummary($employee, $year, $month);

        // Create or update payslip
        $payslip = $this->createOrUpdatePayslip($employee, $employeeSalaryStructure, $year, $month, $attendanceSummary);

        // Calculate all salary components
        $this->calculateSalaryComponents($payslip, $employeeSalaryStructure, $attendanceSummary);

        // Update payslip totals
        $this->updatePayslipTotals($payslip);

        return $payslip->fresh();
    }

    protected function getEmployeeSalaryStructure(Employee $employee, int $year, int $month): ?EmployeeSalaryStructure
    {
        $date = Carbon::createFromDate($year, $month, 1);
        
        return EmployeeSalaryStructure::forEmployee($employee->id)
            ->active()
            ->effectiveOn($date)
            ->first();
    }

    protected function getAttendanceSummary(Employee $employee, int $year, int $month): AttendanceSummary
    {
        return AttendanceSummary::firstOrCreate([
            'employee_id' => $employee->id,
            'year' => $year,
            'month' => $month,
            'company_id' => $employee->company_id,
        ], [
            'total_working_days' => config('payslip.attendance_integration.working_days_per_month', 22),
            'days_present' => 22, // Default to full attendance
            'days_absent' => 0,
            'days_weekend' => 8,
            'days_holiday' => 0,
            'days_leave' => 0,
            'regular_hours' => 176, // 22 days * 8 hours
            'overtime_hours' => 0,
            'late_hours' => 0,
            'early_departure_hours' => 0,
        ]);
    }

    protected function createOrUpdatePayslip(
        Employee $employee,
        EmployeeSalaryStructure $employeeSalaryStructure,
        int $year,
        int $month,
        AttendanceSummary $attendanceSummary
    ): Payslip {
        $payPeriodStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $payPeriodEnd = $payPeriodStart->copy()->endOfMonth();

        return Payslip::updateOrCreate([
            'employee_id' => $employee->id,
            'pay_year' => $year,
            'pay_month' => $month,
        ], [
            'salary_structure_id' => $employeeSalaryStructure->salary_structure_id,
            'pay_period_start' => $payPeriodStart,
            'pay_period_end' => $payPeriodEnd,
            'status' => PayslipStatus::DRAFT,
            'basic_salary' => $employeeSalaryStructure->basic_salary,
            'total_working_days' => $attendanceSummary->total_working_days,
            'days_present' => $attendanceSummary->days_present,
            'days_absent' => $attendanceSummary->days_absent,
            'overtime_hours' => $attendanceSummary->overtime_hours,
            'company_id' => $employee->company_id,
            'created_by' => auth()->id(),
        ]);
    }

    protected function calculateSalaryComponents(
        Payslip $payslip,
        EmployeeSalaryStructure $employeeSalaryStructure,
        AttendanceSummary $attendanceSummary
    ): void {
        // Clear existing payslip items
        $payslip->payslipItems()->delete();

        // Get all active salary components for the company
        $salaryComponents = SalaryComponent::forCompany($payslip->company_id)
            ->active()
            ->ordered()
            ->get();

        $calculationContext = $this->buildCalculationContext($payslip, $employeeSalaryStructure, $attendanceSummary);

        foreach ($salaryComponents as $component) {
            $this->calculateComponent($payslip, $component, $calculationContext);
        }
    }

    protected function buildCalculationContext(
        Payslip $payslip,
        EmployeeSalaryStructure $employeeSalaryStructure,
        AttendanceSummary $attendanceSummary
    ): array {
        $basicSalary = $employeeSalaryStructure->basic_salary;
        $attendanceRatio = $attendanceSummary->days_present / max($attendanceSummary->total_working_days, 1);
        $adjustedBasicSalary = $basicSalary * $attendanceRatio;

        return [
            'basic_salary' => $basicSalary,
            'adjusted_basic_salary' => $adjustedBasicSalary,
            'gross_salary' => 0, // Will be calculated iteratively
            'total_earnings' => 0,
            'total_deductions' => 0,
            'attendance_ratio' => $attendanceRatio,
            'days_present' => $attendanceSummary->days_present,
            'total_working_days' => $attendanceSummary->total_working_days,
            'overtime_hours' => $attendanceSummary->overtime_hours,
            'regular_hours' => $attendanceSummary->regular_hours,
            'hourly_rate' => $basicSalary / max($attendanceSummary->regular_hours, 1),
        ];
    }

    protected function calculateComponent(
        Payslip $payslip,
        SalaryComponent $component,
        array &$context
    ): void {
        // Determine base amount for calculation
        $baseAmount = match ($component->type) {
            ComponentType::EARNING => $context['adjusted_basic_salary'],
            ComponentType::DEDUCTION => $context['total_earnings'] ?: $context['adjusted_basic_salary'],
            ComponentType::EMPLOYER_CONTRIBUTION => $context['adjusted_basic_salary'],
        };

        // Calculate component amount
        $calculatedAmount = $component->calculateAmount($baseAmount, $context);

        // Apply attendance ratio for earnings (except overtime)
        if ($component->type === ComponentType::EARNING && $component->code !== 'OT') {
            $calculatedAmount *= $context['attendance_ratio'];
        }

        // Special handling for overtime
        if ($component->code === 'OT') {
            $calculatedAmount = $this->calculateOvertime($context);
        }

        // Create payslip item
        PayslipItem::create([
            'payslip_id' => $payslip->id,
            'salary_component_id' => $component->id,
            'component_name' => $component->name,
            'component_code' => $component->code,
            'component_type' => $component->type,
            'base_amount' => $baseAmount,
            'rate' => $component->default_rate,
            'calculated_amount' => $calculatedAmount,
            'calculation_notes' => $this->generateCalculationNotes($component, $baseAmount, $calculatedAmount, $context),
            'display_order' => $component->display_order,
        ]);

        // Update context for next calculations
        match ($component->type) {
            ComponentType::EARNING => $context['total_earnings'] += $calculatedAmount,
            ComponentType::DEDUCTION => $context['total_deductions'] += $calculatedAmount,
            default => null,
        };

        $context['gross_salary'] = $context['total_earnings'];
    }

    protected function calculateOvertime(array $context): float
    {
        $overtimeHours = $context['overtime_hours'] ?? 0;
        $hourlyRate = $context['hourly_rate'] ?? 0;
        $overtimeMultiplier = config('payslip.attendance_integration.overtime_multiplier', 1.5);

        return $overtimeHours * $hourlyRate * $overtimeMultiplier;
    }

    protected function generateCalculationNotes(
        SalaryComponent $component,
        float $baseAmount,
        float $calculatedAmount,
        array $context
    ): string {
        return match ($component->calculation_type->value) {
            'fixed' => "Fixed amount: {$component->default_amount}",
            'percentage' => "({$component->default_rate}% of {$baseAmount}) = {$calculatedAmount}",
            'computed' => "Formula: {$component->formula}",
            'variable' => "Variable amount: {$calculatedAmount}",
        };
    }

    protected function updatePayslipTotals(Payslip $payslip): void
    {
        $earnings = $payslip->payslipItems()->earnings()->sum('calculated_amount');
        $deductions = $payslip->payslipItems()->deductions()->sum('calculated_amount');
        $employerContributions = $payslip->payslipItems()->employerContributions()->sum('calculated_amount');

        // Calculate taxes
        $taxableIncome = $this->calculateTaxableIncome($payslip);
        $taxDeducted = $this->calculateIncomeTax($taxableIncome);
        $providentFund = $this->calculateProvidentFund($payslip);

        // Calculate overtime amount
        $overtimeAmount = $payslip->payslipItems()
            ->where('component_code', 'OT')
            ->sum('calculated_amount');

        $payslip->update([
            'total_earnings' => $earnings,
            'total_deductions' => $deductions,
            'employer_contributions' => $employerContributions,
            'gross_salary' => $earnings,
            'net_salary' => $earnings - $deductions,
            'taxable_income' => $taxableIncome,
            'tax_deducted' => $taxDeducted,
            'provident_fund' => $providentFund,
            'overtime_amount' => $overtimeAmount,
            'calculation_details' => $this->buildCalculationDetails($payslip),
        ]);
    }

    protected function calculateTaxableIncome(Payslip $payslip): float
    {
        return $payslip->payslipItems()
            ->earnings()
            ->whereHas('salaryComponent', function ($query) {
                $query->where('is_taxable', true);
            })
            ->sum('calculated_amount');
    }

    protected function calculateIncomeTax(float $taxableIncome): float
    {
        $taxSlabs = config('payslip.tax_slabs', []);
        $annualTaxableIncome = $taxableIncome * 12; // Convert monthly to annual
        $totalTax = 0;

        foreach ($taxSlabs as $slab) {
            $minAmount = $slab['min_amount'];
            $maxAmount = $slab['max_amount'] ?? PHP_FLOAT_MAX;
            $taxRate = $slab['tax_rate'];

            if ($annualTaxableIncome > $minAmount) {
                $taxableAmountInSlab = min($annualTaxableIncome, $maxAmount) - $minAmount;
                $totalTax += ($taxableAmountInSlab * $taxRate) / 100;
            }
        }

        return $totalTax / 12; // Convert annual tax to monthly
    }

    protected function calculateProvidentFund(Payslip $payslip): float
    {
        return $payslip->payslipItems()
            ->earnings()
            ->whereHas('salaryComponent', function ($query) {
                $query->where('is_provident_fund_applicable', true);
            })
            ->sum('calculated_amount') * 0.12; // 12% PF contribution
    }

    protected function buildCalculationDetails(Payslip $payslip): array
    {
        return [
            'calculation_date' => now()->toISOString(),
            'calculation_method' => 'automated',
            'items_breakdown' => $payslip->payslipItems()
                ->with('salaryComponent')
                ->get()
                ->map(function ($item) {
                    return [
                        'component' => $item->component_name,
                        'type' => $item->component_type->value,
                        'base_amount' => $item->base_amount,
                        'rate' => $item->rate,
                        'calculated_amount' => $item->calculated_amount,
                        'notes' => $item->calculation_notes,
                    ];
                })
                ->toArray(),
        ];
    }

    public function bulkCalculatePayslips(Collection $employees, int $year, int $month): Collection
    {
        $payslips = collect();

        foreach ($employees as $employee) {
            try {
                $payslip = $this->calculatePayslip($employee, $year, $month);
                $payslips->push($payslip);
            } catch (\Exception $e) {
                // Log error and continue with next employee
                logger()->error("Failed to calculate payslip for employee {$employee->id}: " . $e->getMessage());
            }
        }

        return $payslips;
    }

    public function recalculatePayslip(Payslip $payslip): Payslip
    {
        return $this->calculatePayslip(
            $payslip->employee,
            $payslip->pay_year,
            $payslip->pay_month
        );
    }
}