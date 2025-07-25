<?php

namespace Webkul\Claims\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Claims\Enums\ApprovalStatus;
use Webkul\Claims\Models\Claim;
use Webkul\Claims\Models\ClaimApproval;
use Webkul\Employee\Models\Employee;
use Webkul\Support\Models\Company;

class ClaimApprovalFactory extends Factory
{
    protected $model = ClaimApproval::class;

    public function definition(): array
    {
        return [
            'claim_id' => Claim::factory(),
            'approver_id' => Employee::factory(),
            'level' => $this->faker->numberBetween(1, 3),
            'status' => $this->faker->randomElement(ApprovalStatus::cases()),
            'comments' => $this->faker->optional(0.6)->sentence(),
            'approved_at' => $this->faker->optional(0.5)->dateTimeBetween('-1 month', 'now'),
            'company_id' => Company::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApprovalStatus::PENDING,
            'comments' => null,
            'approved_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApprovalStatus::APPROVED,
            'comments' => $this->faker->optional(0.7)->sentence(),
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApprovalStatus::REJECTED,
            'comments' => $this->faker->sentence(),
            'approved_at' => null,
        ]);
    }

    public function forClaim(Claim $claim): static
    {
        return $this->state(fn (array $attributes) => [
            'claim_id' => $claim->id,
            'company_id' => $claim->company_id,
        ]);
    }

    public function forApprover(Employee $approver): static
    {
        return $this->state(fn (array $attributes) => [
            'approver_id' => $approver->id,
            'company_id' => $approver->company_id,
        ]);
    }

    public function level(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
        ]);
    }

    public function withComments(string $comments): static
    {
        return $this->state(fn (array $attributes) => [
            'comments' => $comments,
        ]);
    }

    public function withoutComments(): static
    {
        return $this->state(fn (array $attributes) => [
            'comments' => null,
        ]);
    }
}