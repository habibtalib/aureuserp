<?php

namespace Webkul\BOM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\BOM\Models\BomVersion;

class BomVersionFactory extends Factory
{
    protected $model = BomVersion::class;

    public function definition(): array
    {
        return [
            'bom_id' => \Webkul\BOM\Models\BillOfMaterial::factory(),
            'version_number' => $this->faker->randomElement(['1.0', '1.1', '1.2', '2.0', '2.1']),
            'effective_date' => $this->faker->optional(0.8)->dateTimeBetween('-6 months', 'now'),
            'expiry_date' => $this->faker->optional(0.4)->dateTimeBetween('now', '+1 year'),
            'change_description' => $this->faker->optional(0.9)->sentence(),
            'change_reason' => $this->faker->optional(0.7)->randomElement([
                'Engineering change request',
                'Cost optimization',
                'Material substitution',
                'Quality improvement',
                'Supplier change',
                'Design update',
                'Regulatory compliance',
            ]),
            'created_by' => 1, // Assuming admin user
            'company_id' => 1, // Assuming default company
        ];
    }

    public function effective(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_date' => now()->subDays(rand(1, 90)),
            'expiry_date' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_date' => now()->subMonths(rand(3, 12)),
            'expiry_date' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_date' => now()->addDays(rand(1, 90)),
            'expiry_date' => null,
        ]);
    }

    public function majorVersion(): static
    {
        return $this->state(fn (array $attributes) => [
            'version_number' => $this->faker->randomElement(['2.0', '3.0', '4.0']),
            'change_reason' => 'Major design revision',
            'change_description' => 'Significant changes to product design and manufacturing process',
        ]);
    }

    public function minorVersion(): static
    {
        return $this->state(fn (array $attributes) => [
            'version_number' => $this->faker->randomElement(['1.1', '1.2', '1.3', '2.1', '2.2']),
            'change_reason' => 'Minor improvement',
            'change_description' => 'Small adjustments and optimizations',
        ]);
    }
}