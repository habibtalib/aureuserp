<?php

namespace Webkul\Claims\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Webkul\Claims\Models\ClaimCategory;

class InstallCommand extends Command
{
    protected $signature = 'claims:install {--seed : Seed default claim categories}';

    protected $description = 'Install the Claims module';

    public function handle(): int
    {
        $this->info('Installing Claims module...');

        // Run migrations
        $this->info('Running migrations...');
        try {
            Artisan::call('migrate', [
                '--path' => 'plugins/webkul/claims/database/migrations',
                '--force' => true,
            ]);
            $this->info('✓ Migrations completed successfully');
        } catch (\Exception $e) {
            $this->error('✗ Migration failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Seed default categories if requested
        if ($this->option('seed')) {
            $this->info('Seeding default claim categories...');
            $this->seedDefaultCategories();
        }

        // Clear caches
        $this->info('Clearing caches...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        $this->info('✓ Claims module installed successfully!');
        
        $this->line('');
        $this->info('Next steps:');
        $this->line('1. Add the Claims plugin to your bootstrap/plugins.php file');
        $this->line('2. Add the ClaimsServiceProvider to your bootstrap/providers.php file');
        $this->line('3. Configure claim approval settings in config/claims.php');
        $this->line('4. Set up claim number sequences and approval workflows');

        return Command::SUCCESS;
    }

    protected function seedDefaultCategories(): void
    {
        $categories = [
            [
                'name' => 'Travel',
                'code' => 'TRAVEL',
                'description' => 'Travel expenses including transportation, accommodation, and meals during business trips',
                'max_amount' => 5000.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'OFFICE',
                'description' => 'Office supplies and equipment purchases for workplace use',
                'max_amount' => 500.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Training & Development',
                'code' => 'TRAINING',
                'description' => 'Training courses, certifications, conferences, and educational expenses',
                'max_amount' => 2000.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Meals & Entertainment',
                'code' => 'MEALS',
                'description' => 'Business meals, client entertainment, and related hospitality expenses',
                'max_amount' => 300.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Communications',
                'code' => 'COMMS',
                'description' => 'Phone, internet, mobile, and other communication-related expenses',
                'max_amount' => 200.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Software & Subscriptions',
                'code' => 'SOFTWARE',
                'description' => 'Software licenses, subscriptions, and digital tools for business use',
                'max_amount' => 1000.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Marketing & Advertising',
                'code' => 'MARKETING',
                'description' => 'Marketing materials, advertising costs, and promotional expenses',
                'max_amount' => 1500.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Medical & Health',
                'code' => 'MEDICAL',
                'description' => 'Medical expenses, health insurance, and wellness-related costs',
                'max_amount' => 1000.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Miscellaneous',
                'code' => 'MISC',
                'description' => 'Other business-related expenses not covered by specific categories',
                'max_amount' => 250.00,
                'requires_receipt' => false,
                'is_active' => true,
            ],
        ];

        $companiesWithCategories = DB::table('companies')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('claims_categories')
                    ->whereColumn('claims_categories.company_id', 'companies.id');
            })
            ->pluck('id');

        $companiesNeedingCategories = DB::table('companies')
            ->whereNotIn('id', $companiesWithCategories)
            ->get();

        if ($companiesNeedingCategories->isEmpty()) {
            $this->info('✓ Default categories already exist for all companies');
            return;
        }

        $createdCount = 0;
        foreach ($companiesNeedingCategories as $company) {
            foreach ($categories as $categoryData) {
                ClaimCategory::create(array_merge($categoryData, [
                    'company_id' => $company->id,
                    'created_by' => 1, // System user
                ]));
                $createdCount++;
            }
        }

        $this->info("✓ Created {$createdCount} default claim categories for " . $companiesNeedingCategories->count() . " companies");
    }
}