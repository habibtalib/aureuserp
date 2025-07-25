<?php

namespace Webkul\Claims\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Claims\Models\Claim;
use Webkul\Claims\Models\ClaimLine;
use Webkul\Support\Models\Company;

class ClaimLineFactory extends Factory
{
    protected $model = ClaimLine::class;

    public function definition(): array
    {
        $expenseTypes = [
            'Hotel accommodation',
            'Flight ticket',
            'Taxi fare',
            'Business meal',
            'Office supplies',
            'Software license',
            'Training course',
            'Conference registration',
            'Client entertainment',
            'Phone bill',
            'Internet service',
            'Parking fee',
            'Fuel expense',
            'Equipment purchase',
            'Marketing materials',
        ];

        return [
            'claim_id' => Claim::factory(),
            'description' => $this->faker->randomElement($expenseTypes) . ' - ' . $this->faker->sentence(3),
            'amount' => $this->faker->randomFloat(2, 5, 500),
            'currency' => 'USD',
            'expense_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'receipt_reference' => $this->faker->optional(0.7)->regexify('REC-[0-9]{6}'),
            'notes' => $this->faker->optional(0.4)->sentence(),
            'sequence' => $this->faker->numberBetween(1, 10),
            'company_id' => Company::factory(),
        ];
    }

    public function forClaim(Claim $claim): static
    {
        return $this->state(fn (array $attributes) => [
            'claim_id' => $claim->id,
            'company_id' => $claim->company_id,
        ]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    public function withSequence(int $sequence): static
    {
        return $this->state(fn (array $attributes) => [
            'sequence' => $sequence,
        ]);
    }

    public function withReceipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'receipt_reference' => $this->faker->regexify('REC-[0-9]{6}'),
        ]);
    }

    public function withoutReceipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'receipt_reference' => null,
        ]);
    }

    public function travel(): static
    {
        $travelExpenses = [
            'Flight ticket to conference',
            'Hotel accommodation',
            'Taxi to airport',
            'Business dinner with client',
            'Parking at hotel',
        ];

        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->randomElement($travelExpenses),
            'amount' => $this->faker->randomFloat(2, 50, 800),
        ]);
    }

    public function office(): static
    {
        $officeExpenses = [
            'Office supplies - pens and paper',
            'Printer cartridges',
            'Desk organizer',
            'Whiteboard markers',
            'Filing folders',
        ];

        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->randomElement($officeExpenses),
            'amount' => $this->faker->randomFloat(2, 10, 150),
        ]);
    }

    public function software(): static
    {
        $softwareExpenses = [
            'Adobe Creative Suite license',
            'Microsoft Office subscription',
            'Project management tool',
            'Design software license',
            'Cloud storage upgrade',
        ];

        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->randomElement($softwareExpenses),
            'amount' => $this->faker->randomFloat(2, 20, 300),
        ]);
    }
}