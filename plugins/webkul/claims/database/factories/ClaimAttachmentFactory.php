<?php

namespace Webkul\Claims\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Claims\Models\Claim;
use Webkul\Claims\Models\ClaimAttachment;
use Webkul\Claims\Models\ClaimLine;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

class ClaimAttachmentFactory extends Factory
{
    protected $model = ClaimAttachment::class;

    public function definition(): array
    {
        $fileTypes = [
            ['ext' => 'pdf', 'mime' => 'application/pdf', 'size_range' => [50000, 500000]],
            ['ext' => 'jpg', 'mime' => 'image/jpeg', 'size_range' => [100000, 2000000]],
            ['ext' => 'png', 'mime' => 'image/png', 'size_range' => [80000, 1500000]],
            ['ext' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'size_range' => [20000, 200000]],
            ['ext' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'size_range' => [15000, 150000]],
        ];

        $fileType = $this->faker->randomElement($fileTypes);
        $filename = $this->faker->uuid() . '.' . $fileType['ext'];
        $originalFilename = $this->faker->word() . '_receipt.' . $fileType['ext'];

        return [
            'claim_id' => Claim::factory(),
            'claim_line_id' => $this->faker->optional(0.6)->randomElement([null, ClaimLine::factory()]),
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'mime_type' => $fileType['mime'],
            'size' => $this->faker->numberBetween($fileType['size_range'][0], $fileType['size_range'][1]),
            'path' => 'claims/attachments/' . date('Y/m/') . $filename,
            'disk' => 'local',
            'description' => $this->faker->optional(0.4)->sentence(),
            'company_id' => Company::factory(),
            'uploaded_by' => User::factory(),
        ];
    }

    public function forClaim(Claim $claim): static
    {
        return $this->state(fn (array $attributes) => [
            'claim_id' => $claim->id,
            'company_id' => $claim->company_id,
        ]);
    }

    public function forClaimLine(ClaimLine $claimLine): static
    {
        return $this->state(fn (array $attributes) => [
            'claim_id' => $claimLine->claim_id,
            'claim_line_id' => $claimLine->id,
            'company_id' => $claimLine->company_id,
        ]);
    }

    public function uploadedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_by' => $user->id,
        ]);
    }

    public function pdf(): static
    {
        $filename = $this->faker->uuid() . '.pdf';
        return $this->state(fn (array $attributes) => [
            'filename' => $filename,
            'original_filename' => $this->faker->word() . '_receipt.pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(50000, 500000),
            'path' => 'claims/attachments/' . date('Y/m/') . $filename,
        ]);
    }

    public function image(): static
    {
        $ext = $this->faker->randomElement(['jpg', 'png']);
        $mime = $ext === 'jpg' ? 'image/jpeg' : 'image/png';
        $filename = $this->faker->uuid() . '.' . $ext;
        
        return $this->state(fn (array $attributes) => [
            'filename' => $filename,
            'original_filename' => $this->faker->word() . '_receipt.' . $ext,
            'mime_type' => $mime,
            'size' => $this->faker->numberBetween(100000, 2000000),
            'path' => 'claims/attachments/' . date('Y/m/') . $filename,
        ]);
    }

    public function document(): static
    {
        $types = [
            ['ext' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['ext' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];
        
        $type = $this->faker->randomElement($types);
        $filename = $this->faker->uuid() . '.' . $type['ext'];
        
        return $this->state(fn (array $attributes) => [
            'filename' => $filename,
            'original_filename' => $this->faker->word() . '_document.' . $type['ext'],
            'mime_type' => $type['mime'],
            'size' => $this->faker->numberBetween(20000, 200000),
            'path' => 'claims/attachments/' . date('Y/m/') . $filename,
        ]);
    }

    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
        ]);
    }

    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }
}