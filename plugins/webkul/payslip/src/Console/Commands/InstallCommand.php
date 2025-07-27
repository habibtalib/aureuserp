<?php

namespace Webkul\Payslip\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Webkul\Payslip\Models\SalaryComponent;
use Webkul\Payslip\Models\SalaryStructure;

class InstallCommand extends Command
{
    protected $signature = 'payslip:install {--seed : Seed default salary components and structures}';

    protected $description = 'Install the Payslip module';

    public function handle(): int
    {
        $this->info('Installing Payslip module...');

        // Run migrations
        $this->info('Running migrations...');
        try {
            Artisan::call('migrate', [
                '--path' => 'plugins/webkul/payslip/database/migrations',
                '--force' => true,
            ]);
            $this->info('✓ Migrations completed successfully');
        } catch (\Exception $e) {
            $this->error('✗ Migration failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Seed default data if requested
        if ($this->option('seed')) {
            $this->info('Seeding default salary components and structures...');
            $this->seedDefaultData();
        }

        // Clear caches
        $this->info('Clearing caches...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        $this->info('✓ Payslip module installed successfully!');
        
        $this->line('');
        $this->info('Next steps:');
        $this->line('1. Add the Payslip plugin to your bootstrap/plugins.php file');
        $this->line('2. Add the PayslipServiceProvider to your bootstrap/providers.php file');
        $this->line('3. Configure payslip settings in config/payslip.php');
        $this->line('4. Set up salary structures and assign to employees');
        $this->line('5. Configure attendance integration if needed');

        return Command::SUCCESS;
    }

    protected function seedDefaultData(): void
    {
        $this->seedSalaryComponents();
        $this->seedDefaultSalaryStructure();
    }

    protected function seedSalaryComponents(): void
    {
        $components = config('payslip.salary_components');
        $createdCount = 0;

        $companies = DB::table('companies')->get();

        foreach ($companies as $company) {
            // Earnings
            foreach ($components['earnings'] as $code => $config) {
                SalaryComponent::firstOrCreate([
                    'code' => strtoupper($config['code']),
                    'company_id' => $company->id,
                ], [
                    'name' => $config['name'],
                    'description' => "Default {$config['name']} component",
                    'type' => $config['type'],
                    'calculation_type' => 'fixed',
                    'default_amount' => 0,
                    'is_taxable' => $config['taxable'],
                    'is_provident_fund_applicable' => $config['provident_fund_applicable'],
                    'is_active' => true,
                    'display_order' => $config['order'],
                    'created_by' => 1,
                ]);
                $createdCount++;
            }

            // Deductions
            foreach ($components['deductions'] as $code => $config) {
                $defaultRate = isset($config['rate']) ? $config['rate'] : null;
                $defaultAmount = isset($config['amount']) ? $config['amount'] : null;
                $calculationType = $config['calculation_type'];

                SalaryComponent::firstOrCreate([
                    'code' => strtoupper($config['code']),
                    'company_id' => $company->id,
                ], [
                    'name' => $config['name'],
                    'description' => "Default {$config['name']} component",
                    'type' => $config['type'],
                    'calculation_type' => $calculationType,
                    'default_amount' => $defaultAmount,
                    'default_rate' => $defaultRate,
                    'is_taxable' => false,
                    'is_provident_fund_applicable' => false,
                    'is_active' => true,
                    'display_order' => $config['order'],
                    'created_by' => 1,
                ]);
                $createdCount++;
            }
        }

        $this->info("✓ Created {$createdCount} default salary components");
    }

    protected function seedDefaultSalaryStructure(): void
    {
        $companies = DB::table('companies')->get();
        $createdCount = 0;

        foreach ($companies as $company) {
            $existingStructure = SalaryStructure::where('company_id', $company->id)->first();
            
            if (!$existingStructure) {
                SalaryStructure::create([
                    'name' => 'Default Salary Structure',
                    'code' => 'DEFAULT-SS',
                    'description' => 'Default salary structure for all employees',
                    'pay_period' => 'monthly',
                    'basic_salary' => 50000.00,
                    'allowances' => [
                        ['component' => 'HRA', 'amount' => 20000.00],
                        ['component' => 'TA', 'amount' => 5000.00],
                        ['component' => 'MA', 'amount' => 3000.00],
                    ],
                    'deductions' => [
                        ['component' => 'PF', 'rate' => 12.0],
                        ['component' => 'PT', 'amount' => 200.00],
                    ],
                    'is_active' => true,
                    'is_default' => true,
                    'company_id' => $company->id,
                    'created_by' => 1,
                ]);
                $createdCount++;
            }
        }

        $this->info("✓ Created {$createdCount} default salary structures");
    }
}