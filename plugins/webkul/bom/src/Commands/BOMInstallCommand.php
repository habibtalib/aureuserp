<?php

namespace Webkul\BOM\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BOMInstallCommand extends Command
{
    protected $signature = 'bom:install {--force : Force the operation to run}';

    protected $description = 'Install the Bill of Materials (BOM) plugin';

    public function handle(): int
    {
        $this->info('Installing Bill of Materials (BOM) Plugin...');

        // Check dependencies
        if (!$this->checkDependencies()) {
            return self::FAILURE;
        }

        // Run migrations
        $this->info('Running BOM migrations...');
        Artisan::call('migrate', [
            '--path' => 'plugins/webkul/bom/database/migrations',
            '--force' => $this->option('force'),
        ]);

        // Publish assets if any
        $this->info('Publishing BOM assets...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Webkul\\BOM\\BOMServiceProvider',
            '--force' => $this->option('force'),
        ]);

        // Run seeders
        if ($this->confirm('Do you want to run BOM seeders?', true)) {
            $this->info('Running BOM seeders...');
            Artisan::call('db:seed', [
                '--class' => 'Webkul\\BOM\\Database\\Seeders\\DatabaseSeeder',
                '--force' => $this->option('force'),
            ]);
        }

        // Generate permissions
        $this->info('Generating BOM permissions...');
        Artisan::call('shield:generate', [
            '--all' => true,
        ]);

        $this->info('✅ BOM Plugin installed successfully!');
        
        $this->newLine();
        $this->info('🚀 You can now access Bill of Materials from the Manufacturing menu.');
        
        return self::SUCCESS;
    }

    private function checkDependencies(): bool
    {
        $dependencies = [
            'Webkul\Product\ProductServiceProvider' => 'Products plugin is required for BOM functionality',
            'Webkul\Inventory\InventoryServiceProvider' => 'Inventories plugin is required for BOM functionality',
        ];

        $missing = [];

        foreach ($dependencies as $providerClass => $message) {
            if (!class_exists($providerClass)) {
                $missing[] = $providerClass;
                $this->error("❌ {$message}");
            }
        }

        if (!empty($missing)) {
            $this->error('Please install the missing dependencies first.');
            $this->error('Make sure the following service providers are registered:');
            foreach ($missing as $provider) {
                $this->line("  {$provider}");
            }
            return false;
        }

        $this->info('✅ All dependencies are satisfied.');
        return true;
    }
}