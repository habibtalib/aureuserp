<?php

namespace Webkul\Claims\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Claims\Enums\ClaimStatus;
use Webkul\Claims\Models\Claim;
use Webkul\Claims\Models\ClaimCategory;
use Webkul\Employee\Models\Employee;
use Webkul\Support\Models\Company;

class ClaimFactory extends Factory
{
    protected $model = Claim::class;

    public function definition(): array
    {
        return [
            'claim_number' => 'CLM-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'employee_id' => Employee::factory(),
            'category_id' => ClaimCategory::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'total_amount' => $this->faker->randomFloat(2, 10, 2000),
            'currency' => 'USD',
            'status' => $this->faker->randomElement(ClaimStatus::cases()),
            'expense_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'submitted_at' => $this->faker->optional(0.8)->dateTimeBetween('-2 months', 'now'),
            'approved_at' => $this->faker->optional(0.4)->dateTimeBetween('-1 month', 'now'),
            'paid_at' => $this->faker->optional(0.2)->dateTimeBetween('-2 weeks', 'now'),
            'approved_by' => null, // Will be set by state methods
            'approval_notes' => $this->faker->optional(0.3)->sentence(),
            'rejection_reason' => $this->faker->optional(0.1)->sentence(),
            'company_id' => Company::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClaimStatus::DRAFT,
            'submitted_at' => null,
            'approved_at' => null,
            'paid_at' => null,
            'approved_by' => null,
            'approval_notes' => null,
            'rejection_reason' => null,
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClaimStatus::SUBMITTED,
            'submitted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'approved_at' => null,
            'paid_at' => null,
            'approved_by' => null,
            'approval_notes' => null,
            'rejection_reason' => null,
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClaimStatus::UNDER_REVIEW,
            'submitted_at' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
            'approved_at' => null,
            'paid_at' => null,
            'approved_by' => null,
            'approval_notes' => null,
            'rejection_reason' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClaimStatus::APPROVED,
            'submitted_at' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
            'approved_at' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
            'paid_at' => null,
            'approved_by' => 1, // Use system admin ID for testing
            'approval_notes' => $this->faker->optional(0.7)->sentence(),
            'rejection_reason' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClaimStatus::REJECTED,
            'submitted_at' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
            'approved_at' => null,
            'paid_at' => null,
            'approved_by' => null,
            'approval_notes' => null,
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClaimStatus::PAID,
            'submitted_at' => $this->faker->dateTimeBetween('-3 months', '-2 months'),
            'approved_at' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
            'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'approved_by' => 1, // Use system admin ID for testing
            'approval_notes' => $this->faker->optional(0.7)->sentence(),
            'rejection_reason' => null,
        ]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $amount,
        ]);
    }

    public function forEmployee(Employee $employee): static
    {
        return $this->state(fn (array $attributes) => [
            'employee_id' => $employee->id,
            'company_id' => $employee->company_id,
        ]);
    }

    public function forCategory(ClaimCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
            'company_id' => $category->company_id,
        ]);
    }
}