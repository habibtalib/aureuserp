<?php

namespace Webkul\BOM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\BOM\Enums\ComponentType;
use Webkul\BOM\Models\BomLine;

class BomLineFactory extends Factory
{
    protected $model = BomLine::class;

    public function definition(): array
    {
        return [
            'bom_id' => \Webkul\BOM\Models\BillOfMaterial::factory(),
            'product_id' => function () {
                return \Webkul\Product\Models\Product::inRandomOrder()->first()?->id ?? 1;
            },
            'quantity' => $this->faker->randomFloat(4, 0.1, 50),
            'unit_id' => function () {
                return \Webkul\Support\Models\UOM::inRandomOrder()->first()?->id ?? 1;
            },
            'sequence' => $this->faker->numberBetween(10, 100),
            'component_type' => $this->faker->randomElement(ComponentType::cases()),
            'sub_bom_id' => null, // Will be set by specific factory states when needed
            'waste_percentage' => $this->faker->randomFloat(2, 0, 10),
            'is_optional' => $this->faker->boolean(20), // 20% chance of being optional
            'notes' => $this->faker->optional(0.4)->sentence(),
            'company_id' => 1, // Assuming default company
        ];
    }

    public function material(): static
    {
        return $this->state(fn (array $attributes) => [
            'component_type' => ComponentType::MATERIAL,
            'sub_bom_id' => null,
        ]);
    }

    public function component(): static
    {
        return $this->state(fn (array $attributes) => [
            'component_type' => ComponentType::COMPONENT,
            'sub_bom_id' => null,
        ]);
    }

    public function subAssembly(): static
    {
        return $this->state(fn (array $attributes) => [
            'component_type' => ComponentType::SUB_ASSEMBLY,
            'sub_bom_id' => function () {
                return \Webkul\BOM\Models\BillOfMaterial::inRandomOrder()->first()?->id;
            },
        ]);
    }

    public function consumable(): static
    {
        return $this->state(fn (array $attributes) => [
            'component_type' => ComponentType::CONSUMABLE,
            'sub_bom_id' => null,
            'waste_percentage' => $this->faker->randomFloat(2, 5, 25), // Higher waste for consumables
        ]);
    }

    public function byproduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'component_type' => ComponentType::BYPRODUCT,
            'sub_bom_id' => null,
            'quantity' => $this->faker->randomFloat(4, 0.1, 5), // Usually smaller quantities
        ]);
    }

    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_optional' => true,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_optional' => false,
        ]);
    }

    public function withWaste(float $percentage = 5.0): static
    {
        return $this->state(fn (array $attributes) => [
            'waste_percentage' => $percentage,
        ]);
    }
}