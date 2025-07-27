<?php

namespace Webkul\Payslip\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Webkul\Payslip\Enums\PayslipStatus;
use Webkul\Payslip\Mail\PayslipEmail;
use Webkul\Payslip\Models\Payslip;

class SendPayslipEmailsCommand extends Command
{
    protected $signature = 'payslip:send-emails 
                           {--year= : Year to send payslips for}
                           {--month= : Month to send payslips for}
                           {--company= : Company ID to send payslips for}
                           {--limit=50 : Limit number of emails to send per run}';

    protected $description = 'Send payslip emails to employees';

    public function handle(): int
    {
        if (!config('payslip.email.send_payslip_on_generation', true)) {
            $this->info('Payslip email sending is disabled in configuration.');
            return Command::SUCCESS;
        }

        $year = $this->option('year');
        $month = $this->option('month');
        $companyId = $this->option('company');
        $limit = (int) $this->option('limit');

        $this->info('Sending pending payslip emails...');

        // Get payslips that need email sending
        $payslips = $this->getPendingEmailPayslips($year, $month, $companyId, $limit);

        if ($payslips->isEmpty()) {
            $this->info('No pending payslip emails to send.');
            return Command::SUCCESS;
        }

        $this->info("Found {$payslips->count()} payslips to send emails for.");

        $sent = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($payslips->count());
        $progressBar->start();

        foreach ($payslips as $payslip) {
            try {
                $this->sendPayslipEmail($payslip);
                $payslip->markEmailSent();
                $sent++;

                if ($this->output->isVerbose()) {
                    $this->line("");
                    $this->info("✓ Sent payslip email to {$payslip->employee->name}");
                }
            } catch (\Exception $e) {
                $errors++;
                if ($this->output->isVerbose()) {
                    $this->line("");
                    $this->error("✗ Failed to send payslip email to {$payslip->employee->name}: {$e->getMessage()}");
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line("");

        // Summary
        $this->info("Email sending completed!");
        $this->line("Sent: {$sent}");
        $this->line("Errors: {$errors}");

        if ($errors > 0) {
            $this->warn("Some emails failed to send. Check the logs for details.");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function getPendingEmailPayslips(?string $year, ?string $month, ?string $companyId, int $limit)
    {
        $query = Payslip::with(['employee', 'employee.user'])
            ->whereIn('status', [PayslipStatus::APPROVED, PayslipStatus::PAID])
            ->emailNotSent()
            ->whereHas('employee.user') // Only employees with user accounts
            ->limit($limit);

        if ($year) {
            $query->where('pay_year', $year);
        }

        if ($month) {
            $query->where('pay_month', $month);
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->get();
    }

    protected function sendPayslipEmail(Payslip $payslip): void
    {
        $employee = $payslip->employee;
        $user = $employee->user;

        if (!$user || !$user->email) {
            throw new \Exception("Employee {$employee->name} does not have a valid email address");
        }

        Mail::to($user->email)
            ->send(new PayslipEmail($payslip));
    }
}