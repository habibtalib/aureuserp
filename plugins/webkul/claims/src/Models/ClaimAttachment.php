<?php

namespace Webkul\Claims\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Webkul\Claims\Database\Factories\ClaimAttachmentFactory;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

class ClaimAttachment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'claims_attachments';

    protected $fillable = [
        'claim_id',
        'claim_line_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
        'disk',
        'description',
        'company_id',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected static function newFactory()
    {
        return ClaimAttachmentFactory::new();
    }

    // Relationships
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function claimLine(): BelongsTo
    {
        return $this->belongsTo(ClaimLine::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helper methods
    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getContents(): string
    {
        return Storage::disk($this->disk)->get($this->path);
    }

    public function download()
    {
        return Storage::disk($this->disk)->download($this->path, $this->original_filename);
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    public function delete(): bool
    {
        if ($this->exists()) {
            Storage::disk($this->disk)->delete($this->path);
        }
        
        return parent::delete();
    }

    // Scopes
    public function scopeForClaim($query, $claimId)
    {
        return $query->where('claim_id', $claimId);
    }

    public function scopeForClaimLine($query, $claimLineId)
    {
        return $query->where('claim_line_id', $claimLineId);
    }

    public function scopeByType($query, string $mimeType)
    {
        return $query->where('mime_type', 'like', $mimeType . '%');
    }

    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeDocuments($query)
    {
        return $query->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}