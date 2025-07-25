<?php

namespace Webkul\Claims\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Chatter\Traits\HasChatter;
use Webkul\Claims\Database\Factories\ClaimFactory;
use Webkul\Claims\Enums\ClaimStatus;
use Webkul\Employee\Models\Employee;
use Webkul\Field\Traits\HasCustomFields;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

class Claim extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasChatter, HasCustomFields;

    protected $table = 'claims_claims';

    protected $fillable = [
        'claim_number',
        'employee_id',
        'category_id',
        'title',
        'description',
        'total_amount',
        'currency',
        'status',
        'expense_date',
        'submitted_at',
        'approved_at',
        'paid_at',
        'approved_by',
        'approval_notes',
        'rejection_reason',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => ClaimStatus::class,
        'total_amount' => 'decimal:2',
        'expense_date' => 'date',
        'submitted_at' => 'date',
        'approved_at' => 'date',
        'paid_at' => 'date',
    ];

    protected static function newFactory()
    {
        return ClaimFactory::new();
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($claim) {
            if (empty($claim->claim_number)) {
                $claim->claim_number = static::generateClaimNumber();
            }
        });
    }

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ClaimCategory::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function claimLines(): HasMany
    {
        return $this->hasMany(ClaimLine::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ClaimApproval::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClaimAttachment::class);
    }

    // Helper methods
    public static function generateClaimNumber(): string
    {
        $prefix = config('claims.claim_number_prefix', 'CLM-');
        $lastClaim = static::withTrashed()
            ->where('claim_number', 'like', $prefix . '%')
            ->orderBy('claim_number', 'desc')
            ->first();

        if (!$lastClaim) {
            return $prefix . '0001';
        }

        $lastNumber = (int) str_replace($prefix, '', $lastClaim->claim_number);
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function submit(): void
    {
        $this->update([
            'status' => ClaimStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        // Create approval workflow
        $this->createApprovalWorkflow();
    }

    public function approve(Employee $approver, string $notes = null): void
    {
        $this->update([
            'status' => ClaimStatus::APPROVED,
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'approval_notes' => $notes,
        ]);
    }

    public function reject(string $reason): void
    {
        $this->update([
            'status' => ClaimStatus::REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => ClaimStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    protected function createApprovalWorkflow(): void
    {
        $approvalLevels = config('claims.approval_levels', []);
        $level = 1;

        foreach ($approvalLevels as $levelAmount) {
            if ($this->total_amount > $levelAmount) {
                // Find appropriate approver (this would be customized based on business logic)
                $approver = $this->findApproverForLevel($level);
                
                if ($approver) {
                    ClaimApproval::create([
                        'claim_id' => $this->id,
                        'approver_id' => $approver->id,
                        'level' => $level,
                        'company_id' => $this->company_id,
                    ]);
                }
                
                $level++;
            }
        }

        if ($level === 1) {
            // Auto-approve if below threshold
            $autoApproveThreshold = config('claims.auto_approve_threshold', 100);
            if ($this->total_amount <= $autoApproveThreshold) {
                $this->update(['status' => ClaimStatus::APPROVED]);
            }
        }
    }

    protected function findApproverForLevel(int $level): ?Employee
    {
        // This would be customized based on business logic
        // For now, return the employee's manager
        return $this->employee->parent ?? $this->employee->department?->manager ?? null;
    }

    // Scopes
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByStatus($query, ClaimStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePendingApproval($query)
    {
        return $query->whereIn('status', [ClaimStatus::SUBMITTED, ClaimStatus::UNDER_REVIEW]);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}