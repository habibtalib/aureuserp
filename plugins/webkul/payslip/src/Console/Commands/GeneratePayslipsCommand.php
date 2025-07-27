<?php

namespace Webkul\Payslip\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Webkul\Employee\Models\Employee;
use Webkul\Payslip\Services\PayslipCalculationService;

class GeneratePayslipsCommand extends Command
{
    protected $signature = 'payslip:generate-monthly 
                           {--year= : Year for payslip generation (default: current year)}
                           {--month= : Month for payslip generation (default: previous month)}
                           {--company= : Company ID to generate payslips for}
                           {--employee= : Specific employee ID to generate payslip for}
                           {--force : Force regeneration of existing payslips}';

    protected $description = 'Generate monthly payslips for employees';

    protected PayslipCalculationService $calculationService;

    public function __construct(PayslipCalculationService $calculationService)
    {
        parent::__construct();
        $this->calculationService = $calculationService;
    }

    public function handle(): int
    {
        $year = $this->option('year') ?: now()->year;
        $month = $this->option('month') ?: now()->subMonth()->month;
        $companyId = $this->option('company');
        $employeeId = $this->option('employee');
        $force = $this->option('force');

        $this->info("Generating payslips for {$this->getMonthName($month)} {$year}...");

        // Get employees to process
        $employees = $this->getEmployees($companyId, $employeeId);

        if ($employees->isEmpty()) {
            $this->warn('No employees found to process payslips for.');
            return Command::SUCCESS;
        }

        $this->info("Found {$employees->count()} employees to process.");

        $processed = 0;
        $errors = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($employees->count());
        $progressBar->start();

        foreach ($employees as $employee) {
            try {
                // Check if payslip already exists
                $existingPayslip = \Webkul\Payslip\Models\Payslip::where([
                    'employee_id' => $employee->id,
                    'pay_year' => $year,
                    'pay_month' => $month,
                ])->first();

                if ($existingPayslip && !$force) {
                    $skipped++;
                } else {
                    $payslip = $this->calculationService->calculatePayslip($employee, $year, $month);
                    $processed++;

                    if ($this->output->isVerbose()) {
                        $this->line("");
                        $this->info("✓ Generated payslip for {$employee->name} - Net Salary: {$payslip->net_salary}");
                    }
                }
            } catch (\Exception $e) {
                $errors++;
                if ($this->output->isVerbose()) {
                    $this->line("");
                    $this->error("✗ Failed to generate payslip for {$employee->name}: {$e->getMessage()}");
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line("");

        // Summary
        $this->info("Payslip generation completed!");
        $this->line("Processed: {$processed}");
        $this->line("Skipped (already exists): {$skipped}");
        $this->line("Errors: {$errors}");

        if ($errors > 0) {
            $this->warn("Some payslips failed to generate. Check the logs for details.");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function getEmployees(?string $companyId, ?string $employeeId)
    {
        $query = Employee::query();

        if ($employeeId) {
            $query->where('id', $employeeId);
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        // Only include active employees with salary structures
        $query->whereHas('employeeSalaryStructures', function ($q) {
            $q->active();
        });

        return $query->get();
    }

    protected function getMonthName(int $month): string
    {
        return Carbon::createFromDate(null, $month, 1)->format('F');
    }
}