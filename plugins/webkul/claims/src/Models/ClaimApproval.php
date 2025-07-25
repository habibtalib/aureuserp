<?php

namespace Webkul\Claims\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Claims\Database\Factories\ClaimApprovalFactory;
use Webkul\Claims\Enums\ApprovalStatus;
use Webkul\Employee\Models\Employee;
use Webkul\Support\Models\Company;

class ClaimApproval extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'claims_approvals';

    protected $fillable = [
        'claim_id',
        'approver_id',
        'level',
        'status',
        'comments',
        'approved_at',
        'company_id',
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
        'level' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return ClaimApprovalFactory::new();
    }

    // Relationships
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Helper methods
    public function approve(string $comments = null): void
    {
        $this->update([
            'status' => ApprovalStatus::APPROVED,
            'comments' => $comments,
            'approved_at' => now(),
        ]);

        // Check if this was the final approval
        $this->checkFinalApproval();
    }

    public function reject(string $comments): void
    {
        $this->update([
            'status' => ApprovalStatus::REJECTED,
            'comments' => $comments,
        ]);

        // Reject the entire claim
        $this->claim->reject($comments);
    }

    protected function checkFinalApproval(): void
    {
        $pendingApprovals = $this->claim->approvals()
            ->where('status', ApprovalStatus::PENDING)
            ->count();

        if ($pendingApprovals === 0) {
            // All approvals completed, approve the claim
            $this->claim->approve($this->approver);
        }
    }

    // Scopes
    public function scopeForApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    public function scopePending($query)
    {
        return $query->where('status', ApprovalStatus::PENDING);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }
}