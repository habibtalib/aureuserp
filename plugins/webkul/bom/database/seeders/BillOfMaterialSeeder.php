<?php

namespace Webkul\BOM\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\BOM\Models\BillOfMaterial;
use Webkul\BOM\Models\BomLine;

class BillOfMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating sample Bills of Material...');

        // Create sample BOMs with components
        $standardBom = BillOfMaterial::factory()
            ->standard()
            ->active()
            ->create([
                'name' => 'Standard Product Assembly BOM',
                'reference' => 'BOM-STD-001',
            ]);

        // Add components to the standard BOM
        BomLine::factory()->material()->create([
            'bom_id' => $standardBom->id,
            'quantity' => 2.0,
            'sequence' => 10,
        ]);

        BomLine::factory()->component()->create([
            'bom_id' => $standardBom->id,
            'quantity' => 1.0,
            'sequence' => 20,
        ]);

        BomLine::factory()->consumable()->create([
            'bom_id' => $standardBom->id,
            'quantity' => 0.5,
            'sequence' => 30,
            'waste_percentage' => 10.0,
        ]);

        // Create a kit BOM
        $kitBom = BillOfMaterial::factory()
            ->kit()
            ->active()
            ->create([
                'name' => 'Product Kit BOM',
                'reference' => 'BOM-KIT-001',
            ]);

        // Add kit components
        for ($i = 1; $i <= 4; $i++) {
            BomLine::factory()->component()->create([
                'bom_id' => $kitBom->id,
                'quantity' => 1.0,
                'sequence' => $i * 10,
            ]);
        }

        // Create an assembly BOM with sub-assemblies
        $assemblyBom = BillOfMaterial::factory()
            ->assembly()
            ->active()
            ->create([
                'name' => 'Complex Assembly BOM',
                'reference' => 'BOM-ASM-001',
            ]);

        // Create a sub-assembly BOM
        $subAssemblyBom = BillOfMaterial::factory()
            ->standard()
            ->active()
            ->create([
                'name' => 'Sub-Assembly BOM',
                'reference' => 'BOM-SUB-001',
            ]);

        // Add components to sub-assembly
        BomLine::factory()->material()->count(3)->create([
            'bom_id' => $subAssemblyBom->id,
        ]);

        // Add sub-assembly to main assembly
        BomLine::factory()->subAssembly()->create([
            'bom_id' => $assemblyBom->id,
            'sub_bom_id' => $subAssemblyBom->id,
            'quantity' => 2.0,
            'sequence' => 10,
        ]);

        // Add other components to main assembly
        BomLine::factory()->material()->create([
            'bom_id' => $assemblyBom->id,
            'quantity' => 1.0,
            'sequence' => 20,
        ]);

        BomLine::factory()->byproduct()->create([
            'bom_id' => $assemblyBom->id,
            'quantity' => 0.2,
            'sequence' => 30,
        ]);

        // Create some draft BOMs
        BillOfMaterial::factory()
            ->draft()
            ->count(3)
            ->create()
            ->each(function ($bom) {
                BomLine::factory()
                    ->count(rand(2, 6))
                    ->create(['bom_id' => $bom->id]);
            });

        // Create some obsolete BOMs
        BillOfMaterial::factory()
            ->obsolete()
            ->count(2)
            ->create()
            ->each(function ($bom) {
                BomLine::factory()
                    ->count(rand(3, 5))
                    ->create(['bom_id' => $bom->id]);
            });

        $this->command->info('✅ Sample Bills of Material created successfully!');
        $this->command->line('   - ' . BillOfMaterial::count() . ' BOMs created');
        $this->command->line('   - ' . BomLine::count() . ' BOM lines created');
    }
}