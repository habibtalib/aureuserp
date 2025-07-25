<?php

namespace Webkul\Claims\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Claims\Models\ClaimCategory;
use Webkul\Support\Models\Company;

class ClaimCategoryFactory extends Factory
{
    protected $model = ClaimCategory::class;

    public function definition(): array
    {
        $categories = [
            ['name' => 'Travel', 'code' => 'TRAVEL', 'description' => 'Travel expenses including transportation, accommodation, and meals'],
            ['name' => 'Office Supplies', 'code' => 'OFFICE', 'description' => 'Office supplies and equipment purchases'],
            ['name' => 'Training', 'code' => 'TRAINING', 'description' => 'Training courses, certifications, and educational expenses'],
            ['name' => 'Meals & Entertainment', 'code' => 'MEALS', 'description' => 'Business meals and client entertainment'],
            ['name' => 'Communications', 'code' => 'COMMS', 'description' => 'Phone, internet, and communication expenses'],
            ['name' => 'Software & Subscriptions', 'code' => 'SOFTWARE', 'description' => 'Software licenses and subscription services'],
            ['name' => 'Marketing', 'code' => 'MARKETING', 'description' => 'Marketing and promotional expenses'],
            ['name' => 'Medical', 'code' => 'MEDICAL', 'description' => 'Medical and health-related expenses'],
        ];

        $category = $this->faker->randomElement($categories);

        return [
            'name' => $category['name'],
            'code' => $category['code'],
            'description' => $category['description'],
            'max_amount' => $this->faker->optional(0.7)->randomFloat(2, 100, 5000),
            'requires_receipt' => $this->faker->boolean(80),
            'is_active' => $this->faker->boolean(90),
            'company_id' => Company::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withReceiptRequired(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_receipt' => true,
        ]);
    }

    public function withoutReceiptRequired(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_receipt' => false,
        ]);
    }

    public function withMaxAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'max_amount' => $amount,
        ]);
    }

    public function withoutMaxAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_amount' => null,
        ]);
    }
}