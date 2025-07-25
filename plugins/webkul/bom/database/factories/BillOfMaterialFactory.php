<?php

namespace Webkul\BOM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\BOM\Enums\BomState;
use Webkul\BOM\Enums\BomType;
use Webkul\BOM\Models\BillOfMaterial;

class BillOfMaterialFactory extends Factory
{
    protected $model = BillOfMaterial::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true) . ' BOM',
            'reference' => 'BOM-' . $this->faker->unique()->numberBetween(1000, 9999),
            'product_id' => function () {
                return \Webkul\Product\Models\Product::inRandomOrder()->first()?->id ?? 1;
            },
            'version' => $this->faker->randomElement(['1.0', '1.1', '2.0', '2.1']),
            'type' => $this->faker->randomElement(BomType::cases()),
            'state' => $this->faker->randomElement([BomState::DRAFT, BomState::ACTIVE]),
            'quantity_to_produce' => $this->faker->randomFloat(4, 1, 100),
            'unit_id' => function () {
                return \Webkul\Support\Models\UOM::inRandomOrder()->first()?->id ?? 1;
            },
            'effective_date' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', '+1 month'),
            'expiry_date' => $this->faker->optional(0.3)->dateTimeBetween('+1 month', '+1 year'),
            'description' => $this->faker->optional(0.8)->sentence(),
            'notes' => $this->faker->optional(0.5)->paragraph(),
            'company_id' => 1, // Assuming default company
            'created_by' => 1, // Assuming admin user
            'updated_by' => 1,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => BomState::ACTIVE,
            'effective_date' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => BomState::DRAFT,
        ]);
    }

    public function obsolete(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => BomState::OBSOLETE,
            'effective_date' => now()->subMonths(rand(1, 6)),
            'expiry_date' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BomType::STANDARD,
        ]);
    }

    public function kit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BomType::KIT,
        ]);
    }

    public function assembly(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BomType::ASSEMBLY,
        ]);
    }

    public function withComponents(int $count = 5): static
    {
        return $this->has(
            \Webkul\BOM\Models\BomLine::factory()
                ->count($count),
            'bomLines'
        );
    }
}